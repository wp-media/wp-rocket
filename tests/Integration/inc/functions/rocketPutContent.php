<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

class Test_RocketPutContent extends FilesystemTestCase
{
    protected $path_to_test_data = '/inc/functions/rocketPutContent.php';
    /**
     * @dataProvider providerTestData
     */
    public function testShouldPutContent($file, $content)
    {
        $original_content = $this->filesystem->get_contents($file);
        $this->assertTrue(rocket_put_content($file, $content));
        // Check that the file exists.
        $this->assertTrue($this->filesystem->exists($file));
        // Check the content.
        $new_content = $this->filesystem->get_contents($file);
        $this->assertNotSame($original_content, $new_content);
        $this->assertSame($content, $new_content);
    }
}
