<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_AddPurgeTermLink extends AdminTestCase
{
    private $tag;
    public function tear_down()
    {
        parent::tear_down();
        wp_delete_term($this->tag->term_id, 'post_tag');
    }
    /**
     * @dataProvider providerTestData
     */
    public function testShouldAddCallbackForEachTerm($config, $expected)
    {
        $this->tag = $this->factory->tag->create_and_get(['name' => 'Ipseum']);
        if ($config['cap']) {
            $this->setRoleCap('administrator', 'rocket_purge_terms');
            $this->setCurrentUser('administrator');
            Functions\expect('wp_create_nonce')->once()->with("purge_cache_term-{$this->tag->term_id}")->andReturn($config['nonce']);
        }
        $this->setEditTagsAsCurrentScreen('post_tag');
        $this->fireAdminInit();
        $this->hasCallbackRegistered('post_tag_row_actions', AdminSubscriber::class, 'add_purge_term_link');
        $actions = apply_filters('post_tag_row_actions', [], $this->tag);
        if ($config['cap']) {
            $this->assertArrayHasKey('rocket_purge', $actions);
            // Populate the term's ID.
            $expected = str_replace('term-1', "term-{$this->tag->term_id}", $expected);
            $this->assertSame($expected, $actions['rocket_purge']);
        } else {
            $this->assertArrayNotHasKey('rocket_purge', $actions);
        }
    }
    public function providerTestData()
    {
        return $this->getTestData(__DIR__, 'addPurgeTermLink');
    }
}
