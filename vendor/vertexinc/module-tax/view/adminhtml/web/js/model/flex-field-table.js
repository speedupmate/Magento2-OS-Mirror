/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'uiComponent',
    'ko',
    'uiLayout',
    'Vertex_Tax/js/form/caption-formatter'
], function (Component, ko, layout, captionFormatter) {
    'use strict';

    return Component.extend({
        defaults: {
            elementName: '', // Prefix to use for input elements
            fieldType: '', // One of code, numeric, or date
            tableId: '',
            template: 'Vertex_Tax/flex-field-table',
            defaultPlaceholder: 'No Data',
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
            var i, name, fieldId, fieldSource, toLayOut = [];

            for (i in this.values) {
                if (this.values.hasOwnProperty(i)) {
                    fieldSource = this.values[i]['field_source'];
                    fieldId = this.values[i]['field_id'];
                    name = this.fieldType + 'FlexField' + fieldId;

                    this.retrieveFields.push({
                        fieldId: fieldId,
                        fieldSource: fieldSource,
                        fieldLabel: this.getFieldLabelFromSource(fieldSource),
                        editMode: ko.observable(false),
                        childName: name
                    });
                }
            }

            layout(toLayOut);
        },

        /**
         * Replace the label value with a dropdown
         * @param {Object} child
         */
        enableEditMode: function (child) {
            child.editMode(true);

            layout([{
                component: 'Vertex_Tax/js/form/flex-field-select',
                template: 'ui/grid/filters/elements/ui-select',
                parent: this.name,
                name: child.childName,
                dataScope: '',
                multiple: false,
                selectType: 'optgroup',
                selectedPlaceholders: {
                    defaultPlaceholder: this.defaultPlaceholder
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
                value: child.fieldSource
            }]);
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
         * Retrieve the label for the selected source
         * @param {String} source
         * @returns {String}
         */
        getFieldLabelFromSource: function (source) {
            var i, j, selected;

            for (i in this.selectOptions) {
                if (this.selectOptions[i].optgroup === undefined) {
                    continue;
                }
                for (j in this.selectOptions[i].optgroup) {
                    selected = this.selectOptions[i].optgroup[j];

                    if (selected.value === source) {
                        return captionFormatter.getFormattedValue(selected);
                    }
                }
            }
            return this.defaultPlaceholder;
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
