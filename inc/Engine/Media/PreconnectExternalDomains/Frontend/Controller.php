<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Engine\Common\Head\ElementTrait;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as PreconnectDomains;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Row\PreconnectExternalDomains;
use WP_Rocket\Engine\Support\CommentTrait;

class Controller implements ControllerInterface {
	use CommentTrait;
	use ElementTrait;

	/**
	 * Queries instance
	 *
	 * @var PreconnectDomains
	 */
	private $query;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param PreconnectDomains $query Queries instance.
	 * @param Context           $context Context instance.
	 */
	public function __construct( PreconnectDomains $query, Context $context ) {
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Applies preconnect domains optimization.
	 *
	 * @param string $html HTML content.
	 * @param object $row Database row.
	 * @return string
	 */
	public function optimize( string $html, $row ): string {
		if ( ! $this->context->is_allowed() || ! $row->has_preconnect_external_domains() ) {
			return $html;
		}

		return $this->add_meta_comment( 'preconnect_external_domains', $html );
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		if ( ! $this->context->is_allowed() ) {
			return $data;
		}

		$elements = [
			'link',
			'script',
			'iframe',
		];

		/**
		 * Filters the array of eligible elements to be processed by the preconnect external domain beacon.
		 *
		 * @since 3.19
		 *
		 * @param array $elements Array of elements
		 */
		$elements = wpm_apply_filters_typed( 'array', 'rocket_preconnect_external_domain_elements', $elements );
		$elements = array_filter( $elements, 'is_string' );

		$data['preconnect_external_domain_elements'] = $elements;

		$exclusions = [
			[
				'type'  => 'attribute',
				'key'   => 'rel',
				'value' => 'profile',
			],
			[
				'type'  => 'attribute',
				'key'   => 'rel',
				'value' => 'preconnect',
			],
			[
				'type'  => 'attribute',
				'key'   => 'rel',
				'value' => 'dns-prefetch',
			],
			[
				'type'  => 'attribute',
				'key'   => 'async',
				'value' => '',
			],
			[
				'type'  => 'domain',
				'value' => 'static.cloudflareinsights.com',
			],
		];

		/**
		 * Filters the array of elements to be excluded from being processed by the preconnect external domain beacon.
		 *
		 * @since 3.19
		 *
		 * @param array $excluded_elements Array of elements
		 */
		$exclusions = wpm_apply_filters_typed( 'array', 'preconnect_external_domain_exclusions', $exclusions );

		$data['preconnect_external_domain_exclusions'] = $exclusions;
		$data['status']['preconnect_external_domain']  = $this->context->is_allowed();

		return $data;
	}

	/**
	 * Add preconnect item into head.
	 *
	 * @param array $items Head items.
	 * @return mixed
	 */
	public function add_preconnect_to_head( $items ) {
		if ( ! $this->context->is_allowed() ) {
			return $items;
		}

		$row = $this->get_current_url_row();
		if ( empty( $row ) || ! $row->has_preconnect_external_domains() ) {
			return $items;
		}

		$domains = json_decode( $row->domains, true );
		foreach ( $domains as $domain ) {
			$domain_item = $this->get_domain_preconnect_item( $domain );
			if ( empty( $domain_item ) ) {
				continue;
			}
			$items[] = $domain_item;
		}

		return $items;
	}

	/**
	 * Get current visited page row in DB.
	 *
	 * @return false|PreconnectExternalDomains
	 */
	private function get_current_url_row() {
		global $wp;

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->context->is_mobile_allowed();

		return $this->query->get_row( $url, $is_mobile );
	}

	/**
	 * Get specific domain preconnect item to be added to head.
	 *
	 * @param string $domain Domain url.
	 * @return array|string[]
	 */
	private function get_domain_preconnect_item( $domain ) {
		if ( $this->use_prefetch( $domain ) ) {
			// Use dns-prefetch.
			return $this->prefetch_link(
				[
					'href' => esc_url( $domain ),
					1      => 'crossorigin',
					2      => 'data-rocket-prefetch',
				]
			);
		}

		// Use preconnect by default.
		return $this->preconnect_link(
			[
				'href' => esc_url( $domain ),
				1      => 'crossorigin',
				2      => 'data-rocket-preconnect',
			]
		);
	}

	/**
	 * Check if we need to use prefetch instead of preconnect.
	 *
	 * @param string $domain Domain url.
	 * @return bool
	 */
	private function use_prefetch( $domain ) {
		return wpm_apply_filters_typed( 'boolean', 'rocket_preconnect_external_domains_use_prefetch', false, $domain );
	}
}
