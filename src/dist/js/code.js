jQuery(function($) {
    $('textarea.form-control.pom-form-code-editor').each(function() {
        let $this = $(this);
        console.log($this);

        settings.codeMirrorSettings.codemirror.mode = $this.attr('language');
        let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);

        // Whenever something changes, update the main textarea so there's no issues when saving
        $editor.codemirror.on('change', function(cm, change) {
            cm.save();
            $this.trigger('change');
        });
    });
});
