import { useState } from '@wordpress/element';


const UsernameList = ({ items, saveLabel, onSave, parentItem, ...args }) => {
	const [editing, setEditing] = useState({});
	const [newUsernames, setNewUsernames] = useState({});
	const toggleEdit = (user) => {
		setEditing((prev) => ({
			...prev,
			[user]: !prev[user],
		}));
	};

	const [errors, setErrors] = useState({});

	const closeModal = (user) => {
		setEditing((prev) => ({
			...prev,
			[user]: false,
		}));
	};

	const handleChange = (user, value) => {
		setNewUsernames((prev) => ({
			...prev,
			[user]: value,
		}));
	};
	const usernameRegex = /^[a-zA-Z0-9_\-.@]+$/;

	const handleSave = async (userObj, newUsername) => {
		let error = '';

		if (!newUsername.trim()) {
			error = ocHMconstants.errEmptyUsername;
		} else if (!usernameRegex.test(newUsername)) {
			error = ocHMconstants.errUsernameFormat;
		} else if (newUsername === userObj.user) {
			error = ocHMconstants.errorDuplicate;
		}

		if (error) {
			setErrors(prev => ({ ...prev, [userObj.user]: error }));
			return;
		}

		// Clear error before proceeding
		setErrors(prev => ({ ...prev, [userObj.user]: '' }));


		await onSave(parentItem, userObj.user, newUsername, ocHMconstants.userNonce, args.activeTab);
		// Close the edit modal
		closeModal(userObj.user);
	};

	return (
		<>
			{items.map((item, index) => (
				item.user === ocHMconstants.currentUser && (
				<>
					<a
						className={` gv-mr-md gv-button ${ocHMconstants.isPremium? 'gv-ml-md gv-button-primary' : 'gv-button-primary'}`}
						onClick={() => toggleEdit(item.user)}
					>
						{item.label}
					</a>

					{editing[item.user] && (
						<div className="gv-modal">
							<div className="gv-modal-content">
								<button className="gv-modal-close" onClick={() => closeModal(item.user)}>
									<gv-icon src={`${ocHMconstants.imageDIR}assets/images/close-modal.svg`}></gv-icon>
								</button>
								<div className="gv-modal-body">
									<h2 className="gv-modal-title">{ocHMconstants.changeUsername}</h2>
									<p>{ocHMconstants.descUsername}</p>
									<div className="gv-notice gv-notice-warning">
										<gv-icon
											src={`${ocHMconstants.imageDIR}assets/images/warning-modal.svg`}></gv-icon>
										<p className="gv-notice-content">{ocHMconstants.confirmUsername}</p>
									</div>
									<label className="gv-form-option">
										<span className="gv-label">{ocHMconstants.currUsername}</span>
										<input
											type="text"
											className="gv-mr-md gv-input gv-disabled"
											readOnly={true}
											value={item.user}
										/>
									</label>

									<label className="gv-form-option gv-mb-md">
										<span className="gv-label">{ocHMconstants.newUsername}</span>
										<input
											type="text"
											className="gv-mr-md gv-input"
											value={newUsernames[item.user] || ''}
											onChange={(e) => handleChange(item.user, e.target.value)}
										/>
										{errors[item.user] && (
											<span className="gv-input-message gv-error">{errors[item.user]}</span>
										)}
									</label>
								</div>
								<div className="gv-button-group">
									<button type="button" className="gv-button gv-button-cancel" onClick={() => closeModal(item.user)}>{ocHMconstants.labelCancel}</button>
									<button type="button" onClick={() => handleSave(item, newUsernames[item.user] || '')} className="gv-button gv-button-primary">{saveLabel}</button>
								</div>
							</div>
						</div>
					)}
				</>
			)))}
		</>
	);
};

export default UsernameList;