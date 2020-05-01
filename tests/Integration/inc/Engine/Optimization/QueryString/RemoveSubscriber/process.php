<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\QueryString\RemoveSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\QueryString\RemoveSubscriber::process
 *
 * @group  RemoveQueryStrings
 */
class Test_Process extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Optimization/QueryString/RemoveSubscriber/remove-query-strings.php';
    protected $cnames;
    protected $zones;
    private $settings;

	public function setUp() {
		parent::setUp();

		// Mocks constants for the virtual filesystem.
		$this->whenRocketGetConstant();
	}

	public function tearDown() {
		parent::tearDown();

		$this->unset_settings();
		remove_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRemoveQueryStrings( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );

		$this->settings = $settings;
		$this->set_settings();

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);
	}

    private function set_settings() {
        foreach ( (array) $this->settings as $key => $value ) {
	        $this->handleSetting( $key, $value );
        }
    }

    private function unset_settings() {
        foreach ( (array) $this->settings as $key => $value ) {
        	$this->handleSetting( $key, $value, false );
        }
    }

    private function handleSetting( $key, $value, $set = true ) {
		$func = $set ? 'add_filter' : 'remove_filter';

		switch( $key ) {
		    case 'cdn':
			    $callback = 0 === $value ? 'return_false' : 'return_true';
			    $func( 'pre_get_rocket_option_cdn', [ $this, $callback ] );

			    break;
		    case 'cdn_cnames':
			    $this->cnames = $value;
			    $func( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames'] );
			    break;
		    case 'cdn_zone':
			    $this->zones = $value;
			    $func( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones'] );
	    }
    }

    public function set_cnames() {
        return $this->cnames;
    }

    public function set_zones() {
        return $this->zones;
    }
}
