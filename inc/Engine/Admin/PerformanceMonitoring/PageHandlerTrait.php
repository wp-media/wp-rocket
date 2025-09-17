<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

trait PageHandlerTrait {
	/**
	 * Extracts and sanitizes the page title from the provided HTML string.
	 *
	 * This method attempts to find the <title> tag in the given HTML, decodes any HTML entities,
	 * strips all tags, sanitizes the text, and then trims the title at common separators
	 * (such as " | ", " - ", " – ", " » ") to return a clean, concise page title.
	 *
	 * @param string $html The HTML content from which to extract the page title.
	 *
	 * @return string The sanitized and trimmed page title, or an empty string if not found.
	 */
	public function get_page_title( string $html ): string {
		$title = '';

		if ( empty( $html ) ) {
			return $title;
		}

		// Extract title from title tag.
		if ( ! preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
			return $title;
		}

		// Clean up and sanitize the title.
		$title = html_entity_decode( trim( $matches[1] ), ENT_QUOTES, 'UTF-8' );

		if ( empty( $title ) ) {
			return $title;
		}

		$title = wp_strip_all_tags( $title );
		$title = sanitize_text_field( $title );

		return $title;
	}


	/**
	 * Fetches the HTML content of a given URL using a custom user agent.
	 *
	 * Performs a remote GET request to the specified URL, simulating a mobile browser user agent,
	 * and returns the response body if the request is successful (HTTP 200).
	 *
	 * @param string $url The URL to fetch the HTML content from.
	 *
	 * @return string|false The HTML content of the page on success, or false on failure.
	 */
	public function get_page_content( string $url ) {
		$user_agent = 'WP Rocket/Fetch Page Buffer for Performance Monitoring Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';
		$args       = [
			'user-agent' => $user_agent,
			'timeout'    => 60,
		];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}
}
