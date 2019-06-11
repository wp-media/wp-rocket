<?php
namespace WP_Rocket\Subscriber\CDN;

class CDNSubscriber implements Subscriber_Interface {
    public static function get_subscribed_events() {
        return [];
    }
}
