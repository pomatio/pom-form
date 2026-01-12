jQuery(function($) {
  let $media_modal;
  let $clicked_button;
  const mediaFrames = {};
  
  $(document).on('click', '.open-font-picker', function(e) {
    e.preventDefault();
    
    $clicked_button = $(this);

    let buttonMimeTypes = $clicked_button.data('mime-types');
    if (typeof buttonMimeTypes === 'string') {
      try {
        buttonMimeTypes = JSON.parse(buttonMimeTypes);
      }
      catch (error) {
        buttonMimeTypes = null;
      }
    }

    const globalMimeTypes = (typeof pom_form_font_picker !== 'undefined' && Array.isArray(pom_form_font_picker.mime_types) && pom_form_font_picker.mime_types.length)
      ? pom_form_font_picker.mime_types
      : ['font/woff2', 'font/woff', 'font/ttf'];
    const mimeTypes = Array.isArray(buttonMimeTypes) && buttonMimeTypes.length
      ? buttonMimeTypes
      : globalMimeTypes;
    const mimeTypesKey = JSON.stringify(mimeTypes);

    if (!mediaFrames[mimeTypesKey]) {
      // Extend the wp.media object
      mediaFrames[mimeTypesKey] = wp.media.frames.file_frame = wp.media({
        title: pom_form_font_picker.title,
        button: {
          text: pom_form_font_picker.button
        },
        multiple: false,
        library: {
          type: mimeTypes,
          pom_form_font_picker: true
        }
      });
      
      // When a file is selected, grab the URL and set it as the field's value
      mediaFrames[mimeTypesKey].on('select', function() {
        let $attachment = mediaFrames[mimeTypesKey].state().get('selection').first().toJSON();
        $clicked_button.closest('.font-variant').find('input[type="url"]').val($attachment.url);
        $clicked_button.closest('.font-variant').find('input[type="url"]').trigger('change');
      });
    }

    $media_modal = mediaFrames[mimeTypesKey];
    $media_modal.open();
  });
  
});
