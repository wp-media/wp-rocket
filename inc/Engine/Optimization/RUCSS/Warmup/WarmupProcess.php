<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\ResourceRow;
use \WP_Rocket_WP_Background_Process;

class WarmupProcess extends WP_Rocket_WP_Background_Process {

	/**
	 * Background process action name.
	 *
	 * @var string
	 */
	protected $action = 'rucss_warmup_saas_call';

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
	 * WarmupProcess constructor.
	 *
	 * @param ResourcesQuery $resources_query ResourcesQuery instance.
	 * @param APIClient      $api_client      APIClient instance.
	 */
	public function __construct( ResourcesQuery $resources_query, APIClient $api_client ) {
		parent::__construct();

		$this->resources_query = $resources_query;
		$this->api_client      = $api_client;
	}

	/**
	 * Background process task for each resource
	 *
	 * @param int $resource_id Resource DB id.
	 *
	 * @return bool
	 */
	protected function task( $resource_id ) {
		if ( ! is_int( $resource_id ) ) {
			return false;
		}

		$resource_row = $this->resources_query->get_item( $resource_id );
		if ( ! $resource_row ) {
			return false;
		}

		return ! $this->send_request( $resource_row );
	}

	/**
	 * Send the warmup request.
	 *
	 * @param object $resource_row Resource DB row.
	 *
	 * @return bool
	 */
	private function send_request( $resource_row ) {
		// Send the request.
		$sent = $this->api_client->send_warmup_request(
			[
				'url'     => $resource_row->url,
				'type'    => $resource_row->type,
				'content' => $resource_row->content,
			]
		);

		// If success, update the database row to reset the column resend_to_warmup.
		if ( $sent ) {
			$this->resources_query->update_item(
				$resource_row->id,
				[
					'resend_to_warmup' => 0,
				]
			);

			return true;
		}

		return false;
	}
}
