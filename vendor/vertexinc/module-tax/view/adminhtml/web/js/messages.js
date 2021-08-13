/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

define([], function() {
    return function(config) {
        var messageObject = config.messageObject;
        var messageHtml = messageObject.messages;
        var mainContainer = $('page:main-container');
        var parent = mainContainer.parentNode;
        var messages = $('messages');

        if (messages === null) {
            messages = document.createElement('div');
            messages.id = 'messages';
            parent.insertBefore(messages, mainContainer);
        }

        messages.innerHTML = messageHtml;
    }
});
