<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Model\ResourceModel\Experience;

use Psr\Log\LoggerInterface;
use Temando\Shipping\Model\ResourceModel\Repository\ExperienceRepositoryInterface;
use Temando\Shipping\Rest\Adapter\ExperienceApiInterface;
use Temando\Shipping\Rest\EntityMapper\ExperienceResponseMapper;
use Temando\Shipping\Rest\Exception\AdapterException;
use Temando\Shipping\Rest\Request\ListRequestInterfaceFactory;
use Temando\Shipping\Rest\Response\Type\ExperienceResponseType;

/**
 * Temando Experience Repository
 *
 * @package  Temando\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.temando.com/
 */
class ExperienceRepository implements ExperienceRepositoryInterface
{
    /**
     * @var ExperienceApiInterface
     */
    private $apiAdapter;

    /**
     * @var ListRequestInterfaceFactory
     */
    private $listRequestFactory;

    /**
     * @var ExperienceResponseMapper
     */
    private $experienceMapper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExperienceRepository constructor.
     * @param ExperienceApiInterface $apiAdapter
     * @param ListRequestInterfaceFactory $listRequestFactory
     * @param ExperienceResponseMapper $experienceMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExperienceApiInterface $apiAdapter,
        ListRequestInterfaceFactory $listRequestFactory,
        ExperienceResponseMapper $experienceMapper,
        LoggerInterface $logger
    ) {
        $this->apiAdapter = $apiAdapter;
        $this->listRequestFactory = $listRequestFactory;
        $this->experienceMapper = $experienceMapper;
        $this->logger = $logger;
    }

    /**
     * @return \Temando\Shipping\Model\ExperienceInterface[]
     */
    public function getExperiences()
    {
        try {
            $request = $this->listRequestFactory->create();

            $apiExperiences = $this->apiAdapter->getExperiences($request);
            $experiences = array_map(function (ExperienceResponseType $apiExperience) {
                return $this->experienceMapper->map($apiExperience);
            }, $apiExperiences);
        } catch (AdapterException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $experiences = [];
        }

        return $experiences;
    }
}
