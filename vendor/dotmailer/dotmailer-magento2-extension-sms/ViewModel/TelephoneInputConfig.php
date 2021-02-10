<?php

namespace Dotdigitalgroup\Sms\ViewModel;

use Dotdigitalgroup\Sms\Model\Config\TransactionalSms;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class TelephoneInputConfig implements ArgumentInterface
{
    /**
     * @var TransactionalSms
     */
    private $transactionalSmsConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * PhoneConfig constructor.
     * @param TransactionalSms $transactionalSmsConfig
     * @param RequestInterface $request
     * @param Escaper $escaper
     * @param SerializerInterface $serializer
     * @param AssetRepository $assetRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransactionalSms $transactionalSmsConfig,
        RequestInterface $request,
        Escaper $escaper,
        SerializerInterface $serializer,
        AssetRepository $assetRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->transactionalSmsConfig = $transactionalSmsConfig;
        $this->request = $request;
        $this->escaper = $escaper;
        $this->serializer = $serializer;
        $this->assetRepository = $assetRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool|string
     */
    public function getConfig()
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $config  = [
            "nationalMode" => false,
            "utilsScript"  => $this->getViewFileUrl('Dotdigitalgroup_Sms::js/utils.js'),
            "preferredCountries" => [$this->transactionalSmsConfig->getPreferredCountry($websiteId)]
        ];

        if ($this->transactionalSmsConfig->getAllowedCountries($websiteId)) {
            $config["onlyCountries"] = $this->escaper->escapeJs(
                explode(",", $this->transactionalSmsConfig->getAllowedCountries($websiteId))
            );
        }

        return $this->serializer->serialize($config);
    }

    /**
     * @param $fileId
     * @return string
     */
    private function getViewFileUrl($fileId)
    {
        return $this->assetRepository->getUrlWithParams(
            $fileId,
            ['_secure' => $this->request->isSecure()]
        );
    }
}
