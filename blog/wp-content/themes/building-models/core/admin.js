(function ($) {
    $(document).ready(function ($) {

        if ($('.colorpicker').length > 0) {
            $('.colorpicker').wpColorPicker();
        }

        if ($('.one-social-default-checkbox').length > 0) {
            $('.one-social-default-checkbox').each(function (event, checkbox) {
                var checked = ($(this).is(':checked')) ? true : false;
                if (checked) {
                    $(this).parents('table:first').find('.toggle-tr').hide();
                } else {
                    $(this).parents('table:first').find('.toggle-tr').show();
                }
            });

            $(document).on('click', '.one-social-default-checkbox', function (event) {
                var checked = ($(this).is(':checked')) ? true : false;
                console.log(checked);
                if (checked) {
                    $(this).parents('table:first').find('.toggle-tr').hide();
                } else {
                    $(this).parents('table:first').find('.toggle-tr').show();
                }
            });

            $(document).on('widget-updated', function (e, widget) {
                $('.colorpicker').wpColorPicker();
                var checkbox = $(widget).find('.one-social-default-checkbox');
                var checked = ($(checkbox).is(':checked')) ? true : false;
                if (checked) {
                    $(checkbox).parents('table:first').find('.toggle-tr').hide();
                } else {
                    $(checkbox).parents('table:first').find('.toggle-tr').show();
                }
            });
        }
    });
})(jQuery);