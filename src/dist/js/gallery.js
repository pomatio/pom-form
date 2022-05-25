jQuery(function($) {

    // Display remove button only if image is selected.
    let $display_delete_button = function () {
        $('.gallery-wrapper .item-wrapper').each(function() {
            let $this = $(this);

            if ($this.find('img').length) {
                $(this).closest('.gallery-wrapper').find('.remove-selected-item').css('display', 'inherit');
            }
        });
    }
    $display_delete_button();

    if (typeof $.fn.sortable !== 'undefined') {
        $('.items-wrapper').sortable({
            update: function (event, ui) {
                let $this = $(this);

                let $values = $this.closest('.gallery-wrapper').find('input[type="hidden"]').val().split(',');
                if (!$values) {
                    return true;
                }

                let $ids = [];

                let $items_wrapper = $this.children('.item-wrapper');

                $items_wrapper.each(function () {
                    $ids.push($(this).find('.remove-selected-item').attr('data-id'));
                });

                $this.closest('.gallery-wrapper').find('input[type="hidden"]').val($ids.toString());
            }
        });
    }

    // Store the icon picker button that has been clicked
    let $clicked_button;
    let $gallery_modal;

    $(document).on('click', '.open-gallery-modal', function(e) {
        e.preventDefault();

        $clicked_button = $(this);

        if ($gallery_modal) {
            $gallery_modal.open();
            return;
        }

        $gallery_modal = wp.media({
            title: pom_form_gallery.title,
            multiple: 'add',
        });

        $gallery_modal.on('select', function() {
            // On close, get selections and save to the hidden input
            let $selection =  $gallery_modal.state().get('selection');
            let $ids = [];
            let $urls = [];
            let $i = 0;

            $selection.each(function($attachment) {
                $ids[$i] = $attachment['id'];
                $urls[$i] = $attachment['attributes']['sizes']['thumbnail']['url'];
                $i++;
            });

            // Update hidden input
            $clicked_button.closest('.gallery-wrapper').find('input[type="hidden"]').val($ids.toString());

            // Update preview
            $clicked_button.closest('.gallery-wrapper').find('.items-wrapper').empty();
            $i = 0;
            $urls.forEach(function($url) {
                $clicked_button.closest('.gallery-wrapper').find('.items-wrapper').append('<div class="item-wrapper"><span class="remove-selected-item dashicons dashicons-trash" data-id="' + $ids[$i] + '"></span><img src="' + $url + '" alt=""></div>');
                $i++;
            });

            $display_delete_button();
        });
    });

    $(document).on('click', '.gallery-wrapper .remove-selected-item', function(e) {
        e.preventDefault();

        let $this = $(this);
        let $id = $this.attr('data-id');

        let $values = $this.closest('.gallery-wrapper').find('input[type="hidden"]').val().split(',');

        const $index = $values.indexOf($id);
        if ($index > -1) {
            $values.splice($index, 1);
        }

        $this.closest('.gallery-wrapper').find('input[type="hidden"]').val($values.toString());

        $this.closest('.item-wrapper').remove();
    });

});
