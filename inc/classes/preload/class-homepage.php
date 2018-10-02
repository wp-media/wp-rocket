<?php
namespace WP_Rocket\Preload;

/**
 * Preloads the homepage and the internal URLs on it
 *
 * @since 3.2
 * @author Remy Perona
 */
class Homepage {
	/**
	 * Preload Process instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Sitemap_Process
	 */
	private $preload_process;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Sitemap_Process $preload_process Preload process instance.
	 */
	public function __construct( Sitemap_Process $preload_process ) {
		$this->preload_process = $preload_process;
	}

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

		$this->preload_process->save()->dispatch();
	}

	/**
	 * Cancels any preload process running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function cancel_preload() {
		if ( \method_exists( $this->preload_process, 'cancel_process' ) ) {
			$this->preload_process->cancel_process();
		}

		delete_transient( 'rocket_sitemap_preload_running' );
		delete_transient( 'rocket_sitemap_preload_complete' );
	}
}
