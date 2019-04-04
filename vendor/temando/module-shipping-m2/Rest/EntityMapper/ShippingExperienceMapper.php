<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\EntityMapper;

use Magento\Backend\Model\Session\Quote as BackendSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterface;
use Temando\Shipping\Api\Data\Order\ShippingExperienceInterfaceFactory;
use Temando\Shipping\Rest\Response\DataObject\OrderQualification;
use Temando\Shipping\Rest\Response\Fields\Generic\MonetaryValue;
use Temando\Shipping\Rest\Response\Fields\OrderQualification\Description;

/**
 * Map API data to application data object
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class ShippingExperienceMapper
{
    /**
     * @var SessionManagerInterface|BackendSession
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ShippingExperienceInterfaceFactory
     */
    private $shippingExperienceFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ShippingExperienceMapper constructor.
     * @param SessionManagerInterface $session
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     * @param ShippingExperienceInterfaceFactory $shippingExperienceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        SessionManagerInterface $session,
        StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        ShippingExperienceInterfaceFactory $shippingExperienceFactory,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->shippingExperienceFactory = $shippingExperienceFactory;
        $this->logger = $logger;
    }

    /**
     * Extract those shipping costs which match the current store's base currency.
     *
     * - For admin orders, the store scope cannot be accessed through store manager. Use backend session.
     * - For store front orders, the store scope must not be accessed through checkout session. Use store manager.
     *
     * @link https://wp.me/p7M4FY-g6
     * @link https://github.com/magento/magento2/pull/18678
     *
     * @param MonetaryValue[] $cost
     *
     * @return float
     * @throws LocalizedException
     */
    private function extractShippingCost(array $cost)
    {
        if ($this->session instanceof BackendSession) {
            $currentStore = $this->session->getStore();
        } else {
            $currentStore = $this->storeManager->getStore();
        }

        $baseCurrency = $currentStore->getBaseCurrencyCode();
        $warningTemplate = "%1 is not a valid shipping method currency. Use %2 when configuring rates.";

        $applicableCosts = array_filter($cost, function (MonetaryValue $item) use ($baseCurrency, $warningTemplate) {
            if ($item->getCurrency() !== $baseCurrency) {
                $message = __($warningTemplate, $item->getCurrency(), $baseCurrency);
                $this->logger->warning($message->render());

                return false;
            }

            return true;
        });

        if (empty($applicableCosts)) {
            throw new NotFoundException(__('No applicable shipping cost found.'));
        }

        // return first available cost
        $item = current($applicableCosts);
        return $item->getAmount();
    }

    /**
     * @param Description[] $descriptions
     * @return string
     */
    private function getLocalizedDescription(array $descriptions)
    {
        $descriptionFilter = function ($descriptions, $locale) {
            /** @var Description $description */
            foreach ($descriptions as $description) {
                if ($description->getLocale() === $locale) {
                    return $description;
                }
            }

            return null;
        };

        // try locale exact match first
        $locale = $this->localeResolver->getLocale();
        $fallbacks = [$locale, substr($locale, 0, 2), 'en'];

        do {
            $lang = array_shift($fallbacks);
            $localizedDescription = $descriptionFilter($descriptions, $lang);
        } while (!empty($fallbacks) && !$localizedDescription);

        return ($localizedDescription ? $localizedDescription->getText() : '');
    }

    /**
     * @param OrderQualification $apiQualification
     * @return ShippingExperienceInterface
     * @throws LocalizedException
     */
    public function map(OrderQualification $apiQualification)
    {
        $cost = $this->extractShippingCost($apiQualification->getAttributes()->getCost());
        $description = $this->getLocalizedDescription($apiQualification->getAttributes()->getDescription());
        $experienceId = current($apiQualification->getExperienceIds());

        $shippingExperience = $this->shippingExperienceFactory->create([
            ShippingExperienceInterface::LABEL => $description,
            ShippingExperienceInterface::CODE => $experienceId,
            ShippingExperienceInterface::COST => $cost,
        ]);

        return $shippingExperience;
    }
}
