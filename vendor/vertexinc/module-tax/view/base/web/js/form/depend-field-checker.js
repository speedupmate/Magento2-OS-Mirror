/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * Makes sure a value is set if its depending field is also set.
         *
         * @param {Element} dependField
         * @param {Boolean} valueCheck
         * @returns {Boolean}
         */
        validateValues: function (dependField, valueCheck) {
            if ($(dependField).length) {
                let dependValue = $(dependField).val();

                return !(dependValue && !valueCheck);
            }

            return true;
        }
    };
});
