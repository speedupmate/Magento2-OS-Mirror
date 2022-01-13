/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(function () {
    'use strict';

    return {
        /**
         * Load an entry from the registry
         */
        load: function (name, req, onload, config) {
            req(['uiRegistry'], function (registry) {
                let count = 0;
                const timer = setInterval(function () {
                    if (typeof (registry.get(name)) !== 'undefined') {
                        onload(registry.get(name));
                        clearInterval(timer);
                    }
                    count++;

                    if (count >= 10) {
                        clearInterval(timer);
                        onload.error(new Error(name + 'never loaded into the registry'));
                    }
                }, 500);
            });
        }
    }
});
