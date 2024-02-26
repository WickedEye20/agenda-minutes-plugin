function openMediaLibrary(event) {
    event.preventDefault();

    // Create a new instance of the media library frame
    const customMediaFrame = wp.media({
        title: 'Choose from Media Library',
        button: {
            text: 'Choose',
        },
        multiple: false,
    });

    // When an image/file is selected, run a callback.
    customMediaFrame.on('select', function () {
        const attachment = customMediaFrame.state().get('selection').first().toJSON();
        jQuery('#agenda_minutes_upload_option_media').val(attachment.url);
    });

    // Open the media frame.
    customMediaFrame.open();
}

// Ensure the custom function is called when the document is ready
jQuery(document).ready(function ($) {
    $('#agenda_minutes_upload_option_media_button').on('click', openMediaLibrary);
});
