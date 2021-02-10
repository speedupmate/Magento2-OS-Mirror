define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'uiElement',
    'Magento_Ui/js/lib/validation/validator'
], function (_, utils, layout, Element, validator) {
    'use strict';

    var mixin = {
        /**
         *
         */
        validate: function () {
            if (this.elementTmpl === 'Dotdigitalgroup_Sms/form/element/telephone') {
                this.validationParams = {
                    uid: this.uid
                };
            }
            this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
