(function ($) {
  $('#pkun-enquiry-form form').on('submit', function (event) {
    event.preventDefault();

    var data = $(this).serialize();

    $.post(pkun_data.ajax_url, data, function (response) {
      console.log('response ', response);
    }).fail(function () {
      console.log(pkun_data.message);
    });
  });
})(jQuery);
