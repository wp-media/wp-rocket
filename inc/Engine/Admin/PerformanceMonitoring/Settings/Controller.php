<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Settings;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;

class Controller extends Abstract_Render {
	/**
	 * User API client
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Performance Monitoring context
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $pm_context;

	/**
	 * Instantiate the class
	 *
	 * @param User                         $user          User API client.
	 * @param string                       $template_path Path to the templates.
	 * @param PerformanceMonitoringContext $pm_context Performance Monitoring context.
	 */
	public function __construct( User $user, $template_path, PerformanceMonitoringContext $pm_context ) {
		parent::__construct( $template_path );
		$this->user       = $user;
		$this->pm_context = $pm_context;
	}

	/**
	 * Displays the Add-On license status on the dashboard tab
	 *
	 * @since 3.20
	 *
	 * @return void
	 */
	public function display_addon_status() {
		if ( (bool) rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		// Hide Rocket Insights status for reseller accounts and non-live installations.
		if ( $this->pm_context->is_reseller_or_non_live() ) {
			return;
		}

		$status_class = ' wpr-isInvalid';
		$label        = '';
		$status_text  = __( 'No Subscription', 'rocket' );
		$service_name = __( 'Rocket Insights', 'rocket' );
		$sku          = $this->user->get_pma_addon_sku_active();
		$upgrade_skus = $this->user->get_pma_addon_upgrade_skus( $sku );
		$is_active    = $this->user->is_pma_addon_active( $sku );

		if ( $is_active ) {
			$label        = __( 'Next Billing Date', 'rocket' );
			$status_class = ' wpr-isValid';
			$status_text  = date_i18n( get_option( 'date_format' ), $this->user->get_pma_license_expiration() ); // @phpstan-ignore-line
		}

		$data = [
			'is_live_site'    => rocket_is_live_site(),
			'container_class' => '',
			'label'           => $label,
			'status_class'    => $status_class,
			'status_text'     => $status_text,
			'is_active'       => $is_active,
			'service_name'    => $service_name,
		];

		if ( count( $upgrade_skus ) > 0 ) {
			$upgrade_sku             = array_shift( $upgrade_skus );
			$upgrade_link            = $this->user->get_pma_addon_btn_url( $upgrade_sku );
			$data['upgrade_link']    = $upgrade_link;
			$data['upgrade_text']    = $this->user->get_pma_addon_btn_text( $upgrade_sku );
			$data['container_class'] = ' wpr-flex--egal';
		}

		echo $this->generate( 'dashboard-addon-status', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Check if the current plan is free or not.
	 *
	 * @return bool
	 */
	public function is_free_plan() {
		return $this->user->is_pma_free_active( $this->user->get_pma_addon_sku_active() );
	}
}
