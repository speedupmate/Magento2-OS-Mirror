/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'Vertex_AddressValidation/js/view/billing-validation-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Vertex_AddressValidation/js/view/shipping-validation-mixin': true
            }
        }
    }
};
