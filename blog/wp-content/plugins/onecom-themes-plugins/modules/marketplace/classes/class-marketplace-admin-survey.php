<?php
if (!defined('ABSPATH')) {
	exit;
}

class Marketplace_Admin_Survey {

	const SURVEY_VERSION   = 'v1';
	const TRANSIENT_KEY    = 'mp_admin_survey_clicked';

	const POPUP_CLOSED_KEY = 'mp_admin_survey_popup_closed';

	const SCRIPT_HANDLE    = 'mp-admin-survey-js';

	public function __construct() {
		add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
		add_action('admin_footer', [$this, 'renderMarkup']);
		add_action('wp_ajax_mp_admin_mark_survey_completed', [$this, 'markSurveyCompleted']);
		add_action('wp_ajax_mp_admin_mark_survey_popup_closed', [$this, 'markPopupClosed']);
	}

	/**
	 * Check if admin already completed a survey
	 */
	private function isUserCompleted(): bool
	{
		return (bool) get_user_meta(get_current_user_id(), self::TRANSIENT_KEY.'_'.self::SURVEY_VERSION);
	}

	/**
	 * Restrict to Marketplace admin pages
	 */
	private function isMarketplaceScreen($hook = ''): bool
	{
		return ($hook === 'one-com_page_onecom-marketplace');
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueueAssets($hook): void
	{
		if ( ! $this->isEligibleForSurvey() ) {
			return;
		}

		if (!$this->isMarketplaceScreen($hook)) {
			return;
		}

		if ($this->isUserCompleted()) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			plugin_dir_url(__FILE__) . '../assets/js/admin-survey.js',
			['jquery'],
			'1.0',
			true
		);

		wp_localize_script(self::SCRIPT_HANDLE, 'MPAdminSurvey', [
			'survey_url' 		=> 'https://www.surveymonkey.com/r/JQR9H8K',
			'delay'      		=> 20000,
			'ajax_url'   		=> admin_url('admin-ajax.php'),
			'nonce'      		=> wp_create_nonce('mp_admin_survey_nonce'),
			'close_nonce'      	=> wp_create_nonce('mp_admin_survey_popup_closed'),
			'popup_closed' 		=> get_user_meta(get_current_user_id(), self::POPUP_CLOSED_KEY.'_'.self::SURVEY_VERSION, true) ?? false,
			'survey_clicked' 	=> get_user_meta(get_current_user_id(), self::TRANSIENT_KEY.'_'.self::SURVEY_VERSION, true) ?? false
		]);
	}

	/**
	 * Render admin HTML
	 */
	public function renderMarkup(): void
	{
		if ( ! $this->isEligibleForSurvey() ) {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		$hook = $screen->id ?? null;

		if (!$this->isMarketplaceScreen($hook)) {
			return;
		}

		if ($this->isUserCompleted()) {
			return;
		}

		ob_start();
		?>
		<div class="gv-activated">
		<!-- Floating bubble icon -->
		<div id="mp-survey-bubble" class="mp-survey-bubble">
				<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/survey-bubble.svg" />
		</div>

		<!-- Survey popover -->
		<div id="mp-survey-popover" class="mp-survey-popover">
			<div class="gv-tour gv-arrow-top-left">

				<div class="gv-tour-body">
					<div class="gv-tour-title"><?php echo __('Tell us what you think','onecom-wp');?></div>
					<p><?php echo __('Take a quick survey and help us improve your experience with the Marketplace.','onecom-wp');?></p>
				</div>

				<div class="gv-button-group">
					<button type="button" class="gv-button gv-button-cancel mp-close-btn gv-text-capitalize">
						<?php echo __('close','onecom-wp');?>
					</button>

					<a target="_blank" rel="noopener"
					   class="gv-button gv-button-primary mp-survey-cta">
						<?php echo __('Take survey','onecom-wp');?><img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/open_in_new.svg" height="16" width="16" alt="External link"/>
					</a>
				</div>

				<button type="button" class="gv-tour-close mp-close" aria-label="Close">
					<img src="<?php echo GROUPONE_WP_URL; ?>modules/marketplace/assets/images/close-white.svg" height="24" width="24" alt="Close"/>
				</button>

			</div>
		</div>
		</div>
		<?php
		echo ob_get_clean();
	}

	/**
	 * AJAX → mark survey completed
	 */
	public function markSurveyCompleted(): void
	{
		if ( ! $this->isEligibleForSurvey() ) {
			return;
		}

		check_ajax_referer('mp_admin_survey_nonce', 'nonce');

		update_user_meta(get_current_user_id(), self::TRANSIENT_KEY.'_'.self::SURVEY_VERSION, true);

		wp_send_json_success(array(
			'message' => 'Survey clicked',
			'success' => true,
			'key' => self::TRANSIENT_KEY.'_'.self::SURVEY_VERSION
		));
	}

	public function markPopupClosed(): void
	{
		if ( ! $this->isEligibleForSurvey() ) {
			return;
		}

		check_ajax_referer('mp_admin_survey_popup_closed', 'nonce');

		update_user_meta(get_current_user_id(), self::POPUP_CLOSED_KEY.'_'.self::SURVEY_VERSION, true);

		wp_send_json_success(array(
			'message' => 'Popup Closed',
			'success' => true,
			'key' => self::POPUP_CLOSED_KEY.'_'.self::SURVEY_VERSION
		));
	}

	public function isEligibleForSurvey(): bool
	{
		return is_user_logged_in() && current_user_can('manage_options');
	}
}