/**
 * @var settings // Code editor settings object.
 */

jQuery(function($) {

    let $render_code_editor = function() {
        $('textarea.form-control.pomatio-framework-code-editor-css').each(function() {
            let $this = $(this);

            if ($this.hasClass('codemirror-rendered')) {
                return;
            }

            settings.codeMirrorSettings.codemirror.mode = 'text/css';
            let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

            // Whenever something changes, update the main textarea so there's no issues when saving
            $editor.codemirror.on('change', function(cm, change) {
                cm.save();
                $this.trigger('change');
            });

            $this.addClass('codemirror-rendered');
        });

        $('textarea.form-control.pomatio-framework-code-editor-js').each(function() {
            let $this = $(this);

            if ($this.hasClass('codemirror-rendered')) {
                return;
            }

            settings.codeMirrorSettings.codemirror.mode = 'application/javascript';
            let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

            // Whenever something changes, update the main textarea so there's no issues when saving
            $editor.codemirror.on('change', function(cm, change) {
                cm.save();
                $this.trigger('change');
            });

            $this.addClass('codemirror-rendered');
        });

        $('textarea.form-control.pomatio-framework-code-editor-html').each(function() {
            let $this = $(this);

            if ($this.hasClass('codemirror-rendered')) {
                return;
            }

            settings.codeMirrorSettings.codemirror.mode = 'text/html';
            let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

            // Whenever something changes, update the main textarea so there's no issues when saving
            $editor.codemirror.on('change', function(cm, change) {
                cm.save();
                $this.trigger('change');
            });

            $this.addClass('codemirror-rendered');
        });
    }

    $render_code_editor();

    /**
     * Adjustment so that code editors added by repeaters render correctly.
     */
    $(document).ajaxComplete(function() {
        $render_code_editor();
    });

});
