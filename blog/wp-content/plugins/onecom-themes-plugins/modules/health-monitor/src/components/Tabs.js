import { useEffect, useRef } from "@wordpress/element";
const Tabs = ({
	activeTab,
	setActiveTab,
	tabs,
	setDynamicContent,
	dynamicContent,
	showCount = true,
	iconSrc = '',
}) => {


	return (
		<>
			{/* Mobile Select */}
			<div className="gv-input gv-input-select gv-tab-select">
				<select onChange={(e) => setActiveTab(e.target.value)} value={activeTab}>
					{tabs.map(({ key, label, count }) => (
						<option key={key} value={key}>
							{label} {showCount && <span className="count">({count})</span>}
						</option>
					))}
				</select>
				{iconSrc && <gv-icon src={iconSrc}></gv-icon>}
			</div>

			{/* Desktop Tabs */}
			<div role="tablist" className="gv-tab-list">
				{tabs.map(({ key, label, count, statsClass }) => (
					<button
						role="tab"
						key={key}
						onClick={() => setActiveTab(key)}
						className={`${statsClass || ''} ${activeTab === key ? 'gv-tab-active gv-tab' : 'gv-tab'}`}
						aria-selected={activeTab === key ? 'true' : 'false'}
					>
						<span className="gv-tab-content">{label}</span>
						<span className="gv-tab-counter">{count}</span>
					</button>
				))}
			</div>
		</>
	);
};

export default Tabs;