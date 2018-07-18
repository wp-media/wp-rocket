<?php
namespace WP_Rocket\Traits;

trait Config_Updater {
	/**
	 * Update htaccess and WP Rocket config file if the option was modified.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $old_value Option's previous value.
	 * @param string $value     Option's new value.
	 * @return void
	 */
	public function after_update_single_option( $old_value, $value ) {
		if ( $old_value !== $value ) {
			$this->flush_htaccess();
			$this->generate_config_file();
		}
	}

	/**
	 * Sets the htaccess update request
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	protected function flush_htaccess() {
		wp_cache_set( 'rocket_flush_htaccess', 1 );
	}

	/**
	 * Sets WP Rocket config file update request
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	protected function generate_config_file() {
		wp_cache_set( 'rocket_generate_config_file', 1 );
	}

	/**
	 * Performs the files update if requested
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function maybe_update_config() {
		if ( wp_cache_get( 'rocket_flush_htaccess' ) ) {
			flush_rocket_htaccess();
			wp_cache_delete( 'rocket_flush_htaccess' );
		}

		if ( wp_cache_get( 'rocket_generate_config_file' ) ) {
			\rocket_generate_config_file();
			wp_cache_delete( 'rocket_generate_config_file' );
		}
	}
}
