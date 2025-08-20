<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

/**
 * Performance Monitoring Jobs Factory
 */
class Factory implements SaasFactory {

	/**
	 * Performance Monitoring Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * Performance Monitoring Table.
	 *
	 * @var TableInterface
	 */
	private $table;

	/**
	 * API Client.
	 *
	 * @var AbstractAPIClient
	 */
	private $api_client;

	/**
	 * Instantiate the class.
	 *
	 * @param ManagerInterface  $manager Performance Monitoring Manager.
	 * @param TableInterface    $table Performance Monitoring Table.
	 * @param AbstractAPIClient $api_client API Client instance.
	 */
	public function __construct( ManagerInterface $manager, TableInterface $table, AbstractAPIClient $api_client ) {
		$this->manager    = $manager;
		$this->table      = $table;
		$this->api_client = $api_client;
	}

	/**
	 * Performance Monitoring job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface {
		return $this->manager;
	}

	/**
	 * Performance Monitoring Table.
	 *
	 * @return TableInterface
	 */
	public function table(): TableInterface {
		return $this->table;
	}

	/**
	 * API Client.
	 *
	 * @return AbstractAPIClient
	 */
	public function api(): AbstractAPIClient {
		return $this->api_client;
	}
}
