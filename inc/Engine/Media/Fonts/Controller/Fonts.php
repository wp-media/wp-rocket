<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
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
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Instantiate the class.
	 *
	 * @param Filesystem $filesystem Filesystem Instance.
	 * @param Context    $context Context instance.
	 */
	public function __construct(
		Filesystem $filesystem,
		Context $context
	) {
		$this->filesystem = $filesystem;
		$this->context    = $context;
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
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->filesystem->set_version( $version );

		$this->filesystem->write_font_css( $font_url, $provider );
	}
}
