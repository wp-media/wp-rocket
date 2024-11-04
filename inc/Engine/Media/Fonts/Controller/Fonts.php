<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Engine\Optimization\RegexTrait;

class Fonts {
	use RegexTrait;

	/**
	 * Filesystem instance.
	 *
	 * @var Filesystem
	 */
	private $filesystem;

    private $provider_methods = [
        'google_font'  => 'google_font_provider',
        'font_awesome' => 'font_awesome_provider',
    ];

    /**
     * @param Filesystem $filesystem Filesystem Instance.
    */
	public function __construct(
		Filesystem $filesystem
	) {
		$this->filesystem = $filesystem;
	}

	/**
	 * Process
	 */
	public function process( $font_url, $provider, $version ): void {
        $this->filesystem->write_font_css( $font_url, $provider, $version );
	}
}
