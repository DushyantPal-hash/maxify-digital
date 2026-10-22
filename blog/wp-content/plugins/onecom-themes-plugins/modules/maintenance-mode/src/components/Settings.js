
const SettingsSection = ({ title, description, children }) => {
    return (
        <div className="gv-flex gv-justify-between gv-items-start gv-gap-lg gv-max-mob-flex-col">
            <div className="oc-text-wrap gv-max-w-sm" >
                <p className="gv-text-lg gv-text-bold">{title}</p>
                <p className="gv-text-sm">{description}</p>
            </div>
            <div className="oc-input-wrap">
                {children}
            </div>
        </div>
    );
};

export default SettingsSection;