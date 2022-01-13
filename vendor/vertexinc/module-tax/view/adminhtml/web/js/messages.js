/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([], function () {
    'use strict';

    /**
     * Add messages to the page
     *
     * For use as a Mage-Init on AJAX loaded content
     */
    return function (config) {
        const messageObject = config.messageObject,
            messageHtml = messageObject.messages,
            mainContainer = $('page:main-container'),
            parent = mainContainer.parentNode;
        let messages = $('messages');

        if (messages === null) {
            messages = document.createElement('div');
            messages.id = 'messages';
            parent.insertBefore(messages, mainContainer);
        }

        messages.innerHTML = messageHtml;
    }
});
