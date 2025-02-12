<?php

namespace WP_Rocket\Engine\Common\Head;

trait ElementTrait {
	protected function preload_link( array $args = [] ) {
		$args['rel'] = 'preload';
		$args[ 2 ] = 'data-rocket-preload';
		return $this->link( $args );
	}

	protected function preconnect_link( array $args = [] ) {
		$args['rel'] = 'preconnect';
		return $this->link( $args );
	}

	protected function dns_prefetch_link( array $args = [] ) {
		$args['rel'] = 'dns-prefetch';
		return $this->link( $args );
	}

	protected function prefetch_link( array $args = [] ) {
		$args['rel'] = 'prefetch';
		return $this->link( $args );
	}

	protected function prerender_link( array $args = [] ) {
		$args['rel'] = 'prerender';
		return $this->link( $args );
	}

	protected function stylesheet_link( array $args = [] ) {
		$args['rel'] = 'stylesheet';
		return $this->link( $args );
	}

	protected function style_tag( string $css = '', array $args = [] ) {
		$element = [
			'open_tag' => '<style',
		];
		$element += wp_parse_args( $args, [
			'inner_content' => $css,
		] );
		$element[ 'close_tag' ] = '</style>';

		return $element;
	}

	protected function noscript_tag( string $content = '', array $args = [] ) {
		$element = [
			'open_tag' => '<noscript',
		];
		$element += wp_parse_args( $args, [
			'inner_content' => $content,
		] );
		$element[ 'close_tag' ] = '</noscript>';

		return $element;
	}

	private function link( array $args = [] ) {
		$element = [
			'open_tag' => '<link',
		];
		$element += wp_parse_args( $args, [
			'href' => '',
		] );

		return $element;
	}
}
