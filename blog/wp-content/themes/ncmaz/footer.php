<?php
$hasWidget = is_active_sidebar('footer-1-sidebar') || is_active_sidebar('footer-2-sidebar') || is_active_sidebar('footer-3-sidebar') || is_active_sidebar('footer-4-sidebar');
?>

<footer id="colophon" class="nc-Footer relative border-t border-neutral-200 dark:border-neutral-700 <?php echo esc_attr($hasWidget ? " py-16 lg:py-28 " : "  "); ?>">
	<?php if ($hasWidget) : ?>
		<div class="container grid grid-cols-2 gap-y-10 gap-x-5 sm:gap-x-8 md:grid-cols-4 lg:grid-cols-5 lg:gap-x-10 text-sm overflow-hidden">

			<!-- WIDGET 1 -->
			<div class="nc-Footer-item">
				<?php if (is_active_sidebar('footer-1-sidebar')) : ?>
					<?php dynamic_sidebar('footer-1-sidebar'); ?>
				<?php endif; ?>
			</div>

			<!-- WIDGET 2 -->
			<div class="nc-Footer-item">
				<?php if (is_active_sidebar('footer-2-sidebar')) : ?>
					<?php dynamic_sidebar('footer-2-sidebar'); ?>
				<?php endif; ?>
			</div>

			<!-- WIDGET 3 -->
			<div class="nc-Footer-item">
				<?php if (is_active_sidebar('footer-3-sidebar')) : ?>
					<?php dynamic_sidebar('footer-3-sidebar'); ?>
				<?php endif; ?>
			</div>

			<!-- WIDGET 4 -->
			<div class="nc-Footer-item col-span-2">
				<?php if (is_active_sidebar('footer-4-sidebar')) : ?>
					<?php dynamic_sidebar('footer-4-sidebar'); ?>
				<?php endif; ?>
			</div>

		</div>
	<?php endif; ?>
</footer>

<!-- MOBILE MENU MODAL -->
<?php
get_template_part('template-parts/header/mobile-nav');
?>

<!-- SIGNIN/SIGNUP MODAL -->
<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
	<?php get_template_part('template-parts/footer/footer-modal-form-sign-up'); ?>
	<?php get_template_part('template-parts/footer/footer-modal-form-sign-in'); ?>
	<?php get_template_part('template-parts/footer/footer-modal-form-forgot-password'); ?>
<?php endif; ?>

<?php wp_footer(); ?>
</body>

</html>