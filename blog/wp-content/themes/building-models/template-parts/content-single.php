<div class="post-content-box" role="main">
    <h1 class="main-title">
        <?php the_title(); ?>
    </h1>

    <!-- CPT Metadata -->
    <?php if ('off' != ot_get_option('show_post_meta')): ?>
        <?php if (!is_page(get_the_ID())): ?>
            <?php get_template_part('template-parts/post', 'meta'); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (has_post_thumbnail()) { ?>
        <div class="featured-image-box">
            <?php the_post_thumbnail('content_large'); ?>
        </div>
    <?php } ?>
    <div class="main-content-box"> <?php the_content(); ?> </div>
    <!--  Tags -->
    <?php if (!empty(wp_get_post_tags(get_the_ID()))): ?>
        <div class="cpt-tags">
            <span class="dashicons dashicons-tag"></span> <?php the_tags(''); ?>
        </div>
    <?php endif; ?>
    <?php edit_post_link('edit', '<p>', '</p>'); ?>

</div>
<?php if (comments_open()) : ?>
    <!-- CTP Comments -->
    <div class="post-comments <?php echo get_comments_number() ? 'border' : ''; ?>">
        <?php comments_template(); ?>
    </div>
<?php endif; ?>