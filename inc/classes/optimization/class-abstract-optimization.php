<?php
namespace WP_Rocket\Optimization;

use Wa72\HtmlPageDom\HtmlPageCrawler;

/**
 * Base abstract class for files optimization
 *
 * @since 3.1
 * @author Remy Perona
 */
abstract class Abstract_Optimization {
	/**
	 * Crawler Instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var HtmlPageCrawler
	 */
	protected $crawler;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler Crawler instance.
	 */
	public function __construct( HtmlPageCrawler $crawler ) {
		$this->crawler = $crawler;
	}

	/**
	 * Finds nodes matching the pattern in the HTML
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to match.
	 * @return bool|HtmlPageCrawler
	 */
	protected function find( $pattern ) {
		try {
			$nodes = $this->crawler->filter( $pattern );
		} catch ( Exception $e ) {
			return false;
		}

		if ( 0 === $nodes->count() ) {
			return false;
		}

		return $nodes;
	}

	/**
	 * Determines if the file is external
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $url URL of the file.
	 * @return bool True if external, false otherwise
	 */
	protected function is_external_file( $url ) {
		$file       = get_rocket_parse_url( $url );
		$wp_content = get_rocket_parse_url( WP_CONTENT_URL );
		$hosts      = get_rocket_cnames_host( $this->get_zones() );
		$hosts[]    = $wp_content['host'];
		$langs      = get_rocket_i18n_uri();

		// Get host for all langs.
		if ( $langs ) {
			foreach ( $langs as $lang ) {
				$hosts[] = rocket_extract_url_component( $lang, PHP_URL_HOST );
			}
		}

		$hosts_index = array_flip( array_unique( $hosts ) );

		// URL has domain and domain is not part of the internal domains.
		if ( isset( $file['host'] ) && ! empty( $file['host'] ) && ! isset( $hosts_index[ $file['host'] ] ) ) {
			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		if ( ! isset( $file['host'] ) && ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Writes the minified content to a file
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $content       Minified content.
	 * @param string $minified_file Path to the minified file to write in.
	 * @return bool True if successful, false otherwise
	 */
	protected function write_minify_file( $content, $minified_file ) {
		if ( rocket_direct_filesystem()->exists( $minified_file ) ) {
			return true;
		}

		if ( ! rocket_mkdir_p( dirname( $minified_file ) ) ) {
			return false;
		}

		return rocket_put_content( $minified_file, $content );
	}

	/**
	 * Gets the file path from an URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url File URL.
	 * @return string
	 */
	protected function get_file_path( $url ) {
		$hosts         = get_rocket_cnames_host( $this->get_zones() );
		$hosts['home'] = rocket_extract_url_component( home_url(), PHP_URL_HOST );
		$hosts_index   = array_flip( $hosts );

		return rocket_url_to_path( strtok( $url, '?' ), $hosts_index );
	}

	/**
	 * Gets content of a file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $file File path.
	 * @return string
	 */
	protected function get_file_content( $file ) {
		return rocket_direct_filesystem()->get_contents( $file );
	}
}
