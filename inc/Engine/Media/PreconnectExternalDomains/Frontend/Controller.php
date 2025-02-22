<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as PreconnectDomains;

class Controller implements ControllerInterface {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Queries instance
	 *
	 * @var PreconnectDomains
	 */
	private $query; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Constructor
	 *
	 * @param Options_Data      $options Options instance.
	 * @param PreconnectDomains $query Queries instance.
	 * @param Context           $context Context instance.
	 */
	public function __construct( Options_Data $options, PreconnectDomains $query, Context $context ) {
		$this->options = $options;
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
		if ( ! $row->has_preconnect_external_domains() ) {
			return $html;
		}

		return $html;
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
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
}
