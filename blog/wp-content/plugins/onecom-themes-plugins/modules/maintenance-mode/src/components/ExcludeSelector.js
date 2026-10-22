import React from "react";
import Select from "react-select";
import he from 'he';

const ExcludeSelector = ({ postTypes, excluded = {}, onChange }) => {
    const handleSelectChange = (postTypeSlug, selectedOptions) => {
        const selected = selectedOptions.map(option => option.value);
        onChange(postTypeSlug, selected);
    };
    const customStyles = {
        multiValue: (styles) => ({
            ...styles,
            borderRadius: "16px",
            lineHeight: "25px",
            padding: "0 10px",
            backgroundColor: "#e2e8f0", // optional
        }),
        multiValueLabel: (styles) => ({
            ...styles,
            padding: 0,
            color: "#000", // optional
        }),
        multiValueRemove: (styles) => ({
            ...styles,
            cursor: "pointer",
        }),
        input: (styles) => ({
            ...styles,
            margin: 0,
            padding: 0,
            cursor: "text", // Ensures cursor behaves like normal input
        }),
        control: (styles) => ({
            ...styles,
            cursor: "default", // Optional: adjust cursor if needed
        }),
    };


    return (
        <fieldset>
            {postTypes.map((type) => {
                const selectedValues = excluded[type.slug] || [];

                const options = type.posts.map((post) => ({
                    value: post.id,
                    label: he.decode(post.title),
                }));

                const selectedOptions = options.filter(opt => selectedValues.includes(opt.value));

                return (
                    <div key={type.slug} className="ocmm-post-type-group gv-form-option gv-mb-md">
                        <label className="gv-label" htmlFor={`exclude-${type.slug}`}>{type.label}</label>
                        <Select
                            inputId={`exclude-${type.slug}`}
                            options={options}
                            value={selectedOptions}
                            onChange={(val) => handleSelectChange(type.slug, val)}
                            isMulti
                            styles={customStyles}
                            classNamePrefix="oc-mm-exclude"
                        />
                    </div>
                );
            })}
        </fieldset>
    );
};

export default ExcludeSelector;