<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Provider;

use WP_Rocket\Engine\Media\Fonts\Provider\GoogleFont\CSS2Handler;
use WP_Rocket\Engine\Media\Fonts\Provider\GoogleFont\CSSHandler;

class Provider {
	/**
	 * Array of providers
	 *
	 * @var array
	 */
	private $providers;

	private $css_handler;

	private $css2_handler;

	private $provider_methods = [
		'google_font'  => 'google_font_provider',
		'font_awesome' => 'font_awesome_provider',
	];

	public function __construct(
		array $providers,
		CSSHandler $css_handler,
		CSS2Handler $css2_handler
	) {
		$this->css2_handler = $css2_handler;
		$this->css_handler  = $css_handler;
		$this->providers    = $providers;
	}

    /**
     * Process font link
     * @param string $html
     *
     * @return array
    */
	public function process( string $html ): array {
		$fonts = [];
		foreach ( $this->providers as $provider ) {
			if ( isset( $this->provider_methods[ $provider ] ) ) {
				$font_provider = $this->provider_methods[ $provider ];
				$fonts[]       = $this->$font_provider( $html );
			}
		}

		return array_merge( ...$fonts );
	}

    /**
     * Google font provider
     *
     * @param string $html
     *
     * @return array
    */
	private function google_font_provider( string $html ): array {
		return array_merge(
			$this->css_handler->get_font_from_html( $html ),
			$this->css2_handler->get_font_from_html( $html )
		);
	}
}
