(function ($, window, undefined) {
    $(function(){
        $('.docubot_header').click(function() {
            $('.docubot_popup').toggleClass('docubot_show');
            $('.slide_off_sprite_docubot').toggleClass('docubot_hide');
        });
        $('.slide_off_sprite_docubot').click(function() {
            $('.slide_off_sprite_docubot').toggleClass('docubot_hide');
            $('.docubot_popup').toggleClass('docubot_show');
        });
    });
}) (jQuery, window);
