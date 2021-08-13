/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/ui-select',
    'Vertex_Tax/js/form/caption-formatter'
], function (_, Component, captionFormatter) {
    'use strict';

    return Component.extend({
        defaults: {
            presets: {
                optgroup: {
                    openLevelsAction: true,
                    showOpenLevelsActionIcon: true
                }
            }
        },

        /**
         * Set Caption
         */
        setCaption: function () {
            var length, label;

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }

            if (length && this.getSelected().length) {
                label = captionFormatter.getFormattedValue(this.getSelected()[0]);
                this.placeholder(label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        }
    });
});
