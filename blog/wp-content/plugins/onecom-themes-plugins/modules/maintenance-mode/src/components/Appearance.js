import {useEffect, useState} from "@wordpress/element";
import {useNavigate} from 'react-router-dom';
import {Button, eventEmitter, Overlay, Radio, Select} from "wpin-components-library";
import BreaCrumbs from "./BreaCrumbs";
import SettingsSection from "./Settings";
import Imageuploader from "./Imageuploader";
import ColorPicker from "./ColorPicker";
import SettingsFooter from "./SettingsFooter";
import {useSettings} from "../context/SettingsContext";
const Appearance = () => {
    const navigate = useNavigate();
    const {settings, setSettings, updateSetting, saveSettings, isSaving,setIsSaving,renderNotice,handleUpgradeClick} = useSettings();
    const [selectedImage, setSelectedImage] = useState(settings.uc_theme || '');
    const [backgroundImage, setBackgroundImage] = useState(settings.uc_page_bg_image || '');
    const [logoImage, setLogoImage] = useState(settings.uc_logo || '');
    const [faviconImage, setFaviconImage] = useState(settings.uc_favicon || '');
    const [primaryColour, setPrimaryColour] = useState(settings.uc_primary_color || '');
    const [backgroundColour, setBackgroundColour] = useState(settings.uc_page_bg_color || '');

    const imageList = [
        { key: 'theme-1', filename: 'design-1.jpg' },
        { key: 'theme-2', filename: 'design-2.jpg' },
        { key: 'theme-3', filename: 'design-3.jpg' },
        { key: 'theme-4', filename: 'design-4.jpg' },
        { key: 'theme-5', filename: 'design-5.jpg' },
        { key: 'theme-6', filename: 'design-6.jpg' },
    ];

    const layoutBackgrounds = {
        'theme-1': `${ocmmSettings.imageDIR}assets/images/design-1-bg.jpeg`,
        'theme-2': `${ocmmSettings.imageDIR}assets/images/design-2-bg.jpeg`,
        'theme-3': `${ocmmSettings.imageDIR}assets/images/design-3-bg.jpeg`,
        'theme-4': '',
        'theme-5': '',
        'theme-6': `${ocmmSettings.imageDIR}assets/images/design-6-bg.jpeg`,
    };

    useEffect(() => {
        renderNotice();
    }, []); // only run once after initial render

    const handleAppearanceSave = async () => {
        const updatedSettings = {
            ...settings, // merge with existing settings
            uc_theme: selectedImage,
            uc_page_bg_image: backgroundImage,
            uc_logo: logoImage,
            uc_favicon: faviconImage,
            uc_primary_color: primaryColour,
            uc_page_bg_color: backgroundColour
        };
        setIsSaving(true);
        saveSettings(updatedSettings).then((success) => {
            setIsSaving(false);

            if (success) {
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
    }
    return (
        <>
            <div className="gv-p-fluid gv-pl-0">
                <BreaCrumbs childLabel={ocmmSettings.appearanceLabel} parentLabel="Maintenance Mode" />
                <h3 className="gv-mt-sm">{ocmmSettings.appearanceLabel}</h3>
                <p className="gv-mt-sm gv-text-sm">{ocmmSettings.appearancePageDesc}</p>
            </div>
            <div id="oc-notice-placeholder"></div>

            <div className="gv-p-lg gv-surface-bright">

                <div className="gv-stack-space-lg">
                    <SettingsSection
                        title={ocmmSettings.labelLayout}
                        description={ocmmSettings.descLayout}
                    >
                        <div className="oc-image-wrap gv-grid gv-desk-lg-grid-cols-3 gv-desk-grid-cols-2 gv-tab-grid-cols-2 gv-gap-md">
                            {imageList.map(({ key, filename }, index) => {
                                const imageUrl = `${ocmmSettings.imageDIR}assets/images/${filename}`;
                                const isSelected = selectedImage === key;
                                const isPremium = index >= 3; // 4th, 5th, and 6th designs
                                const isDisabled = isPremium && !ocmmSettings.isPremium;

                                return (
                                    <>
                                        {!isDisabled ? (
                                            <a
                                                href="#"
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    setSelectedImage(key);
                                                    const assetsPath = 'assets/images';
                                                    const currentBg = backgroundImage;

                                                    const shouldUpdateBg =
                                                        !currentBg || currentBg.includes(assetsPath);

                                                    if (shouldUpdateBg && Object.prototype.hasOwnProperty.call(layoutBackgrounds, key)) {
                                                        setBackgroundImage(layoutBackgrounds[key]);
                                                    }
                                                }}
                                                className={isSelected ? 'oc-selected-image' : 'oc-unselected-image gv-border-alt'}
                                            >
                                                <img
                                                    src={imageUrl}
                                                    alt={`Design ${index + 1}`}
                                                    className="oc-theme-image"
                                                />
                                                {isSelected && (
                                                    <img
                                                        src={`${ocmmSettings.imageDIR}assets/images/icons/check.svg`}
                                                        alt="Selected"
                                                        className="oc-selected-icon"
                                                    />
                                                )}
                                            </a>
                                        ) : (
                                            <>
                                                <div className={isSelected ? 'oc-selected-image gv-pos-relative' : 'oc-unselected-image gv-border-alt gv-pos-relative'}>
                                                <img
                                                    src={imageUrl}
                                                    alt={`Design ${index + 1}`}
                                                    className="oc-theme-image oc-disabled-image"
                                                />
                                                <span className="gv-badge gv-badge-generic oc-premium-badge">Premium</span>
                                                </div>
                                                </>
                                        )}
                                    </>
                                );
                            })}
                        </div>
            </SettingsSection>
                    <div className="gv-divider"></div>
        <SettingsSection title={ocmmSettings.labelBackgroundImg} description={ocmmSettings.descBackgroundImg}>
            <div className="oc-spacer gv-flex-1"></div>
            <Imageuploader id="backgroundImage" value={backgroundImage} onChange={setBackgroundImage} />
        </SettingsSection>
            <div className="gv-divider"></div>

            <SettingsSection title={ocmmSettings.labelLogo} description={ocmmSettings.descLogo}>
                <Imageuploader id="logoImage" value={logoImage} onChange={setLogoImage} />
        </SettingsSection>
            <div className="gv-divider"></div>

            <SettingsSection title={ocmmSettings.labelfavicon} description={ocmmSettings.descfavicon}>
                <div className={!ocmmSettings.isPremium ? 'gv-pos-relative gv-mwp-favicon': ''}>
                    {!ocmmSettings.isPremium && (<Overlay
                        heading={ocmmSettings.mwpTitle}
                        message={ocmmSettings.mwpFavicon}
                        overlayIcon={`${ocmmSettings.imageDIR}assets/images/mwp-lock.svg`}
                        handleUpgradeClick={() => handleUpgradeClick('oc_um_overlay')}
                        upgradeLabel={ocmmSettings.mwpUpsellBtn}
                        upgradeIcon={`${ocmmSettings.imageDIR}assets/images/expand_new.svg`}
                    />)}
                    <Imageuploader id="faviconImage" value={faviconImage} onChange={setFaviconImage} />
                </div>
        </SettingsSection>
                    <div className="gv-divider"></div>

            <SettingsSection title={ocmmSettings.labelPrimary} description={ocmmSettings.descPrimary}>
                <div className="gv-flex-1"></div>
                <ColorPicker id="primaryColour" value={primaryColour} onChange={setPrimaryColour}/>
        </SettingsSection>
                    <div className="gv-divider"></div>
                    <SettingsSection title={ocmmSettings.labelbackground} description={ocmmSettings.descbackground}>
                        <div className="gv-flex-1"></div>
                        <ColorPicker value={backgroundColour} onChange={setBackgroundColour}/>
        </SettingsSection>

                </div>

                <SettingsFooter
                    onBack={() => navigate('/')}
                    onSave={handleAppearanceSave}
                    isSaving={isSaving}
                    backLabel={ocmmSettings.labelBack}
                    saveLabel={ocmmSettings.labelSave}
                />

            </div>
        </>
    )
}

export default Appearance;