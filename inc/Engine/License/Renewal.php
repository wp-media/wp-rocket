<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Renewal extends Abstract_Render {
	/**
	 * Pricing instance
	 *
	 * @var Pricing
	 */
	private $pricing;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Pricing      $pricing       Pricing instance.
	 * @param User         $user          User instance.
	 * @param Options_Data $options       Options_Data instance.
	 * @param string       $template_path Path to the views.
	 */
	public function __construct( Pricing $pricing, User $user, Options_Data $options, $template_path ) {
		parent::__construct( $template_path );

		$this->pricing = $pricing;
		$this->user    = $user;
		$this->options = $options;
	}

	/**
	 * Displays the renewal banner for users expiring in less than 30 days
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_soon_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( $this->user->is_license_expired() ) {
			return;
		}

		if ( ! $this->is_expired_soon() ) {
			return;
		}

		$data              = $this->get_banner_data();
		$data['countdown'] = $this->get_countdown_data();
		$discount          = esc_html( '$' . number_format_i18n( $this->get_discount_percent(), 2 ) );
		$price             = esc_html( '$' . number_format_i18n( $this->get_price(), 2 ) );

				$data['message'] = sprintf(
			// translators: %1$s = <strong>, %2$s = price, %3$s = </strong>.
			esc_html__( 'Renew before it is too late, you will only pay %1$s%2$s%3$s!', 'rocket' ),
				'<strong>',
				$price,
				'</strong>'
			);

		if ( $this->get_discount_percent() ) {
			$data['message'] = sprintf(
			// translators: %1$s = <strong>, %2$s = discount, %3$s = </strong>,%4$s = <strong>, %5$s = price, %6$s=</strong>.
			esc_html__( 'Renew with a %1$s%2$s discount%3$s before it is too late, you will only pay %4$s%5$s%6$s!', 'rocket' ),
				'<strong>',
				$discount,
				'</strong>',
				'<strong>',
				$price,
				'</strong>'
			);
		}

		echo $this->generate( 'renewal-soon-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays the renewal banner for expired users
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_expired_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( 0 === $this->user->get_license_expiration() ) {
			return;
		}

		if ( ! $this->user->is_license_expired() ) {
			return;
		}

		if ( false !== get_transient( 'rocket_renewal_banner_' . get_current_user_id() ) ) {
			return;
		}

		$expiration    = $this->user->get_license_expiration();
		$expired_since = ( time() - $expiration ) / DAY_IN_SECONDS;

		if (
			$this->user->is_auto_renew()
			&&
			4 > $expired_since
		) {
			return;
		}

		$ocd_enabled = $this->options->get( 'optimize_css_delivery', 0 );
		$renewal_url = $this->user->get_renewal_url();
		$price       = esc_html( '$' . number_format_i18n( $this->get_price(), 2 ) );

		$message = sprintf(
			// translators: %1$s = <strong>, %2$s = </strong>, %3$s = price.
			esc_html__( 'Renew your license for 1 year now at %1$s%3$s%2$s.', 'rocket' ),
			'<strong>',
			'</strong>',
			$price
		);

		if (
			( $this->is_grandfather() || $this->has_grandmother() )
			&&
			$expired_since < 15
		) {
			$message = sprintf(
				// translators: %1$s = <strong>, %2$s = </strong>, %3$s = discount percentage, %4$s = price.
				esc_html__( 'Renew your license for 1 year now and get %1$s%3$s OFF%2$s immediately: you will only pay %1$s%4$s%2$s!', 'rocket' ),
				'<strong>',
				'</strong>',
				esc_html( '$' . number_format_i18n( $this->get_discount_percent(), 2 ) ),
				$price
			);
		}

		if ( $ocd_enabled ) {
			if ( 15 > $expired_since ) {
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->generate(
					'renewal-expired-banner-ocd',
					[
						'renewal_url'   => $renewal_url,
						'message'       => $message,
						'disabled_date' => date_i18n( get_option( 'date_format' ), $expiration + 15 * DAY_IN_SECONDS ),
					]
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			} elseif ( 90 > $expired_since ) {
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->generate(
					'renewal-expired-banner-ocd-disabled',
					[
						'renewal_url' => $renewal_url,
						'message'     => $message,
					]
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			} elseif ( 90 < $expired_since ) {
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->generate(
					'renewal-expired-banner',
					[
						'renewal_url' => $renewal_url,
						'message'     => $message,
					]
				);
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} elseif ( ! $ocd_enabled ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->generate(
				'renewal-expired-banner',
				[
					'renewal_url' => $renewal_url,
					'message'     => $message,
				]
			);
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Get base data to display in the banners
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	private function get_banner_data() {
		$price = esc_html( '$' . number_format_i18n( $this->get_price(), 2 ) );

		$message = sprintf(
			// translators: %1$s = <strong>, %2$s = </strong>, %3$s = discount price.
			esc_html__( 'Renew before it is too late, you will pay %1$s%3$s%2$s.', 'rocket' ),
			'<strong>',
			'</strong>',
			$price
		);

		if ( $this->is_grandfather() ) {
			$message = sprintf(
				// translators: %1$s = <strong>, %2$s = discount percentage, %3$s = </strong>, %4$s = discount price.
				esc_html__( 'Renew with a %1$s%2$s discount%3$s before it is too late, you will only pay %1$s%4$s%3$s!', 'rocket' ),
				'<strong>',
				esc_html( '$' . number_format_i18n( $this->get_discount_percent(), 2 ) ),
				'</strong>',
				$price
			);
		}

		return [
			'message'     => $message,
			'renewal_url' => $this->user->get_renewal_url(),
		];
	}

	/**
	 * AJAX callback to dismiss the renewal banner for expired users
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function dismiss_renewal_expired_banner() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$transient = 'rocket_renewal_banner_' . get_current_user_id();

		if ( false !== get_transient( $transient ) ) {
			return;
		}

		set_transient( $transient, 1, MONTH_IN_SECONDS );

		wp_send_json_success();
	}

	/**
	 * Adds the license expiration time to WP Rocket localize script data
	 *
	 * @since 3.7.5
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_localize_script_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = (array) $data;
		}

		if ( $this->user->is_license_expired() ) {
			return $data;
		}

		if ( ! $this->is_expired_soon() ) {
			return $data;
		}

		$data['license_expiration'] = $this->user->get_license_expiration();

		return $data;
	}

	/**
	 * Checks if the license expires in less than 30 days
	 *
	 * @since 3.7.5
	 *
	 * @return boolean
	 */
	private function is_expired_soon() {
		if ( $this->user->is_auto_renew() ) {
			return false;
		}

		$expiration_delay = $this->user->get_license_expiration() - time();

		return 30 * DAY_IN_SECONDS > $expiration_delay;
	}

	/**
	 * Gets the discount corresponding to the current user status
	 *
	 * @since 3.7.5
	 *
	 * @return int
	 */
	private function get_discount_percent() {
		$prices = $this->get_license_pricing_data();

		$renewals = $this->get_user_renewal_status();

		if ( false === $renewals || ! isset( $prices->prices, $prices->prices->renewal ) ) {
			return 0;
		}

		$prices = $prices->prices;

		if ( $renewals['is_grandfather'] ) {
			return isset( $prices->renewal->is_grandfather, $prices->renewal->not_grandfather ) ? $prices->renewal->not_grandfather - $prices->renewal->is_grandfather : 0;
		}

		if ( $renewals['is_grandmother'] ) {
			return isset( $prices->renewal->is_grandmother, $prices->renewal->not_grandfather ) ? $prices->renewal->not_grandfather - $prices->renewal->is_grandmother : 0;
		}

		return 0;
	}

	/**
	 * Is user grandfathered
	 *
	 * @return bool
	 */
	private function is_grandfather(): bool {
		$renewals = $this->get_user_renewal_status();

		if ( ! is_array( $renewals ) ) {
			return false;
		}

		return key_exists( 'is_grandfather', $renewals ) && $renewals['is_grandfather'];
	}
	/**
	 * Is user grandmothered
	 *
	 * @return bool
	 */
	private function has_grandmother(): bool {
		$renewals = $this->get_user_renewal_status();

		if ( ! is_array( $renewals ) ) {
			return false;
		}

		return key_exists( 'is_grandmother', $renewals ) && $renewals['is_grandmother'];
	}

	/**
	 * Gets the price corresponding to the current user status
	 *
	 * @since 3.7.5
	 *
	 * @return int
	 */
	private function get_price() {
		$renewals = $this->get_user_renewal_status();

		if ( false === $renewals ) {
			return 0;
		}

		$license = $this->get_license_pricing_data();

		if (
			$renewals['is_grandfather']
			&&
			! $renewals['is_expired']
		) {
			return isset( $license->prices->renewal->is_grandfather ) ? $license->prices->renewal->is_grandfather : 0;
		}

		if ( $renewals['is_grandmother'] &&
			! $renewals['is_expired'] ) {
			return isset( $license->prices->renewal->is_grandmother ) ? $license->prices->renewal->is_grandmother : 0;
		}

		return isset( $license->prices->renewal->not_grandfather ) ? $license->prices->renewal->not_grandfather : 0;
	}

	/**
	 * Gets the user renewal status
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	private function get_user_renewal_status() {
		$renewals = $this->pricing->get_renewals_data();

		if ( ! isset( $renewals->extra_days, $renewals->grandfather_date, $renewals->discount_percent, $renewals->grandmother_date ) ) {
			return false;
		}

		return [
			'discount_percent' => $renewals->discount_percent,
			'is_expired'       => time() > ( $this->user->get_license_expiration() + ( $renewals->extra_days * DAY_IN_SECONDS ) ),
			'is_grandfather'   => $renewals->grandfather_date > $this->user->get_creation_date(),
			'is_grandmother'   => $renewals->grandmother_date > $this->user->get_creation_date(),
		];
	}

	/**
	 * Gets the license pricing data corresponding to the user license
	 *
	 * @since 3.7.5
	 *
	 * @return object|null
	 */
	private function get_license_pricing_data() {
		$license       = $this->user->get_license_type();
		$plus_websites = $this->pricing->get_plus_websites_count();

		if ( $license === $plus_websites ) {
			return $this->pricing->get_plus_pricing();
		} elseif (
			$license >= $this->pricing->get_single_websites_count()
			&&
			$license < $plus_websites
		) {
			return $this->pricing->get_single_pricing();
		}

		return $this->pricing->get_infinite_pricing();
	}

	/**
	 * Gets the countdown data to display for the renewal soon banner
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	private function get_countdown_data() {
		$data = [
			'days'    => 0,
			'hours'   => 0,
			'minutes' => 0,
			'seconds' => 0,
		];

		if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
			return $data;
		}

		$expiration = $this->user->get_license_expiration();

		if ( 0 === $expiration ) {
			return $data;
		}

		$now = date_create();
		$end = date_timestamp_set( date_create(), $expiration );

		if ( $now > $end ) {
			return $data;
		}

		$remaining = date_diff( $now, $end );
		$format    = explode( ' ', $remaining->format( '%d %H %i %s' ) );

		$data['days']    = $format[0];
		$data['hours']   = $format[1];
		$data['minutes'] = $format[2];
		$data['seconds'] = $format[3];

		return $data;
	}

	/**
	 * Add license expiring warning to OCD label
	 *
	 * @param array $args Setting field arguments.
	 *
	 * @return array
	 */
	public function add_license_expire_warning( $args ): array {
		if ( 'optimize_css_delivery' !== $args['id'] ) {
			return $args;
		}

		if ( ! $this->user->is_license_expired() ) {
			return $args;
		}

		$ocd           = $this->options->get( 'optimize_css_delivery', 0 );
		$whitelabel    = rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT', false );
		$expired_since = ( time() - $this->user->get_license_expiration() ) / DAY_IN_SECONDS;
		$message       = ' <span class="wpr-icon-important wpr-checkbox-warning">';

		if (
			(
				$whitelabel
				&&
				15 > $expired_since
				&&
				$ocd
			)
			||
			(
				! $whitelabel
				&&
				$this->user->is_auto_renew()
				&&
				4 > $expired_since
			)
			||
			(
				$whitelabel
				&&
				$this->user->is_auto_renew()
				&&
				4 > $expired_since
				&&
				! $ocd
			)
		) {
			return $args;
		} elseif (
			! $whitelabel
			&&
			15 > $expired_since
			&&
			$ocd
		) {
			$message .= sprintf(
				// translators: %1$s = <a>, %2$s = </a>.
				__( 'You need a valid license to continue using this feature. %1$sRenew now%2$s before losing access.', 'rocket' ),
				'<a href="' . esc_url( $this->user->get_renewal_url() ) . '" target="_blank">',
				'</a>'
			);
		} elseif (
			(
				! $whitelabel
				&&
				15 < $expired_since
			)
			||
			(
				! $whitelabel
				&&
				15 > $expired_since
				&&
				! $ocd
			)
		) {
			$message .= sprintf(
				// translators: %1$s = <a>, %2$s = </a>.
				__( 'You need an active license to enable this option. %1$sRenew now%2$s.', 'rocket' ),
				'<a href="' . esc_url( $this->user->get_renewal_url() ) . '" target="_blank">',
				'</a>'
			);
		} elseif (
			(
				$whitelabel
				&&
				15 < $expired_since
			)
			||
			(
				$whitelabel
				&&
				15 > $expired_since
				&&
				! $ocd
			)
		) {
			$doc    = 'https://docs.wp-rocket.me/article/1711-what-happens-if-my-license-expires';
			$locale = current( array_slice( explode( '_', get_user_locale() ), 0, 1 ) );

			if ( 'fr' === $locale ) {
				$doc = 'https://fr.docs.wp-rocket.me/article/1712-que-se-passe-t-il-si-ma-licence-expire';
			}

			$message .= sprintf(
				// translators: %1$s = <a>, %2$s = </a>.
				__( 'You need an active license to enable this option. %1$sMore info%2$s.', 'rocket' ),
				'<a href="' . $doc . '?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">',
				'</a>'
			);
		}

		$message .= '</span>';

		$args['label'] = $args['label'] . $message;

		return $args;
	}

	/**
	 * Adds the notification bubble to WP Rocket menu item when expired
	 *
	 * @param string $menu_title Menu title.
	 *
	 * @return string
	 */
	public function add_expired_bubble( $menu_title ): string {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT', false ) ) {
			return $menu_title;
		}

		if ( ! $this->user->is_license_expired() ) {
			return $menu_title;
		}

		if ( false !== get_transient( 'wpr_dashboard_seen_' . get_current_user_id() ) ) {
			return $menu_title;
		}

		$expired_since = ( time() - $this->user->get_license_expiration() ) / DAY_IN_SECONDS;
		$auto_renew    = $this->user->is_auto_renew();
		$ocd_enabled   = $this->options->get( 'optimize_css_delivery', 0 );

		if (
			$ocd_enabled
			&&
			$auto_renew
			&&
			4 > $expired_since
		) {
			return $menu_title;
		}

		if (
			! $auto_renew
			&&
			! $ocd_enabled
			&&
			4 < $expired_since

		) {
			return $menu_title;
		}

		if (
			$auto_renew
			&&
			! $ocd_enabled
			&&
			(
				4 > $expired_since
				||
				15 < $expired_since
			)
		) {
			return $menu_title;
		}

		return $menu_title . ' <span class="awaiting-mod">!</span>';
	}

	/**
	 * Sets the dashboard seen transient to hide the expired bubble
	 *
	 * @return void
	 */
	public function set_dashboard_seen_transient() {
		if ( ! $this->user->is_license_expired() ) {
			return;
		}

		if ( ! $this->options->get( 'optimize_css_delivery', 0 ) ) {
			return;
		}

		$current_user = get_current_user_id();

		if ( false !== get_transient( "wpr_dashboard_seen_{$current_user}" ) ) {
			return;
		}

		$expired_since = ( time() - $this->user->get_license_expiration() ) / DAY_IN_SECONDS;

		if ( 15 > $expired_since ) {
			set_transient( "wpr_dashboard_seen_{$current_user}", 1, 15 * DAY_IN_SECONDS );
		} elseif ( 15 < $expired_since ) {
			set_transient( "wpr_dashboard_seen_{$current_user}", 1, YEAR_IN_SECONDS );
		}
	}

	/**
	 * Disable optimize CSS delivery setting
	 *
	 * @param array $args Array of setting field arguments.
	 *
	 * @return array
	 */
	public function maybe_disable_ocd( $args ) {
		if ( 'optimize_css_delivery' !== $args['id'] ) {
			return $args;
		}

		if ( ! $this->user->is_license_expired() ) {
			return $args;
		}

		$expired_since = ( time() - $this->user->get_license_expiration() ) / DAY_IN_SECONDS;

		if (
			15 > $expired_since
			||
			(
				$this->user->is_auto_renew()
				&&
				4 > $expired_since
			)
		) {
			return $args;
		}

		$args['value'] = 0;

		if (
			isset( $args['container_class'] )
			&&
			! in_array( 'wpr-isDisabled', $args['container_class'], true )
		) {
			$args['container_class'][] = 'wpr-isDisabled';
		}

		$args['input_attr']['disabled'] = 1;

		return $args;
	}

	/**
	 * Disables the RUCSS & Async CSS options if license is expired since more than 15 days
	 *
	 * @param mixed $value Current option value.
	 *
	 * @return mixed
	 */
	public function maybe_disable_option( $value ) {
		$expired_since = ( time() - $this->user->get_license_expiration() ) / DAY_IN_SECONDS;

		if ( 15 > $expired_since ) {
			return $value;
		}

		return 0;
	}
}
