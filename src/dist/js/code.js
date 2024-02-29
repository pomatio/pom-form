/**
 * @var settings // Code editor settings object.
 *
 * TODO: Prevent line wrapping in code editors.
 */

jQuery(function($) {
    
    let $render_code_editor = function() {
        const editors = {
            'css': 'text/css',
            'js': 'application/javascript',
            'json': 'application/json',
            'html': 'text/html'
        };
        
        for (const [editorClass, mode] of Object.entries(editors)) {
            $(`textarea.form-control.pomatio-framework-code-editor-${editorClass}`).each(function() {
                let $this = $(this);
                
                if ($this.hasClass('codemirror-rendered')) {
                    return;
                }
                
                settings.codeMirrorSettings.codemirror.mode = mode;
                let $editor = wp.codeEditor.initialize($this, settings.codeMirrorSettings);
                
                // Whenever something changes, update the main textarea so there's no issues when saving
                $editor.codemirror.on('change', function(cm, change) {
                    cm.save();
                    $this.trigger('change');
                });
                
                $this.addClass('codemirror-rendered');
            });
        }
    };
    
    $render_code_editor();

    /**
     * Adjustment so that code editors added by repeaters render correctly.
     */
    $(document).ajaxComplete(function() {
        $render_code_editor();
    });

});
