<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the critical CSS generation process.
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class Rocket_Background_Critical_CSS_Generation extends WP_Background_Process {
	/**
	 * Process prefix
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'critical_css_generation';

	/**
	 * Critical CSS generator API URL.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access protected
	 * @var string Critical CSS generator API URL
	 */
	protected $critical_css_generator_api_url = 'https://cpcss.wp-rocket.me/api/job/';

	/**
	 * Critical CSS values container.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access protected
	 * @var array An array containing the type of item and its associated critical CSS path.
	 */
	protected $critical_css = array();

	/**
	 * Notices container.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access protected
	 * @var array An array containing the type of notices and their associated values.
	 */
	protected $notice = array();

	/**
	 * Launches the background process
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function dispatch() {
		set_transient( 'rocket_critical_css_generation_process', 'running', HOUR_IN_SECONDS );

		// Perform remote post.
		return parent::dispatch();
	}

	/**
	 * Perform the optimization corresponding to $item
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return bool false
	 */
	protected function task( $item ) {
		$response = wp_remote_post(
			$this->critical_css_generator_api_url,
			array(
				/**
				 * Filters the parameters sent to the Critical CSS generator API
				 *
				 * @since 2.11
				 * @author Remy Perona
				 *
				 * @param array An array of parameters to send to the API.
				 */
				'body' => apply_filters( 'rocket_cpcss_job_request', array(
					'url' => $item['url'],
				) ),
			)
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// translators: %s = type of content.
			$this->notice['errors'][] = sprintf( __( 'Critical CSS generation for %s could not be completed.', 'rocket' ), $item['type'] );
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		while ( $job_data = $this->get_critical_path( $data->data->id ) ) {
			if ( 'complete' === $job_data->data->state ) {
				$this->critical_css[ $item['type'] ] = $job_data->data->critical_path;
				// translators: %s = type of content.
				$this->notice['success'][] = sprintf( __( 'Critical CSS generation for %s complete.', 'rocket' ), $item['type'] );
				break;
			}

			sleep( 2 );
		}

		set_transient( 'rocket_critical_css_generation_process', $this->notice, HOUR_IN_SECONDS );

		return false;
	}

	/**
	 * Gets the returned body of a request to a specific job of the Critical CSS generator API
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $job_id Job identifier.
	 * @return object JSON decoded body of the request's response
	 */
	protected function get_critical_path( $job_id ) {
		$response = wp_remote_get(
			$this->critical_css_generator_api_url . $job_id . '/'
		);

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Launches when the background process is complete.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 */
	protected function complete() {
		update_rocket_option( 'critical_css', $this->critical_css );

		parent::complete();
	}
}
