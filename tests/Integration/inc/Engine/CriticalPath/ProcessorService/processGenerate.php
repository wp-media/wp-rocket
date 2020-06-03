<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\ProcessorService;

use WP_Rocket\Engine\CriticalPath\DataManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\ProcessorService::process_generate
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_ProcessGenerate extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/ProcessorService/processGenerate.php';


}
