<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CheckStatusAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CreateAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;
use WP_Rocket\Engine\License\API\User;
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
	 * License User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Subscription creation loading state transient name.
	 *
	 * @var string
	 */
	private $subscription_loading_transient = 'rocket_cdn_subscription_creation_in_progress';

	/**
	 * Subscription local cache.
	 *
	 * @var array
	 */
	private $subscription;

	/**
	 * Website Search API Client instance.
	 *
	 * @var WebsiteSearch
	 */
	private $website_search_api_client;

	/**
	 * Constructor.
	 *
	 * @param APIClient            $api_client API Client instance.
	 * @param CreateAPIClient      $create_api_client Create API Client instance.
	 * @param CDNOptionsManager    $options_manager Options Manager instance.
	 * @param Queue                $queue Queue instance.
	 * @param CheckStatusAPIClient $check_status_api_client Check Status API Client instance.
	 * @param User                 $user  License User instance.
	 * @param WebsiteSearch        $website_search_api_client Website Search API Client instance.
	 */
	public function __construct(
		APIClient $api_client,
		CreateAPIClient $create_api_client,
		CDNOptionsManager $options_manager,
		Queue $queue,
		CheckStatusAPIClient $check_status_api_client,
		User $user,
		WebsiteSearch $website_search_api_client
	) {
		$this->api_client                = $api_client;
		$this->create_api_client         = $create_api_client;
		$this->options_manager           = $options_manager;
		$this->queue                     = $queue;
		$this->check_status_api_client   = $check_status_api_client;
		$this->user                      = $user;
		$this->website_search_api_client = $website_search_api_client;
	}

	/**
	 * Get subscription data.
	 *
	 * @return array
	 */
	public function get_subscription_data() {
		if ( $this->is_subscription_creation_loading() ) {
			return [];
		}

		if ( ! rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) && ! empty( $this->subscription ) ) {
			return $this->subscription;
		}

		$this->subscription = $this->api_client->get_subscription_data();
		if ( 404 === ( $this->subscription['status_code'] ?? null ) ) {
			$this->website_search_api_client->set_site_url( home_url() );
			$website = $this->website_search_api_client->find();
			if ( ! empty( $website ) ) {
				$this->subscription = array_merge( $this->subscription, $website );
			}
		}

		return $this->subscription;
	}

	/**
	 * Check if it has active RocketCDN subscription.
	 *
	 * @return bool
	 */
	public function has_active_subscription(): bool {
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['subscription_status'] ) && 'running' === $subscription['subscription_status'];
	}

	/**
	 * Check if it has WP Rocket license is expired or revoked, regardless of RocketCDN subscription status.
	 *
	 * @return bool
	 */
	public function is_license_invalid(): bool {
		return $this->user->is_license_expired() || $this->user->is_revoked();
	}


	/**
	 * Check if it has inactive RocketCDN subscription.
	 * We are checking the transient since the get_subscription_data will return default value until we add a page to rocketcdn.
	 *
	 * @return bool
	 */
	public function has_inactive_subscription() {
		$transient = get_transient( 'rocketcdn_status' );
		if ( false === $transient ) {
			return false;
		}

		return ! $this->has_active_subscription();
	}

	/**
	 * Create RocketCDN subscription if it doesn't have an active one, and handle the response.
	 *
	 * @param bool $skip_active_check Skip checking if the website has active subscription or not, default is false.
	 *
	 * @return bool|WP_Error
	 */
	public function create_subscription( bool $skip_active_check = false ) {
		if ( ! $skip_active_check && $this->has_active_subscription() ) {
			return false;
		}

		$this->start_subscription_creation_loader();

		$created = $this->create_api_client->create();
		if ( ! $created || ! $created['success'] ) {
			$this->logger::error(
				'RocketCDN: Failed to create subscription.',
				[
					'code'    => $created['data']['code'] ?? 'Unknown',
					'message' => $created['data']['message'] ?? 'Unknown',
				]
			);

			$this->stop_subscription_creation_loader();

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

				$this->stop_subscription_creation_loader();

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
				$this->stop_subscription_creation_loader();

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
			$this->stop_subscription_creation_loader();
			return;
		}

		$this->check_status_api_client->set_task_id( $task_id );
		$status = $this->check_status_api_client->check();
		if ( ! $status ) {
			$this->stop_subscription_creation_loader();
			return;
		}

		if ( ! $status['success'] ) {
			$this->stop_subscription_creation_loader();
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
				$this->options_manager->enable( false );

				$this->stop_subscription_creation_loader();

				/**
				 * Fires when rocketcdn subscription's creation is finished successfully.
				 */
				do_action( 'rocket_cdnfree_website_created' );
				break;
			default:
				$this->stop_subscription_creation_loader();
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
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['plan_type'] ) && 'free' === $subscription['plan_type'];
	}

	/**
	 * If current subscription is paid.
	 *
	 * @return bool
	 */
	public function is_paid(): bool {
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['plan_type'] ) && 'paid' === $subscription['plan_type'];
	}

	/**
	 * Check if website is attached to the subscription correctly or not.
	 *
	 * @return bool
	 */
	public function is_website_attached(): bool {
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['website_attached'] );
	}

	/**
	 * Set subscription creation process transient.
	 * Its value can be used to track creation process time.
	 *
	 * @return void
	 */
	private function start_subscription_creation_loader() {
		set_transient( $this->subscription_loading_transient, time(), HOUR_IN_SECONDS );
	}

	/**
	 * Delete subscription creation process transient.
	 *
	 * @return void
	 */
	private function stop_subscription_creation_loader() {
		delete_transient( $this->subscription_loading_transient );
	}

	/**
	 * Check if subscription creation process is in-progress state.
	 *
	 * @return bool
	 */
	public function is_subscription_creation_loading(): bool {
		return false !== get_transient( $this->subscription_loading_transient );
	}

	/**
	 * Get subscription details, mainly for the rest API endpoint.
	 *
	 * @return array
	 */
	public function get_subscription() {
		return [
			'is_loading'              => $this->is_subscription_creation_loading(),
			'has_active_subscription' => $this->has_active_subscription(),
		];
	}

	/**
	 * Get rocketcdn from the subscription.
	 *
	 * @return string
	 */
	public function get_rocketcdn_url() {
		$subscription = $this->get_subscription_data();
		return $subscription['cdn_url'] ?? '';
	}

	/**
	 * Get rocketcdn transient status
	 *
	 * @return mixed
	 */
	public function get_rocketcdn_status() {
		return get_transient( 'rocketcdn_status' );
	}

	/**
	 * Checks whether the subscription has been cancelled or refunded.
	 *
	 * @since 3.22.0.2
	 *
	 * @return bool True if the subscription status is 'cancelled' or 'refunded', false otherwise.
	 */
	public function is_cancelled(): bool {
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['subscription_status'] ) && in_array( $subscription['subscription_status'], [ 'cancelled', 'refunded' ], true );
	}

	/**
	 * Checks whether the website is pending deletion on the RocketCDN side.
	 *
	 * @since 3.22.0.2
	 *
	 * @return bool True if the website status is 'pending_deletion', false otherwise.
	 */
	public function is_website_pending_deletion(): bool {
		$subscription = $this->get_subscription_data();
		return ! empty( $subscription['website_status'] ) && 'pending_deletion' === $subscription['website_status'];
	}

	/**
	 * Checks whether the subscription is within the cancellation grace period.
	 *
	 * The grace period is active when the subscription is cancelled but the website
	 * is still pending deletion, meaning it has not yet been fully removed.
	 *
	 * @since 3.22.0.2
	 *
	 * @return bool True if the subscription is cancelled and the website is pending deletion, false otherwise.
	 */
	public function is_in_grace_period(): bool {
		return $this->is_cancelled() && $this->is_website_pending_deletion();
	}

	/**
	 * Checks whether the subscription is cancelled and the grace period has elapsed.
	 *
	 * Returns true when the subscription is cancelled and the website is no longer
	 * pending deletion, indicating the grace period has fully passed.
	 *
	 * @since 3.22.0.2
	 *
	 * @return bool True if cancelled and outside the grace period, false otherwise.
	 */
	public function is_cancelled_outside_grace_period(): bool {
		return $this->is_cancelled() && ! $this->is_website_pending_deletion();
	}
}
