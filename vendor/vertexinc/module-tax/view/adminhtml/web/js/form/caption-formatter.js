/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(function () {
    'use strict';

    return {
        /**
         * Return formatted selected option value
         * @param {Object} selected
         * @returns {String}
         */
        getFormattedValue: function (selected) {
            var label = '';

            if (selected.parent) {
                label = selected.parent + ' - ';
            }
            label += selected.label;
            return label;
        }
    };
});
