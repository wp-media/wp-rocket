<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Busting\Google_Analytics;

/**
 * Manages the cache busting of the Google Tag Manager file
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tag_Manager extends Abstract_Busting {
	/**
	 * {@inheritdoc}
	 */
	public function __construct( $busting_path, $busting_url, Google_Analytics $ga_busting ) {
		$blog_id            = get_current_blog_id();
		$this->busting_path = $busting_path . $blog_id . '/';
		$this->busting_url  = $busting_url . $blog_id . '/';
		$this->filename     = 'gtm-local.js';
		$this->ga_busting   = $ga_busting;
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_url( $crawler ) {
		$node = $this->find( $crawler );

		if ( ! $node ) {
			return $crawler->saveHTML();
		}

		$url = $this->get_url( $node );

		if ( ! $url ) {
			return $crawler->saveHTML();
		}

		if ( ! $this->save( $url ) ) {
			return $crawler->saveHTML();
		}

		$this->replace( $node );

		return $crawler->saveHTML();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function find( $crawler ) {
		try {
			$node = $crawler->filter( 'script[src^="https://www.googletagmanager.com"]' );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! $node->count() ) {
			return false;
		}

		return $node;
	}

	/**
	 * Gets the URL from the node
	 *
	 * @param HtmlPageCrawler $node HtmlPageCrawler instance.
	 * @return mixed
	 */
	private function get_url( $node ) {
		try {
			$url = $node->attr( 'src' );
		} catch ( Exception $e ) {
			return false;
		}

		return $url;
	}

	/**
	 * Saves the content of the URL to bust to the busting file if it doesn't exist yet.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url      URL to get the content from.
	 * @return bool
	 */
	public function save( $url ) {
		$path = $this->busting_path . $this->filename;

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			return true;
		}

		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			return false;
		}

		$content = $this->replace_ga_url( $content );

		if ( ! \rocket_direct_filesystem()->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
		}

		if ( ! \rocket_put_content( $path, $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Replaces the Google Analytics URL by the local copy inside the gtm-local.js file content
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content JavaScript content.
	 * @return string
	 */
	protected function replace_ga_url( $content ) {
		if ( ! $this->ga_busting->save( $this->ga_busting->get_url() ) ) {
			return $content;
		}

		return str_replace( $this->ga_busting->get_url(), $this->ga_busting->get_busting_url(), $content );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function replace( $node ) {
		$node->attr( 'src', $this->get_busting_url() )->attr( 'data-no-minify', 1 );
	}
}
