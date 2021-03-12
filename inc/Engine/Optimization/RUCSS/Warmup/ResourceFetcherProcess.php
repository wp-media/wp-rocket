<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use \WP_Rocket_WP_Background_Process;

class ResourceFetcherProcess extends WP_Rocket_WP_Background_Process {

	/**
	 * Background process action name.
	 *
	 * @var string
	 */
	protected $action = 'rucss_warmup_resource_fetcher';

	/**
	 * Background process prefix.
	 *
	 * @var string
	 */

	protected $prefix = 'rocket';

	/**
	 * ResourcesQuery instance.
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * APIClient instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * ResourceFetcherProcess constructor.
	 *
	 * @param ResourcesQuery $resources_query ResourcesQuery instance.
	 * @param APIClient      $api_client APIClient instance.
	 */
	public function __construct( ResourcesQuery $resources_query, APIClient $api_client ) {
		parent::__construct();

		$this->resources_query = $resources_query;
		$this->api_client      = $api_client;
	}


	/**
	 * Do the task for each page resources.
	 *
	 * @param array $resource Resource array consists of url, content and type.
	 *
	 * @return false
	 */
	protected function task( $resource ) {
		if ( ! is_array( $resource ) ) {
			return false;
		}

		// check the database if those resources added before.
		$db_row = $this->resources_query->get_item_by( 'url', $resource['url'] );

		if ( empty( $db_row ) ) {
			// Create this new row in DB.
			$resource_id = $this->resources_query->add_item(
				[
					'url'           => $resource['url'],
					'type'          => $resource['type'],
					'content'       => $resource['content'],
					'hash'          => md5( $resource['content'] ),
					'last_accessed' => gmdate( 'Y-m-d\TH:i:s\Z' ),
				]
			);

			if ( $resource_id ) {
				return ! $this->send_warmup_request( $resource );
			}

			return false;
		}

		// In all cases update last_accessed column with current date/time.
		$this->resources_query->update_item(
			$db_row->id,
			[
				'last_accessed' => gmdate( 'Y-m-d\TH:i:s\Z' ),
			]
		);

		// Check the content hash.
		if ( md5( $resource['content'] ) === $db_row->hash ) {
			// Do nothing.
			return false;
		}

		// Update this row with the new content.
		$this->resources_query->update_item(
			$db_row->id,
			[
				'content' => $resource['content'],
				'hash'    => md5( $resource['content'] ),
			]
		);

		return ! $this->send_warmup_request( $resource );
	}

	/**
	 * Send the warmup request.
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	protected function send_warmup_request( array $resource ) {
		return $this->api_client->send_warmup_request( $resource );
	}
}
