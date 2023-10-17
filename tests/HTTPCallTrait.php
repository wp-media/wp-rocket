<?php

namespace WP_Rocket\Tests;

trait HTTPCallTrait
{

	protected $config;

	public function setup_http() {
		add_filter('pre_http_request', [$this, 'http_callback'], 10, 3);
	}

	public function tear_down_http() {
		remove_filter('pre_http_request', [$this, 'http_callback']);
	}

	public function http_callback($response, $args, $url) {

		if (! $this->config && key_exists('http', $this->config)) {
			return $response;
		}
		foreach ($this->config['http'] as $mocked_url => $response_fixture) {
			if($url === $mocked_url) {
				return $response_fixture;
			}
		}

		return $response;
	}
}
