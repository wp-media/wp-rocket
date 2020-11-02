<?php

namespace WP_Rocket\Engine\Optimization\SaaS\Warmup;

class ResourcesFinder {
	public function __construct( ResourcesFetcher $fetcher ) {
		$this->fetcher = $fetcher;
	}

	public function warmup() {
		$html = $this->get_homepage_html();

		if ( empty( $html ) ) {
			return;
		}

		$this->find_resources( $html );
	}

	private function get_homepage_html() {
		$response = wp_safe_remote_get( home_url() );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	private function find_resources( $html ) {
		
	}
}
