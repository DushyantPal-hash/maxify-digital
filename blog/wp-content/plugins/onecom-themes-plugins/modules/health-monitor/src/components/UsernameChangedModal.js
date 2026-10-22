import { useContext } from '@wordpress/element';
import { ScanContext } from "../ScanContext";
import { sprintf } from '@wordpress/i18n';

const UsernameChangedModal = () => {
	const {
		showUsernameSuccessModal,
		usernameChangeInfo,
		closeUsernameSuccessModal
	} = useContext(ScanContext);

	if (!showUsernameSuccessModal) return null;

	const { oldUsername, newUsername } = usernameChangeInfo;

	const message = sprintf(ocHMconstants.usernameChanged, oldUsername, newUsername);

	const raw = sprintf(
		ocHMconstants.usernameChanged,
		`__OLD__`,
		`__NEW__`
	);

	// Split and replace markers with JSX <strong>
	const parts = raw
		.replace('__OLD__', `<old />`)
		.replace('__NEW__', `<new />`)
		.split(/(<old \/>|<new \/>)/);

	return (
		<div className="gv-modal">
			<div className="gv-modal-content">
				<div className="gv-modal-body">
					<h2 className="gv-modal-title">{ocHMconstants.usermodaltitle}</h2>
					<p>
						{parts.map((part, i) => {
							if (part === '<old />') return <strong key={i}>{oldUsername}</strong>;
							if (part === '<new />') return <strong key={i}>{newUsername}</strong>;
							return part;
						})}
					</p>
				</div>
				<div className="gv-button-group">
					<button
						type="button"
						className="gv-button gv-button-primary"
						onClick={closeUsernameSuccessModal}
					>
						{ocHMconstants.labelLogin}
					</button>
				</div>
			</div>
		</div>
	);
};

export default UsernameChangedModal;