jQuery(function($) {
  $(document).on('click', '.restore-color-palette', function() {
    let $this = $(this);
    let $default = $this.attr('data-default');
    $this.closest('.color-palette-wrapper').find('input[value="' + $default + '"]').prop('checked', 'checked');
  });
});
