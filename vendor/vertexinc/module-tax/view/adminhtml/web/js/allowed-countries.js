/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['jquery', 'jquery/ui'], function ($) {
    'use strict';

    $.widget('vertex.allowedCountries', {
        /**
         * Bind all optgroups under the attached element to mass-select/mass-deselect their children on click
         *
         * @private
         */
        _create: function () {
            $(this.element).on('click', 'optgroup', this.filterClick.bind(this));
        },

        /**
         * Filter out any clicks where the target was not explicitly the optgroup
         *
         * @param {Event} event
         * @return void
         */
        filterClick: function (event) {
            if (!$(event.target).is('optgroup')) {
                return;
            }

            this._processClick(event);
        },

        /**
         * Decide to select or unselect all child elements and execute the chosen task
         *
         * @private
         * @param {Event} event
         * @return void
         */
        _processClick: function (event) {
            var optgroup = $(event.target),
                select = optgroup.closest('select'),
                scrollTop = select.scrollTop();

            if (optgroup.children('option:not(:selected)').length === 0) {
                optgroup.children('option').prop('selected', false);
            } else {
                optgroup.children('option').prop('selected', true);
            }

            //  Maintain current scroll position
            // Default behavior, in chrome at least, is to jump to some other selected option
            setTimeout(function () {
                select.scrollTop(scrollTop);
            }, 0);
        }
    });

    return $.vertex.allowedCountries;
});
