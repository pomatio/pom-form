jQuery(function($) {
    $('textarea.form-control.pom-form-code-editor-css').each(function() {
        let $this = $(this);

        settings.codeMirrorSettings.codemirror.mode = 'text/css';
        let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

        // Whenever something changes, update the main textarea so there's no issues when saving
        $editor.codemirror.on('change', function(cm, change) {
            cm.save();
            $this.trigger('change');
        });
    });

    $('textarea.form-control.pom-form-code-editor-js').each(function() {
        let $this = $(this);

        settings.codeMirrorSettings.codemirror.mode = 'application/javascript';
        let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

        // Whenever something changes, update the main textarea so there's no issues when saving
        $editor.codemirror.on('change', function(cm, change) {
            cm.save();
            $this.trigger('change');
        });
    });

    $('textarea.form-control.pom-form-code-editor-html').each(function() {
        let $this = $(this);

        settings.codeMirrorSettings.codemirror.mode = 'text/html';
        let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

        // Whenever something changes, update the main textarea so there's no issues when saving
        $editor.codemirror.on('change', function(cm, change) {
            cm.save();
            $this.trigger('change');
        });
    });
});
