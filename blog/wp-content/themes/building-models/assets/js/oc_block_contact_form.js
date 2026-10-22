(function ($) {
    let submitButtonText = "Submit";
    $(document).ready(function () {
        $('.wp-block-onecom-contact-form').submit(function (e) {
            e.preventDefault();
            ocSubmitForm($(this))
        })
        ocPopulateFormFields();
    });

    function ocSubmitForm(obj) {
        var formData = [];
        // var form = $(obj).parents('form');
        var form = obj;
        var formInputs = $(form).find('input');
        var textareas = $(form).find('textarea');
        showLoader(form);
        //process inputs
        $.each(formInputs, function (index, value) {
            if (!$(value).hasClass('oc-ignore')) {
                var label = $(value).attr('name');
                var val = $(value).val();
                formData.push({
                    label: label,
                    val: val
                });
            }

        });

        //process textarea
        $.each(textareas, function (index, value) {
            if (!$(value).hasClass('oc-ignore')) {
                var label = $(value).attr('name');
                var val = $(value).val();
                formData.push({
                    label: label,
                    val: val
                });
            }
        });
        var cptField = $(form).find('input[name="oc_cpt"]'),
            csrfField = $(form).find('.oc_csrf_token'),
            nonceField = $(form).find('input[name="validate_nonce"]'),
            captchaField = $(form).find('.oc-captcha-val');
        $.post(ocAjaxData.ajaxUrl, {
            action: 'oc_form_submit',
            recipient: $(form).find('.oc-recipient-email').val(),
            subject: $(form).find('.oc-recipient-subject').val(),
            oc_cpt: cptField.val(),
            oc_csrf_token: csrfField.val(),
            validate_nonce: nonceField.val(),
            oc_captcha_val: captchaField.val(),
            formData: formData
        }, function (response) {
            if (response.status === 'success') {
                cptField.val(response.token);
                $('#oc_cap_img').attr('src', response.image);
                $(form).find('.oc-message').text(response.text).slideDown();
                $(form).trigger('reset');
            } else {
                $('.oc-message').text(response.text).slideDown();
            }
            hideLoader(form);
        });

        if (!ocValidateForm(formData)) {
            return;
        }
    }

    function ocValidateForm(formObj) {
        return true;
    }
    function showLoader(form){
        submitButtonText = $(form).find(".oc-form-button").text();
        $(form).find(".oc-form-button").text(ocAjaxData.waitText);
    }
    function hideLoader(form){
        $(form).find(".oc-form-button").text(submitButtonText);
    }
    function ocPopulateFormFields() {
        if (ocAjaxData.isAdmin == 1) {
            return;
        }
        $.post(ocAjaxData.ajaxUrl, {
            action: 'oc_get_fields'
        }, function (response) {
            $('.oc-form-button').before('<div>' + response.data + '</div>');
        });
    }
})(jQuery)
