<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_PurgeUserCache extends FilesystemTestCase
{
    use GlobTrait;
    protected $path_to_test_data = '/inc/Engine/Cache/PurgeActionsSubscriber/purgeUserCache.php';
    protected static $use_settings_trait = true;
    public function set_up()
    {
        parent::set_up();
        // Unhook WooCommerce, as it throws wpdb::prepare errors.
        remove_action('delete_user', 'wc_delete_user_data');
    }
    public function tear_down()
    {
        parent::tear_down();
        // Rewire WooCommerce.
        add_action('delete_user', 'wc_delete_user_data');
        remove_filter('pre_get_rocket_option_cache_logged_user', [$this, 'return_false']);
        remove_filter('pre_get_rocket_option_cache_logged_user', [$this, 'return_true']);
        remove_filter('rocket_common_cache_logged_users', [$this, 'return_true']);
    }
    public function testShouldNotPurgeUserCacheWhenUserCacheDisabled()
    {
        add_filter('pre_get_rocket_option_cache_logged_user', [$this, 'return_false']);
        Functions\expect('rocket_clean_user')->never();
        do_action('delete_user', $this->getUserId());
    }
    public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled()
    {
        add_filter('pre_get_rocket_option_cache_logged_user', [$this, 'return_true']);
        add_filter('rocket_common_cache_logged_users', [$this, 'return_true']);
        Functions\expect('rocket_clean_user')->never();
        do_action('delete_user', $this->getUserId());
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldPurgeCacheForUser($username, $dir, $userCacheFiles)
    {
        add_filter('pre_get_rocket_option_cache_logged_user', [$this, 'return_true']);
        // Check the files exist before running.
        foreach ($userCacheFiles as $file) {
            $this->assertTrue($this->filesystem->exists($file));
        }
        // rocket_clean_user() uses glob(), which not compatible with vfsStream.
        $this->deleteFiles($dir, $this->filesystem);
        do_action('delete_user', $this->getUserId($username));
        // Check the files were deleted.
        foreach ($userCacheFiles as $file) {
            $this->assertFalse($this->filesystem->exists($file));
        }
        // Check that all the other files were not deleted.
        foreach (array_diff($this->original_files, $userCacheFiles) as $file) {
            $this->assertTrue($this->filesystem->exists($file));
        }
    }
    private function getUserId($username = 'wpmedia')
    {
        return $this->factory->user->create(['user_login' => $username, 'role' => 'editor']);
    }
}
