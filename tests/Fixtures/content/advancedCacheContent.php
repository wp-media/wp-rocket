<?php

$start = <<<STARTING_CONTENTS
<?php

use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;

defined( 'ABSPATH' ) || exit;

define( 'WP_ROCKET_ADVANCED_CACHE', true );

\$rocket_path        = 'vfs://public/wp-content/plugins/wp-rocket/';
\$rocket_config_path = 'vfs://public/wp-content/wp-rocket-config/';
\$rocket_cache_path  = 'vfs://public/wp-content/cache/wp-rocket/';

if (
	version_compare( phpversion(), '5.6', '<' )
	|| ! file_exists( \$rocket_path )
	|| ! file_exists( \$rocket_config_path )
	|| ! file_exists( \$rocket_cache_path )
) {
	define( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM', true );
	return;
}


STARTING_CONTENTS;

$mobile = <<<MOBILE_CONTENTS

if ( file_exists( 'vfs://public/wp-content/plugins/wp-rocket/inc/classes/dependencies/mobiledetect/mobiledetectlib/Mobile_Detect.php' ) && ! class_exists( 'WP_Rocket_Mobile_Detect' ) ) {
	include_once 'vfs://public/wp-content/plugins/wp-rocket/inc/classes/dependencies/mobiledetect/mobiledetectlib/Mobile_Detect.php';
}

MOBILE_CONTENTS;

$end = file_get_contents(__DIR__ . '/endingContent.php');

return [
	'non_mobile' => "{$start}{$end}",
	'mobile'     => "{$start}{$mobile}{$end}",
];
