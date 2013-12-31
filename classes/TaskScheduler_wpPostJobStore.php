<?php

/**
 * Class TaskScheduler_wpPostJobStore
 */
class TaskScheduler_wpPostJobStore extends TaskScheduler_JobStore {
	const POST_TYPE = 'scheduled-task';
	const GROUP_TAXONOMY = 'task-group';
	const SCHEDULE_META_KEY = '_task_manager_schedule';

	public function save_job( TaskScheduler_Job $job ){
		try {
			$post_array = $this->create_post_array( $job );
			$post_id = $this->save_post_array( $post_array );
			$this->save_post_schedule( $post_id, $job->get_schedule() );
			$this->save_job_group( $post_id, $job->get_group() );
			return $post_id;
		} catch ( Exception $e ) {
			throw new RuntimeException( __('Error saving job', 'task-scheduler'), 0, $e );
		}
	}

	protected function create_post_array( TaskScheduler_Job $job ) {
		$post = array(
			'post_type' => self::POST_TYPE,
			'post_title' => $job->get_hook(),
			'post_content' => json_encode($job->get_args()),
			'post_status' => 'pending',
			'post_date_gmt' => $this->get_timestamp($job),
		);
		return $post;
	}

	protected function get_timestamp( TaskScheduler_Job $job ) {
		$next = $job->get_schedule()->next();
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
		$job = new TaskScheduler_Job( $hook, $args, $schedule, $group );
		return $job;
	}

	public function init() {
		$post_type_registrar = new TaskScheduler_wpPostJobStore_PostTypeRegistrar();
		$post_type_registrar->register();

		$taxonomy_registrar = new TaskScheduler_wpPostJobStore_TaxonomyRegistrar();
		$taxonomy_registrar->register();
	}
}
 