import { useRef, useState } from '@wordpress/element';
import { Editor } from '@tinymce/tinymce-react';

const RichTextEditorWithMedia = ({ value, onChange }) => {
    const editorRef = useRef(null);
    const [isReady, setIsReady] = useState(false);

    const handleAddMedia = () => {
        if (!isReady || !editorRef.current) return;

        const mediaFrame = wp.media({
            title: 'Add Media',
            button: { text: 'Insert' },
            multiple: true,
        });

        mediaFrame.on('select', () => {
            const selection = mediaFrame.state().get('selection');

            if (!selection) return;

            selection.forEach((attachment) => {
                const url = attachment.get('url');
                const imgHTML = `<img src="${url}" alt="" />`;
                editorRef.current.insertContent(imgHTML);
            });
        });

        mediaFrame.open();
    };

    return (
        <div className="gv-form-option">
            <label className="gv-label">{ocmmSettings.labelDesc}</label>
            <button
                type="button"
                className="gv-button gv-button-secondary gv-mode-condensed oc-editor-btn"
                onClick={handleAddMedia}
                disabled={!isReady}
            >
                <span className="wp-media-buttons-icon"></span>
                {ocmmSettings.labelMedia}
            </button>

            <Editor
                tinymceScriptSrc={`${ocmmSettings.site_url}/wp-includes/js/tinymce/tinymce.min.js`}
                onInit={(evt, editor) => {
                    editorRef.current = editor;
                    setIsReady(true);
                }}
                value={value}
                onEditorChange={onChange}
                init={{
                    height: 300,
                    menubar: false,
                    plugins: 'lists link wpview',
                    toolbar:
                        'formatselect bold italic underline | blockquote bullist numlist | undo redo  | link unlink | fullscreen | wpview',
                    branding: false,
                }}
            />
        </div>
    );
};

export default RichTextEditorWithMedia;