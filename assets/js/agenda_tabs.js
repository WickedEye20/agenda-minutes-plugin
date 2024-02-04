jQuery(function ($) {
    $('.agenda-tabs a').on('click', function (e) {
        e.preventDefault();
        var tab = $(this).data('tab');
    });
});
