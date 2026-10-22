import { useContext } from '@wordpress/element';
import { ScanContext } from "../ScanContext";
import { Toast, eventEmitter } from "wpin-components-library";

const HeaderTile = ({ setActiveTab,onTabShortcutClick }) => {
	const { counts, score, isScanning, toastData, settoastData } = useContext(ScanContext);

	const handleShortcutClick = (event,tabName) => {
		event.preventDefault();
		if (tabName === 'vuln' && typeof onTabShortcutClick === 'function') {
			onTabShortcutClick('vuln'); // triggers the full AJAX logic
		} else if (typeof setActiveTab === 'function') {
			setActiveTab(tabName); // just switch tab without AJAX
		}
	};

	return (
		<>
			<Toast
				message={toastData.message}
				type={toastData.type}
				onClose={() => settoastData({ message: "", type: "" })}
				containerId="oc-error-toast"
				closeIcon={`${ocHMconstants.imageDIR}assets/images/close-white.svg`}
			/>

			<div className="gv-surface-bright gv-flex gv-justify-between gv-p-lg gv-mb-lg gv-max-mob-flex-col gv-desk-lg-items-center gv-max-mob-items-stretch gv-tab-flex-col gv-tab-items-stretch gv-desk-lg-flex-row">
				<div className="oc-score-wrap gv-tab-mb-md gv-desk-lg-mb-0">
					<div className="gv-option-inline">
						<div className="gv-label-with-tooltip">
							<label className="gv-label gv-label-with-icon gv-text-on-alternative">
								<span>{ocHMconstants.scoreLabel}</span>
								<div className="gv-tooltip-container gv-tooltip-top-center">
									<button className="gv-tooltip-button" aria-label="More info" aria-describedby="tooltip-id">
										<gv-icon src={`${ocHMconstants.imageDIR}assets/images/info.svg`} aria-hidden="true" />
									</button>
									<div id="tooltip-id" className="gv-tooltip gv-arrow-bottom-center" role="tooltip">
										<p>{ocHMconstants.scoreTooltip}</p>
									</div>
								</div>
							</label>
						</div>
					</div>
					<p className="oc-score gv-mt-xs">
						{isScanning ? (
							<div className="oc-score-skeleton gv-skeleton gv-heading-lg"></div>
						) : (
							<>
								<span className={`gv-heading-lg gv-ml-xs ${
									score <= 40 ? 'gv-state-error'
										: score <= 80 ? 'gv-state-warning'
											: 'gv-state-success'
								}`}>
									{score}%
								</span>
								<span className="gv-text-sm gv-ml-sm gv-text-on-alternative">{ocHMconstants.securityPerformanceNote}</span>
							</>
						)}
					</p>
				</div>

				<div className="gv-grid gv-gap-md gv-tab-lg-grid-cols-2 gv-flex-1 oc-hm-shortcuts gv-max-mob-mt-md">
					<div className="gv-shortcut gv-surface-dim gv-items-end gv-cursor-pointer">
						<div className="gv-content">
							<label className="gv-label gv-label-with-icon gv-mb-xs">{ocHMconstants.todoLabel}
								<div className="gv-tooltip-container gv-tooltip-top-center">
									<button className="gv-tooltip-button" aria-label="More info" aria-describedby="tooltip-todo">
										<gv-icon size="small" src={`${ocHMconstants.imageDIR}assets/images/info.svg`} aria-hidden="true" />
									</button>
									<div id="tooltip-todo" className="gv-tooltip gv-arrow-bottom-center" role="tooltip">
										<p>{ocHMconstants.todoTooltip}</p>
									</div>
								</div>
							</label>
							<p className="gv-caption-lg"><span className="gv-text-lg gv-text-bold gv-text-on-default"> {counts.todo}</span> {ocHMconstants.itemsText}</p>
						</div>
						<a href="#" onClick={(e) => handleShortcutClick(e, 'todo')}><gv-icon src={`${ocHMconstants.imageDIR}assets/images/arrow_forward.svg`} aria-hidden="true" /></a>
					</div>

					<div className="gv-shortcut gv-surface-dim gv-items-end gv-cursor-pointer">
						<div className="gv-content">
							<label className="gv-label gv-label-with-icon gv-mb-xs">{ocHMconstants.vulnLabel}
								<div className="gv-tooltip-container gv-tooltip-top-center">
									<button className="gv-tooltip-button" aria-label="More info" aria-describedby="tooltip-vuln">
										<gv-icon size="small" src={`${ocHMconstants.imageDIR}assets/images/info.svg`} aria-hidden="true" />
									</button>
									<div id="tooltip-vuln" className="gv-tooltip gv-arrow-bottom-center" role="tooltip">
										<p>{ocHMconstants.vulnTooltip}</p>
									</div>
								</div>
							</label>
							<p className="gv-caption-lg"><span className="gv-text-lg gv-text-bold gv-text-on-default">{ocHMconstants.vulncount}</span> {ocHMconstants.itemsText}</p>
						</div>
						<a href="#" onClick={(e) => handleShortcutClick(e, 'vuln')}><gv-icon src={`${ocHMconstants.imageDIR}assets/images/arrow_forward.svg`} aria-hidden="true" /></a>
					</div>
				</div>
			</div>
		</>
	);
};

export default HeaderTile;