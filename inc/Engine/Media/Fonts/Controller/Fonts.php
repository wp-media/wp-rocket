<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RegexTrait;

class Fonts {
	use RegexTrait;

	/**
	 * Filesystem instance.
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Filesystem   $filesystem Filesystem Instance.
	 * @param Options_Data $options    Options instance.
	 */
	public function __construct(
		Filesystem $filesystem,
		Options_Data $options
	) {
		$this->filesystem = $filesystem;
		$this->options    = $options;
	}

	/**
	 * Start the process of downloading font locally
	 *
	 * @param string  $font_url URL of the font to be saved locally.
	 * @param string  $provider Provider of the font.
	 * @param integer $version  Version.
	 *
	 * @return void
	 */
	public function process( string $font_url, string $provider, int $version ): void {
		if ( ! $this->options->get( 'host_google_fonts' ) ) {
			return;
		}

		$this->filesystem->set_version( $version );

		$this->filesystem->write_font_css( $font_url, $provider );
	}
}
