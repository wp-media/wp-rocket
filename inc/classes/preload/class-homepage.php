<?php
namespace WP_Rocket\Preload;

/**
 * Preloads the homepage and the internal URLs on it
 *
 * @since 3.2
 * @author Remy Perona
 */
class Homepage extends Abstract_Preload {
	/**
	 * Gets the internal URLs on the homepage and sends them to the preload queue
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $urls Homepages URLs to preload.
	 * @return void
	 */
	public function preload( $urls ) {
		foreach ( $urls as $home_url ) {
			$response = wp_remote_get( $home_url );

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return;
			}

			$content   = wp_remote_retrieve_body( $response );
			$home_host = wp_parse_url( $home_url, PHP_URL_HOST );

			preg_match_all( '/<a\s+(?:[^>]+?[\s"\']|)href\s*=\s*(["\'])(?<href>[^"\']+)\1/imU', $content, $links );

			array_walk(
				$links['href'],
				function( $link ) use ( $home_host ) {
					$host = wp_parse_url( $link, PHP_URL_HOST );

					if ( $home_host !== $host ) {
						return;
					}

					$this->preload_process->push_to_queue( $link );
				}
			);
		}

		set_transient( 'rocket_preload_running', 0 );
		$this->preload_process->save()->dispatch();
	}
}
