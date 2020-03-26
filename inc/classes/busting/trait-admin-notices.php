<?php

namespace WP_Rocket\Busting;

/**
 * Display admin notices.
 *
 * @since  3.6
 * @author Grégory Viguier
 */
trait AdminNotices {
	/**
	 * Display an admin notice if the cache folder is not writable.
	 *
	 * @since  3.6
	 * @uses   $this->busting_factory
	 * @uses   $this->busting_types
	 * @uses   $this->vendor_name
	 * @uses   $this->is_busting_active()
	 * @author Grégory Viguier
	 */
	public function busting_dir_not_writable_admin_notice() {
		if ( ! $this->is_busting_active() || ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$dir_paths = [];

		foreach ( $this->busting_types as $busting_type ) {
			$dir_paths[] = $this->busting_factory->type( $busting_type )->get_busting_dir_path();
		}

		$dir_paths  = array_unique( $dir_paths );
		$filesystem = rocket_direct_filesystem();

		foreach ( $dir_paths as $i => $dir_path ) {
			if ( ! $filesystem->exists( $dir_path ) ) {
				rocket_mkdir_p( $dir_path );
			}
			if ( $filesystem->exists( $dir_path ) && $filesystem->is_writable( $dir_path ) ) {
				unset( $dir_paths[ $i ] );
			} else {
				$dir_paths[ $i ] = '<code>' . esc_html( trim( str_replace( ABSPATH, '', $dir_path ), '/' ) ) . '</code>';
			}
		}

		if ( ! $dir_paths ) {
			return;
		}

		$message  = '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>';
		$message .= sprintf(
			/* translators: %1$s is a list of folder paths, %2$s is a vendor name. */
			_n( 'The folder %1$s used to cache %2$s tracking scripts could not be created or is missing writing permissions.', 'The folders %1$s used to cache %2$s tracking scripts could not be created or are missing writing permissions.', count( $dir_paths ), 'rocket' ),
			wp_sprintf_l( '%l', $dir_paths ),
			$this->vendor_name
		);
		$message .= '<br>' . sprintf(
			/* translators: This is a doc title! %1$s = opening link; %2$s = closing link */
			__( 'Troubleshoot: %1$sHow to make system files writeable%2$s', 'rocket' ),
			/* translators: Documentation exists in EN, DE, FR, ES, IT; use loaclised URL if applicable */
			'<a href="' . __( 'https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) . '" target="_blank">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}
}
