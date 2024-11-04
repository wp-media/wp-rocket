<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Engine\Media\Fonts\Provider\Provider;
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
	 * Provider instance.
	 *
	 * @var Provider
	 */
	private $provider;

    /**
     * @param Provider $provider Provider Instance.
     * @param Filesystem $filesystem Filesystem Instance.
    */
	public function __construct(
		Provider $provider,
		Filesystem $filesystem
	) {
		$this->filesystem = $filesystem;
		$this->provider   = $provider;
	}

	/**
	 * Process
	 */
	public function process( $html ): string {
		global $wp;
		$clean_html = $this->hide_comments( $html );
		$font_links = $this->provider->process( $clean_html );

		if ( empty( $font_links ) ) {
			return $html;
		}
		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		foreach ( $font_links as $link ) {
			$this->filesystem->write_font_css( $url, $link );
		}

		return $html;
	}
}
