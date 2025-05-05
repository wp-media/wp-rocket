<?php
declare(strict_types=1);
namespace WP_Rocket\Engine\Common\Head;

/**
 * Element trait.
 */
trait ElementTrait {
	/**
	 * Preload link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function preload_link( array $args = [] ) {
		$args['rel'] = 'preload';
		$args[]      = 'data-rocket-preload';
		return $this->link( $args );
	}

	/**
	 * Preconnect link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function preconnect_link( array $args = [] ) {
		$args['rel'] = 'preconnect';
		return $this->link( $args );
	}

	/**
	 * Dns_prefetch link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function dns_prefetch_link( array $args = [] ) {
		$args['rel'] = 'dns-prefetch';
		return $this->link( $args );
	}

	/**
	 * Prefetch link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function prefetch_link( array $args = [] ) {
		$args['rel'] = 'prefetch';
		return $this->link( $args );
	}

	/**
	 * Prerender link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function prerender_link( array $args = [] ) {
		$args['rel'] = 'prerender';
		return $this->link( $args );
	}

	/**
	 * Stylesheet link.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	protected function stylesheet_link( array $args = [] ) {
		$args['rel'] = 'stylesheet';
		return $this->link( $args );
	}

	/**
	 * Style tag.
	 *
	 * @param string $css CSS content.
	 * @param array  $args Element args.
	 * @return array|string[]
	 */
	protected function style_tag( string $css = '', array $args = [] ) {
		$element              = [
			'open_tag' => '<style',
		];
		$element             += wp_parse_args(
			$args,
			[
				'inner_content' => $css,
			]
		);
		$element['close_tag'] = '</style>';

		return $element;
	}

	/**
	 * Noscript tag.
	 *
	 * @param string $content Element contents.
	 * @param array  $args Element args.
	 * @return array|string[]
	 */
	protected function noscript_tag( string $content = '', array $args = [] ) {
		$element              = [
			'open_tag' => '<noscript',
		];
		$element             += wp_parse_args(
			$args,
			[
				'inner_content' => $content,
			]
			);
		$element['close_tag'] = '</noscript>';

		return $element;
	}

	/**
	 * Generic link tag.
	 *
	 * @param array $args Element args.
	 * @return array|string[]
	 */
	private function link( array $args = [] ) {
		$element  = [
			'open_tag' => '<link',
		];
		$element += wp_parse_args(
			$args,
			[
				'href' => '',
			]
		);

		return $element;
	}
}
