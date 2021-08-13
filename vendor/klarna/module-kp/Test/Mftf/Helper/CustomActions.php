<?php
namespace Klarna\Kp\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;
use Codeception\Module\WebDriver;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

class CustomActions extends Helper
{
    /**
     * Fills a field in the overlay for the target market only
     *
     * @param string $market
     * @param string $targetMarket
     * @param string $iframeSelector
     * @param string $fieldSelector
     * @param string $fieldValue
     */
    public function fillOverlayFieldMarketDependent(
        string $market,
        string $targetMarket,
        string $iframeSelector,
        string $fieldSelector,
        string $fieldValue
    ): void {
        if ($market === $targetMarket) {
            $webDriver = $this->getWebDriver();
            $webDriver->switchToIFrame($iframeSelector);
            $webDriver->waitForElementVisible($fieldSelector);
            $webDriver->fillField($fieldSelector, $fieldValue);
        }
    }

    /**
     * Getting back the magento web driver
     *
     * @return MagentoWebDriver
     */
    private function getWebDriver(): MagentoWebDriver
    {
        return $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
    }
}
