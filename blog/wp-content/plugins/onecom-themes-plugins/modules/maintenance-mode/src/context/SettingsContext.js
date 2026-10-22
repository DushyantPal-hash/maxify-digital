// context/SettingsContext.js
import { createContext, useContext, useState, createRoot } from '@wordpress/element';
import {Notice} from "wpin-components-library";

const SettingsContext = createContext();

export const SettingsProvider = ({ children, initialSettings }) => {
    const [settings, setSettings] = useState(initialSettings || {});
    const [countDownState, setCountDownState] = useState(settings.uc_timer_switch);
    const [isSaving, setIsSaving] = useState(false);
    const [toastData, settoastData] = useState({message: "", type: "success"});


    // Standard updateSetting (no save)
    const updateSetting = (key, value) => {
        setSettings(prev => ({
            ...prev,
            [key]: value,
        }));
    };

    // Combined updater + immediate save
    const updateAndSaveSetting = async (key, value) => {
        const newSettings = { ...settings, [key]: value };
        await saveSettings(newSettings);
    };

    const handleUpgradeClick = (overlayId = 'oc_um_overlay') => {
        const overlay = document.getElementById(overlayId);
        if (overlay) {
            overlay.style.display = "flex";
        }
    };

    const saveSettings = async (customSettings = settings) => {
        setIsSaving(true);

        try {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({
                    action: 'ocmm_save_settings',
                    nonce: ocmmSettings.nonce,
                    settings: JSON.stringify(customSettings),
                }),
            });

            const result = await response.json();

            if (result.success) {
                setSettings(customSettings);
                console.log('Settings saved successfully');
                return true;
            } else {
                console.error(result.data?.message || 'Failed to save settings');
            }
        } catch (err) {
            console.error('Error saving settings:', err);
        } finally {
            setIsSaving(false);
        }
    };

    const renderNotice = () => {
        const placeholder = document.getElementById('oc-notice-placeholder');
        if (placeholder) {
            const root = createRoot(placeholder);
            root.render(
                <Notice
                    closable={true}
                    iconSrc={`${ocmmSettings.imageDIR}assets/images/icons/close.svg`}
                />
            );
        }
    };

    return (
        <SettingsContext.Provider value={{ settings,setSettings, updateSetting, updateAndSaveSetting, saveSettings, isSaving, setIsSaving,toastData,settoastData,
        countDownState,setCountDownState, renderNotice,handleUpgradeClick}}>
            {children}
        </SettingsContext.Provider>
    );
};

export const useSettings = () => useContext(SettingsContext);