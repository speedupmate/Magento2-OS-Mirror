require([
    'jquery',
    'domReady!',
    'smsCounter'

], function ($) {
    'use strict';

    let selectors = [];
    let unicodeMessageSelector = '#ddg-unicode';

    $(document).ready(function () {
        $('.ddg-note').each(function (i, obj) {
            selectors.push('#' + obj.firstElementChild.id.replace('_counter', ''));
        });

        searchForUnicode();

        selectors.forEach(function (entry) {
            let counterSelector = entry + '_counter';
            let commentSelector = entry + '_comment';
            let totalSelector = entry + '_total';

            if ($(entry).val() !== undefined) {
                updateNote(counterSelector, commentSelector, totalSelector, $(entry).val());
            }
        });
    });

    $(document).on('keyup', selectors.join(', '), delay(function () {
        let counterSelector = '#' + this.id + '_counter';
        let commentSelector = '#' + this.id + '_comment';
        let totalSelector = '#' + this.id + '_total';

        if (this.value !== undefined) {
            updateNote(counterSelector, commentSelector, totalSelector, this.value);
            updateUnicode(this.value);
        }
    }, 500));

    /**
     *
     * @param {String} counterSelector
     * @param {String} commentSelector
     * @param {String} totalSelector
     * @param {String} smsText
     */
    function updateNote(counterSelector, commentSelector, totalSelector, smsText) {
        let smsParsed = SmsCounter.count(smsText);

        $(counterSelector).text(smsParsed.length);
        $(totalSelector).text(smsParsed.messages);

        if (smsText.match(/{{([^}]+)\}}/si)) {
            $(commentSelector).show();
        } else {
            $(commentSelector).hide();
        }
    }

    /**
     * @param {String} smsText
     */
    function updateUnicode(smsText) {
        let smsParsed = SmsCounter.count(smsText);

        if (smsParsed.encoding === 'UTF16') {
            $(unicodeMessageSelector).show();
        } else {
            searchForUnicode();
        }
    }

    /**
     *
     */
    function searchForUnicode() {
        let unicodeFound = false;

        selectors.forEach(function (entry) {
            if ($(entry).val() !== undefined) {
                let smsParsed = SmsCounter.count($(entry).val());

                if (smsParsed.encoding === 'UTF16') {
                    unicodeFound = true;
                    $(unicodeMessageSelector).show();
                }
            }

            if (!unicodeFound) {
                $(unicodeMessageSelector).hide();
            }
        });
    }

    /**
     * @param {Object} callback
     * @param {Integer} ms
     */
    function delay(callback, ms) {
        let timer = 0;

        return function () {
            let context = this,
                args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }
});

