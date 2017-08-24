/*
Copyright (C)  2017, 1LAW Legal Technologies, LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
;(function(window,$,undefined){

    var threadId = undefined;
    var userId = undefined;
    var variables = undefined;
    var docTree = undefined;
    var doc = undefined;
    var nonce = docuajax_object.initial_nonce;
    var firstMessage = true;
    var queryParams = window.location.search.substring(1).split("&").map(function(e) {
         return e.split("=");
      }).reduce(function(val, e) {
           val[e[0]] = decodeURIComponent(e[1]);
           return val;
       }, {});
    $(function() {
        // Docubot Animation Stuff
        setupDocubotAnimation();
        $(".sprite-Docubot").on("click", function(e) {
            e.preventDefault();
            animateDocubot(function() {

                setupDocubotAnimation();

            });
            return false;
        });
        // End Docubot Animation Stuff
        if (queryParams['doctype']) {
            firstMessage = false;
            createDocType();
        } else if (queryParams['docbuilderfile']) {

        }
        var shouldPrintMessage = true;
        $(".docubot_message_form").on("submit", function(e){
            setLoading(true);
            e.preventDefault();
            $.ajax({
                url: docuajax_object.ajax_url,
                method: "POST",
                data: {
                    "action": "docubot_send_message",
                    "thread": threadId,
                    "sender": userId,
                    "variables": variables ? JSON.stringify(variables) : undefined,
                    "docTree": docTree ? JSON.stringify(docTree) : undefined,
                    "document": doc ? JSON.stringify(doc) : undefined,
                    "message": $(".docubot_message").val(),
                    "security": nonce
                },
                dataType: "json",
                success: sendMessageSuccess,
                error: error
            });
            if (firstMessage) {
                stopDocubotAnimation();
                $(".docubot_container").addClass("docubot_conversation_started");
                if (shouldPrintMessage) {
                  $(".docubot_message_display").append("<li><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugin_url+"assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+$(".docubot_message").val()+"</div></li>");
                }
                shouldPrintMessage = true;
                $(".docubot_message_display").trigger('new_message');
                $(".docubot_container").trigger('docubot_animation');
                $(".docubot_message").val("");
                firstMessage = false;
                $(".docubot_getstarted_text").animate({height: 0}, 500);
                $(".sprite-Docubot").animate({height: 0}, 500);
                return;
            }
            if (shouldPrintMessage) {
              printMessageFromUser($(".docubot_message").val());
            }
            shouldPrintMessage = true;
            $(".docubot_message").val("");

        });
        $(".docubot_document_button").on("click", function() {
          shouldPrintMessage = false;
          $(".docubot_message").val($(this).data("value"));
          $(".docubot_message_form").trigger("submit");
        });
    });
    function createDocType() {
        setLoading(true);
        $.ajax({
            url: docuajax_object.ajax_url,
            method: "POST",
            data: {
                "action": "docubot_send_message",
                "thread": threadId,
                "sender": userId,
                "variables": variables ? JSON.stringify(variables) : undefined,
                "docTree": docTree ? JSON.stringify(docTree) : undefined,
                "document": doc ? JSON.stringify(doc) : undefined,
                "message": queryParams['doctype'],
                "security": nonce
            },
            dataType: "json",
            success: sendMessageSuccess,
            error: error,
        });
    }
    function sendMessageSuccess(response) {

        setLoading(false);
        if (response.error == undefined) {
            threadId = response.meta.threadId;
            userId = response.meta.userId;
            variables = response.data.variables;
            docTree = response.data.docTree;
            doc = response.data.document;
            nonce = response.meta.nonce;
            for (i = 0; i < response.data.messages.length; i++) {
                printMessageFromDocubot(response.data.messages[i]);
            }
            return;
        }
        console.log(response);

    }
    function error(response) {
        setLoading(false);
        console.log(response);
    }
    function printMessageFromUser(message) {
        $(".docubot_message_display").append("<li class=\"docubot_from_user\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugin_url+"assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+message+"</div></li>");
        $(".docubot_message_display").trigger('new_message');
    }
    function printMessageFromDocubot(message) {
        if (message === "") {
            return;
        }
        $(".docubot_message_display").append("<li class=\"docubot_from_docubot\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugin_url+"assets/img/docubot-chat-profile.svg);\"/></div><div class=\"docubot_from_message\">"+message.replace(/\n/g, "<br />")+"</div></li>");
        $(".docubot_message_display").trigger('new_message');
    }
    function setLoading(loading) {
        if (loading) {
            $(".docubot_loading").removeClass("docubot_hidden");
        } else {
            $(".docubot_loading").addClass("docubot_hidden");
        }
    }

    var docubotAnimationInterval;
    var docubotAnimationTimeout;
    function animateDocubot(cb) {

        if (docubotAnimationInterval) { return; }
        var elm = $(".sprite-Docubot");
        if (!elm.length) { return; }
        var frame = 0;
        docubotAnimationInterval = window.setInterval(function() {

            if (frame === 0) {

                elm.removeClass("sprite-Docubot_Dance_173");

            } else {

                elm.removeClass("sprite-Docubot_Dance_" + (frame - 1));

            }
            elm.addClass("sprite-Docubot_Dance_" + frame);
            frame = frame + 1;
            if (frame > 173) { // there are 174 frames in the animation

                stopDocubotAnimation();
                if (cb) { cb(); }

            }

        }, 33);

    }
    function setupDocubotAnimation() {

        if (docubotAnimationInterval || docubotAnimationTimeout) { return; }
        docubotAnimationTimeout = window.setTimeout(function() {

            docubotAnimationTimeout = null;
            animateDocubot(function() {

                setupDocubotAnimation();

            });

        }, Math.floor(Math.random() * 7000) + 3000 );

    }
    function stopDocubotAnimation() {

        if (docubotAnimationInterval) {

            window.clearInterval(docubotAnimationInterval);
            docubotAnimationInterval = null;

        }
        if (docubotAnimationTimeout) {

            window.clearTimeout(docubotAnimationTimeout);
            docubotAnimationTimeout = null;

        }

    }

})(window,jQuery);
