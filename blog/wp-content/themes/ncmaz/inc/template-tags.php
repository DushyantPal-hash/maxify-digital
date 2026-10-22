<?php

/**
 * 
 */
function getAuthorImgUrlById(int $useId = 0)
{
	$userImgUrl = get_avatar_url($useId);
	if (defined('_NCMAZ_FRONTEND_VERSION')) {
		$userImg = get_field('featured_image', 'user_' . $useId);
		if ($userImg) {
			$userImgUrl =  $userImg['sizes']['medium'];
		}
	}
	return $userImgUrl;
}


if (!function_exists('ncmaz_entry_footer')) :

	function ncmaz_entry_footer()
	{
		echo '<div class="max-w-screen-md mx-auto pb-10 border-b border-neutral-200 dark:border-neutral-700">';
		$post_tags = get_the_tags();
		if ($post_tags && !empty($post_tags)) {
			echo '<ul class="mt-10 flex flex-wrap">';
			foreach ($post_tags as $post_tag) {
				echo '<li><a class="nc-Tag inline-block bg-white text-sm text-neutral-600 py-1.5 px-3 rounded-lg border border-neutral-100 md:py-2 md:px-4 dark:bg-neutral-700 dark:text-neutral-300 dark:border-neutral-700 hover:border-neutral-200 dark:hover:border-neutral-500 mr-2 mb-2" href="' . get_tag_link($post_tag) . '">' . $post_tag->name . '</a></li>';
			}
			echo '</ul>';
		}
		echo '<div class="mt-7">';
		get_template_part('template-parts/single-page/single-entry-footer-meta-area');
		echo '</div>';
		echo '</div>';
	}
endif;

if (!function_exists('ncmaz_post_thumbnail')) :
	function ncmaz_post_thumbnail()
	{
		if (!is_singular()) {
			return;
		}
		if (post_password_required() || is_attachment() || !has_post_thumbnail()) {
			return;
		}
?>
		<div class="post-thumbnail mt-10">
			<?php the_post_thumbnail('post-thumbnail', [
				'class' => 'w-full m-0 rounded-xl'
			]); ?>
		</div>
<?php
	}
endif;

if (!function_exists('wp_body_open')) :

	function wp_body_open()
	{
		do_action('wp_body_open');
	}
endif;


// 

if (!function_exists('ncmaz_the_posts_navigation')) {
	/**
	 * Print the next and previous posts navigation.
	 *
	 * @since Twenty Twenty-One 1.0
	 *
	 * @return void
	 */
	function ncmaz_the_posts_navigation()
	{
		the_posts_pagination(
			array(
				'before_page_number' => '',
				'prev_text'          => sprintf(
					'<span class="nav-prev-text">%s</span>',
					'<i class="las la-arrow-left"></i>',
				),
				'next_text'          => sprintf(
					'<span class="nav-next-text">%s</span>',
					'<i class="las la-arrow-right"></i>'
				),
			)
		);
	}
}
