<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

class Processor {
	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

    /**
	 * Instantiate the class
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
	}

    /**
	 * Checks existing data for various performance hints feature using their factories,
     * then encodes the result in a single instance.
	 *
	 * @return void
	 */
    public function check_data() {
        foreach( $this->factories as $factory ) {
            $factory->get_ajax_controller()->check_data();
        }
    }

    /**
	 * Adds performance hints data to DB.
	 *
	 * @return void
	 */
    public function add_data() {
        foreach( $this->factories as $factory ) {
            $factory->get_ajax_controller()->add_data();
        }
    }
}
