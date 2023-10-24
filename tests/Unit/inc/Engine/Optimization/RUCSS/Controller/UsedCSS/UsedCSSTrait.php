<?php

namespace Engine\Optimization\RUCSS\Controller\UsedCSS;

use _PHPStan_7c8075089\Nette\Schema\Context;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Tests\Unit\HasLoggerTrait;

trait UsedCSSTrait
{
	use HasLoggerTrait;

	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var UsedCSS_Query
	 */
	protected $used_css_query;

	/**
	 * @var APIClient
	 */
	protected $api;

	/**
	 * @var QueueInterface
	 */
	protected $queue;

	/**
	 * @var DataManager
	 */
	protected $data_manager;

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * @var Database
	 */
	protected $database;

	/**
	 * @var UsedCSS
	 */
	protected $usedcss;

	protected $context;

	protected $optimisedContext;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->used_css_query = $this->createMock(UsedCSS_Query::class);
		$this->api = Mockery::mock(APIClient::class);
		$this->queue = Mockery::mock(QueueInterface::class);
		$this->data_manager = Mockery::mock(DataManager::class);
		$this->filesystem = Mockery::mock(Filesystem::class);
		$this->database = Mockery::mock(Database::class);
		$this->context = Mockery::mock(ContextInterface::class);
		$this->optimisedContext = Mockery::mock(ContextInterface::class);

		$this->usedcss = new UsedCSS($this->options, $this->used_css_query, $this->api, $this->queue, $this->data_manager, $this->filesystem, $this->context, $this->optimisedContext);
		$this->set_logger($this->usedcss);
	}
}
