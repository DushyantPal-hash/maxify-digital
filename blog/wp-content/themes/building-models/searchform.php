<form role="search" method="get" id="searchform" class="searchform form" action="<?php echo home_url('/'); ?>">
    <div>
        <label class="screen-reader-text" for="s"><?php echo _e('Search for:', 'label') ?></label>
        <input type="text" class="search-field input" id="s"
               placeholder="<?php echo esc_attr_x('Search …', 'placeholder') ?>"
               value="<?php echo get_search_query() ?>" name="s"
               title="<?php echo esc_attr_x('Search for:', 'label') ?>" />

        <input type="submit" class="button small-button" id="searchsubmit" value="<?php echo __('Search', OC_TEXT_DOMAIN) ?>">
    </div>
</form>