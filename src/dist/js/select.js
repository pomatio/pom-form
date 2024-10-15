jQuery(function($) {
  if (typeof $.fn.select2 !== 'undefined') {
    $('.pomatio-framework-select.multiple').select2();
    
    $(document).ajaxComplete(function() {
      $('.pomatio-framework-select.multiple').select2();
    });
  }
});
