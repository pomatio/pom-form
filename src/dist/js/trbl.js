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
        const $icon = $button.find('.dashicons');
        $icon.toggleClass('dashicons-lock', locked)
            .toggleClass('dashicons-unlock', !locked);

        const label = locked ? 'Values are locked' : 'Values are independent';
        $button.attr('aria-label', label);
        $state.val(locked ? 'yes' : 'no');

        if (locked) {
            syncAllValues($wrapper);
        }
    };

    const hasMismatchedValues = ($wrapper) => {
        const values = $wrapper.find('.pomatio-trbl__value').map((_, input) => $(input).val()).get();
        const hasStoredValue = values.some((value) => value !== '' && value !== null && value !== undefined);

        if (!hasStoredValue || !values.length) {
            return false;
        }

        const reference = values[0];
        return values.some((value) => value !== reference);
    };

    const initTrbl = ($context = $(document)) => {
        $context.find('.pomatio-trbl').each(function () {
            const $wrapper = $(this);

            if ($wrapper.data('trbl-initialized')) {
                return;
            }

            $wrapper.data('trbl-initialized', true);

            const $syncButton = $wrapper.find('.pomatio-trbl__sync');
            const $state = $wrapper.find('.pomatio-trbl__sync-state');

            if ($state.length) {
                let locked = $state.val() === 'yes';

                if (locked && hasMismatchedValues($wrapper)) {
                    locked = false;
                }

                setLockState($wrapper, locked);
            }

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
    };

    $(document).ready(function () {
        initTrbl();
    });

    $(document).ajaxComplete(function () {
        initTrbl();
    });
})(jQuery);
