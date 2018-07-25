<?php
namespace WP_Rocket\Optimization\CSS;

use WP_Rocket\Admin\Options_Data as Options;
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
	 * @param Options    $options  Options instance.
	 * @param Minify\CSS $minifier Minifier instance.
	 */
	public function __construct( Options $options, Minify\CSS $minifier ) {
		parent::__construct( $options );

		$this->minifier = $minifier;
	}

	/**
	 * Minifies and combines all CSS files into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		$styles = $this->find( '<link\s+([^>]+[\s\'"])?href\s*=\s*[\'"]\s*?([^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html );

		if ( ! $styles ) {
			return $html;
		}

		$styles = array_map( function( $style ) {
			if ( $this->is_external_file( $style[2] ) ) {
				return;
			}

			if ( $this->is_minify_excluded_file( $style ) ) {
				return;
			}

			return $style;
		}, $styles );

		if ( empty( $styles ) ) {
			return $html;
		}

		$urls = array_map( function( $style ) {
			return $style[2];
		}, $styles );

		$minify_url = $this->combine( $urls );

		if ( ! $minify_url ) {
			return $html;
		}

		$html = str_replace( '</title>', '</title><link rel="stylesheet" href="' . $minify_url . '" data-minify="1" />', $html );

		foreach ( $styles as $style ) {
			$html = str_replace( $style[0], '', $html );
		}

		return $html;
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
