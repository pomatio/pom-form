jQuery(function($) {
  let $media_modal;
  let $clicked_button;
  
  $(document).on('click', '.open-font-picker', function(e) {
    e.preventDefault();
    
    $clicked_button = $(this);
    
    // If the uploader object has already been created, reopen the dialog
    if ($media_modal) {
      $media_modal.open();
      return;
    }
    
    const mimeTypes = Array.isArray(pom_form_font_picker.mime_types) && pom_form_font_picker.mime_types.length
      ? pom_form_font_picker.mime_types
      : ['application/vnd.ms-fontobject', 'font/otf', 'font/woff', 'font/woff2', 'font/ttf'];

    // Extend the wp.media object
    $media_modal = wp.media.frames.file_frame = wp.media({
      title: pom_form_font_picker.title,
      button: {
        text: pom_form_font_picker.button
      },
      multiple: false,
      library: {
        type: mimeTypes
      }
    });
    
    // When a file is selected, grab the URL and set it as the field's value
    $media_modal.on('select', function() {
      let $attachment = $media_modal.state().get('selection').first().toJSON();
      $clicked_button.closest('.font-variant').find('input[type="url"]').val($attachment.url);
      $clicked_button.closest('.font-variant').find('input[type="url"]').trigger('change');
    });
    
    // Open the uploader dialog
    $media_modal.open();
  });
  
});
