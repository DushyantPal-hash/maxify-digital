import { Link } from 'react-router-dom';
const Shortcut = ({
                        href = '#',
                        icon = '/icons/info.svg',
                        arrowIcon = `${ocmmSettings.imageDIR}assets/images/icons/arrow_forward.svg`,
                        statusText = '',
                        statusClass = 'gv-inactive-prio-low',
                        showIndicator = false,
                        indicatorClassName = '',
                        title = 'Title',
                        description = 'Description',
                        className = '',
                    }) => {
    return (
        <>
        <Link to={href} className={`gv-shortcut ${className}`}>
            <gv-icon src={icon}></gv-icon>
            <div className="gv-content">
                <div className={`gv-status ${statusClass}`}>
                    {showIndicator && <div className={`gv-indicator ${indicatorClassName}`}></div>}
                    <span style={{'color':'#141414'}}>{statusText}</span>
                </div>
                <h3 className="gv-title">{title}</h3>
                <p>{description}</p>
            </div>
            <gv-icon src={arrowIcon}></gv-icon>
        </Link>
        </>
    );
};

export default Shortcut;