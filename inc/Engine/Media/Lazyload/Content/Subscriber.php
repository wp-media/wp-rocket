<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Media\Lazyload\Content\Processor\Processor;

class Subscriber implements Subscriber_Interface {
	private $processor;

	public function __construct( Processor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer' => 'lazyload_content',
		];
	}

	/**
	 * Lazyload the content
	 *
	 * @param string $buffer The buffer content.
	 *
	 * @return string
	 */
	public function lazyload_content( string $buffer ): string {
		$start = hrtime( true );

		$this->processor->set_processor( 'regex' );

		$buffer = $this->processor->get_processor()->add_locations_hash_to_html( $buffer );

		$end = hrtime( true );

		// Calculate the execution time.
		$execution_time = ( $end - $start ) / 1e9; // Convert to seconds.

		error_log( 'Execution time: ' . $execution_time );
		return $buffer;
	}
}
