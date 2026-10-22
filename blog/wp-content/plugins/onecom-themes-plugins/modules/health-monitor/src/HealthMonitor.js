import {StrictMode, createRoot, useState,useContext,useRef,useEffect} from '@wordpress/element';
import "@group.one/gravity";
import {ScanProvider, ScanContext} from "./ScanContext";
import HeaderTile from "./components/HeaderTile";
import Tabs from "./components/Tabs";
import AuditsList from "./components/AuditsList";
import { initializeVmDataTable } from "./helpers/DataTableInit"
import UsernameChangedModal from "./components/UsernameChangedModal";

const App = () => {
	const [activeTab, setActiveTab] = useState(() => {
		if (window.location.hash === '#vm-page') {
			return 'vuln';
		}
		const params = new URLSearchParams(window.location.search);
		return params.get('tab') || 'todo';
	});
	const [dynamicContent, setDynamicContent] = useState('');
	const { counts } = useContext(ScanContext);


	const tabs = [
		{key: 'todo', label: ocHMconstants.labelTodo, count: counts.todo , statsClass:'ocwp_ocp_plugins_onecom_plugins_tab_visited_event'},
		{
			key: 'done',
			label: ocHMconstants.labelDone,
			count: counts.done,
			statsClass:'ocwp_ocp_plugins_recommended_tab_visited_event'
		},
		{key: 'ignored', label: ocHMconstants.labelIgnore, count: counts.ignored,
			statsClass:'ocwp_ocp_plugins_discouraged_tab_visited_event'},

		{key: 'vuln', label: ocHMconstants.labelVulnerabilities, count: ocHMconstants.vulncount || 0,
			statsClass:'ocwp_ocp_plugins_discouraged_tab_visited_event'},
	];
	const hasLoadedTemplate = useRef(false);
	const hasInitializedDataTable = useRef(false);

	const handleTabChange = async (key) => {
		setActiveTab(key);

		if (key === 'vuln' && !hasLoadedTemplate.current) {
			try {
				const response = await fetch(ajaxurl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						action: 'ocsh_load_tab_template',
					}),
				});

				const result = await response.json();
				if (result.success) {
					setDynamicContent(result.data);
					hasLoadedTemplate.current = true;

					setTimeout(() => {
						if (typeof window.OCVM_INIT_WRAPPER === 'function') {
							window.OCVM_INIT_WRAPPER();
						}
						initializeVmDataTable(hasInitializedDataTable);
					}, 0);
				}
			} catch (err) {
				console.error('Failed to load PHP tab content', err);
			}
		}
	};

	// If the page was opened with the #vm-page hash, trigger vuln tab content load
	useEffect(() => {
		if (window.location.hash === '#vm-page') {
			handleTabChange('vuln');
		}
	}, []);

	return (
		<>
			<UsernameChangedModal/>
			<HeaderTile setActiveTab={setActiveTab} onTabShortcutClick={handleTabChange} />
			<Tabs tabs={tabs} setActiveTab={handleTabChange} activeTab={activeTab} setDynamicContent={setDynamicContent} dynamicContent={dynamicContent} iconSrc={`${ocHMconstants.imageDIR}assets/images/expand_more.svg`}/>
			<AuditsList activeTab={activeTab} key={activeTab} dynamicContent={dynamicContent}/>
		</>
	);
};

const rootElement = document.getElementById("oc-hm-root");
if (rootElement) {
	const root = createRoot(rootElement);
	root.render(
		<StrictMode>
			<ScanProvider>
				<App/>
			</ScanProvider>
		</StrictMode>
	);
}

import DiscouragedPluginsPage from "./components/DiscouragedPluginsPage";

const discouragedRoot = document.getElementById("oc-discouraged-root");
if (discouragedRoot) {
	const root = createRoot(discouragedRoot);
	root.render(
		<StrictMode>
			<DiscouragedPluginsPage />
		</StrictMode>
	);
}