<?php
namespace WP_Rocket\Optimization\CSS;

/**
 * Trait used to rewriter path of files inside CSS file contente
 *
 * @since 3.1
 * @author Remy Perona
 */
trait Path_Rewriter {
	/**
	 * Rewrites the paths inside the CSS file content
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $file    File path.
	 * @param string $content File content.
	 * @return string
	 */
	public function rewrite_paths( $file, $content ) {
		/**
		 * Filters the Document Root path to use during CSS minification to rewrite paths
		 *
		 * @since 2.7
		 *
		 * @param string The Document Root path.
		*/
		$document_root = apply_filters( 'rocket_min_documentRoot', $_SERVER['DOCUMENT_ROOT'] );

		return \rocket_cdn_css_properties( \Minify_CSS_UriRewriter::rewrite( $content, dirname( $file ), $document_root ) );
	}
}
