<div class="container">
    <div class="row text-center">
        <div class="col-md-12">
            <div class="post-content">
                <br  />
                <br  />

                <h2><?php echo __('No entries found!', OC_TEXT_DOMAIN); ?></h2>
                <?php if (is_search()): ?>
                    <h5><?php echo __('Try searching with a different keyword.', OC_TEXT_DOMAIN); ?></h5>
                <?php else: ?>
                    <p><?php echo __('Try a search instead', OC_TEXT_DOMAIN); ?>:</p>
                <?php endif; ?>
                <br />
                <?php get_search_form(); ?>
                <br class="clear" />
            </div>
        </div>
    </div>
</div>