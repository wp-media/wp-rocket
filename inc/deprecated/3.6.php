<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * Class aliases.
 */
class_alias( '\WP_Rocket\ThirdParty\Plugins\Smush', '\WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber' );
class_alias( '\WP_Rocket\Admin\Settings\Page', '\WP_Rocket\Engine\Admin\Settings\Page' );
class_alias( '\WP_Rocket\Admin\Settings\Render', '\WP_Rocket\Engine\Admin\Settings\Render' );
class_alias( '\WP_Rocket\Admin\Settings\Settings', '\WP_Rocket\Engine\Admin\Settings\Settings' );
class_alias( '\WP_Rocket\ServiceProvider\Settings', '\WP_Rocket\Engine\Admin\Settings\ServiceProvider' );
class_alias( '\WP_Rocket\Subscriber\Admin\Settings\Page_Subscriber', '\WP_Rocket\Engine\Admin\Settings\Subscriber' );
