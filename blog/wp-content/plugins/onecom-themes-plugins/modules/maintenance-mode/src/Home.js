import {useState} from "@wordpress/element";

import {Button, ToggleSwitch, Toast} from "wpin-components-library";
import Shortcut from "./components/Shortcut";
import {useSettings} from "./context/SettingsContext";

const Home = () => {
    const {settings, setSettings, saveSettings, isSaving,countDownState} = useSettings();

    const [buttonText, setButtonText] = useState(null);

    const [toastData, settoastData] = useState({message: "", type: "success"});

    const updateAdminBarStatus = (status) => {
        const adminBarItem = document.getElementById('wp-admin-bar-ocuc_options');

        if (!adminBarItem) return;

        const link = adminBarItem.querySelector('a');
        if (!link) return;

        const isOn = status === 'on';

        // Update class
        adminBarItem.classList.remove('ocuc_setting_is_on', 'ocuc_setting_is_off');
        adminBarItem.classList.add(isOn ? 'ocuc_setting_is_on' : 'ocuc_setting_is_off');

        // Update title attribute
        link.setAttribute('title',  `${isOn ? ocmmSettings.headerMenuTitle : ocmmSettings.headerMenuOFF}`);

        // Update text content
        link.innerHTML = isOn
            ? ocmmSettings.headerMenuOn
            : ocmmSettings.headerMenuOFF;
    };

    const handleClick = (status) => {
        const isActivating = status === 'on';
        setButtonText(isActivating ? ocmmSettings.labelActivating : ocmmSettings.labelDeactivating);

        const updatedSettings = {...settings, uc_status: status};

        saveSettings(updatedSettings).then((success) => {
            if (success) {
            setSettings(updatedSettings);
                updateAdminBarStatus(status);
                setSettings(updatedSettings);
                if (status === 'on') {
                    settoastData({
                        message: ocmmSettings.activationSuccess,
                        type: "success"
                    });
                } else {
                    settoastData({
                        message: ocmmSettings.deactivationSuccess,
                        type: "success"
                    });
                }
            } else {
                if (status === 'on') {
                    settoastData({
                        message: ocmmSettings.activationFail,
                        type: "alert"
                    });
                } else {
                    settoastData({
                        message: ocmmSettings.deactivationFail,
                        type: "alert"
                    });
                }
            }
        }).catch((error) => {
            settoastData({
                message: error?.data?.message || ocmmSettings.activationFail || "An error occurred while saving settings.",
                type: "alert"
            });
        }).finally(() => {
            setButtonText(null); // Reset button text after save
        });

    };

    const isMaintenanceOn = settings.uc_status === 'on';
    const [savingToggles, setSavingToggles] = useState({});

    const toggleMessages = {
        uc_http_mode: {
            trueValue: '200',
            messages: {
                success: {
                    on: ocmmSettings.httpModeOff,
                    off: ocmmSettings.httpModeOn,
                },
                error: {
                    on: ocmmSettings.httpModeOffError,
                    off: ocmmSettings.httpModeOnError
                }
            }
        },
        uc_whitelisted_roles: {
            trueValue: 'true',
            messages: {
                success: {
                    on: ocmmSettings.allowUserOn,
                    off: ocmmSettings.allowUserOff
                },
                error: {
                    on: ocmmSettings.allowUserOnError,
                    off: ocmmSettings.allowUserOffError
                }
            }
        }
    };
    const handleToggleChange = (key, checked, trueValue, falseValue) => {
        const newValue = checked ? trueValue : falseValue;
        const updatedSettings = { ...settings, [key]: newValue };
        setSavingToggles(prev => ({ ...prev, [key]: true }));
        setSettings(updatedSettings);
        const isTurningOn = newValue === toggleMessages[key].trueValue;
        const messages = toggleMessages[key].messages;
        saveSettings(updatedSettings)
            .then((success) => {
                if (success) {
                    settoastData({
                        message: isTurningOn ? messages.success.on : messages.success.off,
                        type: "success"
                    });
                }else{
                    settoastData({
                        message: isTurningOn ? messages.error.on : messages.error.off,
                        type: "alert"
                    });
                    // Rollback (optional) if save fails
                    setSettings(settings);
                }
            })
            .catch(() => {
                settoastData({
                    message: isTurningOn ? messages.error.on : messages.error.off,
                    type: "alert"
                });
            })
            .finally(() => {
                setSavingToggles(prev => ({ ...prev, [key]: false }));
            });
    };

    return (
        <>
            <Toast
                message={toastData.message}
                type={toastData.type}
                onClose={() => settoastData({message: "", type: ""})}
                containerId="oc-error-toast"
                closeIcon={`${ocmmSettings.imageDIR}assets/images/close-white.svg`}
            />
            <div className="oc-uc-head-wrap gv-flex gv-max-mob-flex-col gv-items-center gv-justify-between">
                <div className="gv-p-fluid gv-pl-0">
                    <h3>Maintenance Mode</h3>
                    <p className="gv-mt-sm">
                        {ocmmSettings.mmTagline}
                    </p>
                </div>

                {isMaintenanceOn ? (
                    <Button
                        className="gv-button-secondary gv-max-mob-mb-lg gv-max-mob-mode-condensed"
                        type="button"
                        disabled={isSaving}
                        onClick={() => handleClick('off')}
                    >
                        <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/power_rounded.svg`}/>
                        <span>{buttonText || ocmmSettings.labelDeactivate}</span>
                    </Button>
                ) : (
                    <Button
                        className="gv-button-primary gv-max-mob-mb-lg gv-max-mob-mode-condensed"
                        type="button"
                        disabled={isSaving}
                        onClick={() => handleClick('on')}
                    >
                        <span>{buttonText || ocmmSettings.labelActivate}</span>
                    </Button>
                )}

            </div>
            <div className="gv-p-lg gv-surface-bright gv-radius">
                <div className="gv-flex gv-items-center gv-justify-between gv-gap-lg gv-max-mob-flex-col">
                    <div className="oc-left-section gv-w-full">
                        <h4>{ocmmSettings.siteTitle}</h4>
                        <div className="gv-text-indicator gv-mt-sm gv-mb-lg">
                            <div
                                className={`gv-indicator ${isMaintenanceOn ? 'gv-state-positive' : 'gv-state-informative'}`}></div>
                            <span>{isMaintenanceOn ? ocmmSettings.activeMsg : ocmmSettings.notactiveMsg}</span>
                        </div>
                        <div className="gv-stack-space-md">
                            <ToggleSwitch checked={settings.uc_http_mode !== '200'} onChange={(checked) => handleToggleChange('uc_http_mode', checked, '503', '200')}
                                          label={ocmmSettings.hideLabel} description={ocmmSettings.hideDescription}
                                          condensed={true} disabled={!!savingToggles.uc_http_mode} />

                            <ToggleSwitch checked={settings.uc_whitelisted_roles === 'true'} onChange={(checked) => handleToggleChange('uc_whitelisted_roles', checked, 'true', 'false')}
                                          label={ocmmSettings.allowLabel} description={ocmmSettings.allowDescription}
                                          condensed={true}
                                          disabled={!!savingToggles.uc_whitelisted_roles} />
                        </div>
                    </div>
                    <div className="oc-right-section">
                        <img src={`${ocmmSettings.imageDIR}assets/images/right-section.png`}/>
                    </div>
                </div>

            </div>

            <div className="gv-mt-lg oc-mm-lower">
                <p className="gv-text-bold gv-text-lg gv-mb-md">{ocmmSettings.optionsLabel}</p>
                <div className="gv-grid gv-gap-md gv-tab-lg-grid-cols-2 gv-desk-lg-grid-cols-2">
                    <Shortcut href='/countdown' icon={`${ocmmSettings.imageDIR}assets/images/icons/timer.svg`}
                              title={ocmmSettings.countdownLabel} description={ocmmSettings.countdownDesc}
                              showIndicator={true} statusText={countDownState === 'on' ? ocmmSettings.labelActive : ocmmSettings.labelInactive} statusClass={countDownState === 'on' ? 'gv-active': 'gv-inactive-prio-low'}/>
                    <Shortcut href='/content' icon={`${ocmmSettings.imageDIR}assets/images/icons/format_align_left.svg`}
                              title={ocmmSettings.contentLabel} description={ocmmSettings.contentShortcut}/>
                    <Shortcut href='/appearance' icon={`${ocmmSettings.imageDIR}assets/images/icons/format_paint.svg`}
                              title={ocmmSettings.appearanceLabel} description={ocmmSettings.appearanceDesc}/>
                    <Shortcut href='/allowlist' icon={`${ocmmSettings.imageDIR}assets/images/icons/allow.svg`}
                              title={ocmmSettings.allowTitle} description={ocmmSettings.allowDesc}/>
                    <Shortcut href='/advancesettings' icon={`${ocmmSettings.imageDIR}assets/images/icons/settings.svg`}
                              title={ocmmSettings.advanceTitle} description={ocmmSettings.advanceDesc}/>
                </div>
            </div>
        </>
    )
        ;
}

export default Home;