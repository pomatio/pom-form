jQuery(function($) {
  if (typeof $.fn.wpColorPicker !== 'undefined') {
    $('.pom-framework-color-picker').wpColorPicker();
    
    $(document).ajaxComplete(function() {
      $('.pom-framework-color-picker').wpColorPicker();
    });
  }
});
