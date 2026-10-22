import { useEffect } from '@wordpress/element';

const ImageUploader = ({ id = '', value = null, onChange }) => {

    const handleUpload = () => {
        const mediaFrame = wp.media({
            title: 'Select Image',
            button: { text: 'Insert' },
            multiple: false,
        });

        mediaFrame.on('select', () => {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            onChange?.(attachment.url);
        });

        mediaFrame.open();
    };

    const handleRemove = () => {
        onChange?.(null);
    };

    return (
        <div
            className="gv-image-upload-container gv-surface-dim gv-flex gv-flex-col"
            style={{
                height: '232px',
                position: 'relative',
                overflow: 'hidden',
                alignItems: 'center',
                justifyContent: 'center',
                borderRadius: '4px'
            }}
        >
            {!value ? (
                <>
                    <gv-tile size="md" src={`${ocmmSettings.imageDIR}assets/images/add-image.svg`} />
                    <button
                        type="button"
                        className="gv-button gv-button-secondary gv-mt-md oc-image-upload"
                        onClick={handleUpload}
                        style={{ position: 'relative', zIndex: 2 }}
                    >
                        {ocmmSettings.chooseImage}
                    </button>
                </>
            ) : (
                <>
                    <img
                        src={value}
                        alt="Selected"
                        style={{
                            width: '100%',
                            height: '100%',
                            objectFit: 'cover',
                            position: 'absolute',
                            top: 0,
                            left: 0,
                        }}
                    />
                    <div
                        className="image-actions"
                        style={{
                            position: 'absolute',
                            bottom: 5,
                            right: 8
                        }}
                    >
                        <span className="gv-surface-dim gv-mr-xs" onClick={handleUpload}>
                            <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/sync.svg`} />
                        </span>
                        <span className="gv-surface-dim" onClick={handleRemove}>
                            <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/delete.svg`} />
                        </span>
                    </div>
                </>
            )}
        </div>
    );
};

export default ImageUploader;