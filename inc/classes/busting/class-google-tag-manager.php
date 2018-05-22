<?php
namespace WP_Rocket\Busting;

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
	public function __construct( $busting_path, $busting_url ) {
		$blog_id            = get_current_blog_id();
		$this->busting_path = $busting_path . $blog_id . '/';
		$this->busting_url  = $busting_url . $blog_id . '/';
		$this->filename     = 'gtm-local.js';
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
	 * {@inheritdoc}
	 */
	protected function replace( $node ) {
		$node->attr( 'src', $this->get_busting_url() )->attr( 'data-no-minify', 1 );
	}
}
