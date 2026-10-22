import {useEffect, useState} from "@wordpress/element";
import {useNavigate} from 'react-router-dom';
import {Button, eventEmitter, Overlay, Textarea} from "wpin-components-library";
import BreaCrumbs from "./BreaCrumbs";
import {useSettings} from "../context/SettingsContext";
import SettingsFooter from "./SettingsFooter";

const AdvanceSettings = () => {
    const navigate = useNavigate();
    const {settings, setSettings, updateSetting, saveSettings, isSaving,setIsSaving,toastData,settoastData,countDownState,setCountDownState, renderNotice,handleUpgradeClick} = useSettings();

    const [customCSS, setCustomCSS] = useState(settings?.uc_custom_css || '');
    const [headerScripts, setHeaderScripts] = useState(settings?.uc_scripts || '');
    const [footerScripts, setFooterScripts] = useState(settings?.uc_footer_scripts || '');

    useEffect(() => {
        renderNotice();
    }, []); // only run once after initial render

    const handleSave = () => {
        const updated = {
            ...settings,
            uc_custom_css: customCSS,
            uc_scripts: headerScripts,
            uc_footer_scripts: footerScripts,
        };

        saveSettings(updated)
            .then(success => {
                if (success) {
                    setSettings(updated);
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
            });
    };

    return (
        <>
            <div className="gv-p-fluid gv-pl-0">
                <BreaCrumbs childLabel={ocmmSettings.advanceTitle} parentLabel="Maintenance Mode" />
                <h3 className="gv-mt-sm">{ocmmSettings.advanceTitle}</h3>
                <p className="gv-mt-sm gv-text-sm">{ocmmSettings.advancePageDesc}</p>
            </div>
            <div id="oc-notice-placeholder"></div>

            <div className="gv-p-lg gv-surface-bright gv-stack-space-md">
                <Textarea
                    value={customCSS}
                    onChange={(e) => setCustomCSS(e.target.value)}
                    label={ocmmSettings.labelCss}
                    message={ocmmSettings.descCss}
                    placeholder=".selector { property-name: property-value; }"
                />
                <Textarea
                    value={headerScripts}
                    onChange={(e) => setHeaderScripts(e.target.value)}
                    label={ocmmSettings.labelHeaderScript}
                    message={ocmmSettings.descHeaderScript}
                    placeholder={`<script><!--Analytics code --></script>`}
                />
                <div className={!ocmmSettings.isPremium ? "gv-pos-relative gv-mwp-favicon" : ""}>
                    {!ocmmSettings.isPremium && (<Overlay
                        heading={ocmmSettings.mwpTitle}
                        message={ocmmSettings.mwpAdvanced}
                        overlayIcon={`${ocmmSettings.imageDIR}assets/images/mwp-lock.svg`}
                        handleUpgradeClick={() => handleUpgradeClick('oc_um_overlay')}
                        upgradeLabel={ocmmSettings.mwpUpsellBtn}
                        upgradeIcon={`${ocmmSettings.imageDIR}assets/images/expand_new.svg`}
                    />)}
                <Textarea
                    value={footerScripts}
                    onChange={(e) => setFooterScripts(e.target.value)}
                    label={ocmmSettings.labelFooterScript}
                    message={ocmmSettings.descFooterScript}
                    placeholder={`<script><!--Analytics code --></script>`}
                />
                </div>

                <SettingsFooter
                    onBack={() => navigate('/')}
                    onSave={handleSave}
                    isSaving={isSaving}
                    backLabel={ocmmSettings.labelBack}
                    saveLabel={ocmmSettings.labelSave}
                />
            </div>
        </>
    );
};

export default AdvanceSettings;