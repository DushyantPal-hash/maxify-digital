<?php
global $ncmaz_redux_demo;

if (!$ncmaz_redux_demo || _NCMAZ_FRONTEND_PREFIX == null) {
    return;
}

$hasRelated = $ncmaz_redux_demo['nc-single-page-settings--related-active'];
$hasMoreFromAuthor = $ncmaz_redux_demo['nc-single-page-settings--moreFromAuthor-active'];

if (!$hasRelated && !$hasMoreFromAuthor) {
    return;
}
?>

<div class="relative bg-neutral-100 dark:bg-neutral-800 py-16 lg:py-28 mt-16 lg:mt-28">
    <div class="container">

        <!-- RELATED POSTS -->
        <?php
        if ($hasRelated) :
            $SingleRelatedGridPostsProps = (object)[];
            $SingleRelatedGridPostsProps->categoriesId = wp_get_object_terms($post->ID, 'category', array('fields' => 'ids'));
            $SingleRelatedGridPostsProps->orderBy = 'DATE';
            $SingleRelatedGridPostsProps->order = 'DESC';
            $SingleRelatedGridPostsProps->numberOfPosts = $ncmaz_redux_demo['nc-single-page-settings--related-postPerPage'];
        ?>
            <div data-is-react-component="SingleRelatedGridPosts" data-component-props="<?php echo esc_attr(json_encode($SingleRelatedGridPostsProps)); ?>">
            </div>
        <?php endif; ?>

        <!-- MORE FROM AUTHORS POSTS -->
        <?php
        if ($hasMoreFromAuthor) :

            $SingleMoreFromAuthorGridPostsProps = (object)[];
            $SingleMoreFromAuthorGridPostsProps->authorId = $post->post_author;
            $SingleMoreFromAuthorGridPostsProps->orderBy = 'DATE';
            $SingleMoreFromAuthorGridPostsProps->order = 'DESC';
            $SingleMoreFromAuthorGridPostsProps->numberOfPosts = $ncmaz_redux_demo['nc-single-page-settings--moreFromAuthor-postPerPage'];
        ?>
            <div class="mt-20" data-is-react-component="SingleMoreFromAuthorGridPosts" data-component-props="<?php echo esc_attr(json_encode($SingleMoreFromAuthorGridPostsProps)); ?>">
            </div>
        <?php endif; ?>
    </div>
</div>