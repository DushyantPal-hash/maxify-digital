import { useContext } from 'react';
import { ScanContext } from '../ScanContext';
import { Button } from 'wpin-components-library';

const TodoHeader = ({ activeTab }) => {
	const { startScan, isScanning, lastScanTime } = useContext(ScanContext);

	const labelTodo = ocHMconstants?.labelTodo || 'To do';
	const descTodo = ocHMconstants?.descTodo || '';
	const labelIgnore = ocHMconstants?.labelIgnore || 'Ignored';
	const descIgnore = ocHMconstants?.descIgnore || '';
	const scanNow = ocHMconstants?.scanNow || 'Scan now';
	const scanning = ocHMconstants?.scanning || 'Scanning...';
	const lastScanLabel = ocHMconstants?.labelLastScan || 'Last scan:';
	const imageDir = ocHMconstants?.imageDIR || '';

	return (
		<div className="gv-mt-md gv-mb-md">
			{activeTab === 'todo' && (
				<>
					<div className="oc-header-wrap gv-flex gv-justify-between">
						<p className="gv-text-lg gv-text-bold">{labelTodo}</p>
						<div className="gv-mode-condensed">
							<Button
								className="gv-button-primary gv-max-mob-hidden"
								title={scanNow}
								onClick={startScan}
								disabled={isScanning}
								type="button"
							>
								{isScanning ? scanning : scanNow}
							</Button>
						</div>
					</div>
					<p className="gv-mt-sm gv-mb-sm gv-text-sm">{descTodo}</p>
					<div className="gv-flex">
						<gv-icon src={`${imageDir}assets/images/vitalss.svg`} aria-hidden="true"></gv-icon>
						<p className="gv-text-sm gv-text-bold gv-text-on-alternative">
							{lastScanLabel} {lastScanTime}
						</p>
					</div>
					<div className="gv-mode-condensed">
						<Button
							className="gv-button-primary gv-desk-hidden gv-tab-hidden gv-mb-md gv-max-mob-mt-md"
							label={scanNow}
							onClick={startScan}
							disabled={isScanning}
							type="button"
						>
							{isScanning ? scanning : scanNow}
						</Button>
					</div>
				</>
			)}
			{activeTab === 'ignored' && (
				<>
					<div className="oc-header-wrap gv-flex gv-justify-between">
						<p className="gv-text-lg gv-text-bold">{labelIgnore}</p>
					</div>
					<p className="gv-mt-xs gv-mb-sm gv-text-sm">{descIgnore}</p>
				</>
			)}
		</div>
	);
};

export default TodoHeader;