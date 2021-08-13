<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Model\Support;

use Klarna\Core\Mail\Template\TransportBuilder;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email
{
    const TEMPLATE_NAME = 'klarna_core_support_email_template';

    const KLARNA_SUPPORT_MAIL = 'magento@klarna.com';

    const DEBUG_CONTACT_NAME_PATTERN = '_test_';

    const NOT_SENDING_MAIL_NAME_PATTERN = '_not_sending_mail_';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var DriverInterface
     */
    private $driverInterface;
    /**
     * @var InfoExtractor
     */
    private $infoExtractor;

    /**
     * @param TransportBuilder         $transportBuilder
     * @param StoreManagerInterface    $storeManager
     * @param ModuleListInterface      $moduleList
     * @param ProductMetadataInterface $productMetadata
     * @param DriverInterface          $driverInterface
     * @param InfoExtractor            $infoExtractor
     * @codeCoverageIgnore
     */
    public function __construct(
        TransportBuilder         $transportBuilder,
        StoreManagerInterface    $storeManager,
        ModuleListInterface      $moduleList,
        ProductMetadataInterface $productMetadata,
        DriverInterface          $driverInterface,
        InfoExtractor            $infoExtractor
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager     = $storeManager;
        $this->moduleList       = $moduleList;
        $this->productMetadata  = $productMetadata;
        $this->driverInterface  = $driverInterface;
        $this->infoExtractor    = $infoExtractor;
    }

    /**
     * Getting the email content
     *
     * @param array $data
     * @return array
     */
    public function getTemplateContent(array $data): array
    {
        $data = $this->stripCode($data);

        $data['module_versions'] = $this->getModuleVersions();
        $data['php_version'] = phpversion();
        $data['products'] = implode("<br/>", $data['data']);
        $data['shop_version'] = $this->productMetadata->getVersion() . ' ' . $this->productMetadata->getEdition();

        return $data;
    }

    /**
     * Stripping code from the input
     *
     * @param array $data
     * @return array
     */
    private function stripCode(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->stripCode($value);
                continue;
            }

            $data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }

        return $data;
    }

    /**
     * Sending the email
     *
     * @param array $content
     * @throws LocalizedException
     */
    public function send(array $content): void
    {
        $sender = [
            'name'  => $content['contact_name'],
            'email' => $content['contact_email']
        ];
        $addTo = self::KLARNA_SUPPORT_MAIL;

        if (strpos($content['contact_name'], self::DEBUG_CONTACT_NAME_PATTERN) !== false) {
            $addTo = $content['contact_email'];
        }

        $this->transportBuilder->setTemplateIdentifier(self::TEMPLATE_NAME);
        $this->transportBuilder->setFromByScope($sender);
        $this->transportBuilder->addTo($addTo, 'Klarna support');
        $this->transportBuilder->setTemplateVars($content);
        $this->transportBuilder->setTemplateOptions(
            [
                'area' => 'adminhtml',
                'store' => $this->storeManager->getStore()->getId()
            ]
        );

        if (isset($content['attachment'])) {
            foreach ($content['attachment'] as $attachment) {
                $this->transportBuilder->addAttachment(
                    $this->driverInterface->fileGetContents(
                        $attachment['path'] . '/' . $attachment['file']
                    ),
                    $attachment['name']
                );
            }
        }

        if ($this->isSelected($content, 'include_klarna_settings')) {
            foreach ($this->infoExtractor->getKlarnaInfo() as $key => $array) {
                $this->transportBuilder->addAttachment(
                    $this->jsonEncode($array),
                    $key . '.json'
                );
            }
        }

        if ($this->isSelected($content, 'include_tax_settings')) {
            foreach ($this->infoExtractor->getTaxInfo() as $key => $array) {
                $this->transportBuilder->addAttachment(
                    $this->jsonEncode($array),
                    $key . '.json'
                );
            }
        }

        if (strpos($content['contact_name'], self::NOT_SENDING_MAIL_NAME_PATTERN) === false) {
            $this->transportBuilder->getTransport()
                ->sendMessage();
        }
    }

    /**
     * Checks if the field with given key is selected
     *
     * @param array $array
     * @param string $key
     * @return bool
     */
    private function isSelected(array $array, string $key): bool
    {
        return isset($array[$key]) && $array[$key] === "1";
    }

    /**
     * Json-encodes an array to be printed to a file
     *
     * @param array $array
     * @return string
     */
    private function jsonEncode(array $array): string
    {
        return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Getting the module versions
     *
     * @return string
     */
    private function getModuleVersions(): string
    {
        $allModules = $this->moduleList->getAll();
        $klarnaModuleNames = array_filter($allModules, function ($key) {
            return strpos($key, 'Klarna_') === 0;
        }, ARRAY_FILTER_USE_KEY);

        $klarnaVersions = '';
        ksort($klarnaModuleNames);
        foreach ($klarnaModuleNames as $name => $content) {
            $klarnaVersions .= $name . ': ' . $content['setup_version'] . "<br/>";
        }

        return $klarnaVersions;
    }
}
