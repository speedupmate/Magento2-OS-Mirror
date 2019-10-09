/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['uiComponent', 'ko', 'uiLayout'], function (Component, ko, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            elementName: '', // Prefix to use for input elements
            fieldType: '', // One of code, numeric, or date
            tableId: '',
            template: 'Vertex_Tax/flex-field-table',
            selectOptions: [
                {
                    label: 'No Data',
                    value: 'none'
                }
            ]
        },
        retrieveFields: [],

        /**
         * Initializes the table
         * @returns {FlexFieldTable} Chainable.
         */
        initialize: function () {
            this._super();

            this.retrieveFields = ko.observableArray();
            this.initializeFields();

            return this;
        },

        /**
         * Initialize the select components and link them to the form values
         */
        initializeFields: function () {
            var i, name, toLayOut = [];

            for (i in this.values) {
                if (this.values.hasOwnProperty(i)) {
                    name = this.fieldType + 'FlexField' + this.values[i]['field_id'];

                    toLayOut.push({
                        component: 'Vertex_Tax/js/form/flex-field-select',
                        template: 'ui/grid/filters/elements/ui-select',
                        parent: this.name,
                        name: name,
                        dataScope: '',
                        multiple: false,
                        selectType: 'optgroup',
                        selectedPlaceholders: {
                            defaultPlaceholder: 'No Data'
                        },
                        showOpenLevelsActionIcon: true,
                        presets: {
                            optgroup: {
                                showOpenLevelsActionIcon: true
                            }
                        },
                        filterOptions: true,
                        isDisplayMissingValuePlaceholder: true,
                        options: this.selectOptions,
                        value: this.values[i]['field_source']
                    });

                    this.retrieveFields.push({
                        fieldId: this.values[i]['field_id'],
                        childName: name
                    });
                }
            }

            layout(toLayOut);
        },

        /**
         * Retrieve the name for a Field ID input
         *
         * @param {String} fieldId
         * @returns {String}
         */
        getFieldIdInputName: function (fieldId) {
            return this.elementName + '[' + fieldId + '][field_id]';
        },

        /**
         * Retrieve the name for a Field Value input
         * @param {String} fieldId
         * @returns {String}
         */
        getFieldValueInputName: function (fieldId) {
            return this.elementName + '[' + fieldId + '][field_source]';
        },

        /**
         * Retrieve the name for the empty input
         * @returns {String}
         */
        getEmptyName: function () {
            return this.elementName + '[__empty]';
        }
    });
});
