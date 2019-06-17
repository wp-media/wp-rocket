<?php
namespace WP_Rocket\Subscriber\CDN;

class CDNSubscriber implements Subscriber_Interface {
	private $options;
	private $cdn;

	public function __construct( Options_Data $options, CDN $cdn ) {
		$this->options = $options;
		$this->cdn     = $cdn;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'rewrite', 24 ],
		];
	}

	public function rewrite( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $this->cdn->rewrite( $html );
	}

	private function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		if ( ! $this->options->get( 'cdn' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'cdn' ) ) {
			return false;
		}

		return true;
	}
}
