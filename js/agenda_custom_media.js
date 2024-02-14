function openMediaLibrary(event) {
    event.preventDefault();

    // Create a new instance of the media library frame
    const customMediaFrame = wp.media({
        title: 'Select PDF',
        button: {
            text: 'Select PDF',
        },
        multiple: false,
        library: {
            // Custom function to filter media items
            filterable: 'all',
            props: {
                // Callback function to filter media items
                filters: {
                    // Filter by file type
                    type: 'pdf' // Only allow pdf
                }
            }
        }
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
