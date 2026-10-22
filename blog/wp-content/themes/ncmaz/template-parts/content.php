<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div id="ncmaz-single-entry-content" class="entry-content prose prose-neutral !max-w-screen-md lg:prose-lg mx-auto dark:prose-invert <?php echo esc_attr($args['has_sidebar'] ? "entry-content--has-sidebar" : "entry-content--not-has-sidebar"); ?>">
		<?php if ($post->post_status === "pending") : ?>
			<?php
			$singleAlertProps = (object)[];
			$singleAlertProps->children = __('You are previewing a pending post.', 'ncmaz');
			$singleAlertProps->type = "info";
			?>
			<div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($singleAlertProps)); ?>"></div>
		<?php endif; ?>
		<?php if ($post->post_status === "draft") : ?>
			<?php
			$singleAlertProps = (object)[];
			$singleAlertProps->children = __('Your post is in Draft mode.', 'ncmaz');
			$singleAlertProps->type = "info";
			?>
			<div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($singleAlertProps)); ?>"></div>
		<?php endif; ?>
		<?php
		the_content(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__('Continue reading<span class="screen-reader-text"> "%s"</span>', 'ncmaz'),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post(get_the_title())
			)
		);

		// ECHO CLEAR BOTH
		echo '<div class="clear-both"></div>';

		wp_link_pages(
			array(
				'before' => '<div class="page-links mt-10">',
				'after'  => '</div>',
			)
		);
		?>
		<div class="clear-both"></div>
	</div>
	<div class="clear-both"></div>

	<!-- ENTRY FOOTER TAGS -->
	<footer class="entry-footer">
		<?php ncmaz_entry_footer(); ?>
	</footer>

	<!-- ENTRY AUTHOR -->
	<?php get_template_part('template-parts/single-page/single-entry-author') ?>

	<!-- COMMENTS -->
	<?php
	// If comments are open or we have at least one comment, load up the comment template.
	if (comments_open() || get_comments_number()) :
		comments_template();
	endif;
	?>

</article><!-- #post-<?php the_ID(); ?> -->