<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Subscriber implements Subscriber_Interface {
	/**
	 * ATF context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param ContextInterface $context ATF Context.
	 */
	public function __construct( ContextInterface $context ) {
		$this->context = $context;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_saas_api_queued_url' => 'add_wpr_imagedimensions_query_arg',
		];
	}

	/**
	 * Add image dimensions query parameter to URL.
	 *
	 * @param string $url URL to be sent.
	 *
	 * @return string
	 */
	public function add_wpr_imagedimensions_query_arg( string $url ): string {
		if ( ! $this->context->is_allowed() ) {
			return $url;
		}

		return add_query_arg(
			[
				'wpr_imagedimensions' => 1,
			],
			$url
		);
	}
}
