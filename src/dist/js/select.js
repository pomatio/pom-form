jQuery(function($) {
  if (typeof $.fn.select2 !== 'undefined') {
    $('.pom-framework-select.multiple').select2();
    
    $(document).ajaxComplete(function() {
      $('.pom-framework-select.multiple').select2();
    });
  }
});
