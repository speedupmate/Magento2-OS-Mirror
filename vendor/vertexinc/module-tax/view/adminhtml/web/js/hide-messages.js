/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

/**
 * Hide message block if response is empty
 *
 * @see AdminOrder.loadAreaResponseHandler
 */
require([
    'Magento_Sales/order/create/scripts'
], function () {
    'use strict';

    AdminOrder.prototype.loadAreaResponseHandler = AdminOrder.prototype.loadAreaResponseHandler.wrap(
        function (parentFunction, response) {
            parentFunction(response);
            if (response['message'] === '') {
                this.hideArea('message');
            } else {
                this.showArea('message');
            }
        }
    );
});
