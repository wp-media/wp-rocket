<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Media\Fonts\Admin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Queue\AbstractASQueue;
use Exception;

class Data extends AbstractASQueue {
	/**
	 * Options data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Base path.
	 *
	 * @var string
	 */
	private $base_path;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options   = $options;
		$this->base_path = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH', '' ) . 'fonts/' . get_current_blog_id() . '/';
	}

	/**
	 * Schedule data collection.
	 *
	 * @return void
	 */
	public function schedule_data_collection() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->schedule_recurring( time(), WEEK_IN_SECONDS, 'rocket_fonts_data_collection' );
	}

	/**
	 * Unschedule data collection.
	 *
	 * @return void
	 */
	public function unschedule_data_collection() {
		$this->cancel( 'rocket_fonts_data_collection' );
	}

	/**
	 * Collect data.
	 *
	 * @return void
	 */
	public function collect_data() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$fonts_data = get_transient( 'rocket_fonts_data_collection' );

		// If data has been populated, bail out early.
		if ( false !== $fonts_data ) {
			return;
		}

		try {
			$fonts = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $this->base_path . 'google-fonts/fonts/' ) );
		} catch ( Exception $exception ) {
			return;
		}

		$allowed_extensions = [
			'woff',
			'woff2',
			'ttf',
			'otf',
		];

		$total_font_count = 0;
		$total_font_size  = 0;

		foreach ( $fonts as $file ) {
			// check file is not a directory.
			if ( $file->isDir() ) {
				continue;
			}

			$extension = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );

			if ( in_array( $extension, $allowed_extensions, true ) ) {
				++$total_font_count;
				$total_font_size += $file->getSize();
			}
		}

		set_transient(
			'rocket_fonts_data_collection',
			[
				'fonts_total_number' => $total_font_count,
				'fonts_total_size'   => size_format( $total_font_size ),
			],
			WEEK_IN_SECONDS
		);
	}

	/**
	 * Check if the feature & analytics are enabled.
	 *
	 * @return bool
	 */
	private function is_enabled(): bool {
		return $this->options->get( 'host_fonts_locally', 0 ) && $this->options->get( 'analytics_enabled', 0 );
	}
}
