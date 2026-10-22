<?php
global $ncmaz_redux_demo;
$showToogleDarkmode = false;

if ($ncmaz_redux_demo !== null) {
	$showToogleDarkmode = boolval($ncmaz_redux_demo['nc-general-settings--header-switch-enableDarkmode']);
}

$classes =  'nc-Header top-0 w-full left-0 right-0 z-40 ';
if (defined('_NCMAZ_FRONTEND_VERSION')) {
	$classes =  'sticky nc-Header top-0 w-full left-0 right-0 z-40 transition-all ';
}
?>

<header id="masthead" class="<?php echo esc_attr($classes); ?>">
	<div class="nc-Header__MainNav1 relative z-10  ">
		<div class="nc-MainNav1 py-2.5 sm:py-4 z-10 onTop">
			<div class="container relative flex justify-between items-center space-x-4 xl:space-x-8 ">
				<div class="flex justify-start flex-grow items-center space-x-4 sm:space-x-10 2xl:space-x-14">
					<?php get_template_part('template-parts/header/site-branding'); ?>
					<?php get_template_part('template-parts/header/site-nav'); ?>
				</div>
				<div class="flex-shrink-0 flex items-center justify-end text-neutral-700 dark:text-neutral-100">

					<!-- SEARCH AND DARKMODE -->
					<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
						<div class="items-center flex space-x-4 sm:space-x-1">
							<?php
							$SwitchDarkModeProps = (object)[];
							$SwitchDarkModeProps->className = "sm:w-12 h-12"
							?>
							<?php if ($showToogleDarkmode) : ?>
								<div class="hidden sm:block" data-is-react-component="SwitchDarkMode" data-component-props="<?php echo esc_attr(json_encode($SwitchDarkModeProps)); ?>"></div>
							<?php endif; ?>

							<div data-is-react-component="SearchDropdown"></div>

							<?php if (!is_user_logged_in()) : ?>
								<?php if (boolval($ncmaz_redux_demo['nc-header-settings--general--toggle-signIn-btn'])) : ?>
									<!-- YYYYY -->
									<div class="px-1 absolute sm:static hidden sm:block"></div>

									<!-- BUTTON SIGNIN OPEN MODAN SIGN IN -->
									<a data-ncmaz-open-modal="ncmaz-modal-form-sign-in" class="nc-Button relative h-auto inline-flex items-center justify-center rounded-full transition-colors text-sm sm:text-base font-medium px-4 py-3 sm:px-6  ttnc-ButtonPrimary disabled:bg-opacity-70 bg-primary-6000 hover:bg-primary-700 text-neutral-50  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-6000 dark:focus:ring-offset-0" href="/wp-admin">
										<?php echo esc_html__('Sign up', 'ncmaz'); ?>
									</a>
								<?php endif; ?>
							<?php else : ?>
								<?php
								$NavAccountDropdownProps = (object)[
									'data'	=> [],
									'footData' => []
								];

								// MENU1: AUTHOR PAGE
								$NavAccountDropdownProps->data[] = [
									'id'	=>	'menu01',
									'name'	=>	 __('Author page', 'ncmaz'),
									'icon'	=> 	"las la-user-circle text-xl",
									'href'	=>	 esc_url(get_author_posts_url(wp_get_current_user()->ID)),
								];

								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-1']['name'])) {
									$NavAccountDropdownProps->data[] = [
										'id'	=>	'menu1',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-1']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-1']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-1']['url'],
									];
								}
								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-2']['name'])) {
									$NavAccountDropdownProps->data[] = [
										'id'	=>	'menu2',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-2']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-2']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-2']['url'],
									];
								}
								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-3']['name'])) {
									$NavAccountDropdownProps->data[] = [
										'id'	=>	'menu3',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-3']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-3']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-3']['url'],
									];
								}
								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-4']['name'])) {
									$NavAccountDropdownProps->data[] = [
										'id'	=>	'menu4',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-4']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-4']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-4']['url'],
									];
								}

								// MENU FOOTERS 
								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-1']['name'])) {
									$NavAccountDropdownProps->footData[] = [
										'id'	=>	'menuFoot1',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-1']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-1']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-1']['url'],
									];
								}
								if (!empty($ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-2']['name'])) {
									$NavAccountDropdownProps->footData[] = [
										'id'	=>	'menuFoot2',
										'name'	=>	 $ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-2']['name'],
										'icon'	=> 	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-2']['icon'] . ' text-xl',
										'href'	=>	$ncmaz_redux_demo['nc-header-settings--account-dropdown--menu-foot-2']['url'],
									];
								}

								// MENU FOOTER: LOGOUT
								$NavAccountDropdownProps->footData[] = [
									'id'	=>	'menuFoot3',
									'name'	=>	esc_html__('Logout', 'ncmaz'),
									'icon'	=> 	'las la-sign-out-alt text-xl leading-none',
									'href'	=>	 wp_logout_url(get_permalink())
								];
								?>
								<div data-is-react-component="NavAccountDropdown" data-component-props="<?php echo esc_attr(json_encode($NavAccountDropdownProps)); ?>"></div>
							<?php endif; ?>

						</div>
					<?php endif; ?>

					<!-- OPEN MOBILE MODAL MENU -->
					<div class="btn-toogle-mobile-sidebar-menu flex items-center xl:hidden">
						<button class="py-2 pl-3.5 sm:pl-4 pr-0 rounded-lg text-neutral-700 dark:text-neutral-300 focus:outline-none flex items-center justify-center">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
								<path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
							</svg>
						</button>
					</div>

				</div>
			</div>
		</div>
	</div>

	<!-- SINGLE HEADER & SCROLL BAR -->
	<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
		<?php if (is_single() && is_singular()) : ?>
			<div class="nc-SingleHeaderMenu dark text-white py-2.5 bg-neutral-900 dark:bg-neutral-900 transition-opacity absolute inset-x-0 [ -z-50 opacity-0 ]">
				<div class="container">
					<div class="flex justify-end lg:justify-between">

						<div class="hidden lg:flex items-center mr-3">
							<a class="flex items-center space-x-2" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
								<div class="wil-avatar relative flex-shrink-0 inline-flex items-center justify-center overflow-hidden text-neutral-100 uppercase font-semibold  rounded-full shadow-inner h-10 w-10 sm:h-11 sm:w-11 text-xl ring-1 ring-white dark:ring-neutral-900">
									<img class="absolute inset-0 w-full h-full object-cover " src="<?php echo esc_url(getAuthorImgUrlById($post->post_author)); ?>" alt="<?php echo esc_attr(get_the_author()); ?>">
								</div>
							</a>
							<h3 class="ml-4 text-lg line-clamp-1 text-neutral-100">
								<?php the_title(); ?>
							</h3>
						</div>

						<?php get_template_part('template-parts/single-header/single-header-meta-area'); ?>
					</div>
				</div>
				<div class="nc-SingleHeaderMenu__progress-container absolute top-full left-0 w-full progress-container h-[5px] bg-neutral-300 overflow-hidden">
					<div class="nc-SingleHeaderMenu__progress-bar progress-bar h-[5px] w-0 bg-teal-600"></div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

</header>