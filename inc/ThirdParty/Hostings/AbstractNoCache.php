<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;

abstract class NoCacheHost implements ActivationInterface, DeactivationInterface {
	/**
	 * Actions to perform on plugin activation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'no_cache_config' ] );
	}

	/**
	 * Actions to perform on plugin deactivation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'rocket_deactivation', [ $this, 'no_cache_config' ] );
	}

	/**
	 * Prevent writing in advanced-cache.php & wp-config.php when on self-caching host.
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function no_cache_config() {
		add_filter( 'rocket_set_wp_cache_constant', [ $this, 'return_false' ] );
		add_filter( 'rocket_generate_advanced_cache_file', [ $this, 'return_false' ] );
	}
}
