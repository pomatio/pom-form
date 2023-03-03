jQuery(function($) {
    $(document).on('click', 'input[name="horizontal_alignment"]', function() {
        let $this = $(this);
        if ($this.val() === 'custom') {
            $this.closest('.horizontal-alignment').find('.custom-horizontal-wrapper').show();
        }
        else {
            $this.closest('.horizontal-alignment').find('.custom-horizontal-wrapper').hide();
        }
    });

    $(document).on('click', 'input[name="vertical_alignment"]', function() {
        let $this = $(this);
        if ($this.val() === 'custom') {
            $this.closest('.vertical-alignment').find('.custom-horizontal-wrapper').show();
        }
        else {
            $this.closest('.vertical-alignment').find('.custom-horizontal-wrapper').hide();
        }
    });

    $(document).on('change', 'select[name="background_size"]', function() {
        let $this = $(this);

        if ($this.val() === 'custom') {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').show();
        }
        else {
            $this.closest('.background-size-wrapper').find('.custom-background-size-wrapper').hide();
        }
    });
});
