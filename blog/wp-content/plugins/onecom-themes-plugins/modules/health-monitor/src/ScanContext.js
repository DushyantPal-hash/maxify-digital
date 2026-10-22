import  { createContext, useState, useContext, useEffect, useRef } from '@wordpress/element';

export const ScanContext = createContext();

export const ScanProvider = ({ children }) => {
	const [scanResults, setScanResults] = useState(
		ocHMconstants.scanResults || { todo: [], done: [], ignored: [] }
	);
	const [results, setResults] = useState([]);
	const [isScanning, setIsScanning] = useState(false);
	const [counts, setCounts] = useState({
		todo: Number(ocHMconstants.todoCount) || 0,
		done: Number(ocHMconstants.doneCount) || 0,
		ignored: Number(ocHMconstants.ignoreCount) || 0
	});
	const [score, setScore] = useState(ocHMconstants.score ?? 0);
	const [isLoading, setIsLoading] = useState(false);
	const [loadingItem, setLoadingItem] = useState(null);
	const [toastData, settoastData] = useState({message: "", type: "success"});
	const [showUsernameSuccessModal, setShowUsernameSuccessModal] = useState(false);
	const [usernameChangeInfo, setUsernameChangeInfo] = useState({ oldUsername: '', newUsername: '' });
	const [lastScanTime, setLastScanTime] = useState(ocHMconstants.lastScanTime || '');
	const scanInProgress = useRef(false);

	useEffect(() => {
		const newScore = calculateScore(
			Number(counts.todo),
			Number(counts.done),
			Number(counts.ignored)
		);

		setScore(newScore);
	}, [counts]);

	const getCanonicalCheck = (check) => check === 'check_performance_cache' ? check.replace(/^check_/, '') : check;

	useEffect(() => {
		if (ocHMconstants.needsScan && !scanInProgress.current) {
			startScan();
		}
	}, []);

	const startScan = () => {
		if (scanInProgress.current) return;
		scanInProgress.current = true;
		setIsScanning(true);
		setScanResults({ todo: [], done: [], ignored: [] });
		setCounts({ todo: 0, done: 0, ignored: 0 });
		setScore(ocHMconstants.scanScore);

		const checks = ocHMconstants.checks; // assuming ocHMconstants has the checks array
		const doScan = (index = 0) => {
			if (index >= checks.length) { // All checks done
				setIsScanning(false);
				scanInProgress.current = false;
				// Fetch server-formatted scan time to match WP date/time settings
				jQuery.ajax({
					url: ocHMconstants.adminAjaxurl,
					method: 'POST',
					data: { action: 'ocsh_get_last_scan_time' },
					success: (res) => {
						if (res.success && res.data) {
							setLastScanTime(res.data);
						}
					}
				});
				return;
			}

			jQuery.ajax({
				url: ocHMconstants.adminAjaxurl,
				method: 'POST',
				data: { action: 'ocsh_check_' + checks[index] },
				success: (res) => {
					const data = res.data || res;
					const cleanData = Array.isArray(data) ? data : [data]; // only wrap if not already array
					updateResults(cleanData);
					doScan(index + 1);
				},
				error: () => {
					// even on error go to next to avoid freezing
					doScan(index + 1);
				}
			});
		};

		doScan(); // Start scan at index 0
	};

	const calculateScore = (todoCount, doneCount, ignoredCount) => {
		const todo = Number(todoCount);
		const done = Number(doneCount);
		const ignored = Number(ignoredCount);
		const vulncount = Number(ocHMconstants.vulncount) > 0 ? 1: 0;

		const total = todo + done + vulncount + ignored ;
		if (total === 0) return 0;

		return Math.round(((done + ignored) / total) * 100);
	};

	const updateResults = (checkResults) => {
		setScanResults((prevResults) => {
			const newTodo = [...prevResults.todo];
			const newDone = [...prevResults.done];
			const newIgnored = [...prevResults.ignored];

			// Collect existing check names to prevent duplicates
			const existingChecks = new Set([
				...newTodo.map(i => i.check),
				...newDone.map(i => i.check),
				...newIgnored.map(i => i.check),
			]);

			checkResults.forEach((item) => {
				if (existingChecks.has(item.check)) return; // skip duplicate
				existingChecks.add(item.check);

				const status = item.check_result?.status;

				if (item.is_ignored) {
					newIgnored.push(item);
				} else {
					switch (status) {
						case 0:
							newDone.push(item);
							break;
						case 1:
							newTodo.push(item);
							break;
						case 3:
							newIgnored.push(item);
							break;
						case 4:
							newTodo.push(item);
							break;
						default:
							break; // status 2 (hidden) or unknown, skip
					}
				}
			});

			const newCounts = {
				todo: newTodo.length,
				done: newDone.length,
				ignored: newIgnored.length,
			};

			const newScore = calculateScore(newCounts.todo, newCounts.done, newCounts.ignored);

			setCounts(newCounts);
			setScore(newScore);

			return {
				todo: newTodo,
				done: newDone,
				ignored: newIgnored,
			};
		});
	};

	const handleFix = async (item, sourceTab = 'todo') => {
		try {
			const currCheck = getCanonicalCheck(item.check);
			setLoadingItem(currCheck);
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: new URLSearchParams({
					action: `ocsh_fix_${item.check}`,
					check: item.check
				})
			});

			const result = await response.json();

			if (result.status === 0) {
				// Move to 'Done' list
				setScanResults(prev => {
					const updatedSourceList = prev[sourceTab].filter(i => i.check !== currCheck);
					const doneItem = prev[sourceTab].find(i => i.check === currCheck);
					if (!doneItem) return prev;

					const updatedDoneItem = {
						...doneItem,
						status: { ...doneItem.status, status_key: 0, status_desc: result.desc, how_to_fix_text: '' },
						check_result: { ...doneItem.check_result, list_data: [] },
						resolve_button: null,
						fix_button: [],
					};

					// Only set undo_button if user is premium and backend signals undo support
					if (ocHMconstants.isPremium && result.undo) {
						updatedDoneItem.undo_button = {
							text: ocHMconstants.revertText,
							type: "undo",
							class: 'gv-action',
							data: { check: item?.check },
						};
					}

					return {
						...prev,
						[sourceTab]: updatedSourceList,
						done: [...prev.done, updatedDoneItem],
					};
				});

				// Update counts
				setCounts(prev => ({
					...prev,
					[sourceTab]: Math.max(0, Number(prev[sourceTab]) - 1),
					done: Number(prev.done) + 1
				}));

				settoastData({
					message: ocHMconstants.fixsuccess ,
					type: "success"
				});

			} else {
				settoastData({
					message: ocHMconstants.fixError ,
					type: "alert"
				});
			}

		} catch (err) {
			console.error(err);
			settoastData({
				message: ocHMconstants.fixError ,
				type: "alert"
			});
		}finally {
			setLoadingItem(null);
		}
	};

	const handleUsernameSave = async (item, oldUser, newUsername, nonce, sourceTab = 'todo') => {
		if (!newUsername || !newUsername.trim()) return;

		const currCheck = getCanonicalCheck(item?.check || 'usernames');
		setLoadingItem(currCheck);

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: new URLSearchParams({
					action: 'ocsh_change_username',
					username: newUsername,
					oldUser: oldUser,
					_ajax_nonce: nonce,
				})
			});

			const result = await response.json();

			if (result.status === '1') {
				// Username taken or error, show message (optional)
				settoastData({
					message: ocHMconstants.fixError,
					type: 'alert',
				});
				return;
			}

			if (result.status === 0) {
				//  Mark this check as resolved using same logic as handleFix
				setScanResults(prev => {
					const updatedSourceList = prev[sourceTab].filter(i => i.check !== currCheck);
					const doneItem = prev[sourceTab].find(i => i.check === currCheck);
					if (!doneItem) return prev;

					const updatedDoneItem = { // to change the text and buttons
						...doneItem,
						status: {
							...doneItem.status,
							status_key: 0,
							status_desc: result.desc || doneItem.status.status_desc,
							how_to_fix_text: ''
						},
						resolve_button: null,
						fix_button: [],
						check_result: {
							...doneItem.check_result,
							list_data: [], // Properly clear list_data here
						},
						undo_button: null, // remove undo button
					};


					return {
						...prev,
						[sourceTab]: updatedSourceList,
						done: [...prev.done, updatedDoneItem],
					};
				});

				setCounts(prev => ({
					...prev,
					[sourceTab]: Math.max(0, Number(prev[sourceTab]) - 1),
					done: Number(prev.done) + 1,
				}));

				settoastData({
					message: ocHMconstants.fixsuccess,
					type: 'success',
				});

				setUsernameChangeInfo({ oldUsername: oldUser, newUsername: newUsername });
				setShowUsernameSuccessModal(true);
			}
		} catch (err) {
			console.error(err);
			settoastData({
				message: ocHMconstants.fixError,
				type: 'alert'
			});
		} finally {
			setLoadingItem(null);
		}
	};

	const closeUsernameSuccessModal = () => {
		setShowUsernameSuccessModal(false);
		setUsernameChangeInfo({ oldUsername: '', newUsername: '' });
		window.location.href = `${window.location.origin}/wp-login.php?redirect_to=${encodeURIComponent(ocHMconstants.pluginPageURL)}`;

	};

	const handleDeleteBackupFile = async (item, fileToDelete, idx, sourceTab = 'todo') => {
		if (!fileToDelete) return;

		const currCheck = getCanonicalCheck(item.check);
		setLoadingItem(currCheck);

		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'ocsh_fix_backup_zip',
					file: fileToDelete,
				}),
			});
			const result = await response.json();

			let movedToDone = false;

			setScanResults(prev => {
				const updatedSourceList = prev[sourceTab].map(checkItem => {
					if (checkItem.check !== currCheck) return checkItem;

					const newList = checkItem.check_result.list_data.filter((entry, i) => i !== idx);

					if (newList.length === 0) {
						movedToDone = true;

						return null; // Remove from source list
					}

					return {
						...checkItem,
						check_result: {
							...checkItem.check_result,
							list_data: newList,
						},
					};
				}).filter(Boolean);

				const movedItem = prev[sourceTab].find(i => i.check === currCheck && i.check_result.list_data.length === 1);

				const newDoneList = movedToDone
					? [...prev.done, {
						...item,
						status: {
							...item.status,
							status_key: 0,
							status_desc: result.desc || item.status.status_desc,
							how_to_fix_text: '',
						},
						resolve_button: null,
						fix_button: [],
						check_result: {
							...item.check_result,
							list_data: [],
						},
					}]
					: prev.done;

				return {
					...prev,
					[sourceTab]: updatedSourceList,
					done: newDoneList,
				};
			});

			setCounts(prev => {
				const stillInSource = item.check_result.list_data.length > 1;
				return {
					...prev,
					[sourceTab]: stillInSource ? prev[sourceTab] : Math.max(0, Number(prev[sourceTab]) - 1),
					done: stillInSource ? prev.done : Number(prev.done) + 1,
				};
			});

			if (item.check_result.list_data.length === 1) {
				// Only show toast if it was the last entry
				settoastData({
					message: ocHMconstants.fixsuccess,
					type: 'success',
				});
			}
		} catch (err) {
			console.error(err);
			settoastData({
				message: ocHMconstants.fixError,
				type: 'alert',
			});
		} finally {
			setLoadingItem(null);
		}
	};

	const handleUndo = async (check) => {
		if (!check) return;

		const currCheck = getCanonicalCheck(check);
		setLoadingItem(currCheck);

		try {
			const response = await fetch(ocHMconstants.adminAjaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: `ocsh_undo_${check}`,
				}),
			});

			const result = await response.json(); // Expected: { status: 0, title: "", desc: "" }
			if (result.status === 0) {
				setScanResults((prev) => {
					const { done, todo } = prev;
					const itemToRevert = done.find((i) => i.check === currCheck);
					if (!itemToRevert) return prev;

					// Build new item with updated props
					const updatedItem = {
						...itemToRevert,
						status: { ...itemToRevert.status, status_key: 1, status_desc: result.desc, how_to_fix_text: result.how_to_fix || itemToRevert.status.how_to_fix_text, how_to_fix_title: itemToRevert.status.how_to_fix_title  }, // mark as 'to do'
						fix_button: {
							text: result.fix_button_text,
							data: {
								check : check
							},
							type:"button"
						},
						resolve_button: {
							link_text: result.ignore_text,
							data: {
								check : currCheck
							}

						},
						undo_button: null, // remove undo button
					};

					return {
						...prev,
						done: done.filter((i) => i.check !== currCheck),
						todo: [...todo, updatedItem],
					};
				});

				// Update counts
				setCounts((prev) => ({
					...prev,
					todo: Number(prev.todo) + 1,
					done: Math.max(0, Number(prev.done) - 1),
				}));

				settoastData({
					message: ocHMconstants.undoSuccess,
					type: 'success',
				});
			} else {
				// Toast failure
				settoastData({
					message: ocHMconstants.undoError,
					type: 'alert',
				});
			}
		} catch (error) {
			console.log(error);
			settoastData({
				message: ocHMconstants.undoException,
				type: 'alert',
			});
		} finally {
			setLoadingItem(null); // Remove loader
		}
	};

	const handleAlwaysIgnore = async (btn) => {
		try {
			setLoadingItem(btn.data.check);

			const isCurrentlyIgnored = btn.class === "onecom_unignore";
			const action = isCurrentlyIgnored ? 'onecom_unignore' : 'ocsh_mark_resolved' ;

			const response = await fetch(ocHMconstants.adminAjaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: new URLSearchParams({
					action: action,
					check: btn.data.check
				})
			});

			const result = await response.json(); // { status: 0, title: "...", desc: "" }

			// Show toast regardless of success or failure
			settoastData({
				message: result.title ,
				type: result.status === 1 ? "alert" : "success"
			});

// If error, don't update lists
			if (result.status === 1) {
				return;
			}

			setScanResults(prevResults => {
				const { todo, ignored } = prevResults;
				const isCurrentlyIgnored = btn.class === "onecom_unignore";

				if (isCurrentlyIgnored) {
					// Unignore: move from ignored -> todo
					const itemToMove = ignored.find(checkItem => checkItem.check === btn.data.check);
					if (!itemToMove) return prevResults;

					// Change button class to "oc-mark-resolved" for next toggle
					const updatedItem = {
						...itemToMove,
						resolve_button: {
							...itemToMove.resolve_button,
							class: 'oc-mark-resolved',
							link_text: ocHMconstants.ignoreText
						}
					};

					return {
						...prevResults,
						ignored: ignored.filter(checkItem => checkItem.check !== btn.data.check),
						todo: [...todo, updatedItem]
					};
				} else {
					// Ignore: move from todo -> ignored
					const itemToMove = todo.find(checkItem => checkItem.check === btn.data.check);
					if (!itemToMove) return prevResults;

					// Change button class to "onecom_unignore" for next toggle
					const updatedItem = {
						...itemToMove,
						resolve_button: {
							...itemToMove.resolve_button,
							class: 'onecom_unignore',
							link_text: ocHMconstants.unignoreText
						}
					};

					return {
						...prevResults,
						todo: todo.filter(checkItem => checkItem.check !== btn.data.check),
						ignored: [...ignored, updatedItem]
					};
				}
			});


			setCounts(prev => {
				const isCurrentlyIgnored = btn.class === "onecom_unignore";

				return {
					...prev,
					todo: isCurrentlyIgnored
						? Number(prev.todo) + 1
						: Math.max(0, Number(prev.todo) - 1),
					ignored: isCurrentlyIgnored
						? Math.max(0, Number(prev.ignored) - 1)
						: Number(prev.ignored) + 1,
					// 'done' remains the same
				};
			});

		} catch (error) {
			console.error('Error during ignore toggle:', error);
		} finally {
			setLoadingItem(null);
		}
	};


	return (
		<ScanContext.Provider value={{
			isLoading,
			isScanning,
			scanResults,
			counts,
			score,
			lastScanTime,
			startScan,
			handleAlwaysIgnore,
			handleFix,
			loadingItem,
			toastData,
			settoastData,
			handleUndo,
			handleUsernameSave,
			handleDeleteBackupFile,
			showUsernameSuccessModal,
			usernameChangeInfo,
			closeUsernameSuccessModal
		}}>
			{children}
		</ScanContext.Provider>
	);
};
