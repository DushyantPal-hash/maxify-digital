import { useState, useEffect } from '@wordpress/element';
import { Toast } from "wpin-components-library";

const DiscouragedPluginsPage = () => {
	const vars = window.ocHMdiscouragedVars || {};
	const [plugins, setPlugins] = useState([]);
	const [loading, setLoading] = useState(true);
	const [isLoading, setIsLoading] = useState(false);
	const [loadingPlugin, setLoadingPlugin] = useState('');
	const [toastData, setToastData] = useState({ message: '', type: '' });

	useEffect(() => {
		fetchDiscouragedPlugins();
	}, []);

	const fetchDiscouragedPlugins = async () => {
		setLoading(true);
		try {
			const response = await fetch(vars.ajax_url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({ action: 'onecom_fetch_plugins', type: 'discouraged' }),
			});
			const result = await response.json();
			if (result.success && result.data?.plugins) {
				setPlugins(result.data.plugins.flat());
			}
		} catch (error) {
			console.error('Error fetching discouraged plugins', error);
		} finally {
			setLoading(false);
		}
	};

	const handleDeactivate = async (plugin) => {
		setIsLoading(true);
		setLoadingPlugin(plugin.name);
		try {
			const response = await fetch(vars.ajax_url, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: new URLSearchParams({
					action: 'onecom_deactivate_plugin',
					plugin_slug: plugin.slug,
					plugin_name: plugin.name,
					plugin_type: plugin.pluginType || 'discouraged',
				}),
			});
			const result = await response.json();
			if (result.success || result.status === 'success' || result.type === 'success') {
				const remaining = plugins.filter(p => p.slug !== plugin.slug);
				if (remaining.length === 0) {
					await fetch(vars.ajax_url, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({ action: 'ocsh_fix_dis_plugin', check: 'dis_plugin' }),
					});
				}
				setPlugins(remaining);
				setIsLoading(false);
				setLoadingPlugin('');
				setToastData({ type: 'success', message: vars.successMessage });
				setTimeout(() => {
					window.location.reload();
				}, 4000);
			} else {
				setIsLoading(false);
				setLoadingPlugin('');
				setToastData({ type: 'alert', message: result.message || vars.errorMessage });
			}
		} catch (error) {
			console.error('Deactivation failed:', error);
			setIsLoading(false);
			setLoadingPlugin('');
			setToastData({ type: 'alert', message: vars.errorMessage });
		}
	};

	const renderContent = () => {
		if (loading) {
			return (
				<div className="gv-grid gv-gap-lg gv-tab-grid-cols-2 gv-desk-grid-cols-2 gv-mt-fluid">
					<div className="gv-card gv-surface-bright gv-p-lg gv-stack-space-md">
						<div className="gv-skeleton gv-heading-md"></div>
						<div className="gv-skeleton gv-heading-md"></div>
						<div className="gv-skeleton gv-heading-md" style={{'width':'100px'}}></div>
					</div>

					<div className="gv-card gv-surface-bright gv-p-lg gv-stack-space-md">
						<div className="gv-skeleton gv-heading-md"></div>
						<div className="gv-skeleton gv-heading-md"></div>
						<div className="gv-skeleton gv-heading-md" style={{'width':'100px'}}></div>
					</div>
				</div>
			);
		}

		if (plugins.length === 0) {
			return (
				<div className="gv-content-container gv-surface-bright gv-p-fluid gv-text-center">
					<h5 className="gv-mb-sm">{vars.wellDone}</h5>
					<p>{vars.noDiscouragedPlugins}</p>
				</div>
			);
		}

		return (
			<div className="gv-grid gv-gap-lg gv-tab-grid-cols-1 gv-desk-grid-cols-2 gv-mt-md">
				{plugins.map((plugin) => (
					<PluginCard
						key={plugin.slug}
						plugin={plugin}
						onDeactivate={handleDeactivate}
					/>
				))}
			</div>
		);
	};

	return (
		<>
			{isLoading && (
				<div className="loading-overlay show">
					<div className="gv-loader-container">
						<gv-loader src={`${vars.imageURL}assets/images/spinner.svg`}></gv-loader>
						<p>{vars.deactivatingLabel} {loadingPlugin}</p>
					</div>
				</div>
			)}
			<Toast
				message={toastData.message}
				type={toastData.type}
				onClose={() => setToastData({ message: '', type: '' })}
				containerId="oc-discouraged-toast"
				closeIcon={`${vars.imageURL}assets/images/close-white.svg`}
			/>
			<BackButton />
			<Header />
			{renderContent()}
		</>
	);
};

const BackButton = () => {
	const vars = window.ocHMdiscouragedVars || {};
	return (
		<nav className="gv-breadcrumbs gv-area-nav">
			<a
				href={vars.hmPageURL}
				className="gv-flex gv-items-center gv-gap-xs"
			>
				<img
					style={{ minWidth: '24px' }}
					className="gv-tile"
					src={`${vars.imageURL}assets/images/arrow_back.svg`}
					alt="Back"
				/>
				<span>{vars.backToHM}</span>
			</a>
		</nav>
	);
};

const Header = () => {
	const vars = window.ocHMdiscouragedVars || {};
	return (
		<div className="gv-mt-md">
			<div className="oc-header-wrap">
				<p className="gv-heading-lg">{vars.headingDiscouragedPlugins}</p>
				<div className="gv-mode-condensed">
					<a
						className="gv-button gv-button-secondary gv-max-mob-hidden"
						href={vars.discouragedListUrl}
						target="_blank"
						rel="noopener noreferrer"
					>
						<span>{vars.viewDiscouragedPlugins}</span>
						<gv-icon src={`${vars.imageURL}assets/images/open_in_new.svg`}></gv-icon>
					</a>
				</div>
			</div>
			<p className="gv-mt-sm gv-mb-md gv-text-sm gv-w-max-form">{vars.discouragedPluginDesc}</p>
			<div className="gv-mode-condensed">
				<a
					className="gv-button gv-button-secondary gv-desk-hidden gv-tab-hidden gv-mb-md"
					href={vars.discouragedListUrl}
					target="_blank"
					rel="noopener noreferrer"
				>
					<span>{vars.viewDiscouragedPlugins}</span>
					<gv-icon src={`${vars.imageURL}assets/images/open_in_new.svg`}></gv-icon>
				</a>
			</div>
		</div>
	);
};

const PluginCard = ({ plugin, onDeactivate }) => {
	const vars = window.ocHMdiscouragedVars || {};
	return (
		<div id={`plugin-${plugin.slug}`} className="gv-card oc-plugins-box gv-surface-bright gv-pb-lg">
			<div className="gv-card-illustration">
				<img className="gv-tile" src={plugin.thumbnail} alt={plugin.name} width="72" height="72" />
			</div>
			<div className="gv-card-content">
				<h3 className="gv-card-title">{plugin.name}</h3>
				<p>
					{plugin.description || plugin.shortDescription}
					&nbsp;&nbsp;
					<a
						href={`plugin-install.php?tab=plugin-information&plugin=${plugin.slug}&TB_iframe=true&width=772&height=521`}
						className="thickbox open-plugin-details-modal gv-action"
						title={vars.moreDetailsLabel}
					>
						{vars.moreDetailsLabel}
					</a>
				</p>
				<span className="oc-plugin-authors">
					<cite dangerouslySetInnerHTML={{ __html: `By ${plugin?.author}` }} />
				</span>
			</div>
			<div className="plugin-actions gv-card-content">
				<button
					className="gv-button gv-button-secondary"
					onClick={() => onDeactivate(plugin)}
				>
					{vars.deactivateLabel}
				</button>
			</div>
		</div>
	);
};

export default DiscouragedPluginsPage;
