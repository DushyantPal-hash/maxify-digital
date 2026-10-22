<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
get_header();
?>
<section class="section-box" role="main">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-12">
                <div class="section-title">
                    <?php echo ot_get_option('404_content'); ?>
                </div>
                <div class="section-content">
                    <div class="post-content not_found">
                        <br class="clear" />
                        <br class="clear" />
                        <br class="clear" />
                        <br class="clear" />


                        <?php get_search_form(); ?>

                        <br class="clear" />
                        <br class="clear" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>