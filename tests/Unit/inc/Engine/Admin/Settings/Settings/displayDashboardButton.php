<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Admin\Settings\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Settings\AdminBarMenuTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\AdminBarMenuTrait::dashboard_button
 * @group  Admin
 * @group  Settings
 */
class Test_DisplayDashboardButton extends TestCase {

	private $mocked_class;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}

	public function setUp(): void {
		parent::setUp();
		$this->mocked_class = $this->getMockForTrait(
			AdminBarMenuTrait::class,
			[],
			'',
			true,
			true,
			true,
			['generate']
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when( 'wp_get_environment_type' )
			->justReturn( $config['environment'] );

		$data = [
			'title' => $config['title'],
			'action' => $config['action'],
			'label' => $config['label'],
			'hover_text' => $config['title_attr_text']
		];

		if ( null !== $expected ) {
			$this->mocked_class->expects($this->once())
				->method('generate')
				->with('sections/clean-section', $data)
				->willReturn($expected);
		}

		ob_start();
		$this->mocked_class->dashboard_button(
			$config['context'],
			$config['title'],
			$config['label'],
			$config['action'],
			$config['title_attr_text']
		);
		$output = ob_get_clean();

		if ( null === $expected ) {
			$this->assertEmpty( $output );
			return;
		}
		$title_output = '<h4 class="wpr-title3">'. $config['label'] . '</h4>';
		$this->assertStringContainsString($title_output, $output);
	}
}
