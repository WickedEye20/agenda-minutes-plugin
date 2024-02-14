// JavaScript code for handling the accordion functionality
jQuery(document).ready(function($) {
    $('.accordion-title').click(function() {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).next('.accordion-content').slideUp();
        } else {
            $('.accordion-title').removeClass('active');
            $('.accordion-content').slideUp();
            $(this).addClass('active');
            $(this).next('.accordion-content').slideDown();
        }
    });
});
