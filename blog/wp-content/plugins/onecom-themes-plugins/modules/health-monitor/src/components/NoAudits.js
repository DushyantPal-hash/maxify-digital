const NoAudits = ({ activeTab }) => {
	const messages = window.ocHMconstants?.no_audits_messages || {};
	const fallback = messages.fallback || {
		title: 'Nothing here',
		description: 'There is no data to display.',
	};

	const { title, description } = messages[activeTab] || fallback;

	return (
		<div className="gv-content-container gv-surface-bright gv-p-fluid gv-text-center">
			<h5 className='gv-mb-sm'>{title}</h5>
			<p>{description}</p>
		</div>
	);
};

export default NoAudits;