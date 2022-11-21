jQuery(function($) {
    /**
     * Update input on range change.
     */
    $(document).on('input', '.pomatio-framework-range .slider', function() {
        let $this = $(this);
        let $value = $this.val();
        $this.closest('.pomatio-framework-range').find('.value').val($value).trigger('change');
    });

    /**
     * Update range on input change.
     */
    $(document).on('input', '.pomatio-framework-range .value', function() {
        let $this = $(this);
        let $value = $this.val();
        $this.closest('.pomatio-framework-range').find('.slider').val($value).trigger('change');
    });
});
