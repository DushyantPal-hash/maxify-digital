<div class="post-meta" role="contentinfo">
    <ul>
        <!-- Post Author -->
        <li>
            <span class="dashicons dashicons-admin-users"></span>
            <a class="post-author" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                <?php
                if (strlen(get_the_author_meta('first_name'))) {
                    echo get_the_author_meta('first_name') . ' ' . get_the_author_meta('last_name');
                } else {
                    echo get_the_author();
                }
                ?>
            </a>
        </li>

        <!-- Post Publish & Updated Date & Time -->
        <li>
            <span class="dashicons dashicons-clock"></span>
            <?php
            $time_string = '<time class="post-date entry-date published updated" datetime="%1$s">%2$s</time>';
            $time_string = sprintf($time_string, get_the_date(DATE_W3C), get_the_date(), get_the_modified_date(DATE_W3C), get_the_modified_date()
            );
            echo $time_string;
            ?>
        </li>

        <!-- Post Categories -->
        <?php if (!empty(wp_get_post_categories(get_the_ID()))): ?>

            <li class="post-categories">
                <span class="dashicons dashicons-category"></span>
                <?php the_category(', '); ?>
            </li>

        <?php endif; ?>

    </ul>
</div>