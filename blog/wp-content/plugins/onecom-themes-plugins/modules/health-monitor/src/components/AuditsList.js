import { useState, useContext } from '@wordpress/element';
import { ScanContext } from "../ScanContext";
import TodoHeader from './TodoHeader';
import NoAudits from "./NoAudits";
import UsernameList from './UserNameList';

const AuditsList = ({ activeTab, dynamicContent }) => {
	const { scanResults, handleAlwaysIgnore, handleFix, handleUndo, loadingItem,handleUsernameSave,handleDeleteBackupFile } = useContext(ScanContext);

	const [deletingIndexes, setDeletingIndexes] = useState(new Set());

	const [expandedIndices, setExpandedIndices] = useState([]);

// toggle handler
	const handleToggle = (check) => {
		setExpandedIndices((prev) => {
			if (prev.includes(check)) {
				// if it's already open, remove it (close)
				return prev.filter((c) => c !== check);
			} else {
				// otherwise, add it (open)
				return [...prev, check];
			}
		});
	};
	const handleClick = async (e, item, idx, file) => {
		e.preventDefault();

		setDeletingIndexes((prev) => new Set(prev).add(idx));

		await handleDeleteBackupFile(item, file, idx, activeTab);

		setDeletingIndexes((prev) => {
			const newSet = new Set(prev);
			newSet.delete(idx);
			return newSet;
		});
	};

	const renderButton = (btn) => {
		if (!btn || typeof btn !== 'object' || Object.keys(btn).length === 0) return null;

		const commonProps = btn.data?.check ? { 'data-check': btn.data.check } : {};

		switch (btn.type || '') {
			case '':
				return (
					<button
						type="button"
						className="gv-button gv-button-secondary gv-mr-md gv-max-mob-mr-0"
						onClick={() => handleAlwaysIgnore(btn)}
						{...commonProps}
					>
						{btn.link_text || btn.text}
					</button>
				);

			case 'button':
				return (
					<button
						type="button"
						className="gv-button gv-button-primary gv-max-mob-mb-md gv-max-mob-order-first"
						onClick={() => handleFix(btn.data, activeTab)}
						{...commonProps}
					>
						{btn.text}
					</button>
				);

			case 'link':
				return (
					<a
						href={btn.url}
						className={`${btn.class} gv-button gv-button-primary gv-max-mob-mb-md gv-max-mob-order-first`}
						target="_blank"
						rel="noopener noreferrer"
						{...commonProps}
					>
						{btn.text}
					</a>
				);

			case 'install_plugin':
				return (
					<a
						href={btn.install_url}
						className="gv-button gv-button-primary gv-max-mob-mb-md gv-max-mob-order-first"
						target="_blank"
						rel="noopener noreferrer"
					>
						{btn.text}
					</a>
				);

			case 'activate_plugin':
				return (
					<a
						href={btn.activate_url}
						className="activate-now gv-button gv-button-primary gv-max-mob-mb-md gv-max-mob-order-first"
						target="_blank"
						rel="noopener noreferrer"
					>
						{btn.text}
					</a>
				);

			case 'settings_link':
				return (
					<a
						href={btn.settings_url}
						className="oc-fix-button gv-button gv-button-primary gv-max-mob-mb-md gv-max-mob-order-first"
						target="_blank"
						rel="noopener noreferrer"
					>
						{btn.text}
					</a>
				);

			case 'undo':
				return (
					<a
						href="#"
						className={btn.class}
						onClick={(e) => {
							e.preventDefault();
							handleUndo(btn.data?.check);
						}}
						{...commonProps}
					>
						{btn.text}
					</a>
				);

			default:
				return null;
		}
	};



	const getResolveOrUndoButton = (item) => {
		const btn = item.status.status_key !== 0 ? item.resolve_button : item.undo_button;
		return btn && Object.keys(btn).length ? renderButton(btn) : null;
	};

	const tasks = scanResults[activeTab] || [];

	if (activeTab === 'vuln') {
		return (
			<div id="ocvm-parent-wrap" className="ocvm_settings_wrap gv-surface-bright gv-p-lg">
				{!dynamicContent ? (
					<div className="gv-stack-space-md">
						<div className="gv-skeleton gv-heading-md"></div>
						<div className="gv-skeleton"></div>
						<div className="gv-skeleton"></div>
					</div>
				) : (
					<div dangerouslySetInnerHTML={{ __html: dynamicContent }} />
				)}
			</div>
		);
	}

	return (
		<>
			<TodoHeader activeTab={activeTab} />
			{tasks.length > 0 ? (
				<ul className="gv-to-do gv-task-accordion gv-surface-bright">
					{tasks.map((item, index) => {
						// console.log(item);

						const isExpanded = (check) => expandedIndices.includes(check);

						return (
							<li key={item.check} className="gv-item gv-pos-relative gv-mb-0">
								{loadingItem === item.check && (
									<div className="gv-oc-overlay">
										<gv-loader class="gv-mode-condensed" src={`${ocHMconstants.imageDIR}assets/images/spinner.svg`} />
										<p>{ocHMconstants.actionInProgress}</p>
									</div>
								)}
								<button
									id={`trigger-${item.check}`}
									className={`gv-trigger ${isExpanded(item.check) ? 'gv-expanded' : ''}`}
									aria-expanded={isExpanded(item.check)}
									aria-controls={`body-${item.check}`}
									onClick={() => handleToggle(item.check)}
								>
									<div className="gv-trigger-content">
										{activeTab !== 'done' && (
											<div className="gv-badge gv-badge-generic">{item.category}</div>
										)}
										<div className="gv-text">
											<h3 className="gv-title">{item.action_title}</h3>
										</div>
									</div>
									<gv-icon
										src={`${ocHMconstants.imageDIR}assets/images/keyboard_arrow_down.svg`}
										aria-hidden="true"
									/>
								</button>
								<div
									id={`body-${item.check}`}
									aria-labelledby={`trigger-${item.check}`}
									className={`gv-body ${isExpanded(item.check) ? '' : 'gv-hidden'}`}
								>
									<div className="gv-body-content">
										<p className="gv-description" dangerouslySetInnerHTML={{ __html: item.overview }} />
										<p className="gv-text-sm gv-text-bold oc-hmstatus-label">{ocHMconstants.labelStatus}</p>
										<p className={`gv-description ${item.check === 'usernames' ? 'gv-mb-0' : ''}`} dangerouslySetInnerHTML={{ __html: item.status.status_desc }} />
										{Array.isArray(item.check_result.list_data) && item.check_result.list_data.length > 0 && (
											<>
												{item.check !== 'usernames' ? (
													<ul className="gv-list-items gv-list-bullet gv-mode-condensed">
														{item.check_result.list_data.map((entry, idx) => (
															<li key={idx}>
																{entry.text}
																{entry.can_delete && (
																	<a
																		href="#"
																		className="ocsh-delete-link"
																		onClick={(e) => handleClick(e, item, idx, entry.file)}
																		style={{
																			pointerEvents: deletingIndexes.has(idx) ? 'none' : 'auto',
																			opacity: deletingIndexes.has(idx) ? 0.5 : 1,
																		}}
																	>
																		{ocHMconstants.labelDelete}
																	</a>
																)}
															</li>
														))}
													</ul>
												):(item.check === 'usernames' &&
													<ul className="gv-list-items gv-list-bullet gv-mode-condensed">
														{item.check_result.list_data.map((entry, idx) => (
															<li key={idx}>
																{entry.user}
															</li>
														))}
													</ul>
												)}
											</>
										)}
										{item.status.how_to_fix_text && (
											<>
												<p className="gv-text-sm gv-text-bold oc-hmstatus-label">{item.status.how_to_fix_title}</p>
												<p className="gv-text-sm" dangerouslySetInnerHTML={{ __html: item.status.how_to_fix_text }} />
											</>
										)}
									</div>
									<div className="ocsh-actions gv-pl-lg gv-max-mob-pr-lg gv-max-mob-pl-lg gv-max-mob-flex gv-max-mob-flex-col gv-body-content">
										{getResolveOrUndoButton(item)}
										{(item.check === 'usernames' && item.check_result.list_data.some(entry => entry.user === ocHMconstants.currentUser)) ? (
											<UsernameList
												parentItem={item}
												items={item.check_result.list_data}
												saveLabel={ocHMconstants.labelConfirm}
												onSave={handleUsernameSave}
												activeTab={activeTab}
											/>
										) : (
											<>
												{renderButton(item.fix_button)}
											</>
										)}
										{item.upsell_text && (
											<div className="ocsh-upsell" dangerouslySetInnerHTML={{ __html: item.upsell_text }} />
										)}
									</div>

								</div>
							</li>
						);
					})}
				</ul>
			) : (
				<NoAudits activeTab={activeTab} />
			)}
		</>
	);
};

export default AuditsList;