<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

class SubscriptionController {

	private $api_client;

	/**
	 * @var CreateAPIClient
	 */
	private $create_api_client;

	public function __construct( APIClient $api_client, CreateAPIClient $create_api_client ) {
		$this->api_client        = $api_client;
		$this->create_api_client = $create_api_client;
	}

	private function has_active_subscription(): bool{
		$subscription = $this->api_client->get_subscription_data();
		return ! empty( $subscription['subscription_status'] ) && 'running' === $subscription['subscription_status'];
	}

	public function create_subscription() {
		if ( $this->has_active_subscription() ) {
			return;
		}

		$created = $this->create_api_client->create();
		if ( ! $created || ! $created->success ) {
			return;
		}

		switch ( $created->data->code ) {
			case 'cdn_task_enqueued':
				// Enqueue AS single task after 30 seconds from now to check the status.

			case 'already_free_subscribed':
		}

		do_action( 'rocket_cdn_subscription_created' );

		return $created->data->task_id ?? null;
	}
}
