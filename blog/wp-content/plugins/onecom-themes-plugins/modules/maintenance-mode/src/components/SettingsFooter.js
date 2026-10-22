import {Button} from 'wpin-components-library'; // adjust import path

const SettingsFooter = ({ onBack, onSave, isSaving, backLabel, saveLabel }) => {
    return (
        <div className="gv-mt-md gv-flex gv-gap-md gv-max-mob-flex-col">
            <Button
                className="gv-button-cancel"
                onClick={onBack}
                type="button"
                disabled={isSaving}
            >
                {backLabel || 'Back'}
            </Button>
            <Button
                className="gv-button-primary gv-max-mob-order-first"
                onClick={onSave}
                type="button"
                disabled={isSaving}
            >
                {isSaving ? ocmmSettings.labelSaving : (saveLabel || 'Save')}
            </Button>
        </div>
    );
};


export default SettingsFooter;