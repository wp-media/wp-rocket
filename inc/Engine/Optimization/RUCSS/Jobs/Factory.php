<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\TableInterface;

class Factory implements SaasFactory {

	/**
	 * RUCSS Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * RUCSS Table.
	 *
	 * @var TableInterface
	 */
	private $table;

	/**
	 * API Client instance.
	 *
	 * @var AbstractAPIClient
	 */
	private $api_client;

	/**
	 * Instantiate the class.
	 *
	 * @param ManagerInterface  $manager RUCSS Manager.
	 * @param TableInterface    $table RUCSS Table.
	 * @param AbstractAPIClient $api_client API Client instance.
	 */
	public function __construct( ManagerInterface $manager, TableInterface $table, AbstractAPIClient $api_client ) {
		$this->manager    = $manager;
		$this->table      = $table;
		$this->api_client = $api_client;
	}

	/**
	 * RUCSS job manager.
	 *
	 * @return ManagerInterface
	 */
	public function manager(): ManagerInterface {
		return $this->manager;
	}

	/**
	 * RUCSS Table.
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
