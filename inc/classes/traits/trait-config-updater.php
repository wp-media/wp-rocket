<?php
namespace WP_Rocket\Traits;

trait Config_Updater {
	public function after_update_single_option( $old_value, $value ) {
		if ( $old_value !== $value ) {
			$this->flush_htaccess();
			$this->generate_config_file();
		}
	}

	protected function flush_htaccess() {
		wp_cache_set( 'rocket_flush_htaccess', 1 );
	}

	protected function generate_config_file() {
		wp_cache_set( 'rocket_generate_config_file', 1 );
	}

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
