<?php

/**
 * Class TaskScheduler_wpPostJobStore
 */
class TaskScheduler_wpPostJobStore extends TaskScheduler_JobStore {
	const POST_TYPE = 'scheduled-task';
	const GROUP_TAXONOMY = 'task-group';
	const SCHEDULE_META_KEY = '_task_manager_schedule';

	public function save_job( TaskScheduler_Job $job, DateTime $date = NULL ){
		try {
			$post_array = $this->create_post_array( $job, $date );
			$post_id = $this->save_post_array( $post_array );
			$this->save_post_schedule( $post_id, $job->get_schedule() );
			$this->save_job_group( $post_id, $job->get_group() );
			return $post_id;
		} catch ( Exception $e ) {
			throw new RuntimeException( __('Error saving job', 'task-scheduler'), 0, $e );
		}
	}

	protected function create_post_array( TaskScheduler_Job $job, DateTime $date = NULL ) {
		$post = array(
			'post_type' => self::POST_TYPE,
			'post_title' => $job->get_hook(),
			'post_content' => json_encode($job->get_args()),
			'post_status' => ( $job->is_finished() ? 'publish' : 'pending' ),
			'post_date_gmt' => $this->get_timestamp($job, $date),
		);
		return $post;
	}

	protected function get_timestamp( TaskScheduler_Job $job, DateTime $date = NULL ) {
		$next = is_null($date) ? $job->get_schedule()->next() : $date;
		if ( !$next ) {
			throw new InvalidArgumentException(__('Invalid schedule. Cannot save job.', 'task-scheduler'));
		}
		return $next->format('Y-m-d H:i:s');
	}

	protected function save_post_array( $post_array ) {
		$post_id = wp_insert_post($post_array);
		if ( is_wp_error($post_id) || empty($post_id) ) {
			throw new RuntimeException(__('Unable to save job.', 'task-scheduler'));
		}
		return $post_id;
	}

	protected function save_post_schedule( $post_id, $schedule ) {
		update_post_meta( $post_id, self::SCHEDULE_META_KEY, $schedule );
	}

	protected function save_job_group( $post_id, $group ) {
		if ( empty($group) ) {
			wp_set_object_terms( $post_id, array(), self::GROUP_TAXONOMY, FALSE );
		} else {
			wp_set_object_terms( $post_id, array($group), self::GROUP_TAXONOMY, FALSE );
		}
	}

	public function fetch_job( $job_id ) {
		$post = $this->get_post( $job_id );
		if ( empty($post) || $post->post_type != self::POST_TYPE ) {
			return $this->get_null_job();
		}
		return $this->make_job_from_post($post);
	}

	protected function get_post( $job_id ) {
		if ( empty($job_id) ) {
			return NULL;
		}
		return get_post($job_id);
	}

	protected function get_null_job() {
		return new TaskScheduler_NullJob();
	}

	protected function make_job_from_post( $post ) {
		$hook = $post->post_title;
		$args = json_decode($post->post_content);
		$schedule = get_post_meta( $post->ID, self::SCHEDULE_META_KEY, true );
		if ( empty($schedule) ) {
			$schedule = new TaskScheduler_NullSchedule();
		}
		$group = wp_get_object_terms( $post->ID, self::GROUP_TAXONOMY, array('fields' => 'names') );
		$group = empty( $group ) ? '' : reset($group);
		if ( $post->post_status == 'publish' ) {
			$job = new TaskScheduler_FinishedJob( $hook, $args, $schedule, $group );
		} else {
			$job = new TaskScheduler_Job( $hook, $args, $schedule, $group );
		}
		return $job;
	}

	/**
	 * @param int $max_jobs
	 * @param DateTime $before_date Jobs must be schedule before this date. Defaults to now.
	 *
	 * @return TaskScheduler_JobClaim
	 */
	public function stake_claim( $max_jobs = 10, DateTime $before_date = NULL ){
		$claim_id = $this->generate_claim_id();
		$this->claim_jobs( $claim_id, $max_jobs, $before_date );
		$job_ids = $this->find_jobs_by_claim_id( $claim_id );
		return new TaskScheduler_JobClaim( $claim_id, $job_ids );
	}

	protected function generate_claim_id() {
		$claim_id = md5(microtime(true) . rand(0,1000));
		return substr($claim_id, 0, 20); // to fit in db field with 20 char limit
	}

	/**
	 * @param string $claim_id
	 * @param int $limit
	 * @param DateTime $before_date
	 * @return int The number of jobs that were claimed
	 * @throws RuntimeException
	 */
	protected function claim_jobs( $claim_id, $limit, DateTime $before_date = NULL ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$date = is_null($before_date) ? new DateTime() : $before_date;
		// can't use $wpdb->update() because of the <= condition
		$sql = "UPDATE {$wpdb->posts} SET post_password = %s WHERE post_type = %s AND post_status = %s AND post_password = '' AND post_date_gmt <= %s LIMIT %d";
		$sql = $wpdb->prepare( $sql, array( $claim_id, self::POST_TYPE, 'pending', $date->format('Y-m-d H:i:s'), $limit ) );
		$rows_affected = $wpdb->query($sql);
		if ( $rows_affected === false ) {
			throw new RuntimeException(__('Unable to claim jobs. Database error.', 'task-scheduler'));
		}
		return (int)$rows_affected;
	}

	/**
	 * @param string $claim_id
	 * @return array
	 */
	protected function find_jobs_by_claim_id( $claim_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_password = %s";
		$sql = $wpdb->prepare( $sql, array( self::POST_TYPE, $claim_id ) );
		$job_ids = $wpdb->get_col( $sql );
		return $job_ids;
	}

	public function release_claim( TaskScheduler_JobClaim $claim ) {
		$job_ids = $this->find_jobs_by_claim_id( $claim->get_id() );
		if ( empty($job_ids) ) {
			return; // nothing to do
		}
		$job_id_string = implode(',', array_map('intval', $job_ids));
		/** @var wpdb $wpdb */
		global $wpdb;
		$sql = "UPDATE {$wpdb->posts} SET post_password = '' WHERE ID IN ($job_id_string) AND post_password = %s";
		$sql = $wpdb->prepare( $sql, array( $claim->get_id() ) );
		$result = $wpdb->query($sql);
		if ( $result === false ) {
			throw new RuntimeException( sprintf( __('Unable to unlock claim %s. Database error.', 'task-scheduler'), $claim->get_id() ) );
		}
	}

	public function mark_complete( $job_id ) {
		$post = get_post($job_id);
		if ( empty($post) || ($post->post_type != self::POST_TYPE) ) {
			throw new InvalidArgumentException(sprintf(__('Unidentified job %s', 'task-scheduler'), $job_id));
		}
		$result = wp_update_post(array(
			'ID' => $job_id,
			'post_status' => 'publish',
		), TRUE);
		if ( is_wp_error($result) ) {
			throw new RuntimeException($result->get_error_message());
		}
	}

	public function init() {
		$post_type_registrar = new TaskScheduler_wpPostJobStore_PostTypeRegistrar();
		$post_type_registrar->register();

		$taxonomy_registrar = new TaskScheduler_wpPostJobStore_TaxonomyRegistrar();
		$taxonomy_registrar->register();
	}
}
 