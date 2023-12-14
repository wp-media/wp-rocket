<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Jobs;

use WP_Rocket\Engine\Common\JobManager\AbstractFactory\SaasFactory;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;
use WP_Rocket\Engine\Common\Database\Table;

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
	 * @var Table
	 */
	private $table;

	/**
	 * Instatiate the class.
	 *
	 * @param ManagerInterface $manager RUCSS Manager.
	 * @param Table            $table RUCSS Table.
	 */
	public function __construct( ManagerInterface $manager, Table $table ) {
		$this->manager = $manager;
		$this->table   = $table;
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
	 * @return Table
	 */
	public function table(): Table {
		return $this->table;
	}
}
