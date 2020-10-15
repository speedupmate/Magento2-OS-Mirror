/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define(['underscore', 'uiClass'], function (_, Component) {
    'use strict';

    /**
     * @typedef {Object} vertexDifferenceRendererObject
     * @property {vertexDifferenceObject_Message} message
     */

    /**
     * @typedef {Object} vertexDifferenceObject_Message
     * @property {string} text - Informative message to end user
     * @property {vertexDifferenceObject_Difference[]} differences - Array of differences
     */

    /**
     * @typedef {Object} vertexDifferenceObject_Difference
     * @property {string} name - Human readable name of field that has a difference
     * @property {string} value - New value for the field
     */

    /**
     * @api
     */
    return Component.extend({
        /**
         * @var {string} template - Location of file to render
         */
        template: 'Vertex_AddressValidation/template/validation-result.html',

        /**
         * @var {*} renderer - Underscore.js template object
         */
        renderer: null,

        /**
         * @constructor
         * @param {string} template - File to render
         * @returns {*}
         */
        initialize: function (template) {
            if (typeof this.template !== 'undefined') {
                this.template = template;
            }

            require(['text!' + this.template], function (templateContents) {
                this.renderer = _.template(templateContents);
            }.bind(this));

            return this;
        },

        /**
         * @param {vertexDifferenceRendererObject} message
         * @returns {string} HTML
         */
        render: function (message) {
            return this.renderer(message);
        }
    });
});
