<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\WarmUp\Controller;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller::fetch_links
 *
 * @group Media
 * @group ATF
 */
class Test_fetchLinks extends TestCase {
	private $context;
	private $options;
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->context = Mockery::mock( ContextInterface::class );
		$this->options = Mockery::mock( Options_Data::class );
		$this->controller = new Controller( $this->context, $this->options );
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        Functions\when( 'home_url' )->alias( function( $link = '' ) {
            return '' === $link ? 'https://example.org' : 'https://example.org' . $link;
        } );

        Functions\expect( 'wp_remote_get' )
            ->once()
            ->with( 'https://example.org', $config['headers'] )
            ->andReturn( $config['response'] );

        Functions\expect( 'wp_remote_retrieve_response_code' )
            ->once()
            ->with( $config['response'] )
            ->andReturn( $config['response']['response']['code'] );

        if ( 200 === $config['response']['response']['code'] ) {
            Functions\expect( 'wp_remote_retrieve_body' )
                ->once()
                ->with( $config['response'] )
                ->andReturn( $config['response']['body'] );
        }

        if ( isset( $config['found_link'] ) && $config['found_link'] ) {

            Functions\when( 'wp_parse_url' )->alias( function( $link ) {
                return parse_url( $link );
            } );
    
            Functions\when( 'wp_http_validate_url' )->alias( function( $link ) {
                return false !== strpos( $link, 'https' ) ? $link : false;
            } );
    
            Filters\expectApplied( 'rocket_atf_warmup_links_number' )
                ->once()
                ->with( 10 );
        }

		$this->assertSame(
			$expected,
			$this->controller->fetch_links()
		);
	}
}
