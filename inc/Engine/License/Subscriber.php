<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Upgrade instance
	 *
	 * @var Upgrade
	 */
	private $upgrade;

	/**
	 * Renewal instance
	 *
	 * @var Renewal
	 */
	private $renewal;

	/**
	 * Instantiate the class
	 *
	 * @param Upgrade $upgrade Upgrade instance.
	 * @param Renewal $renewal Renewal instance.
	 */
	public function __construct( Upgrade $upgrade, Renewal $renewal ) {
		$this->upgrade = $upgrade;
		$this->renewal = $renewal;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_license_info'       => 'display_upgrade_section',
			'rocket_settings_page_footer'         => 'display_upgrade_popin',
			'rocket_menu_title'                   => [
				[ 'add_notification_bubble' ],
				[ 'add_notification_bubble_expired' ],
			],
			'admin_footer-settings_page_wprocket' => [
				[ 'dismiss_notification_bubble' ],
				[ 'set_dashboard_seen_transient' ],
			],
			'rocket_before_dashboard_content'     => [
				[ 'display_promo_banner' ],
				[ 'display_renewal_soon_banner', 11 ],
				[ 'display_renewal_expired_banner', 12 ],
			],
			'wp_ajax_rocket_dismiss_promo'        => 'dismiss_promo_banner',
			'wp_ajax_rocket_dismiss_renewal'      => 'dismiss_renewal_banner',
			'rocket_localize_admin_script'        => 'add_localize_script_data',
			'wp_rocket_upgrade'                   => [ 'clean_user_transient', 15, 2 ],
			'rocket_before_add_field_to_settings' => [
				[ 'maybe_disable_ocd', 11 ],
				[ 'add_license_expire_warning' ],
			],
			'get_rocket_option_remove_unused_css' => [ 'maybe_disable_option', PHP_INT_MAX ],
			'get_rocket_option_async_css'         => [ 'maybe_disable_option', PHP_INT_MAX ],
		];
	}

	/**
	 * Displays the upgrade section in the license info block
	 *
	 * @since 3.7.3
	 *
	 * @return void
	 */
	public function display_upgrade_section() {
		$this->upgrade->display_upgrade_section();
	}

	/**
	 * Displays the upgrade popin
	 *
	 * @since 3.7.3
	 *
	 * @return void
	 */
	public function display_upgrade_popin() {
		$this->upgrade->display_upgrade_popin();
	}

	/**
	 * Adds the notification bubble to the menu title if a promotion is active
	 *
	 * @since 3.7.4
	 *
	 * @param string $menu_title The text to be used for the menu.
	 * @return string
	 */
	public function add_notification_bubble( $menu_title ) {
		return $this->upgrade->add_notification_bubble( $menu_title );
	}

	/**
	 * Prevents the notification bubble from showing once the user accessed the dashboard once
	 *
	 * @since 3.7.4
	 *
	 * @return void
	 */
	public function dismiss_notification_bubble() {
		$this->upgrade->dismiss_notification_bubble();
	}

	/**
	 * Displays the promotions banner when a promotion is active
	 *
	 * @since 3.7.4
	 *
	 * @return void
	 */
	public function display_promo_banner() {
		$this->upgrade->display_promo_banner();
	}

	/**
	 * AJAX callback to dismiss the promotion banner
	 *
	 * @since 3.7.4
	 *
	 * @return void
	 */
	public function dismiss_promo_banner() {
		$this->upgrade->dismiss_promo_banner();
	}

	/**
	 * Adds the current time and promotion end time to WP Rocket localize script data
	 *
	 * @since 3.7.5 Add the renewal localize data
	 * @since 3.7.4
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_localize_script_data( $data ) {
		$data = $this->upgrade->add_localize_script_data( $data );

		return $this->renewal->add_localize_script_data( $data );
	}

	/**
	 * Deletes the user data transient on 3.7.4 update
	 *
	 * @since 3.7.4
	 *
	 * @param string $new_version New version of the plugin.
	 * @param string $old_version Installed version of the plugin.
	 * @return void
	 */
	public function clean_user_transient( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.7.4', '>' ) ) {
			return;
		}

		delete_transient( 'wp_rocket_customer_data' );
	}

	/**
	 * Displays the renewal banner for users expiring in less than 30 days
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_soon_banner() {
		$this->renewal->display_renewal_soon_banner();
	}

	/**
	 * Displays the renewal banner for expired users
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_expired_banner() {
		$this->renewal->display_renewal_expired_banner();
	}

	/**
	 * AJAX callback to dismiss the renewal banner
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function dismiss_renewal_banner() {
		$this->renewal->dismiss_renewal_expired_banner();
	}

	/**
	 * Add license expiring warning to OCD label
	 *
	 * @param array $args Setting field arguments.
	 *
	 * @return array
	 */
	public function add_license_expire_warning( $args ): array {
		return $this->renewal->add_license_expire_warning( $args );
	}

	/**
	 * Adds the notification bubble to WP Rocket menu item when expired
	 *
	 * @param string $menu_title Menu title.
	 *
	 * @return string
	 */
	public function add_notification_bubble_expired( $menu_title ) {
		return $this->renewal->add_expired_bubble( $menu_title );
	}

	/**
	 * Sets the dashboard seen transient to hide the expired bubble
	 *
	 * @return void
	 */
	public function set_dashboard_seen_transient() {
		$this->renewal->set_dashboard_seen_transient();
	}

	/**
	 * Disable optimize CSS delivery setting
	 *
	 * @param array $args Array of setting field arguments.
	 *
	 * @return array
	 */
	public function maybe_disable_ocd( $args ) {
		return $this->renewal->maybe_disable_ocd( $args );
	}

	/**
	 * Disables the RUCSS & Async CSS options if license is expired
	 *
	 * @param mixed $value Current option value.
	 *
	 * @return mixed
	 */
	public function maybe_disable_option( $value ) {
		return $this->renewal->maybe_disable_option( $value );
	}
}
