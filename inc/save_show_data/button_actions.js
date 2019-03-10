$(function () {
    const postWithData = function (key, value) {
        const input = $('#form-for-action-input');
        input.attr('name', key);
        input.attr('value', value);
        $('#form-for-action').submit();
    };

    $.each($('.btn-reload'), function () {
        $(this).click(function () {
            postWithData('reload', $(this).data('id'));
        });
    });

    $.each($('.btn-delete'), function () {
        $(this).click(function () {
            postWithData('delete', $(this).data('id'));
        });
    });
});