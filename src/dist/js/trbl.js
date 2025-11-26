(function ($) {
    'use strict';

    const lockedClass = 'is-locked';

    const syncAllValues = ($wrapper) => {
        const $values = $wrapper.find('.pomatio-trbl__value');
        const reference = $values.first().val();
        $values.val(reference);

        const $firstUnit = $wrapper.find('.pomatio-trbl__unit-select').first();
        if ($firstUnit.length) {
            $wrapper.find('.pomatio-trbl__unit-select').val($firstUnit.val());
        }
    };

    const setLockState = ($wrapper, locked) => {
        const $button = $wrapper.find('.pomatio-trbl__sync');
        const $state = $wrapper.find('.pomatio-trbl__sync-state');

        if (!$button.length) {
            return;
        }

        $wrapper.toggleClass(lockedClass, locked);
        $button.toggleClass('is-locked', locked);
        $button.toggleClass('is-unlocked', !locked);
        $button.attr('aria-pressed', locked ? 'true' : 'false');
        $button.find('.dashicons')
            .toggleClass('dashicons-lock', locked)
            .toggleClass('dashicons-unlock', !locked);
        $state.val(locked ? 'yes' : 'no');

        if (locked) {
            syncAllValues($wrapper);
        }
    };

    $(document).ready(function () {
        $('.pomatio-trbl').each(function () {
            const $wrapper = $(this);
            const $syncButton = $wrapper.find('.pomatio-trbl__sync');

            if ($syncButton.length) {
                $syncButton.on('click', function () {
                    const locked = !$wrapper.hasClass(lockedClass);
                    setLockState($wrapper, locked);
                });
            }

            $wrapper.on('input change', '.pomatio-trbl__value', function () {
                if (!$wrapper.hasClass(lockedClass)) {
                    return;
                }

                const value = $(this).val();
                $wrapper.find('.pomatio-trbl__value').val(value);
            });

            $wrapper.on('change', '.pomatio-trbl__unit-select', function () {
                if (!$wrapper.hasClass(lockedClass)) {
                    return;
                }

                const unit = $(this).val();
                $wrapper.find('.pomatio-trbl__unit-select').val(unit);
            });
        });
    });
})(jQuery);
