<?php

namespace WP_Rocket\Engine\Common\JobManager\Managers;

use WP_Rocket\Engine\Common\Context\ContextInterface;

class AbstractManager {
    /**
	 * Query instance.
	 *
	 * @var object
	 */
	protected $query;

    /**
     * Context.
     *
     * @var ContextInterface
     */
    protected $context;

    /**
	 * Determine if the action is allowed.
	 *
	 * @return boolean
	 */
	public function is_allowed(): bool {
		return $this->context->is_allowed();
	}

    /**
	 * Query object.
	 *
	 * @return object
	 */
	public function query() {
		return $this->query;
	}

    /**
	 * Send the request to add url into the queue.
	 *
	 * @param string $url page URL.
	 * @param bool   $is_mobile page is for mobile.
	 *
	 * @return void
	 */
    public function add_url_to_the_queue( string $url, bool $is_mobile ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

		$row = $this->query->get_row( $url, (bool) $is_mobile );

		if ( empty( $row ) ) {
			$this->query->create_new_job( $url, '', '', $is_mobile );
			return;
		}
		$this->query->reset_job( (int) $row->id );
	}

    /**
	 * Clear failed jobs.
	 *
	 * @param float  $delay delay before the urls are deleted.
	 * @param string $unit unit from the delay.
	 * @return array
	 */
    public function clear_failed_jobs( float $delay, string $unit ): array {
        $rows = $this->query->get_failed_rows( $delay, $unit );

        if ( empty( $rows ) ) {
            return [];
        }

        $failed_urls = [];

        foreach ( $rows as  $row ) {
            $failed_urls[] = $row->url;

            $id = (int) $row->id;

            if ( empty( $id ) ) {
                continue;
            }

            $this->add_url_to_the_queue( $row->url, (bool) $row->is_mobile );
        }

        return $failed_urls;
    }

     /**
     * Change the status to be in-progress.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @return void
     */
    public function make_status_inprogress( string $url, bool $is_mobile ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->make_status_inprogress( $url, $is_mobile );
    }

    /**
     * Get single job.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @return bool|object
     */
    public function get_single_job( string $url, bool $is_mobile ) {
        return $this->query->get_row( $url, $is_mobile );
    }

    /**
     * Get on submit jobs based on enabled option.
     *
     * @param integer $num_rows Number of rows to grab with each CRON iteration.
     * @return array
     */
    public function get_on_submit_jobs( int $num_rows ): array {
        return $this->query->get_on_submit_jobs( $num_rows );
    }

    /**
     * Change the job status to be failed.
     *
     * @param string $url Url from DB row.
     * @param boolean $is_mobile Is mobile from DB row.
     * @param string $error_code error code.
     * @param string $error_message error message.
     * @return void
     */
    public function make_status_failed( string $url, bool $is_mobile, string $error_code, string $error_message ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->make_status_failed( $url, $is_mobile, $error_code, $error_message );
    }

    /**
     * Change the job status to be pending.
     *
     * @param string $url Url from DB row.
     * @param string $job_id API job_id.
     * @param string $queue_name API Queue name.
     * @param boolean $is_mobile if the request is for mobile page.
     * @return void
     */
    public function make_status_pending( string $url, string $job_id, string $queue_name, bool $is_mobile ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->make_status_pending( $url, $job_id, $queue_name, $is_mobile );
    }

    /**
	 * Increment retries number and change status back to pending.
	 *
 	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string    $error_code error code.
	 * @param string $error_message error message.
	 *
	 * @return void
	 */
    public function increment_retries( string $url, bool $is_mobile, string $error_code, string $error_message ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->increment_retries( $url, $is_mobile, $error_code, $error_message );
    }

    /**
	 * Update the error message.
	 *
 	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param int    $error_code error code.
	 * @param string $error_message error message.
     * @param string $previous_message Previous saved message.
	 *
	 * @return void
	 */
	public function update_message( string $url, bool $is_mobile, string $error_code, string $error_message, string $previous_message ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->update_message( $url, $is_mobile, $error_code, $error_message, $previous_message );
    }

    /**
	 * Updates the next_retry_time field
	 *
 	 * @param string $url Url from DB row.
	 * @param boolean $is_mobile Is mobile from DB row.
	 * @param string|int $next_retry_time timestamp or mysql format date.
	 *
	 * @return void
	 */
	public function update_next_retry_time( string $url, bool $is_mobile, $next_retry_time ): void {
        if ( ! $this->is_allowed() ) {
            return;
        }

        $this->query->update_next_retry_time( $url, $is_mobile, $next_retry_time );
    }
}