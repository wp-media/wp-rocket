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
	 * Launches the background process
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function dispatch() {
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
	 * @return bool false if task performed successfully, true otherwise to re-queue the item
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
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $data->data ) ) {
			return false;
		}

		while ( $job_data = $this->get_critical_path( $data->data->id ) ) {
			if ( 400 === (int) $job_data->status ) {
				break;
			}

			if ( isset( $job_data->data->state ) && 'complete' === $job_data->data->state ) {
				$critical_css_path = WP_ROCKET_CRITICAL_CSS_PATH . get_current_blog_id();

				if ( ! rocket_direct_filesystem()->is_dir( $critical_css_path ) ) {
					rocket_mkdir_p( $critical_css_path );
				}

				$file_path = $critical_css_path . '/' . $item['type'] . '.css';
				$critical_css_content = wp_kses( $job_data->data->critical_path, array( "\'", '\"' ) );
				$result    = rocket_direct_filesystem()->put_contents( $file_path, $job_data->data->critical_path );

				if ( ! $result ) {
					break;
				}

				$transient = get_transient( 'rocket_critical_css_generation_process_running' );
				// translators: %s = type of content.
				$transient['items'][] = sprintf( __( 'Critical CSS for %s generated.', 'rocket' ), $item['type'] );
				$transient['generated']++;
				set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );

				break;
			}

			sleep( 2 );
		}

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
		set_transient( 'rocket_critical_css_generation_process_complete', get_transient( 'rocket_critical_css_generation_process_running' ), HOUR_IN_SECONDS );
		delete_transient( 'rocket_critical_css_generation_process_running' );

		parent::complete();
	}
}
