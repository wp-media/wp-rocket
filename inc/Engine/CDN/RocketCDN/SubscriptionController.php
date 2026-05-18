<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CheckStatusAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CreateAPIClient;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class SubscriptionController implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Create API Client instance.
	 *
	 * @var CreateAPIClient
	 */
	private $create_api_client;

	/**
	 * Options Manager instance.
	 *
	 * @var CDNOptionsManager
	 */
	private $options_manager;

	/**
	 * Queue instance.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Check Status API Client instance.
	 *
	 * @var CheckStatusAPIClient
	 */
	private $check_status_api_client;

	/**
	 * Constructor.
	 *
	 * @param APIClient            $api_client API Client instance.
	 * @param CreateAPIClient      $create_api_client Create API Client instance.
	 * @param CDNOptionsManager    $options_manager Options Manager instance.
	 * @param Queue                $queue Queue instance.
	 * @param CheckStatusAPIClient $check_status_api_client Check Status API Client instance.
	 */
	public function __construct( APIClient $api_client, CreateAPIClient $create_api_client, CDNOptionsManager $options_manager, Queue $queue, CheckStatusAPIClient $check_status_api_client ) {
		$this->api_client              = $api_client;
		$this->create_api_client       = $create_api_client;
		$this->options_manager         = $options_manager;
		$this->queue                   = $queue;
		$this->check_status_api_client = $check_status_api_client;
	}

	/**
	 * Check if it has active RocketCDN subscription.
	 *
	 * @return bool
	 */
	private function has_active_subscription(): bool {
		$subscription = $this->api_client->get_subscription_data();
		return ! empty( $subscription['subscription_status'] ) && 'running' === $subscription['subscription_status'];
	}

	/**
	 * Create RocketCDN subscription if it doesn't have an active one, and handle the response.
	 *
	 * @return bool|WP_Error
	 */
	public function create_subscription() {
		if ( $this->has_active_subscription() ) {
			return false;
		}

		$created = $this->create_api_client->create();
		if ( ! $created || ! $created['success'] ) {
			$this->logger::error(
				'RocketCDN: Failed to create subscription.',
				[
					'code'    => $created['data']['code'] ?? 'Unknown',
					'message' => $created['data']['message'] ?? 'Unknown',
				]
			);
			return new WP_Error( $created['data']['code'] ?? 'rocketcdn_account_notcreated', $created['data']['message'] ?? 'Unknown' );
		}

		switch ( $created['data']['code'] ) {
			case 'cdn_task_enqueued':
				// Save CDN token.
				$this->options_manager->save_token( $created['data']['cdn_token'] );

				// Enqueue AS single task after 30 seconds from now to check the status.
				$this->queue->schedule_create_status_job( $created['data']['task_id'] );

				/**
				 * Fires when rocketcdn subscription is created.
				 *
				 * @param string $task_id Enqueued task ID.
				 * @param string $token CDN Subscription token.
				 */
				do_action( 'rocket_cdn_subscription_created', $created['data']['task_id'], $created['data']['cdn_token'] );
				break;

			case 'already_free_subscribed':
				// Clear subscription cache so it can get the final state, and save the token before if it's not saved before.
				if ( ! $this->options_manager->has_token() ) {
					$this->options_manager->save_token( $created['data']['cdn_token'] );
				}

				$this->options_manager->flush_subscription_cache();
				break;
			default:
				// Log this not known code.
				$this->logger::error(
					'RocketCDN: Received not known response code when creating subscription.',
					[
						'code'    => $created['data']['code'],
						'message' => $created['data']['message'],
					]
				);
				return false;
		}

		return true;
	}

	/**
	 * Check subscription's creation status.
	 *
	 * @param string $task_id Task ID.
	 * @return void
	 */
	public function check_status( string $task_id ) {
		if ( $this->has_active_subscription() ) {
			return;
		}

		$this->check_status_api_client->set_task_id( $task_id );
		$status = $this->check_status_api_client->check();
		if ( ! $status ) {
			return;
		}

		if ( ! $status['success'] ) {
			$this->logger::error(
				'RocketCDN: Failed to check creation status.',
				$status
			);
			return;
		}

		switch ( $status['status'] ) {
			case 'PENDING':
				// Re-add the action scheduler task to check status again after 30 seconds.
				$this->queue->schedule_create_status_job( $task_id );
				break;
			case 'SUCCESS':
				$this->options_manager->enable( $status['cdn_url'], false );

				/**
				 * Fires when rocketcdn subscription's creation is finished successfully.
				 */
				do_action( 'rocket_cdnfree_website_created' );
				break;
			default:
				$this->logger::error(
					'RocketCDN: Received not known response code when check subscription\'s status.',
					$status
				);
		}
	}

	/**
	 * If current subscription is free.
	 *
	 * @return bool
	 */
	public function is_free(): bool {
		$subscription = $this->api_client->get_subscription_data();
		return ! empty( $subscription['plan_type'] ) && 'free' === $subscription['plan_type'];
	}

	/**
	 * If current subscription is paid.
	 *
	 * @return bool
	 */
	public function is_paid(): bool {
		$subscription = $this->api_client->get_subscription_data();
		return ! empty( $subscription['plan_type'] ) && 'paid' === $subscription['plan_type'];
	}
}
