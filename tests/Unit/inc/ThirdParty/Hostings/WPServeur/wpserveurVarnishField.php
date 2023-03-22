<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPServeur;

use WP_Rocket\ThirdParty\Hostings\WPServeur;


use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPServeur::wpserveur_varnish_field
 *
 */
class Test_wpserveurVarnishField extends TestCase {

    /**
    * @var WPServeur
    */
    protected $wpserveur;

    public function set_up() {
        parent::set_up();

        $this->wpserveur = new WPServeur();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		$this->stubTranslationFunctions();
		$this->assertSame($expected, $this->wpserveur->wpserveur_varnish_field($config));
    }
}
