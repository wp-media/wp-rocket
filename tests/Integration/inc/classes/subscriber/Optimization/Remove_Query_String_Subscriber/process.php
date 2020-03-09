<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Remove_Query_String_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Optimization\Remove_Query_String_Subscriber;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Remove_Query_String_Subscriber::process
 * @group  RemoveQueryString
 */
class Test_Process extends FilesystemTestCase {
	private static $container;
	private $subscriber;
	protected $rootVirtualDir = 'wordpress';
	protected $structure = [
        'wp-includes' => [
            'js' => [
                'jquery' => [
                    'jquery.js' => 'jquery',
                ],
            ],
            'css' => [
                'dashicons.min.css' => '',
            ],
        ],
        'wp-content' => [
            'cache' => [
                'busting' => [
                    '1' => [],
                ],
            ],
            'themes' => [
                'twentytwenty' => [
                    'style.css' => 'test',
                    'assets'    => [
                        'script.js' => 'test',
                    ]
                ]
            ],
            'plugins' => [
                'hello-dolly' => [
                    'style.css'  => 'test',
                    'script.js' => 'test',
                ]
            ],
        ],
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )->andReturn( $this->filesystem->getUrl( 'wp-content/cache/busting/' ) );
		$this->subscriber = new Remove_Query_String_Subscriber( self::$container->get( 'remove_query_string' ) );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldRemoveQueryStrings( $original, $expected ) {
		add_filter( 'pre_get_rocket_option_remove_query_strings', '__return_true' );

		$this->assertSame(
			$expected,
			$this->subscriber->process( $original )
		);

		remove_filter( 'pre_get_rocket_option_remove_query_strings', '__return_true' );
	}

	public function addDataProvider() {
        return $this->getTestData( __DIR__, 'remove-query-strings' );
    }
}
