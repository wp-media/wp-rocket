<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\HTML;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\HTML::move_meta_charset_to_head
 * @group  Optimize
 * @group  DelayJS
 */
class Test_MoveMetaCharsetToHead extends TestCase {
	/**
	 * @dataProvider providerSpecialCharacters
	 */
	public function testShouldKeepSpecialCharactersLiteral( $html_template, $suffix ) {
		$meta = '<meta charset="UTF-8" data-test="' . $suffix . '">';
		$html = sprintf( $html_template, $meta );
		$html_object = new HTML(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( DataManager::class ),
			Mockery::mock( Logger::class )
		);

		$result = $html_object->move_meta_charset_to_head( $html );

		$this->assertSame( 1, substr_count( $result, $meta ) );
		$this->assertStringNotContainsString( '<meta charset="UTF-8" data-test="' . $suffix . '"><meta', $result );
	}

	public function providerSpecialCharacters() {
		$templates = [
			'head'    => '<html><head><title>t</title>%s</head><body></body></html>',
			'html'    => '<html>%s</head><body></body></html>',
		];
		$values = [ '$1 foo', '${1} foo', '\\1 foo' ];
		$data   = [];
		foreach ( $templates as $name => $template ) {
			foreach ( $values as $value ) {
				$data[ $name . ' ' . $value ] = [ $template, $value ];
			}
		}
		return $data;
	}
}
