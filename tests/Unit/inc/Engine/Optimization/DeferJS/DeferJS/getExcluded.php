<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::get_excluded
 *
 * @group  DeferJS
 */
class Test_GetExcluded extends TestCase {
	private $options;
	private $defer_js;
	private $data_manager;
	private $exclusions_list;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->defer_js = new DeferJS( $this->options, $this->data_manager );
		$this->exclusions_list = (object) [
			'defer_js_external_exclusions' => [
				'gist.github.com',
				'content.jwplatform.com',
				'js.hsforms.net',
				'www.uplaunch.com',
				'google.com/recaptcha',
				'widget.reviews.co.uk',
				'verify.authorize.net/anetseal',
				'lib/admin/assets/lib/webfont/webfont.min.js',
				'app.mailerlite.com',
				'widget.reviews.io',
				'simplybook.(.*)/v2/widget/widget.js',
				'/wp-includes/js/dist/i18n.min.js',
				'/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
				'/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
				'/wp-content/plugins/ewww-image-optimizer/includes/check-webp(.min)?.js',
				'static.mailerlite.com/data/(.*).js',
				'cdn.voxpow.com/static/libs/v1/(.*).js',
				'cdn.voxpow.com/media/trackers/js/(.*).js',
				'use.typekit.net',
				'www.idxhome.com',
				'/wp-includes/js/dist/vendor/lodash(.min)?.js',
				'/wp-includes/js/dist/api-fetch(.min)?.js',
				'/wp-includes/js/dist/i18n(.min)?.js',
				'/wp-includes/js/dist/vendor/wp-polyfill(.min)?.js',
				'/wp-includes/js/dist/url(.min)?.js',
				'/wp-includes/js/dist/hooks(.min)?.js',
				'www.paypal.com/sdk/js',
				'js-eu1.hsforms.net',
				'yanovis.Voucher.js',
				'/carousel-upsells-and-related-product-for-woocommerce/assets/js/glide.min.js',
				'use.typekit.com',
				'/artale/modules/kirki/assets/webfont.js',
				'/api/scripts/lb_cs.js',
			],
		];
	}

	public function testShouldReturnEmptyWhenCannotDefer() {
		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( false );

		$this->assertEmpty( $this->defer_js->get_excluded() );
	}

	public function testShouldNotDeferDefaultItems() {
		$this->data_manager->shouldReceive( 'get_lists' )
			->once()
			->andReturn( $this->exclusions_list );

		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn( [] );

		$this->assertContains(
			'gist.github.com',
			$this->defer_js->get_excluded()
		);
	}

	public function testShouldNotDeferUserExcludedItems() {
		$this->data_manager->shouldReceive( 'get_lists' )
			->once()
			->andReturn( $this->exclusions_list );

		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn(
			              [
				              '/path/to/my/userfile.js'
			              ]
		              );

		$this->assertContains(
			'/path/to/my/userfile.js',
			$this->defer_js->get_excluded()
		);
	}

	public function testShouldUniquelyMergeDefaultAndUserExclusions() {
		$this->data_manager->shouldReceive( 'get_lists' )
			->once()
			->andReturn( $this->exclusions_list );

		$this->options->shouldReceive( 'get' )
		              ->with( 'defer_all_js', 0 )
		              ->once()
		              ->andReturn( true );

		$this->options->shouldReceive( 'get' )
		              ->with( 'exclude_defer_js', [] )
		              ->once()
		              ->andReturn(
			              [
				              // user adds an item already in default list.
				              'gist.github.com'
			              ]
		              );

		$excluded_items = $this->defer_js->get_excluded();

		$this->assertFalse(
			count( array_unique( $excluded_items ) ) < count( $excluded_items )
		);
	}
}
