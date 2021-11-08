<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Error;

class ProcessorService {

	/**
	 * Responsible for dealing with data/database.
	 *
	 * @var DataManager datamanager instance.
	 */
	private $data_manager;

	/**
	 * Responsible for dealing with CPCSS APIs.
	 *
	 * @var APIClient api_client instance.
	 */
	private $api_client;

	/**
	 * RESTWP constructor.
	 *
	 * @since 3.6
	 *
	 * @param DataManager $data_manager Data manager instance, responsible for dealing with data/database.
	 * @param APIClient   $api_client   API Client instance to deal with CPCSS APIs.
	 */
	public function __construct( DataManager $data_manager, APIClient $api_client ) {
		$this->data_manager = $data_manager;
		$this->api_client   = $api_client;
	}

	/**
	 * Process CPCSS generation, Check timeout and send the generation request.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param string $item_path Path for item to be processed.
	 * @param array  $additional_parameters additional parameters for generation.
	 *
	 * @return array|WP_Error
	 */
	public function process_generate( $item_url, $item_path, $additional_parameters = [] ) {
		$defaults = [
			'timeout'   => false,
			'is_mobile' => false,
			'item_type' => 'custom',
		];
		$args     = array_merge( $defaults, $additional_parameters );

		// Ajax call requested a timeout.
		if ( $args['timeout'] ) {
			return $this->process_timeout( $item_url, $args['is_mobile'], $args['item_type'] );
		}

		$cpcss_job_id = $this->data_manager->get_cache_job_id( $item_url, $args['is_mobile'] );
		if ( false === $cpcss_job_id ) {
			return $this->send_generation_request( $item_url, $item_path, $args['is_mobile'], $args['item_type'] );
		}

		// job_id is found and we need to check status for it.
		return $this->check_cpcss_job_status( $cpcss_job_id, $item_path, $item_url, $args['is_mobile'], $args['item_type'] );
	}

	/**
	 * Send Generation first request.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  Url for item to send the generation request for.
	 * @param string $item_path Path for item to send the generation request for.
	 * @param bool   $is_mobile If this request is for mobile cpcss.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return array
	 */
	private function send_generation_request( $item_url, $item_path, $is_mobile = false, $item_type = 'custom' ) {
		// call send generation request from APIClient for the first time.
		$params        = [
			'mobile'     => (int) $is_mobile,
			'nofontface' => false,
		];
		$generated_job = $this->api_client->send_generation_request( $item_url, $params, $item_type );

		// validate generate response.
		if ( is_wp_error( $generated_job ) ) {
			// Failed so return back the data.
			return $generated_job;
		}

		// Send generation request succeeded.
		// Save job_id into cache.
		$this->data_manager->set_cache_job_id( $item_url, $generated_job->data->id, $is_mobile );

		return $this->check_cpcss_job_status( $generated_job->data->id, $item_path, $item_url, $is_mobile, $item_type );
	}

	/**
	 * Get job details by job_id.
	 *
	 * @since 3.6
	 *
	 * @param string $job_id   ID for the job to get details.
	 * @param string $item_url URL for item to be used in error messages.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return array|mixed|WP_Error
	 */
	private function get_cpcss_job_details( $job_id, $item_url, $item_type = 'custom' ) {
		$job_details = $this->api_client->get_job_details( $job_id, $item_url, $item_type );

		if ( is_wp_error( $job_details ) ) {
			return $job_details;
		}

		return $job_details;
	}

	/**
	 * Check status and process the output for a job.
	 *
	 * @since 3.6
	 *
	 * @param string $job_id    ID for the job to get details.
	 * @param string $item_path Path for this item to be validated.
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return array|WP_Error Response in case of success, failure or pending.
	 */
	private function check_cpcss_job_status( $job_id, $item_path, $item_url, $is_mobile = false, $item_type = 'custom' ) {
		$job_details = $this->api_client->get_job_details( $job_id, $item_url, $is_mobile, $item_type );

		if ( is_wp_error( $job_details ) ) {
			$this->data_manager->delete_cache_job_id( $item_url, $is_mobile );

			return $job_details;
		}

		if ( 200 !== $job_details->status ) {
			// On job error.
			return $this->on_job_error( $job_details, $item_url, $is_mobile, $item_type );
		}

		// On job status 200.
		$job_state = $job_details->data->state;

		// For pending job status.
		if ( isset( $job_state ) && 'complete' !== $job_state ) {
			return $this->on_job_pending( $item_url, $item_type );
		}

		// For successful job status.
		if (
			isset( $job_state, $job_details->data->critical_path )
			&&
			'complete' === $job_state
		) {
			return $this->on_job_success( $item_path, $item_url, $job_details->data->critical_path, $is_mobile, $item_type );
		}
	}

	/**
	 * Process logic for job error.
	 *
	 * @since 3.6
	 *
	 * @param array  $job_details Job details array.
	 * @param string $item_url    Url for web page to be processed, used for error messages.
	 * @param bool   $is_mobile   Bool identifier for is_mobile CPCSS generation.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return WP_Error
	 */
	private function on_job_error( $job_details, $item_url, $is_mobile = false, $item_type = 'custom' ) {
		$this->data_manager->delete_cache_job_id( $item_url, $is_mobile );

		if ( $is_mobile ) {

			$error = sprintf(
				// translators: %1$s = item URL or item type.
				__( 'Mobile Critical CSS for %1$s not generated.', 'rocket' ),
				( 'custom' === $item_type ) ? $item_url : $item_type
			);
		} else {

			$error = sprintf(
				// translators: %1$s = item URL or item type.
				__( 'Critical CSS for %1$s not generated.', 'rocket' ),
				( 'custom' === $item_type ) ? $item_url : $item_type
			);
		}

		if ( isset( $job_details->message ) ) {
			// translators: %1$s = error message.
			$error .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $job_details->message );
		}

		return new WP_Error(
			'cpcss_generation_failed',
			$error,
			[
				'status' => 400,
			]
		);
	}

	/**
	 * Process logic for job pending status.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url Url for web page to be processed, used for error messages.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return array
	 */
	private function on_job_pending( $item_url, $item_type = 'custom' ) {
		return [
			'code'    => 'cpcss_generation_pending',
			'message' => sprintf(
				// translators: %1$s = Item URL or item type.
				__( 'Critical CSS for %s in progress.', 'rocket' ),
				( 'custom' === $item_type ) ? $item_url : $item_type
			),
		];
	}

	/**
	 * Process logic for job success status.
	 *
	 * @since 3.6
	 *
	 * @param string $item_path  Item Path for web page to be processed.
	 * @param string $item_url   Item Url for web page to be processed.
	 * @param string $cpcss_code CPCSS Code to be saved.
	 * @param bool   $is_mobile  Bool identifier for is_mobile CPCSS generation.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return array|WP_Error
	 */
	private function on_job_success( $item_path, $item_url, $cpcss_code, $is_mobile = false, $item_type = 'custom' ) {
		// delete cache job_id for this item.
		$this->data_manager->delete_cache_job_id( $item_url, $is_mobile );

		// save the generated CPCSS code into file.
		$saved = $this->data_manager->save_cpcss( $item_path, $cpcss_code, $item_url, $is_mobile, $item_type );
		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		if ( $is_mobile ) {
			return [
				'code'    => 'cpcss_generation_successful',
				'message' => sprintf(
					// translators: %1$s = Item URL or item type.
					__( 'Mobile Critical CSS for %s generated.', 'rocket' ),
					( 'custom' === $item_type ) ? $item_url : $item_type
				),
			];
		}

		// Send the current status of job.
		return [
			'code'    => 'cpcss_generation_successful',
			'message' => sprintf(
				// translators: %1$s = Item URL or item type.
				__( 'Critical CSS for %s generated.', 'rocket' ),
				( 'custom' === $item_type ) ? $item_url : $item_type
			),
		];
	}

	/**
	 * Process the login for CPCSS deletion.
	 *
	 * @param string $item_path Path for item to delete CPCSS code.
	 *
	 * @return array|WP_Error
	 */
	public function process_delete( $item_path ) {
		$deleted = $this->data_manager->delete_cpcss( $item_path );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return [
			'code'    => 'success',
			'message' => __( 'Critical CSS file deleted successfully.', 'rocket' ),
		];
	}

	/**
	 * Process timeout action for CPCSS generation.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 * @return WP_Error
	 */
	private function process_timeout( $item_url, $is_mobile = false, $item_type = 'custom' ) {
		$this->data_manager->delete_cache_job_id( $item_url, $is_mobile );

		if ( $is_mobile ) {
			return new WP_Error(
				'cpcss_generation_timeout',
				sprintf(
					// translators: %1$s = Item URL or item type.
					__( 'Mobile Critical CSS for %1$s timeout. Please retry a little later.', 'rocket' ),
					( 'custom' === $item_type ) ? $item_url : $item_type
				),
				[
					'status' => 400,
				]
			);
		}

		return new WP_Error(
			'cpcss_generation_timeout',
			sprintf(
				// translators: %1$s = Item URL or item type.
				__( 'Critical CSS for %1$s timeout. Please retry a little later.', 'rocket' ),
				( 'custom' === $item_type ) ? $item_url : $item_type
			),
			[
				'status' => 400,
			]
		);
	}

}
