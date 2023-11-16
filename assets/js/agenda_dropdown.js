jQuery(document).ready(function ($) {
  $(".agenda-years-dropdown").change(function () {
    var selectedYear = $(this).val();
    $(".agenda_main").hide();
    $(".agenda_main-" + selectedYear).show();
    $('#selected-result-year').text(selectedYear);
  });
  $(".agenda-years-dropdown").trigger("change");
});
