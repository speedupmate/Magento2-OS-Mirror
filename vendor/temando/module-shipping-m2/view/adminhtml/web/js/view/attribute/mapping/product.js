/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Temando_Shipping/js/action/attribute/mapping/product-delete'
    ],
    function ($, ko, Component, productMappingDelete) {
        "use strict";

        var listShippingAttributes = ko.observableArray([]);
        var listProductAttributes = ko.observableArray([]);
        var newMapping = 0;

        return Component.extend({
            initialize: function () {
                this._super();
                $('#product_attribute_mapping_form').loader('show');
                $('#save-mapping').bind('click', this.submitForm);

                let token = this.token;
                let urlProductAttributes = this.getProductAttributesUrl;

                jQuery.ajax({
                    url: urlProductAttributes,
                    type: 'GET',
                    async: false,
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    complete: function (data) {
                        listProductAttributes(JSON.parse(data.responseText));
                    }
                });


                let urlShippingAttributes = this.getShippingAttributesUrl;

                jQuery.ajax({
                    url: urlShippingAttributes,
                    type: 'GET',
                    async: false,
                    headers: {
                        "Authorization": "Bearer " + token
                    },
                    complete: function (data) {
                        let respData = JSON.parse(data.responseText);

                        respData.forEach(function (item, index) {
                            item.attributes = listProductAttributes;
                            respData[index] = item;
                        });

                        listShippingAttributes(respData);
                    }
                });
            },

            getListShippingAttributes: function () {
                return listShippingAttributes;
            },

            getListProductAttributes: function () {
                return listProductAttributes;
            },

            newMapping: function () {
                listShippingAttributes.push(
                    {
                        id: 'NEW_' + newMapping++,
                        description: '',
                        is_default: 0,
                        attributes: listProductAttributes
                    }
                );
            },

            isNewMapping: function (id) {
                return /^NEW_[0-9]*/.test(id);
            },

            submitForm: function () {
                let dataForm = $('#product_attribute_mapping_form');
                if (!dataForm.validation() && dataForm.validation('isValid')) {
                    return false;
                }

                dataForm.submit();
            },

            removeMapping: function (data) {
                listShippingAttributes.remove(function (item) {
                    return item.id === data.id;
                });
            },

            canDelete: function (id, is_default) {
                if (parseInt(is_default) || this.isNewMapping(id)) {
                    return false;
                }

                return true;
            },

            getDeleteUrl: function () {
                return this.deleteUrl;
            },

            deleteMapping: function (id, url, token) {
                $('body').trigger('processStart');

                productMappingDelete(id, url, token)
                    .done(function (resp) {
                        let respData = JSON.parse(resp);
                        if (respData.status === 'OK') {
                            listShippingAttributes.remove(function (item) {
                                return item.id === id;
                            });
                        }

                        $('body').trigger('processStop');
                    });
            }
        });
    }
);