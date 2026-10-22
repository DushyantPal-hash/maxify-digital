import {useState, useEffect, StrictMode} from '@wordpress/element';
import "@group.one/gravity";
import {createRoot} from "react-dom/client";
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import CountDown from "./components/CountDown";
import Content from "./components/Content";
import Home from "./Home";
import AdvanceSettings from "./components/AdvanceSettings";
import AllowListPostPages from "./components/AllowPostPages";
import Appearance from "./components/Appearance";
import { SettingsProvider } from './context/SettingsContext';


const initialSettings = ocmmSettings.initialSettings || {};

const App = () => {
return(
    <Router>
        <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/countdown" element={<CountDown />} />
            <Route path="/content" element={<Content />} />
            <Route path="/advancesettings" element={<AdvanceSettings />} />
            <Route path="/allowlist" element={<AllowListPostPages />} />
            <Route path="/appearance" element={<Appearance />} />
        </Routes>
    </Router>
    );


};

const rootElement = document.getElementById("oc-maintenance-mode-root");
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(
        // <StrictMode>
            <SettingsProvider initialSettings={initialSettings}>
            <App/>
            </SettingsProvider>
        // </StrictMode>
    );
}