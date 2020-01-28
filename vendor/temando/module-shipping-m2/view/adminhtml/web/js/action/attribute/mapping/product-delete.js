/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (id, url, token) {
        return $.ajax({
            url: url + '/' + id,
            type: 'POST',
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            beforeSend: function () {}
        })
        .done(function (resp) {
            let respData = JSON.parse(resp);
            if (respData.status === 'OK') {
                $('body').notification('clear')
                    .notification('add', {
                        error: false,
                        message: $.mage.__(
                            respData.message
                        ),
                        insertMethod: function (message) {
                            message = $(message).children('div.message').addClass('message-success');
                            let $wrapper = $('<div/>').html(message);
                            $('.page-main-actions').after($wrapper);
                        }
                    });
            } else {
                $('body').notification('clear')
                    .notification('add', {
                        error: true,
                        message: $.mage.__(
                            respData.message
                        ),
                        insertMethod: function (message) {
                            let $wrapper = $('<div/>').html(message);
                            $('.page-main-actions').after($wrapper);
                        }
                    });
            }

            return resp;
        })
        .fail(function (resp) {
            let respData = JSON.parse(resp);

            $('body').notification('clear')
                .notification('add', {
                    error: true,
                    message: $.mage.__(
                        respData.message
                    ),
                    insertMethod: function (message) {
                        let $wrapper = $('<div/>').html(message);
                        $('.page-main-actions').after($wrapper);
                    }
                });
        });
    };
});
