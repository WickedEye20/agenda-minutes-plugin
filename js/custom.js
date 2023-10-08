jQuery(document).ready(function($) {
    $(document).on('click', '.download-pdf-link', function(e) {
        e.preventDefault();
        var pdfUrl = $(this).data('pdf-url');
        window.open(pdfUrl, '_blank');
    });

    $(".download-pdf-link").each(function() {
        var $this = $(this);
        var pdfUrl = $this.attr("href");
        var pdfFilename = pdfUrl.substring(pdfUrl.lastIndexOf("/") + 1);
        $this.text("Download PDF");
        $this.attr("data-pdf-url", pdfUrl); // Store the PDF URL in the data-pdf-url attribute
    });
});
