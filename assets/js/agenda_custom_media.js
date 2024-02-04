function openMediaLibrary(event) {
    event.preventDefault();

    // Create a new instance of the media library frame
    const customMediaFrame = wp.media({
        title: 'Choose from Media Library',
        library: {
            type: 'application/pdf', // Set the media library filter to PDF files
        },
        button: {
            text: 'Choose',
        },
        multiple: false,
    });

    // When a PDF file is selected, run a callback.
    customMediaFrame.on('select', function () {
        const selection = customMediaFrame.state().get('selection').first();
        const attachment = selection.toJSON();

        // Check if the selected file is a PDF
        if (attachment.mime === 'application/pdf') {
            jQuery('#agenda_minutes_upload_option_media').val(attachment.url);
        } else {
            alert('Please select a PDF file.');
        }
    });

    // Open the media frame.
    customMediaFrame.open();
}

// Ensure the custom function is called when the document is ready
jQuery(document).ready(function ($) {
    $('#agenda_minutes_upload_option_media_button').on('click', openMediaLibrary);
});
