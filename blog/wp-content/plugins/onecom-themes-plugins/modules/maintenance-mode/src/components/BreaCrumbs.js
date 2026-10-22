import {Link} from "react-router-dom";

const BreaCrumbs = ({parentLabel, childLabel}) =>{
    return(
        <>
            <div className="gv-max-mob-hidden">
                <ul className="gv-breadcrumbs">
                    <li>
                        <Link to="/">{parentLabel}</Link>
                        <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/chevron_right.svg`}></gv-icon>
                    </li>
                    <li>
                        <span>{childLabel}</span>
                    </li>
                </ul>
            </div>

            {/* Mobile Back Button */}
            <div className="gv-breadcrumbs gv-desk-hidden gv-tab-hidden gv-back-nav">
                <Link to="/">
                    <gv-icon src={`${ocmmSettings.imageDIR}assets/images/icons/chevron_left.svg`}/>
                    <span>Back</span>
                </Link>
            </div>

        </>
    );
}
export default BreaCrumbs;