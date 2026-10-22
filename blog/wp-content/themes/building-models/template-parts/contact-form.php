<?php
$post_id = get_the_id();
$custom_form_switch = get_post_meta($post_id, 'custom_form_switch', true);
$custom_form_embed = get_post_meta($post_id, 'custom_form_embed', true);
?> 

<div class="text-center contact-form-box ">
    <div class="container no-padding">
        <div class="row text-left">
            <div class="col-md-12">
                <div class="contact-form">
                    <?php if ($custom_form_switch != 'on') : ?>
                        <form id="booking_form" class="form text-left" role="form">
                            <fieldset>
                                <?php
                                $label_1 = get_post_meta(get_the_ID(), 'form_label_1', true);
                                $label_1 = (isset($label_1) && strlen($label_1)) ? $label_1 : __("Name", OC_TEXT_DOMAIN);
                                ?>
                                <label><?php echo $label_1; ?> *</label>
                                <input type="text" class="input booking_name"  maxlength="120" required />
                                <input type="hidden" name="label_1" id="label_1" value="<?php echo $label_1; ?>" />
                            </fieldset>
                            <fieldset>
                                <?php
                                $label_2 = get_post_meta(get_the_ID(), 'form_label_2', true);
                                $label_2 = (isset($label_2) && strlen($label_2)) ? $label_2 : __("Email", OC_TEXT_DOMAIN);
                                ?>
                                <label><?php echo $label_2; ?> *</label>
                                <input type="email" class="input booking_email" maxlength="120" required />
                                <input type="hidden" name="label_2" id="label_2" value="<?php echo $label_2; ?>" />
                            </fieldset>
                            <fieldset>
                                <?php
                                $label_3 = get_post_meta(get_the_ID(), 'form_label_3', true);
                                $label_3 = (isset($label_3) && strlen($label_3)) ? $label_3 : __("Message", OC_TEXT_DOMAIN);
                                ?>
                                <label><?php echo $label_3; ?> *</label>
                                <textarea rows="10" cols="50" class="input booking_msg" name="message" required></textarea>
                                <input type="hidden" name="label_3" id="label_3" value="<?php echo $label_3; ?>" />
                            </fieldset>

                            <?php
                            /* Subject of the contact email */
                            $subject = get_post_meta(get_the_ID(), 'cmail_subject', true);

                            if (!isset($subject) && !strlen($subject)) {
                                /* Set default if not subject saved from admin */
                                $subject = 'Contact query from ' . get_bloginfo('name');
                            }

                            $contact_recipient = get_post_meta(get_the_ID(), 'recipient_email', true);
                            if (!isset($contact_recipient) && !strlen($contact_recipient)) {
                                /* Set default recipient to Admin Email */
                                $contact_recipient = get_option('admin_email');
                            }
                            ?>
                            <input type="hidden" name="contact_subject" id="contact_subject" value="<?php echo $subject; ?>" />
                            <input type="hidden" name="contact_recipient" id="contact_recipient" value="<?php echo $contact_recipient; ?>" />

                            <fieldset>
                                <?php echo oc_secure_fields(); ?>
                            </fieldset>
                            <fieldset>
                                <?php $label_4 = get_post_meta(get_the_ID(), 'form_label_4', true); ?>
                                <input type="submit" class="submit button small-button round pull-right" value="<?php echo (isset($label_4) && strlen($label_4)) ? $label_4 : __('SUBMIT', 'building-models'); ?>" name="booking-submit" />
                            </fieldset>
                            <fieldset>
                                <div class="form_message"></div>
                            </fieldset>
                        </form>
                    <?php else : ?>
                        <?php echo apply_filters('the_content', $custom_form_embed); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>