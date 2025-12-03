<?php

namespace WP_Rocket\Engine\License\API;

class User {
	/**
	 * The user object
	 *
	 * @var object
	 */
	private $user;

	/**
	 * Instantiate the class
	 *
	 * @param object|false $user The user object.
	 */
	public function __construct( $user ) {
		$this->user = is_object( $user ) ? $user : new \stdClass();
	}

	/**
	 * Set the user object.
	 *
	 * @param object $user The user object.
	 *
	 * @return void
	 */
	public function set_user( $user ) {
		$this->user = $user;
	}

	/**
	 * Gets the user license type
	 *
	 * @return int
	 */
	public function get_license_type() {
		if ( ! isset( $this->user->licence_account ) ) {
			return 0;
		}

		return (int) $this->user->licence_account;
	}

	/**
	 * Gets the user license expiration timestamp
	 *
	 * @return int
	 */
	public function get_license_expiration() {
		if ( ! isset( $this->user->licence_expiration ) ) {
			return 0;
		}

		return (int) $this->user->licence_expiration;
	}

	/**
	 * Checks if the user license is expired
	 *
	 * @return boolean
	 */
	public function is_license_expired() {
		return time() > $this->get_license_expiration();
	}

	/**
	 * Gets the user license creation date
	 *
	 * @return int
	 */
	public function get_creation_date() {
		if ( ! isset( $this->user->date_created ) ) {
			return time();
		}

		return (int) $this->user->date_created > 0
			? (int) $this->user->date_created
			: time();
	}

	/**
	 * Checks if user has auto-renew enabled
	 *
	 * @return boolean
	 */
	public function is_auto_renew() {
		if ( ! isset( $this->user->has_auto_renew ) ) {
			return false;
		}

		return (bool) $this->user->has_auto_renew;
	}

	/**
	 * Gets the upgrade to plus URL
	 *
	 * @return string
	 */
	public function get_upgrade_plus_url() {
		if ( ! isset( $this->user->upgrade_plus_url ) ) {
			return '';
		}

		return $this->user->upgrade_plus_url;
	}

	/**
	 * Gets the upgrade to infinite url
	 *
	 * @return string
	 */
	public function get_upgrade_infinite_url() {
		if ( ! isset( $this->user->upgrade_infinite_url ) ) {
			return '';
		}

		return $this->user->upgrade_infinite_url;
	}

	/**
	 * Gets the renewal url
	 *
	 * @return string
	 */
	public function get_renewal_url() {
		if ( ! isset( $this->user->renewal_url ) ) {
			return '';
		}

		return $this->user->renewal_url;
	}

	/**
	 * Checks if the user license has expired for more than 15 days
	 *
	 * @return boolean
	 */
	public function is_license_expired_grace_period() {
		if ( $this->is_license_expired() && ( time() - $this->get_license_expiration() > 15 * 24 * 60 * 60 ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get available upgrades from the API.
	 *
	 * @return array
	 */
	public function get_available_upgrades() {
		if ( empty( $this->user->licence->prices->upgrades ) ) {
			return [];
		}
		return (array) $this->user->licence->prices->upgrades;
	}

	/**
	 * Gets the addon license expiration timestamp
	 *
	 * @since 3.20
	 *
	 * @return int
	 */
	public function get_rocket_insights_license_expiration() {
		if ( ! isset( $this->user->performance_monitoring->expiration ) ) {
			return 0;
		}

		return (int) $this->user->performance_monitoring->expiration;
	}

	/**
	 * Checks if the addon license is active
	 *
	 * @param string $sku The SKU of the addon.
	 *
	 * @since 3.20
	 *
	 * @return boolean
	 */
	public function is_rocket_insights_addon_active( string $sku ) {
		return 'perf-monitor-free' !== $sku;
	}

	/**
	 * Checks if license is on free plan.
	 *
	 * @param string $sku The SKU of the addon.
	 *
	 * @since 3.20
	 *
	 * @return boolean
	 */
	public function is_rocket_insights_free_active( string $sku ) {
		return 'perf-monitor-free' === $sku;
	}

	/**
	 * Retrieves the active SKU for the Rocket Insights Addon.
	 *
	 * @since 3.20
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_sku_active(): string {
		if ( ! isset( $this->user->performance_monitoring ) || ! isset( $this->user->performance_monitoring->active_sku ) ) {
			return 'perf-monitor-free';
		}

		return (string) $this->user->performance_monitoring->active_sku;
	}

	/**
	 * Retrieves the Rocket Insights addon upgrade SKUs based on the provided SKU.
	 *
	 * @param string $sku The SKU for which to retrieve the upgrade data.
	 *
	 * @return array
	 */
	public function get_rocket_insights_addon_upgrade_skus( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );
		if ( ! $plan || ! isset( $plan->upgrades ) ) {
			return [];
		}

		return $plan->upgrades;
	}

	/**
	 * Retrieves the button text for the Rocket Insights addon based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_btn_text( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );
		if ( ! $plan ) {
			return '';
		}

		$label = $plan->button->label;

		// Translate known button labels.
		if ( 'Get Advanced' === $label ) {
			return __( 'Get Rocket Insights', 'rocket' );
		}

		if ( 'Your plan' === $label ) {
			return __( 'Your plan', 'rocket' );
		}

		return $label;
	}

	/**
	 * Retrieves the URL for the Rocket Insights add-on button associated with the specified SKU.
	 *
	 * @param string $sku The SKU identifier used to fetch.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_btn_url( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan ) {
			return '';
		}

		if ( ! isset( $plan->button->url ) || '' === $plan->button->url ) {
			return '';
		}

		$url = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_insights_upgrade=true#rocket_insights' );

		return add_query_arg( 'dashboard_url', rawurlencode( $url ), $plan->button->url );
	}

	/**
	 * Retrieves the limit for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return int
	 */
	public function get_rocket_insights_addon_limit( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan || ! isset( $plan->limit ) ) {
			return 3;
		}

		return (int) $plan->limit;
	}

	/**
	 * Retrieves the subtitle for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_subtitle( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan || ! isset( $plan->subtitle ) ) {
			return '';
		}

		if ( 'See how your top pages perform and quickly spot and optimize what slows your site down.' === $plan->subtitle ) {
			return __( 'See how your top pages perform and quickly spot and optimize what slows your site down.', 'rocket' );
		}

		return $plan->subtitle;
	}

	/**
	 * Retrieves the billing for the PMA add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_billing( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan || ! isset( $plan->billing ) ) {
			return '';
		}

		if ( '* Billed monthly. You can cancel at any time, each month started is due.' === $plan->billing ) {
			return __( '* Billed monthly. You can cancel at any time, each month started is due.', 'rocket' );
		}

		return $plan->billing;
	}


	/**
	 * Retrieves the highlights for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return array
	 */
	public function get_rocket_insights_addon_highlights( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan || ! isset( $plan->highlights ) ) {
			return [];
		}

		$highlights = [];

		foreach ( $plan->highlights as $highlight ) {
			if ( 'Up to 10 pages tracked' === $highlight ) {
				// translators: %1$s opening strong tag, %2$s number of pages, %3$s closing strong tag.
				$highlights [] = sprintf( __( 'Up to %1$s%2$s pages%3$s tracked', 'rocket' ), '<strong>', '10', '</strong>' );
				continue;
			}

			if ( 'Automatic performance monitoring' === $highlight ) {
				// translators: %1$s opening strong tag, %2$s closing strong tag.
				$highlights [] = sprintf( __( 'Automatic %1$sperformance monitoring%2$s', 'rocket' ), '<strong>', '</strong>' );
				continue;
			}

			if ( 'Unlimited on-demand tests' === $highlight ) {
				// translators: %1$s opening strong tag, %2$s closing strong tag.
				$highlights [] = sprintf( __( 'Unlimited %1$son-demand tests%2$s', 'rocket' ), '<strong>', '</strong>' );
				continue;
			}

			if ( 'Full GTmetrix performance reports' === $highlight ) {
				// translators: %1$s opening strong tag, %2$s closing strong tag.
				$highlights [] = sprintf( __( 'Full %1$sGTmetrix performance reports%2$s', 'rocket' ), '<strong>', '</strong>' );
				continue;
			}

			$highlights [] = $highlight;
		}

		return $highlights;
	}

	/**
	 * Checks if the Rocket Insights add-on has a promo based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return bool
	 */
	public function has_rocket_insights_addon_promo( string $sku ) {
		return $this->get_rocket_insights_addon_promo( $sku ) !== false;
	}

	/**
	 * Retrieves the price for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_price( string $sku ) {
		$data = $this->get_rocket_insights_data( $sku );

		if ( ! $data || ! isset( $data->price ) ) {
			return '';
		}

		return $data->price;
	}

	/**
	 * Retrieves the promo price for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_promo_price( string $sku ) {
		$promo = $this->get_rocket_insights_addon_promo( $sku );

		if ( ! $promo || ! isset( $promo->price ) ) {
			return '';
		}

		return $promo->price;
	}

	/**
	 * Retrieves the promo name for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_promo_name( string $sku ) {
		$promo = $this->get_rocket_insights_addon_promo( $sku );

		if ( ! $promo || ! isset( $promo->name ) ) {
			return '';
		}

		if ( 'Launch Offer' === $promo->name ) {
			return __( 'Launch Offer', 'rocket' );
		}

		return $promo->name;
	}

	/**
	 * Retrieves the promo billing for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return string
	 */
	public function get_rocket_insights_addon_promo_billing( string $sku ) {
		$promo = $this->get_rocket_insights_addon_promo( $sku );
		if ( ! $promo || ! isset( $promo->billing ) ) {
			return '';
		}

		if ( 'Launch price valid for the first 12 months, after which standard pricing applies.' === $promo->billing ) {
			return __( 'Launch price valid for the first 12 months, after which standard pricing applies.', 'rocket' );
		}

		return $promo->billing;
	}

	/**
	 * Retrieves the promo data for the Rocket Insights add-on based on the provided SKU.
	 *
	 * @param string $sku The SKU used to fetch the Rocket Insights addon data.
	 *
	 * @return false|object
	 */
	protected function get_rocket_insights_addon_promo( string $sku ) {
		$plan = $this->get_rocket_insights_data( $sku );

		if ( ! $plan || ! isset( $plan->promo ) ) {
			return false;
		}

		if ( ! isset( $plan->promo->expires_at ) || ( (int) $plan->promo->expires_at ) < time() ) {
			return false;
		}

		return $plan->promo;
	}

	/**
	 * Checks if the user account is from a reseller license
	 *
	 * @since 3.20
	 *
	 * @return boolean
	 */
	public function is_reseller_account() {
		if ( ! isset( $this->user->is_reseller ) ) {
			return false;
		}

		return (bool) $this->user->is_reseller;
	}

	/**
	 * Retrieves the Rocket Insights plan data associated with the specified SKU.
	 *
	 * @param string $sku The SKU identifier used to find the corresponding Rocket Insights plan.
	 *
	 * @return object|null
	 */
	protected function get_rocket_insights_data( string $sku ) {

		if ( ! isset( $this->user->performance_monitoring ) || ! isset( $this->user->performance_monitoring->plans ) ) {
			return null;
		}

		foreach ( $this->user->performance_monitoring->plans as $plan ) {
			if ( $plan->sku === $sku ) {
				return $plan;
			}
		}
		return null;
	}
}
