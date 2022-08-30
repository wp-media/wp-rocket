<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_MaybeDisplayAsMissedTablesNotice extends TestCase
{
	protected $settings;
	protected $options;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->settings = new Settings($this->options);
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->stubTranslationFunctions();
		Functions\expect('menu_page_url')->with('action-scheduler', false)->andReturn($config['links']);
		Functions\expect('rocket_notice_html')->with($expected['notice']);
		$this->settings->maybe_display_as_missed_tables_notice();
	}
}
