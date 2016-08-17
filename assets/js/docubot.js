;(function(window,$,undefined){

    var threadId = undefined;
    var userId = undefined;
    var firstMessage = true;
    var queryParamas = window.location.search.substring(1).split("&").map(function(e) {
         return e.split("=");
      }).reduce(function(val, e) {
           val[e[0]] = decodeURIComponent(e[1]);
           return val;
       }, {});
    $(function() {
        setupDocubotAnimation();
        if (queryParamas['doctype']) {
            firstMessage = false;
            createDocType();
        }
        $(".docubot_message_form").on("submit", function(e){
            e.preventDefault();
            $.ajax({
                url: docuajax_object.ajax_url,
                method: "POST",
                data: {
                    "action": "docubot_send_message",
                    "thread": threadId,
                    "sender": userId,
                    "message": $(".docubot_message").val()
                },
                dataType: "json",
                success: sendMessageSuccess,
                error: error
            });
            if (firstMessage) {
                stopDocubotAnimation();
                $(".docubot_container").addClass("docubot_conversation_started");
                $(".docubot_message_display").append("<li><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+$(".docubot_message").val()+"</div></li>");
                $(".docubot_message_display").trigger('new_message');
                $(".docubot_container").trigger('docubot_animation');
                $(".docubot_message").val("");
                firstMessage = false;
                $(".docubot_getstarted_text").animate({height: 0}, 500);
                $(".sprite-Docubot").animate({height: 0}, 500);
                return;
            }
            printMessageFromUser($(".docubot_message").val());
            $(".docubot_message").val("");

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
                "message": queryParamas['doctype']
            },
            dataType: "json",
            success: function(response) {
                setLoading(false);
                sendMessageSuccess(response);
            },
            error: function(response) {
                setLoading(false);
                error(response);
            },
        });
    }
    function sendMessageSuccess(response) {

        if (response.error == undefined) {
            threadId = response.meta.threadId;
            userId = response.meta.userId;
            for (i = 0; i < response.data.messages.length; i++) {
                printMessageFromDocubot(response.data.messages[i]);
            }
            return;
        }
        console.log(response);

    }
    function error(response) {
        console.log(response);
    }
    function printMessageFromUser(message) {
        $(".docubot_message_display").append("<li class=\"docubot_from_user\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+message+"</div></li>");
        $(".docubot_message_display").trigger('new_message');
    }
    function printMessageFromDocubot(message) {
        if (message === "") {
            return;
        }
        $(".docubot_message_display").append("<li class=\"docubot_from_docubot\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/docubot-chat-profile.svg);\"/></div><div class=\"docubot_from_message\">"+message.replace(/\n/g, "<br />")+"</div></li>");
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
    function setupDocubotAnimation() {

        if (docubotAnimationInterval) { return; }
        var elm = $(".sprite-Docubot");
        if (!elm.length) { return; }
        var frame = 0;
        docubotAnimationTimeout = window.setTimeout(function() {

            docubotAnimationTimeout = null;
            docubotAnimationInterval = window.setInterval(function() {

                console.log("here");
                if (frame === 0) {

                    elm.removeClass("sprite-Docubot_Dance_173");

                } else {

                    elm.removeClass("sprite-Docubot_Dance_" + (frame - 1));

                }
                elm.addClass("sprite-Docubot_Dance_" + frame);
                frame = frame + 1;
                if (frame > 173) { // there are 174 frames in the animation

                    frame = 0;

                }

            }, 33);

        }, 3000);

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
