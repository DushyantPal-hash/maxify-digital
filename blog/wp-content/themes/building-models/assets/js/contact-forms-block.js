(function (blocks, editor, element, wp) {
    const { useEffect } = element;
    var el = element.createElement,
        Fragment = element.Fragment,
        InspectorControls = editor.InspectorControls,
        TextControl = wp.components.TextControl;
    blocks.registerBlockType('onecom/contact-form', {
        title: 'Contact form',
        icon: 'email-alt',
        category: 'onecom-blocks',
        attributes: {
            labelName: {
                type: 'string',
                default: 'Name'
            },
            labelPhone: {
                type: 'string',
                default: 'Phone'
            },
            labelEmail: {
                type: 'string',
                default: 'Email'
            },
            labelMessage: {
                type: 'string',
                default: 'Message'
            },
            labelButton: {
                type: 'string',
                default: 'Send'
            },
            subject: {
                type: 'string',
                default: 'Subject'
            },
            recipientEmail: {
                type: 'string',
                default: 'email@domain.com'
            }
        },
        examples: {},
        edit: function (props) {

            var defaults = {
                labelName: '',
                labelPhone: '',
                labelEmail: '',
                labelMessage: '',
                labelButton: '',
                subject: '',
                recipientEmail: ''
            };

            var labelName = props.attributes.labelName || defaults.labelName,
                labelPhone = props.attributes.labelPhone,
                labelEmail = props.attributes.labelEmail,
                labelMessage = props.attributes.labelMessage,
                labelButton = props.attributes.labelButton,
                subject = props.attributes.subject,
                recipientEmail = props.attributes.recipientEmail;

            useEffect(() => {
                props.setAttributes({
                    labelName: labelName,
                    labelPhone: labelPhone,
                    labelEmail: labelEmail,
                    labelMessage: labelMessage,
                    labelButton: labelButton,
                    subject: subject,
                    recipientEmail: recipientEmail
                })
            }, []);

            function onChangeTextField(newValue) {
                props.setAttributes({
                    labelName: newValue,
                    changedFlag: true
                });
            }

            function onChangeLabelPhone(newValue) {
                props.setAttributes({labelPhone: newValue, changedFlag: true});
            }

            function onChangeLabelEmail(newValue) {
                props.setAttributes({labelEmail: newValue, changedFlag: true});
            }

            function onChangeLabelMessage(newValue) {
                props.setAttributes({labelMessage: newValue, changedFlag: true});
            }

            function onChangeLabelButton(newValue) {
                props.setAttributes({labelButton: newValue, changedFlag: true});
            }

            function onChangeRecipientEmail(newValue) {
                props.setAttributes({recipientEmail: newValue, changedFlag: true});
            }

            function onChangeSubject(newValue) {
                props.setAttributes({subject: newValue, changedFlag: true});
            }

            return (
                el(
                    Fragment,
                    {},
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: labelName,
                                help: 'This is the label for your name field',
                                value: labelName,
                                onChange: onChangeTextField
                            }
                        ),
                    ),
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: 'Recipient email',
                                help: 'The email to which this form will send message',
                                value: recipientEmail,
                                onChange: onChangeRecipientEmail
                            }
                        ),
                    ),
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: 'Subject',
                                help: 'The subject of email received from this form',
                                value: subject,
                                onChange: onChangeSubject
                            }
                        ),
                    ),
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: labelPhone,
                                help: 'This is the label for your phone field',
                                value: labelPhone,
                                onChange: onChangeLabelPhone
                            }
                        ),
                    ), el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: labelEmail,
                                help: 'This is the label for your email field',
                                value: labelEmail,
                                onChange: onChangeLabelEmail
                            }
                        ),
                    ),
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: labelMessage,
                                help: 'This is the label for your message field',
                                value: labelMessage,
                                onChange: onChangeLabelMessage
                            }
                        ),
                    ),
                    el(
                        InspectorControls,
                        null,
                        el(
                            TextControl,
                            {
                                label: labelButton,
                                help: 'This is the label for your button',
                                value: labelButton,
                                onChange: onChangeLabelButton
                            }
                        ),
                    ),

                    el('div',
                        {className: 'oc-form-wrap'},
                        el('div',
                            {className: 'labelNameContainer'},
                            el('label', {}, labelName + '*'),
                            el(
                                'input',
                                {
                                    type: 'text',
                                    readOnly: 'readOnly',
                                    className: 'form-control shadow-none'
                                }
                            )
                        ),
                        el('div',
                            {className: 'labelPhoneContainer'},
                            el('label', {}, labelPhone + '*'),
                            el(
                                'input',
                                {
                                    type: 'text',
                                    readOnly: 'readOnly',
                                    className: 'form-control shadow-none'
                                }
                            )
                        ),
                        el('div',
                            {className: 'labelEmailContainer'},
                            el('label', {}, labelEmail + '*'),
                            el(
                                'input',
                                {
                                    type: 'text',
                                    readOnly: 'readOnly',
                                    className: 'form-control shadow-none'
                                }
                            )
                        ),
                        el('div',
                            {className: 'labelMessageContainer'},
                            el('label', {}, labelMessage + '*'),
                            el(
                                'textarea',
                                {
                                    readOnly: 'readOnly',
                                    className: 'form-control shadow-none'
                                }
                            )
                        ),
                        el('div',
                            {className: 'labelButtonContainer'},
                            el('button', {
                                type: 'button',
                                className: 'oc-form-button'
                            }, labelButton)
                        )
                    ),
                )
            );
        },
        save: function (props) {
            var labelName = props.attributes.labelName || 'Name',
                labelPhone = props.attributes.labelPhone || 'Phone',
                labelEmail = props.attributes.labelEmail || 'Email',
                labelMessage = props.attributes.labelMessage || 'Message',
                subject = props.attributes.subject || 'Subject',
                recipientEmail = props.attributes.recipientEmail || 'email@domain.com',
                labelButton = props.attributes.labelButton || 'Send';
            return el('form',
                {},
                el('div',
                    {className: 'labelNameContainer'},
                    el('label', {}, labelName + '*'),
                    el(
                        'input',
                        {
                            type: 'text',
                            name: labelName,
                            className: 'labelName form-control shadow-none',
                            required: 'required'
                        }
                    ),
                    el(
                        'input',
                        {
                            type: 'hidden',
                            value: recipientEmail,
                            className: 'oc-recipient-email oc-ignore form-control shadow-none',
                            required: 'required'
                        },
                    ),
                    el(
                        'input',
                        {
                            type: 'hidden',
                            value: subject,
                            className: 'oc-recipient-subject oc-ignore form-control shadow-none',
                            required: 'required'
                        }
                    )
                ),
                el('div',
                    {className: 'labelPhoneContainer'},
                    el('label', {}, labelPhone),
                    el(
                        'input',
                        {
                            type: 'text',
                            name: labelPhone,
                            className: 'labelPhone form-control shadow-none'
                        }
                    )
                ),
                el('div',
                    {className: 'labelEmailContainer'},
                    el('label', {}, labelEmail + '*'),
                    el(
                        'input',
                        {
                            type: 'text',
                            name: labelEmail,
                            className: 'labelEmail form-control shadow-none',
                            required: 'required'
                        }
                    )
                ),
                el('div',
                    {className: 'labelMessageContainer'},
                    el('label', {}, labelMessage + '*'),
                    el(
                        'textarea',
                        {
                            name: labelMessage,
                            className: 'labelMessage form-control shadow-none'
                        }
                    )
                ),
                el('div',
                    {className: 'labelButtonContainer'},
                    el('button', {
                        type: 'submit',
                        className: 'oc-form-button'
                    }, labelButton),
                    el('span',
                        {className: 'dashicons dashicons-image-rotate oc-spinner d-none ml-2'}
                    ),
                    el('p', {
                        class: 'oc-message',
                        style: 'display:none'
                    })
                )
            );
        }
    });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp);