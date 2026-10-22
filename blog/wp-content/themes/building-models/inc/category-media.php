<?php
/**
 * Add Featured Image button in taxonomies
 * */
if (!class_exists('ONE_TAX_META')) {

    class ONE_TAX_META {

        public function __construct() {
            
        }

        /*
         * Initialize the class and start calling our hooks and filters
         * @since 1.0.0
         */

        public function init() {
            // Category or Taxonomoies where you want to add image field
            $taxonomy_name = 'service_type';
            add_action($taxonomy_name . '_add_form_fields', array($this, 'add_category_image'), 10, 2);
            add_action('created_' . $taxonomy_name, array($this, 'save_category_image'), 10, 2);
            add_action($taxonomy_name . '_edit_form_fields', array($this, 'update_category_image'), 10, 2);
            add_action('edited_' . $taxonomy_name, array($this, 'updated_category_image'), 10, 2);
            add_action('admin_enqueue_scripts', array($this, 'load_media'));
            add_action('admin_footer', array($this, 'add_script'));
        }

        public function load_media() {
            wp_enqueue_media();
        }

        /*
         * Add a form field in the new category page
         * @since 1.0.0
         */

        public function add_category_image($taxonomy) {
            ?>
            <div class="form-field term-group">
                <label for="category-image-id"><?php _e('Image', OC_TEXT_DOMAIN); ?></label>
                <input type="hidden" id="category-image-id" name="category-image-id" class="custom_media_url" value="">
                <div id="category-image-wrapper"></div>
                <p>
                    <input type="button" class="button button-secondary one_tax_media_button" id="one_tax_media_button" name="one_tax_media_button" value="<?php _e('Add Image', OC_TEXT_DOMAIN); ?>" />
                    <input type="button" class="button button-secondary one_tax_media_remove" id="one_tax_media_remove" name="one_tax_media_remove" value="<?php _e('Remove Image', OC_TEXT_DOMAIN); ?>" />
                </p>
            </div>
            <?php
        }

        /*
         * Save the form field
         * @since 1.0.0
         */

        public function save_category_image($term_id, $tt_id) {
            if (isset($_POST['category-image-id']) && '' !== $_POST['category-image-id']) {
                $image = $_POST['category-image-id'];
                add_term_meta($term_id, 'category-image-id', $image, true);
            }
        }

        /*
         * Edit the form field
         * @since 1.0.0
         */

        public function update_category_image($term, $taxonomy) {
            ?>
            <tr class="form-field term-group-wrap">
                <th scope="row">
                    <label for="category-image-id"><?php _e('Image', OC_TEXT_DOMAIN); ?></label>
                </th>
                <td>
                    <?php $image_id = get_term_meta($term->term_id, 'category-image-id', true); ?>
                    <input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo $image_id; ?>">
                    <div id="category-image-wrapper">
                        <?php if ($image_id) { ?>
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                        <?php } ?>
                    </div>
                    <p>
                        <input type="button" class="button button-secondary one_tax_media_button" id="one_tax_media_button" name="one_tax_media_button" value="<?php _e('Add Image', OC_TEXT_DOMAIN); ?>" />
                        <input type="button" class="button button-secondary one_tax_media_remove" id="one_tax_media_remove" name="one_tax_media_remove" value="<?php _e('Remove Image', OC_TEXT_DOMAIN); ?>" />
                    </p>
                </td>
            </tr>
            <?php
        }

        /*
         * Update the form field value
         * @since 1.0.0
         */

        public function updated_category_image($term_id, $tt_id) {
            if (isset($_POST['category-image-id']) && '' !== $_POST['category-image-id']) {
                $image = $_POST['category-image-id'];
                update_term_meta($term_id, 'category-image-id', $image);
            } else {
                update_term_meta($term_id, 'category-image-id', '');
            }
        }

        /*
         * Add script
         * @since 1.0.0
         */

        public function add_script() {
            wp_enqueue_script('media-upload');
            wp_enqueue_media();
            wp_enqueue_script('our_admin', get_template_directory_uri() . '/inc/category-media.js', array('jquery'));
        }

    }

    $ONE_TAX_META = new ONE_TAX_META();
    $ONE_TAX_META->init();
}
