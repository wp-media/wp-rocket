<?php
namespace WP_Rocket\Tests\Fixtures\inc\classes\subscriber\Tools;

use WP_Rocket\Subscriber\Tools\Cache_Dir_Size_Check_Subscriber;

/**
 * Allow to test the class Cache_Dir_Size_Check_Subscriber.
 */
class CacheDirSizeCheckSubscriber extends Cache_Dir_Size_Check_Subscriber {
	public $tests_dir_size = 0;

	private function get_dir_size( $dir ) {
		return $this->tests_dir_size;
	}
}
