import {useState, useEffect, createRoot} from "@wordpress/element";
import {useNavigate} from 'react-router-dom';
import {eventEmitter, Radio, Select, Overlay} from "wpin-components-library";
import 'wpin-components-library/dist/index.esm.css';
import BreaCrumbs from "./BreaCrumbs";
import {useSettings} from "../context/SettingsContext";
import SettingsFooter from "./SettingsFooter";

const CountDown = () => {
    const {settings, setSettings, updateSetting, saveSettings, isSaving,setIsSaving,handleUpgradeClick,countDownState,setCountDownState,renderNotice} = useSettings();
    const navigate = useNavigate();
    const [selectedValue, setSelectedValue] = useState(settings.uc_timer_action);
    const [countDownDate, setCountDownDate] = useState('');
    const [countDownTime, setCountDownTime] = useState('');
    const today = new Date().toISOString().split('T')[0];

    const timerValue = countDownDate && countDownTime
        ? `${countDownDate} ${countDownTime}`
        : ''; // Optional: fallback to empty if either is missing



    useEffect(() => {
        renderNotice();
    }, []); // only run once after initial render

    useEffect(() => {
        if (settings.uc_timer_action) {
            setSelectedValue(settings.uc_timer_action);
        }
    }, [settings.uc_timer_action])

    useEffect(() => {
        if (countDownState === 'on') {
            const now = new Date();
            const countdownDateTime = new Date(`${countDownDate}T${countDownTime || '00:00'}`);

            if (isNaN(countdownDateTime.getTime()) || countdownDateTime <= now) {
                const future = new Date();
                future.setDate(future.getDate() + 7);

                const futureDate = future.toISOString().split('T')[0]; // YYYY-MM-DD
                const futureTime = '00:00'; // or '12:00' if needed

                setCountDownDate(futureDate);
                setCountDownTime(futureTime);
            }
        }
    }, [countDownState]);

    const handlecountdownsave = () => {

        const updatedSettings = {
            ...settings, // merge with existing settings
            uc_timer_switch: countDownState,
            uc_timer_action: selectedValue,
            uc_timer: timerValue,
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
    };

    useEffect(() => {
        if (settings.uc_timer) {
            const [datePart, timePart] = settings.uc_timer.split(' ');
            setCountDownDate(datePart || '');
            setCountDownTime(timePart || '');
        }
    }, [settings.uc_timer]);

    return (
        <>
            <div className="gv-p-fluid gv-pl-0">
                <BreaCrumbs childLabel={ocmmSettings.countdownTitle} parentLabel="Maintenance Mode" />
                <h3 className="gv-mt-sm">{ocmmSettings.countdownTitle}</h3>
                <p className="gv-mt-sm gv-text-sm">{ocmmSettings.countdownDesc}</p>
            </div>
            <div id="oc-notice-placeholder"></div>
            <div className="gv-p-lg gv-surface-bright">
                <div className={`${!ocmmSettings.isPremium ? 'gv-pos-relative gv-mwp-favicon gv-max-mob-p-lg': ''}`}>
                {!ocmmSettings.isPremium && (<Overlay
                    heading={ocmmSettings.mwpTitle}
                    message={ocmmSettings.mwpCountdownDesc}
                    overlayIcon={`${ocmmSettings.imageDIR}assets/images/mwp-lock.svg`}
                    handleUpgradeClick={() => handleUpgradeClick('oc_um_overlay')}
                    upgradeLabel={ocmmSettings.mwpUpsellBtn}
                    upgradeIcon={`${ocmmSettings.imageDIR}assets/images/expand_new.svg`}
                /> )}
                <div className="gv-mode-condensed gv-mb-md gv-form-option">
                    <Radio
                        id='oc-count-active'
                        name='oc-count-status'
                        checked={countDownState === 'on'}
                        value="on"
                        onChange={(e) => setCountDownState(e.target.value)}
                        label={ocmmSettings.labelActive}
                    />
                </div>
                <div className="gv-mode-condensed gv-mb-md gv-form-option">
                    <Radio
                        id='oc-count-inactive'
                        name='oc-count-status'
                        checked={countDownState === 'off'}
                        value="off"
                        onChange={(e) => setCountDownState(e.target.value)}
                        label={ocmmSettings.labelInactive}
                    />
                </div>

                <div className="gv-mt-md">
                    <p className="gv-text-bold gv-text-sm gv-mb-sm">{ocmmSettings.labelDate}</p>
                    <div className="gv-flex gv-gap-md gv-max-mob-flex-col">
                        <div className="gv-input gv-input-date gv-with-addon">
                            <input
                                type="date"
                                value={countDownDate}
                                onChange={(e) => setCountDownDate(e.target.value)}
                                min={today}
                                className="gv-items-center"
                            />
                            <div className="gv-addon">
                                <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/date_range.svg`}></gv-icon>
                            </div>
                        </div>
                        <div className="gv-input gv-input-date gv-with-addon">
                            <input
                                type="time"
                                value={countDownTime || '00:00'}
                                onChange={(e) => setCountDownTime(e.target.value)}
                                className="gv-items-center"
                            />
                            <div className="gv-addon">
                                <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/schedule.svg`}></gv-icon>
                            </div>
                        </div>
                    </div>
                    <p className="gv-caption-lg gv-mt-xs" dangerouslySetInnerHTML={{ __html: ocmmSettings.countdownnotice }} />
                </div>

                    <div className="gv-mt-md">
                        <p className="gv-text-sm gv-text-bold gv-mb-sm">{ocmmSettings.countdownEnds}</p>

                        {ocmmSettings.countDownOptions.map((option, index) => (
                            <div key={index} className="gv-mode-condensed gv-mb-md gv-form-option">
                                <Radio
                                    id={`oc-count-option-${index}`}
                                    name="oc-countdown-options"
                                    value={option.value}
                                    checked={selectedValue === option.value}
                                    onChange={(e) => setSelectedValue(e.target.value)}
                                    label={option.label}
                                />
                            </div>
                        ))}
                    </div>

                <SettingsFooter
                    onBack={() => navigate('/')}
                    onSave={handlecountdownsave}
                    isSaving={isSaving}
                    backLabel={ocmmSettings.labelBack}
                    saveLabel={ocmmSettings.labelSave}
                />
            </div>
            </div>
        </>
    )
}

export default CountDown;