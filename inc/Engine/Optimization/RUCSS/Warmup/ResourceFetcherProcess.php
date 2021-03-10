<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Logger\Logger;
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
	 * ResourceFetcherProcess constructor.
	 *
	 * @param ResourcesQuery $resources_query ResourcesQuery instance.
	 */
	public function __construct( ResourcesQuery $resources_query ) {
		parent::__construct();

		$this->resources_query = $resources_query;
	}

	/**
	 * @inheritDoc
	 */
	protected function task( $resources ) {
		if ( ! is_array( $resources ) ) {
			return false;
		}

		$send_to_warmup = [];

		foreach ( $resources as $resource ) {
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
					$send_to_warmup[] = (int) $resource_id;
				}

				continue;
			}

			// In all cases update last_accessed column with current date/time.
			$this->resources_query->update_item(
				 $db_row->id,
				[
					'last_accessed' => gmdate( 'Y-m-d\TH:i:s\Z' ),
				]
				);

			// Check the content hash.
			if ( $db_row->hash === md5( $resource['content'] ) ) {
				// Do nothing.
				continue;
			}

			// Update this row with the new content and change resend_to_warmup to be 1.
			$this->resources_query->update_item(
				 $db_row->id,
				 [
					'content'          => $resource['content'],
					'hash'             => md5( $resource['content'] ),
					'resend_to_warmup' => 1,
				 ]
			);

			$send_to_warmup[] = $db_row->id;

		}

		if ( ! empty( $send_to_warmup ) ) {
			do_action( 'rocket_trigger_call_warmup', $send_to_warmup );
		}

		return false;
	}
}
