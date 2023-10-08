jQuery(document).ready(function ($) {
  $(".agenda-years-dropdown").change(function () {
    var selectedYear = $(this).val();
    console.log("Selected year:", selectedYear);
    $(".agenda_main").hide();
    $(".agenda_main#agenda_main-" + selectedYear).show();
    $('#selected-result-year').text(selectedYear);
  });
});
