/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['underscore', 'Magento_Ui/js/form/element/select'], function (_, Select) {
    'use strict';

    return Select.extend({
        /**
         * Overwrites the parent's filter to allow for checking if a value is
         * in an array and for allowing the value of "unmapped" through all
         * filters
         *
         * @param {String} value
         * @param {String} field
         */
        filter: function (value, field) {
            var source = this.initialOptions,
                result;

            field = field || this.filterBy.field;

            result = _.filter(source, function (item) {
                return Array.isArray(item[field]) && item[field].includes(value) ||
                    item[field] === value ||
                    item.value === '' ||
                    item.value === 'unmapped';
            });

            this.setOptions(result);
        }
    });
});
