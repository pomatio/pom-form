jQuery(function($) {
    $('textarea.form-control.code-editor').each(function() {
        let $this = $(this);
        settings.codeMirrorSettings.codemirror.mode = $this.attr('code_language');
        editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

        // Whenever something changes, update the main textarea so there's no issues when saving
        editor.codemirror.on('change', function(cm, change) {
            cm.save();
            $this.trigger('change');
        });

    });

});
