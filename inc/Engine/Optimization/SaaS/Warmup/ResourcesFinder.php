<?php

namespace WP_Rocket\Engine\Optimization\SaaS\Warmup;

use \WP_Rocket_WP_Async_Request;

class ResourcesFinder extends WP_Rocket_WP_Async_Request {
	protected $prefix = 'rocket';
	protected $action = 'launch_warmup';
	private $styles = [];
	private $scripts = [];

	public function __construct( ResourcesFetcher $fetcher ) {
		parent::__construct();

		$this->fetcher = $fetcher;
	}

	protected function handle() {
		$html = wp_unslash( $_POST['html'] );

		if ( empty( $html ) ) {
			return;
		}

		$this->find_styles( $html );
		$this->find_scripts( $html );

		$this->queue_styles();
		$this->queue_scripts();

		$this->fetcher->save()->dispatch();
	}

	private function get_homepage_html() {
		$response = wp_safe_remote_get(
			home_url(),
			[
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	private function find_styles( $html ) {
		$links = $this->find( '<link(?:[^>]+[\s"\'])?href\s*=\s*[\'"]\s*(?<url>[^\'"\s]+)\s*?[\'"](?:[^>]+)?\/?>', $html );

		if ( empty( $links ) ) {
			return;
		}

		foreach ( $links as $link ) {
			if ( ! (bool) preg_match( '/rel=[\'"]stylesheet[\'"]/is', $link[0] ) ) {
				continue;
			}

			$this->styles[] = rocket_add_url_protocol( $link['url'] );
		}
	}

	private function find_scripts( $html ) {
		$scripts = $this->find( '<script\s+(?:[^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"\s]+)\s*?[\'"](?:[^>]+)?\/?>', $html );

		if ( empty( $scripts ) ) {
			return;
		}

		foreach ( $scripts as $script ) {
			$this->scripts[] = rocket_add_url_protocol( $script['url'] );
		}
	}

	private function find( $pattern, $html ) {
		if ( ! preg_match_all( '/' . $pattern . '/is', $html, $matches, PREG_SET_ORDER ) ) {
			return [];
		}

		if ( empty( $matches ) ) {
			return [];
		}

		return $matches;
	}

	private function queue_styles() {
		if ( empty( $this->styles ) ) {
			return;
		}

		foreach ( $this->styles as $style ) {
			if ( get_option( $style, false ) ) {
				continue;
			}

			$this->fetcher->push_to_queue(
				[
					'url'  => $style,
					'type' => 'css',
				]
			);
		}
	}

	private function queue_scripts() {
		if ( empty( $this->scripts ) ) {
			return;
		}

		foreach ( $this->scripts as $script ) {
			if ( get_option( $script, false ) ) {
				continue;
			}

			$this->fetcher->push_to_queue(
				[
					'url'  => $script,
					'type' => 'js',
				]
			);
		}
	}
}
