<?php
namespace WP_Rocket\Subscriber\CDN\RocketCDN;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the RocketCDN integration in WP Rocket settings page
 *
 * @since 3.5
 * @author Remy Perona
 */
class AdminPageSubscriber extends Abstract_Render implements Subscriber_Interface {
	const ROCKETCDN_API = 'https://rocketcdn.me/api/';

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                       => 'promote_rocketcdn_notice',
			'rocket_dashboard_after_account_data' => 'display_rocketcdn_status',
			'rocket_before_cdn_sections'          => 'display_rocketcdn_cta',
			'rocket_cdn_settings_fields'          => 'rocketcdn_field',
			'wp_ajax_toggle_rocketcdn_cta'        => 'toggle_cta',
			'wp_ajax_rocketcdn_dismiss_notice'    => 'dismiss_notice',
			'admin_footer'                        => 'add_dismiss_script',
		];
	}

	/**
	 * Adds notice to promote Rocket CDN on settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function promote_rocketcdn_notice() {
		if ( ! $this->should_display_notice() ) {
			return;
		}

		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['is_active'] ) {
			return;
		}

		echo $this->generate( 'promote-notice' );
	}

	/**
	 * Adds inline script to permanently dismissing the RocketCDN promotion notice
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_dismiss_script() {
		if ( ! $this->should_display_notice() ) {
			return;
		}

		$nonce = wp_create_nonce( 'rocketcdn_dismiss_notice' );
		?>
		<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelector( '#rocketcdn-promote-notice .notice-dismiss' );

			dismissBtn.addEventListener( 'click', function( event ) {
				var httpRequest = new XMLHttpRequest(),
					postData    = '';

				postData += 'action=rocketcdn_dismiss_notice';
				postData += '&nonce=<?php echo esc_html( $nonce ); ?>';
				httpRequest.open( 'POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>' );
				httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
				httpRequest.send( postData );
			});
		});
		</script>
		<?php
	}

	/**
	 * Ajax callback to save the dismiss as a user meta
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'rocketcdn_dismiss_notice', 'nonce', true );

		if ( ! isset( $_POST['action'] ) || 'rocketcdn_dismiss_notice' !== $_POST['action'] ) {
			return;
		}

		update_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );
	}

	/**
	 * Checks if the promotion notice should be displayed
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function should_display_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return false;
		}

		if ( get_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Displays the Rocket CDN section on the dashboard tab
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function display_rocketcdn_status() {
		$subscription_data = $this->get_subscription_data();
		$label             = 'Rocket CDN';
		$status_text       = __( 'No Subscription', 'rocket' );
		$status_class      = 'wpr-isInvalid';
		$container_class   = 'wpr-flex--egal';

		if ( $subscription_data['is_active'] ) {
			$label          .= ' ' . __( 'Next Billing Date', 'rocket' );
			$status_class    = 'wpr-isValid';
			$container_class = '';
		}

		if ( 'cancelled' !== $subscription_data['subscription_status'] ) {
			$status_text = date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['subscription_next_date_update'] ) );
		}

		$data = [
			'container_class' => $container_class,
			'label'           => $label,
			'status_class'    => $status_class,
			'status_text'     => $status_text,
			'is_active'       => $subscription_data['is_active'],
		];

		echo $this->generate( 'dashboard-status', $data );
	}

	/**
	 * Displays the Rocket CDN Call to Action on the CDN tab of WP Rocket settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function display_rocketcdn_cta() {
		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['is_active'] ) {
			return;
		}

		$pricing            = $this->get_pricing_data();
		$current_price      = $pricing['regular_price'];
		$regular_price      = '';
		$promotion_campaign = $pricing['discount_campaign_name'];
		$promotion_end_date = date_i18n( get_option( 'date_format' ), strtotime( $pricing['end_date'] ) );
		$nopromo_variant    = '--no-promo';
		$cta_small_class    = 'wpr-isHidden';
		$cta_big_class      = '';

		if ( get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) ) {
			$cta_small_class = '';
			$cta_big_class   = 'wpr-isHidden';
		}

		if ( $pricing['is_discount_active'] ) {
			$regular_price   = $current_price;
			$current_price   = $pricing['discounted_price'];
			$nopromo_variant = '';
		}

		$small_cta_data = [
			'container_class' => $cta_small_class,
		];

		$big_cta_data = [
			'container_class'    => $cta_big_class,
			'promotion_campaign' => $promotion_campaign,
			'promotion_end_date' => $promotion_end_date,
			'nopromo_variant'    => $nopromo_variant,
			'regular_price'      => $regular_price,
			'current_price'      => $current_price,
		];

		echo $this->generate( 'cta-small', $small_cta_data );
		echo $this->generate( 'cta-big', $big_cta_data );
	}

	/**
	 * Adds the Rocket CDN fields to the CDN section
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param array $fields CDN settings fields.
	 * @return array
	 */
	public function rocketcdn_field( $fields ) {
		$subscription_data = $this->get_subscription_data();

		if ( ! $subscription_data['is_active'] ) {
			return $fields;
		}

		$fields['cdn_cnames'] = [
			'type'        => 'rocket_cdn',
			'label'       => __( 'CDN CNAME(s)', 'rocket' ),
			'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
			'helper'      => __( 'Rocket CDN is currently active.', 'rocket' ) . ' <button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">' . __( 'Unsubscribe', 'rocket' ) . '</button>',
			'default'     => '',
			'section'     => 'cnames_section',
			'page'        => 'page_cdn',
		];

		return $fields;
	}

	/**
	 * Toggles display of the Rocket CDN CTAs on the settings page
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_cta() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! isset( $_POST['action'], $_POST['status'] ) || 'toggle_rocketcdn_cta' !== $_POST['action'] ) {
			return;
		}

		if ( 'big' === $_POST['status'] ) {
			delete_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden' );
		} elseif ( 'small' === $_POST['status'] ) {
			update_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true );
		}
	}

	/**
	 * Gets current RocketCDN subscription data
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function get_subscription_data() {
		$status = get_transient( 'rocketcdn_status' );

		if ( false !== $status ) {
			return $status;
		}

		$default = [
			'is_active'                     => false,
			'subscription_next_date_update' => 0,
			'subscription_status'           => 'cancelled',
		];

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'website/?url=' . home_url()
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			set_transient( 'rocketcdn_status', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = json_decode( $data );
		$data = array_intersect_key( $data, $default );

		set_transient( 'rocketcdn_status', $data, WEEK_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets pricing & promotion data for RocketCDN
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function get_pricing_data() {
		$pricing = get_transient( 'rocketcdn_pricing' );

		if ( false !== $pricing ) {
			return $pricing;
		}

		$default = [
			'regular_price'          => 79.0,
			'is_discount_active'     => false,
			'discounted_price'       => 69.0,
			'discount_campaign_name' => '',
			'end_date'               => 0,
		];

		$response = wp_remote_get(
			self::ROCKETCDN_API . 'pricing'
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			set_transient( 'rocketcdn_pricing', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( empty( $data ) ) {
			set_transient( 'rocketcdn_pricing', $default, WEEK_IN_SECONDS );

			return $default;
		}

		$data = json_decode( $data, true );
		$data = array_intersect_key( $data, $default );

		set_transient( 'rocketcdn_pricing', $data, WEEK_IN_SECONDS );

		return $data;
	}
}
