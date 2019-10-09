/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['underscore', 'Magento_Ui/js/form/element/ui-select'], function (_, Component) {
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
            var length,
                label = '',
                selected;

            if (!_.isArray(this.value()) && this.value()) {
                length = 1;
            } else if (this.value()) {
                length = this.value().length;
            } else {
                this.value([]);
                length = 0;
            }

            if (length && this.getSelected().length) {
                selected = this.getSelected()[0];

                if (selected.parent) {
                    label = selected.parent + ' - ';
                }
                label += selected.label;
                this.placeholder(label);
            } else {
                this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
            }

            return this.placeholder();
        }
    });
});
