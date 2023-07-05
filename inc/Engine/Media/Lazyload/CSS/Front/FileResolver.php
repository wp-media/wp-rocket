<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class FileResolver
{
	public function resolve(string $url): string {
		$parsed_url = wp_parse_url( $url );
		$home_parsed_url = wp_parse_url( home_url(), PHP_URL_HOST );
		$host_url = key_exists('host', $parsed_url) ? $parsed_url['host'] : $home_parsed_url;

		if($host_url !== $home_parsed_url || ! key_exists('path', $parsed_url)) {
			return '';
		}

		$path = $parsed_url['path'];

		return get_home_path() . $path;
	}
}
