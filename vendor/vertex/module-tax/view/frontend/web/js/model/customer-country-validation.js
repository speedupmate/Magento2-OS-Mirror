/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'jquery',
    'Vertex_Tax/js/form/depend-field-checker',
    'jquery/validate',
    'mage/translate'
], function ($, dependFieldChecker) {
    'use strict';

    /**
     * Validates if customer added a VAT number, then selecting a Country is required.
     */
    return function (config) {
        $.validator.addMethod(
            "vertex-customer-country",
            function (value, element, dependField) {
                return dependFieldChecker.validateValues(dependField, value);
            },
            $.mage.__("Please select a Country.")
        );
    }
});
