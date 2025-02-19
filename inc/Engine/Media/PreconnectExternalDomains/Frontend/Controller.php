<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as PreconnectDomains;

class Controller implements ControllerInterface {

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
		return $data;
	}
}
