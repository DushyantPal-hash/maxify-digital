import { useState, useRef, useEffect } from '@wordpress/element';
import { ChromePicker } from 'react-color';

const ColorPicker = ({ id = '', value = '#999999', onChange }) => {
    const [showPicker, setShowPicker] = useState(false);
    const pickerRef = useRef(null);

    const handleColorChange = (color) => {
        onChange?.(color.hex);
    };

    const handleClickOutside = (event) => {
        if (pickerRef.current && !pickerRef.current.contains(event.target)) {
            setShowPicker(false);
        }
    };

    useEffect(() => {
        if (showPicker) {
            document.addEventListener('mousedown', handleClickOutside);
        } else {
            document.removeEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [showPicker]);

    return (
        <div className="gv-color-picker-wrap" style={{ position: 'relative' }}>
            <div
                className="gv-flex gv-items-center gv-gap-sm oc-color-picker"
                onClick={() => setShowPicker((prev) => !prev)}
            >
                <div className="oc-color-pallete" style={{ backgroundColor: value }} />
                <span className="oc-select-color">{ocmmSettings.choosecolor}</span>
            </div>

            {showPicker && (
                <div
                    ref={pickerRef}
                    className="oc-colorpicker-popup"
                >
                    <ChromePicker
                        color={value}
                        onChange={handleColorChange}
                        disableAlpha
                    />
                </div>
            )}
        </div>
    );
};

export default ColorPicker;