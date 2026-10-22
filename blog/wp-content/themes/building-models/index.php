<?php get_header(); ?>
<!-- Blog Layout -->
<?php $blog_layout = ot_get_option('blog_layout_radio'); ?>

<?php get_template_part('template-parts/section', 'blog-info'); ?>

<?php if ($blog_layout == 'left-sidebar') {
    get_template_part('template-parts/content', 'left-sidebar');
} else if ($blog_layout == 'right-sidebar') {
    get_template_part('template-parts/content', 'right-sidebar');
} else {
    get_template_part('template-parts/content', 'full-width');
}
?>
<!-- END generic page section  -->

<?php get_footer(); ?>