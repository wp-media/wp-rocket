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
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		$subscription_data = $this->get_subscription_data();

		if ( $subscription_data['active'] ) {
			return;
		}

		echo $this->generate( 'promote-notice' );
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

		if ( $subscription_data['active'] ) {
			$label          .= ' ' . __( 'Next Billing Date', 'rocket' );
			$status_text     = date_i18n( get_option( 'date_format' ), strtotime( $subscription_data['next_renewal'] ) );
			$status_class    = 'wpr-isValid';
			$container_class = '';
		}

		$data = [
			'container_class'     => $container_class,
			'label'               => $label,
			'status_class'        => $status_class,
			'status_text'         => $status_text,
			'subscription_status' => $subscription_data['active'],
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

		if ( $subscription_data['active'] ) {
			return;
		}

		$pricing            = $this->get_pricing_data();
		$current_price      = $pricing['regular_price'];
		$regular_price      = '';
		$promotion_campaign = $pricing['promotion']['campaign'];
		$promotion_end_date = date_i18n( get_option( 'date_format' ), strtotime( $pricing['promotion']['end_date'] ) );
		$nopromo_variant    = empty( $pricing['promotion']['campaign'] ) ? '--no-promo' : '';
		$cta_small_class    = 'wpr-isHidden';
		$cta_big_class      = '';

		if ( get_user_meta( get_current_user_id(), 'rocket_rocketcdn_cta_hidden', true ) ) {
			$cta_small_class = '';
			$cta_big_class   = 'wpr-isHidden';
		}

		if ( ! empty( $pricing['promotion']['price'] ) ) {
			$regular_price = $current_price;
			$current_price = $pricing['promotion']['price'];
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

		if ( ! $subscription_data['active'] ) {
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
		return [
			'active'       => false,
			'next_renewal' => '2020-04-10T13:59:42.356081Z',
		];
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
		return [
			'regular_price' => '$7.99',
			'promotion'     => [
				'campaign' => '',
				'price'    => '',
				'end_date' => '',
			],
		];
	}
}
