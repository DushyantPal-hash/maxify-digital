<?php
if (post_password_required()) {
	return;
}
?>

<div id="comments" class="comments-area max-w-screen-md mx-auto mt-14">

	<?php

	// COMMENT FORM
	comment_form();

	// You can start editing here -- including this comment!
	if (have_comments()) :
	?>
		<h3 class="comments-title text-xl font-semibold text-neutral-800 dark:text-neutral-200 mt-10">
			<?php
			$ncmaz_comment_count = get_comments_number();
			if ('1' === $ncmaz_comment_count) {
				printf(
					/* translators: 1: title. */
					esc_html__('One thought on &ldquo;%1$s&rdquo;', 'ncmaz'),
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			} else {
				printf(
					/* translators: 1: comment count number, 2: title. */
					esc_html(_nx('%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $ncmaz_comment_count, 'comments title', 'ncmaz')),
					number_format_i18n($ncmaz_comment_count), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<span>' . wp_kses_post(get_the_title()) . '</span>'
				);
			}
			?>
		</h3><!-- .comments-title -->

		<?php the_comments_navigation(); ?>

		<ol class="comment-list mt-5">
			<?php
			wp_list_comments(
				'callback=mytheme_comment'
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_navigation();

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if (!comments_open()) : ?>
			<p class="no-comments"><?php esc_html_e('Comments are closed.', 'ncmaz'); ?></p>
	<?php
		endif;
	endif;
	// End Check for have_comments().


	?>

</div>

<?php

function mytheme_comment($comment, $args, $depth)
{
	if ('div' === $args['style']) {
		$tag       = 'div';
		$add_below = 'comment';
	} else {
		$tag       = 'li';
		$add_below = 'div-comment';
	} ?>
	<<?php echo esc_html($tag); ?> <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?> id="comment-<?php comment_ID() ?>">
		<?php
		if ('div' != $args['style']) { ?>
			<div id="div-comment-<?php comment_ID() ?>" class="comment-body nc-CommentCard flex ">
			<?php } ?>
			<div class="comment-author vcard">
				<div class="wil-avatar relative flex-shrink-0 inline-flex items-center justify-center overflow-hidden text-neutral-100 uppercase font-semibold shadow-inner rounded-full h-6 w-6 text-base mt-4">
					<?php
					if ($args['avatar_size'] != 0) {
						echo get_avatar($comment, $args['avatar_size']);
					} ?>
				</div>


			</div>
			<div class="relative flex-grow flex flex-col p-4 ml-2 text-sm border border-neutral-200 rounded-xl sm:ml-3 sm:text-base dark:border-neutral-700 overflow-hidden">
				<div class="relative flex items-center pr-6">

					<div class="comment-body__author-link flex-shrink-0 font-semibold text-neutral-800 dark:text-neutral-100">
						<?php echo get_comment_author_link(); ?>
					</div>
					<span class="comment-body__dot mx-2">·</span>
					<a class="comment-body__date text-neutral-500 dark:text-neutral-400 text-xs line-clamp-1 sm:text-sm" href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)); ?>">
						<?php
						printf(
							__('%1$s at %2$s', 'ncmaz'),
							get_comment_date(),
							get_comment_time()
						); ?>
					</a>

				</div>
				<?php
				if ($comment->comment_approved == '0') { ?>
					<em class="comment-awaiting-moderation block text-red-500 text-xs italic font-light">
						<?php echo esc_html__('Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.', 'ncmaz'); ?>
					</em>
				<?php 	} ?>

				<div class="prose prose-neutral prose prose-sm sm:prose dark:prose-invert mt-2 mb-3 sm:mt-3 sm:mb-4">
					<?php comment_text(); ?>
				</div>

				<div class="reply flex justify-between items-center">

					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'reply_text' => __('<p class="inline-flex items-center min-w-[68px] rounded-full text-neutral-6000 bg-neutral-100 dark:text-neutral-200 dark:bg-neutral-800 px-3 h-8 hover:bg-teal-50 hover:text-teal-600 dark:hover:text-teal-500 focus:outline-none"> <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg><span class="text-xs leading-none text-neutral-900 dark:text-neutral-200">Reply</span></p>', 'ncmaz'),
								'add_below' => $add_below,
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],

							)
						)
					);
					?>

					<div class="absolute right-2.5 bottom-1">
						<?php edit_comment_link(__('<i class="las la-edit"></i>', 'ncmaz'), '  ', ''); ?>
					</div>
				</div>
				<?php if ('div' != $args['style']) : 	?>
			</div>
			</div>
	<?php
				endif;
			}
