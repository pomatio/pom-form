jQuery(function($) {
  $(document).on('click', '.quantity .number-up', function() {
    let $input = $(this).siblings('input');
    let max = $input.attr('max') || 9999999;
    
    let oldValue = parseFloat($input.val());
    let newVal = oldValue >= max ? oldValue : oldValue + 1;
    
    $input.val(newVal);
    $input.trigger('change');
  });
  
  $(document).on('click', '.quantity .number-down', function() {
    let $input = $(this).siblings('input');
    let min = $input.attr('min') || -9999999;
    
    let oldValue = parseFloat($input.val());
    let newVal = oldValue <= min ? oldValue : oldValue - 1;
    
    $input.val(newVal);
    $input.trigger('change');
  });
});
