jQuery(function($) {
  /**
   * Update input on range change.
   */
  $(document).on('input', '.pom-framework-range .slider', function() {
    let $this = $(this);
    let $value = $this.val();
    $this.closest('.pom-framework-range').find('.value').val($value).trigger('change');
  });
  
  /**
   * Update range on input change.
   */
  $(document).on('input', '.pom-framework-range .value', function() {
    let $this = $(this);
    let $value = $this.val();
    $this.closest('.pom-framework-range').find('.slider').val($value).trigger('change');
  });
  
  $(document).on('click', '.restore-range', function() {
    let $this = $(this);
    let $default = $this.attr('data-default');
    $this.closest('.pom-framework-range').find('.slider').val($default);
    $this.closest('.pom-framework-range').find('.value').val($default);
  });
});
