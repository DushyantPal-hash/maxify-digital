import {useState,useEffect,createRoot} from "@wordpress/element";
import {useNavigate} from 'react-router-dom';
import {Button, Textarea, Toast, Notice, eventEmitter, Overlay} from "wpin-components-library";
import BreaCrumbs from "./BreaCrumbs";
import ExcludeSelector from "./ExcludeSelector";
import {useSettings} from "../context/SettingsContext";
import SettingsFooter from "./SettingsFooter";


const AllowListPostPages = () => {



    const navigate = useNavigate();
    const {settings, setSettings, updateSetting, saveSettings, isSaving,setIsSaving,toastData,settoastData,countDownState,setCountDownState, renderNotice,handleUpgradeClick} = useSettings();

    const [excluded, setExcluded] = useState(settings?.uc_exclude_pages || {});

    const handleExcludeChange = (postTypeSlug, selected) => {
        setExcluded(prev => ({
            ...prev,
            [postTypeSlug]: selected,
        }));
    };

    useEffect(() => {
        renderNotice();
    }, []); // only run once after initial render

    const handleSubmit = () => {
        setIsSaving(true);
        const updatedSettings = {
            ...settings,
            uc_exclude_pages: excluded,
        };

        saveSettings(updatedSettings).then(success => {
            if (success) {
                setSettings(updatedSettings);
                eventEmitter.emit('showNotice', {
                    message: ocmmSettings.noticeSuccess,
                    icon: `${ocmmSettings.imageDIR}assets/images/icons/success.svg`,
                    noticeClass: 'gv-notice-success',
                });
            } else {
                eventEmitter.emit('showNotice', {
                    message: ocmmSettings.noticeError,
                    icon: `${ocmmSettings.imageDIR}assets/images/icons/error.svg`,
                    noticeClass: 'gv-notice-alert',
                });
            }
        }).finally(() => setIsSaving(false));
    };

    return (
        <>
            <div className="gv-p-fluid gv-pl-0">
                <BreaCrumbs childLabel={ocmmSettings.allowTitle} parentLabel="Maintenance Mode"/>
                <h3 className="gv-mt-sm">{ocmmSettings.allowTitle}</h3>
                <p className="gv-mt-sm gv-text-sm" style={{maxWidth: '700px'}}>{ocmmSettings.allowPageDesc}</p>
            </div>
            <div id="oc-notice-placeholder"></div>
            <div className="gv-p-lg gv-surface-bright gv-stack-space-md">
                <div className={`${!ocmmSettings.isPremium ? 'gv-pos-relative gv-mwp-favicon gv-max-mob-p-lg': ''}`}>

                {!ocmmSettings.isPremium && (<Overlay
                    heading={ocmmSettings.mwpTitle}
                    message={ocmmSettings.mwpAllowDesc}
                    overlayIcon={`${ocmmSettings.imageDIR}assets/images/mwp-lock.svg`}
                    handleUpgradeClick={() => handleUpgradeClick('oc_um_overlay')}
                    upgradeLabel={ocmmSettings.mwpUpsellBtn}
                    upgradeIcon={`${ocmmSettings.imageDIR}assets/images/expand_new.svg`}
                />)}
                <ExcludeSelector
                    postTypes={ocmmSettings.postTypes}
                    excluded={excluded}
                    onChange={handleExcludeChange}
                />

                <SettingsFooter
                    onBack={() => navigate('/')}
                    onSave={handleSubmit}
                    isSaving={isSaving}
                    backLabel={ocmmSettings.labelBack}
                    saveLabel={ocmmSettings.labelSave}
                />

            </div>
            </div>
        </>
    )
}

export default AllowListPostPages;