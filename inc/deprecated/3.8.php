<?php

defined( 'ABSPATH' ) || exit;

class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache', '\WP_Rocket\Cache\Expired_Cache_Purge');
class_alias( '\WP_Rocket\Engine\Cache\PurgeExpired\Subscriber', '\WP_Rocket\Subscriber\Cache\Expired_Cache_Purge_Subscriber');
