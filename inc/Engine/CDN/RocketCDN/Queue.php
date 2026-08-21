<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Engine\Common\Queue\AbstractASQueue;

/**
 * RocketCDN Queue
 *
 * Manages Action Scheduler jobs for RocketCDN workflow
 */
class Queue extends AbstractASQueue {

	/**
	 * Queue group for RocketCDN.
	 *
	 * @var string
	 */
	protected $group = 'rocket-cdn';

	/**
	 * Website creation status task hook.
	 *
	 * @var string
	 */
	private $create_status_job = 'rocket_cdnfree_website_create_status';

	/**
	 * Pro detection task hook.
	 *
	 * @var string
	 */
	private $pro_detect_job = 'rocket_cdn_auto_detect';

	/**
	 * Cancel create job.
	 */
	public function cancel_create_status_job(): void {
		if ( ! $this->is_scheduled( $this->create_status_job ) ) {
			return;
		}
		$this->cancel( $this->create_status_job );
	}

	/**
	 * Schedule reset task.
	 *
	 * @param string $task_id Task ID to check the status of RocketCDN free website creation.
	 *
	 * @return void
	 */
	public function schedule_create_status_job( string $task_id ): void {
		// Schedule weekly cleanup.
		$this->schedule_single(
			time() + 30, // After 30 seconds from now.
			$this->create_status_job,
			[
				'task_id' => $task_id,
			]
		);
	}

	/**
	 * Schedule the Pro subscription detection job.
	 *
	 * @param int $attempt Number of remaining detection attempts.
	 *
	 * @return void
	 */
	public function schedule_pro_detection_job( int $attempt = 3 ): void {
		$this->schedule_single(
			time() + 30, // After 30 seconds from now.
			$this->pro_detect_job,
			[
				'attempt' => $attempt,
			]
		);
	}

	/**
	 * Cancel the Pro subscription detection job.
	 *
	 * @return void
	 */
	public function cancel_pro_detection_job(): void {
		if ( ! $this->is_scheduled( $this->pro_detect_job ) ) {
			return;
		}
		$this->cancel( $this->pro_detect_job );
	}

	/**
	 * Cancel all scheduled tasks.
	 *
	 * @return void
	 */
	public function cancel_all_tasks() {
		$this->cancel_create_status_job();
		$this->cancel_pro_detection_job();
	}
}
