import {useState, useEffect, createRoot} from "@wordpress/element";
import {useNavigate,useLocation} from 'react-router-dom';
import {
    Button,
    Input,
    Radio,
    Select,
    ToggleSwitch,
    Textarea,
    eventEmitter,
    Notice,
    Overlay
} from "wpin-components-library";
import BreaCrumbs from "./BreaCrumbs";
import RichTextEditorWithMedia from "./RichTextEditor";
import {useSettings} from "../context/SettingsContext";
import SettingsFooter from "./SettingsFooter";


const Content = () => {
    const navigate = useNavigate();
    const location = useLocation();

    const { settings, setSettings, saveSettings, setToastData, isSaving, setIsSaving,renderNotice,handleUpgradeClick } = useSettings();

    // Initialize states from context settings
    const [headline, setHeadline] = useState(settings.uc_headline || '');
    const [content, setContent] = useState(settings.uc_description || '');
    const [seoTitle, setSeoTitle] = useState(settings.uc_seo_title || '');
    const [seoMeta, setSeoMeta] = useState(settings.uc_seo_description || '');
    const [copyright, setCopyright] = useState(settings.uc_copyright || '');
    const [facebook, setFacebook] = useState(settings.uc_facebook_url || '');
    const [xTwitter, setXTwitter] = useState(settings.uc_twitter_url || '');
    const [instagram, setInstagram] = useState(settings.uc_instagram_url || '');
    const [linkedin, setLinkedin] = useState(settings.uc_linkedin_url || '');
    const [youtube, setYoutube] = useState(settings.uc_youtube_url || '');
    const [showSignup, setShowSignup] = useState(settings.uc_subscribe_form === 'on');
    const [errors, setErrors] = useState({});

    const isValidURL = (url) => {
        if (!url) return true; // Allow empty strings

        try {
            new URL(url);
            return true;
        } catch (_) {
            return false;
        }
    };

    const handleInputChange = (platform, value, setter) => {
        setter(value);
        setErrors(prev => ({
            ...prev,
            [platform]: isValidURL(value) ? '' : ocmmSettings.validationURLErr,
        }));
    };

    const handleContentSave = () => {
        const newErrors = {};
        if (!isValidURL(facebook)) newErrors.facebook = ocmmSettings.validationURLErr;
        if (!isValidURL(xTwitter)) newErrors.xTwitter = ocmmSettings.validationURLErr;
        if (!isValidURL(instagram)) newErrors.instagram = ocmmSettings.validationURLErr;
        if (!isValidURL(linkedin)) newErrors.linkedin = ocmmSettings.validationURLErr;
        if (!isValidURL(youtube)) newErrors.youtube = ocmmSettings.validationURLErr;

        setErrors(newErrors);

        if (Object.values(newErrors).some(Boolean)) return;

        const updatedSettings = {
            ...settings,
            uc_headline: headline,
            uc_description: content,
            uc_seo_title: seoTitle,
            uc_seo_description: seoMeta,
            uc_copyright: copyright,
            uc_facebook_url: facebook,
            uc_twitter_url: xTwitter,
            uc_instagram_url: instagram,
            uc_linkedin_url: linkedin,
            uc_youtube_url: youtube,
            uc_subscribe_form: showSignup ? 'on' : 'off',
        };
        setIsSaving(true);

        saveSettings(updatedSettings).then((success) => {
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
    useEffect(() => {
        renderNotice();
    }, []); // only run once after initial render

    return (
        <>
            <div className="gv-p-fluid gv-pl-0">
                <BreaCrumbs childLabel={ocmmSettings.contentLabel} parentLabel="Maintenance Mode" />
                <h3 className="gv-mt-sm">{ocmmSettings.contentLabel}</h3>
                <p className="gv-mt-sm gv-text-sm">{ocmmSettings.contentDesc}</p>
            </div>
            <div id="oc-notice-placeholder"></div>
            <div className="gv-p-lg gv-surface-bright gv-stack-space-md">
                <Input
                    value={headline}
                    onChange={(e) => setHeadline(e.target.value)}
                    type="text"
                    placeholder={ocmmSettings.placeholder}
                    label={ocmmSettings.labelHeadline}
                />

                <RichTextEditorWithMedia
                    value={content}
                    onChange={(newContent) => setContent(newContent)}
                />
                <div className={!ocmmSettings.isPremium ? "gv-pos-relative gv-mwp-favicon" : ""}>
                    {!ocmmSettings.isPremium && (<Overlay
                        heading={ocmmSettings.mwpTitle}
                        message={ocmmSettings.mwpCountdownDesc}
                        overlayIcon={`${ocmmSettings.imageDIR}assets/images/mwp-lock.svg`}
                        handleUpgradeClick={() => handleUpgradeClick('oc_um_overlay')}
                        upgradeLabel={ocmmSettings.mwpUpsellBtn}
                        upgradeIcon={`${ocmmSettings.imageDIR}assets/images/expand_new.svg`}
                    />)}
                    <Input
                        value={seoTitle}
                        onChange={(e) => setSeoTitle(e.target.value)}
                        type="text"
                        label={ocmmSettings.labelSeo}
                        message={ocmmSettings.descriptionSeo}
                    />
                    <div className="gv-mb-md"></div>


                <Textarea
                    value={seoMeta}
                    onChange={(e) => setSeoMeta(e.target.value)}
                    label={ocmmSettings.labelSeoMeta}
                    message={ocmmSettings.descSeoMeta}
                />
                </div>

                <Input
                    value={copyright}
                    onChange={(e) => setCopyright(e.target.value)}
                    type="text"
                    label={ocmmSettings.labelcopyright}
                    placeholder={ocmmSettings.placeholderCopyright}
                />

                <Input value={facebook} onChange={(e) => handleInputChange('facebook', e.target.value, setFacebook)} type="text" label="Facebook" message={errors.facebook ? errors.facebook : ''} className={errors.facebook ? 'gv-error': ''}
                       messageType={errors.facebook ? 'error' : 'info'} />
                <Input value={xTwitter} onChange={(e) => handleInputChange('xTwitter', e.target.value, setXTwitter)} type="text" label="X (formerly Twitter)" message={errors.xTwitter ? errors.xTwitter : ''} className={errors.xTwitter ? 'gv-error': ''}
                       messageType={errors.xTwitter ? 'error' : 'info'}/>
                <Input value={instagram} onChange={(e) => handleInputChange('instagram', e.target.value, setInstagram)} type="text" label="Instagram" message={errors.instagram ? errors.instagram : ''} className={errors.instagram ? 'gv-error': ''}
                       messageType={errors.instagram ? 'error' : 'info'}/>
                <Input value={linkedin} onChange={(e) => handleInputChange('linkedin', e.target.value, setLinkedin)} type="text" label="LinkedIn" message={errors.linkedin ? errors.linkedin : ''} className={errors.linkedin ? 'gv-error': ''}
                       messageType={errors.linkedin ? 'error' : 'info'}/>
                <Input value={youtube} onChange={(e) =>handleInputChange('youtube', e.target.value, setYoutube)} type="text" label="YouTube" message={errors.youtube ? errors.youtube : ''} className={errors.youtube ? 'gv-error': ''}
                       messageType={errors.youtube ? 'error' : 'info'}/>

                <ToggleSwitch
                    checked={showSignup}
                    onChange={setShowSignup}
                    condensed={true}
                    label={ocmmSettings.labelsignup}
                    description={ocmmSettings.descriptionsignup}
                />

                <SettingsFooter
                    onBack={() => navigate('/')}
                    onSave={handleContentSave}
                    isSaving={isSaving}
                    backLabel={ocmmSettings.labelBack}
                    saveLabel={ocmmSettings.labelSave}
                />
            </div>
        </>
    )
}

export default Content;