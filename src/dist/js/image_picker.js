jQuery(function($) {
  
  // Display remove button only if image is selected.
  $('.pomatio-framework-image-wrapper .image-wrapper').each(function() {
    let $this = $(this);
    
    if ($this.find('img').length) {
      $(this).closest('.pomatio-framework-image-wrapper').find('.remove-selected-image').css('display', 'inherit');
    }
  });
  
  let $media_modal;
  let $clicked_button;
  
  $(document).on('click', '.open-image-picker', function(e) {
    e.preventDefault();
    
    $clicked_button = $(this);
    
    // If the uploader object has already been created, reopen the dialog
    if ($media_modal) {
      $media_modal.open();
      return;
    }
    
    // Extend the wp.media object
    $media_modal = wp.media.frames.file_frame = wp.media({
      title: pom_form_image_picker.title,
      button: {
        text: pom_form_image_picker.button
      },
      multiple: false
    });
    
    // When a file is selected, grab the URL and set it as the field's value
    $media_modal.on('select', function() {
      let $attachment = $media_modal.state().get('selection').first().toJSON();
      
      $clicked_button.closest('.pomatio-framework-image-wrapper').find('input[type="url"]').val($attachment.url);
      $clicked_button.closest('.pomatio-framework-image-wrapper').find('input[type="url"]').trigger('change');
      $clicked_button.closest('.pomatio-framework-image-wrapper').find('.image-wrapper').empty().append('<img width="280px" alt="" src="' + $attachment.url + '">');
      $clicked_button.closest('.pomatio-framework-image-wrapper').find('.remove-selected-image').css('display', 'inherit');
    });
    
    // Open the uploader dialog
    $media_modal.open();
  });
  
  $(document).on('click', '.pomatio-framework-image-wrapper .remove-selected-image', function(e) {
    e.preventDefault();
    
    let $this = $(this);
    let $wrapper = $this.closest('.pomatio-framework-image-wrapper');
    
    $wrapper.find('.image-wrapper').empty();
    $wrapper.find('input[type="url"]').val('');
    $this.css('display', 'none');
  });
  
});
