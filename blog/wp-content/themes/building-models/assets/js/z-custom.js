(function ($) {
    jQuery.each(
            jQuery('#sticky_menu').find('li.menu-item-has-children'),
            function (i, v) {
                jQuery(v).append('<i />');
            }
    );
    //Slick slider seting
    jQuery(".slick-slides").slick({
           adaptiveHeight : false,
           autoplay: true,
           infinite: true,
           speed: 500,
           fade: true,
           cssEase: 'linear',
           arrows:false
    });
    jQuery('#sticky_menu').find('li.menu-item-has-children i').bind('click', function () {
        jQuery(this).parent().find('.sub-menu').first().slideToggle('fast').parent().toggleClass('expanded');
    });
    jQuery('.menu-toggle, .sticky_menu_collapse').bind('click', function () {
        if (jQuery('#page').hasClass('shifted')) {
            jQuery('#page').removeClass('shifted');
        } else {
            jQuery('#page').addClass('shifted');
        }

    });

    jQuery(document).on('keyup', '#booking_form [required]', function(){
        jQuery(this).attr('oc-touched', '1');
    });
    
    jQuery('#booking_form [required]').first().focus(function(){        
        if (jQuery('#validate_nonce').val() != ''){
            return;
        }
        jQuery.post(one_ajax.ajaxurl, {
            action: 'oc_booking_nonce'
        }, function(response){
            jQuery('#validate_nonce').val(response.nonce);            
        }); 
    });
    function oc_validate_interactions(){
        var requiredFieldCount = jQuery('#booking_form').find('[required]').length;
        var touchedFieldCount = jQuery('#booking_form').find('[oc-touched]').length;
        return requiredFieldCount == touchedFieldCount;
    }
    function oc_reset_security(response){
        jQuery('#oc_cap_img').attr('src', response.image);
        jQuery('#oc_cpt').val(response.token);
        jQuery('#validate_nonce').val('');
        jQuery('[oc-touched]').removeAttr('oc-touched');
    }

    jQuery('#booking_form').bind('submit', function (event) {
        console.log('Please wait...');
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false;
        }
        var msg = one_ajax.msg;
        jQuery('.form_message').slideUp().text('');
        jQuery('#booking_form').find('input[type="submit"]').attr('disabled', 'disabled').val(msg);

        var ajax_url = one_ajax.ajaxurl;
        var btn_title = one_ajax.send;

        var email = jQuery('#booking_form').find('.booking_email').val();
        var name = jQuery('#booking_form').find('.booking_name').val();
        var service = jQuery('#booking_form').find('#booking_service option:selected').val();
        var message = jQuery('#booking_form').find('.booking_msg').val();
        var subject = jQuery('#booking_form').find('#contact_subject').val();
        var recipient = jQuery('#booking_form').find('#contact_recipient').val();
        var label_1 = jQuery('#booking_form').find('#label_1').val();
        var label_2 = jQuery('#booking_form').find('#label_2').val();
        var label_3 = jQuery('#booking_form').find('#label_3').val();
        var label_5 = jQuery('#booking_form').find('#label_5').val();
        var validate_nonce = jQuery('#booking_form').find('#validate_nonce').val();
        var oc_cpt = jQuery('#booking_form').find('#oc_cpt').val();
        var oc_captcha_val = jQuery('#booking_form').find('#oc-captcha-val').val();
        var oc_csrf_token = jQuery('#booking_form').find('#oc_csrf_token').val();

        /* Data to send */
        var data = {
            action: 'send_contact_form',
            name: name,
            email: email,
            service: service,
            message: message,
            subject: subject,
            recipient: recipient,
            label_1: label_1,
            label_2: label_2,
            label_3: label_3,
            label_5: label_5,
            validate_nonce: validate_nonce,
            oc_csrf_token: oc_csrf_token
        };
        if (oc_validate_interactions()){
            data.oc_cpt = oc_cpt;
            data.oc_captcha_val = oc_captcha_val;
        }
    
        jQuery.post(ajax_url, data, function (response) {


            if (response.type == 'error') {
                jQuery('#booking_form').find('input[type="submit"]').removeAttr('disabled').val(btn_title);
                jQuery('.form_message').html(response.text).slideDown();
                console.log(response.text);
                //console.log(response.body);
            } else if (response.type == 'success') {
                jQuery('#booking_form').find('input[type="submit"]').removeAttr('disabled').val(btn_title);
                jQuery('#booking_form').trigger('reset');
                jQuery('.form_message').html(response.text).slideDown();
                oc_reset_security(response);
                console.log(response.text);
            } else {
                jQuery('#booking_form').find('input[type="submit"]').removeAttr('disabled').val(btn_title);
                jQuery('.form_message').html(response.text).slideDown();
                console.log(response.text);
            }

        }, "json");

    });

})(jQuery);