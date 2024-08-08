<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend;

use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Processor;

class Controller {
	/**
	 * Processor instance
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Constructor
	 *
	 * @param Processor $processor Processor instance.
	 */
	public function __construct( Processor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Optimize the HTML content
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function optimize( $html ) {
		return $html;
	}

	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		$this->processor->set_processor( 'dom' );

		return $this->processor->add_hashes( $html );
	}
}
