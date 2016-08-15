;(function(window,$,undefined){

    var threadId = undefined;
    var userId = undefined;
    var firstMessage = true;
    $(function() {
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
                $(".docubot_container").addClass("docubot_conversation_started");
                $(".docubot_message_display").append("<li ><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+$(".docubot_message").val()+"</div></li>");
                $(".docubot_container").trigger('docubot_animation');
                $(".docubot_message_display").trigger('new_message');
                $(".docubot_message").val("");
                firstMessage = false;
                return;
            }
            $(".docubot_message_display").append("<li class=\"docubot_from_user\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/anonymous-user.svg);\"/></div><div class=\"docubot_from_message\">"+$(".docubot_message").val()+"</div></li>");
            $(".docubot_message_display").trigger('new_message');
            $(".docubot_message").val("");

        });
    });
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
    function printMessageFromDocubot(message) {
        if (message === "") {
            return;
        }
        $(".docubot_message_display").append("<li class=\"docubot_from_docubot\"><div class=\"docubot_from_img_container\"><div class=\"docubot_from_img\" style=\"background-image: url( "+docuajax_object.plugins_url+"/docubot_wp_plugin/assets/img/docubot-chat-profile.svg);\"/></div><div class=\"docubot_from_message\">"+message.replace(/\n/g, "<br />")+"</div></li>");
        $(".docubot_message_display").trigger('new_message');

    }

})(window,jQuery);
