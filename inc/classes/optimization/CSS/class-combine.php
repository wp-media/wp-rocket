<?php
namespace WP_Rocket\Optimization\CSS;

use WP_Rocket\Admin\Options_Data as Options;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use MatthiasMullie\Minify;

/**
 * Minify & Combine CSS files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine extends Abstract_CSS_Optimization {
	use Path_Rewriter;

	/**
	 * Minifier instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Minify\CSS
	 */
	private $minifier;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler  Crawler instance.
	 * @param Options         $options  Options instance.
	 * @param Minify\CSS      $minifier Minifier instance.
	 */
	public function __construct( HtmlPageCrawler $crawler, Options $options, Minify\CSS $minifier ) {
		parent::__construct( $crawler, $options );

		$this->minifier = $minifier;
	}

	/**
	 * Combines multiple Google Fonts links into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function optimize() {
		$nodes = $this->find( 'link[href*=".css"]' );

		if ( ! $nodes ) {
			return $this->crawler->saveHTML();
		}

		$combine_nodes = $nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
			$src = $node->attr( 'href' );

			if ( $this->is_external_file( $src ) ) {
				return;
			}

			if ( $this->is_minify_excluded_file( $node ) ) {
				return;
			}

			return $node;
		} );

		if ( empty( $combine_nodes ) ) {
			return $this->crawler->saveHTML();
		}

		$urls = array_map( function( $node ) {
			return $node->attr( 'href' );
		}, $combine_nodes );

		$minify_url = $this->combine( $urls );

		if ( ! $minify_url ) {
			return $this->crawler->saveHTML();
		}

		if ( ! $this->inject_combined_url( $minify_url ) ) {
			return $this->crawler->saveHTML();
		}

		foreach ( $combine_nodes as $node ) {
			$node->remove();
		}

		return $this->crawler->saveHTML();
	}

	/**
	 * Adds the combined CSS URL to the HTML after the title tag
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $minify_url URL to insert.
	 * @return bool
	 */
	protected function inject_combined_url( $minify_url ) {
		try {
			$this->crawler->filter( 'title' )->after( '<link rel="stylesheet" href="' . $minify_url . '" data-minify="1" />' );
		} catch ( Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $urls Original file URL.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function combine( $urls ) {
		if ( empty( $urls ) ) {
			return false;
		}

		foreach ( $urls as $url ) {
			$file_path[] = $this->get_file_path( $url );
		}

		$file_hash = implode( ',', $urls );
		$filename  = md5( $file_hash . $this->minify_key ) . '.css';

		$minified_file = $this->minify_base_path . $filename;

		if ( ! rocket_direct_filesystem()->exists( $minified_file ) ) {
			$minified_content = $this->minify( $file_path );

			if ( ! $minified_content ) {
				return false;
			}

			$minify_filepath = $this->write_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				return false;
			}
		}

		return $this->get_minify_url( $filename );
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string|array $files     Files to minify.
	 * @return string|bool Minified content, false if empty
	 */
	protected function minify( $files ) {
		foreach ( $files as $file ) {
			$file_content = $this->get_file_content( $file );
			$file_content = $this->rewrite_paths( $file, $file_content );

			$this->minifier->add( $file_content );
		}

		$minified_content = $this->minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}
}
