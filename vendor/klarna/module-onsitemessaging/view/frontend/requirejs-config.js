/**
 * This file is part of the Klarna Onsitemessaging module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
var config = {
    config: {
        mixins: {
            "Magento_Catalog/js/price-box": {
                "Klarna_Onsitemessaging/js/pricebox-widget-mixin": true
            }
        }
    }
};
