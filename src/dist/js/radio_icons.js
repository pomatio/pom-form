jQuery(function($) {
  $(document).on('click', '.restore-radio-icon', function() {
    let $this = $(this);
    let $default = $this.attr('data-default');
    $this.closest('.pomatio-framework-radio-icons-wrapper').find('input[value="' + $default + '"]').prop('checked', 'checked');
  });
});
