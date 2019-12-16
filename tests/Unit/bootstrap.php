<?php
/**
 * Bootstraps the WP Rocket Plugin Unit Tests
 *
 * @package WP_Rocket\Tests\Unit
 */

namespace WP_Rocket\Tests\Unit;

use function WP_Rocket\Tests\init_test_suite;

require_once dirname( dirname( __FILE__ ) ) . '/boostrap-functions.php';
init_test_suite( 'Unit' );
