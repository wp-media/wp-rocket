<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

/**
 * CloudFlare API
 */
class WP_Rocket_CloudFlareAPI
{
    // The URL of the API
    private $api_endpoint  = 'https://www.cloudflare.com/api_json.html';
	
	// The URL of Spam API
	private $spam_endpoint = 'https://www.cloudflare.com/ajax/external-event.html';
	
    // Timeout for the API requests in seconds
    const TIMEOUT = 5;

    // Stores the api key
    private $api_key;

    // Stores the email login
    private $email;

    /**
	 * @var The single instance of the class
	 */
	protected static $_instance = null;

    /**
     * Make a new instance of the API client
     */
    public function __construct( $email, $api_key )
    {
        $this->email   = $email;
        $this->api_key = $api_key;
    }

	/**
	 * Main WP_Rocket_CloudFlareAPI Instance
	 *
	 * Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return Main instance
	 */
	public static function instance( $email, $api_key ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $email, $api_key );
		}
		return self::$_instance;
	}

    /**
     * Retrieve A List Of The Domains
     * This lists all domains in a CloudFlare account along with other data.
     */
    public function zone_load_multi()
    {
        $data = array(
            'a' => 'zone_load_multi'
        );
        return $this->http_post( $data );
    }

    /**
     * List All The Current Settings
     * This function retrieves all the current settings for a given domain.
     */
    public function zone_settings( $domain )
    {
        $data = array(
            'a' => 'zone_settings',
            'z' => $domain
        );
        return $this->http_post( $data );
    }

    /**
     * Set The Cache Level
     * This function sets the Caching Level to Aggressive or Basic.
     * The switches are: (agg|basic).
     */
    public function cache_lvl( $domain, $mode )
    {
        $data = array(
            'a' => 'cache_lvl',
            'z' => $domain,
            'v' => (strtolower($mode) == 'agg') ? 'agg' : 'basic'
        );
        return $this->http_post( $data );
    }

    /**
     * Toggling Development Mode
     * This function allows you to toggle Development Mode on or off for a particular domain.
     * When Development Mode is on the cache is bypassed.
     * Development mode remains on for 3 hours or until when it is toggled back off.
     */
    public function devmode( $domain, $mode )
    {
        $data = array(
            'a' => 'devmode',
            'z' => $domain,
            'v' => ($mode == true) ? 1 : 0
        );
        return $this->http_post( $data );
    }

    /**
     * Clear CloudFlare's Cache
     * This function will purge CloudFlare of any cached files.
     * It may take up to 48 hours for the cache to rebuild and optimum performance to be achieved.
     * This function should be used sparingly.
     */
    public function fpurge_ts( $domain )
    {
        $data = array(
            'a' => 'fpurge_ts',
            'z' => $domain,
            'v' => 1
        );
        return $this->http_post( $data );
    }

    /**
     * Purge A Single File In CloudFlare's Cache
     * This function will purge a single file from CloudFlare's cache.
     */
    public function zone_file_purge( $domain, $url )
    {
        $data = array(
            'a'   => 'zone_file_purge',
            'z'   => $domain,
            'url' => $url
        );
        return $this->http_post( $data );
    }

    /**
     * Set Rocket Loader
     * This function changes Rocket Loader setting.
     */
    public function async( $domain, $mode )
    {
        $data = array(
            'a' => 'async',
            'z' => $domain,
            'v' => $mode
        );
        return $this->http_post( $data );
    }

    /**
     * Set Minification
     * This function changes minification settings.
     */
    public function minify( $domain, $mode )
    {
        $data = array(
            'a' => 'minify',
            'z' => $domain,
            'v' => $mode
        );
        return $this->http_post( $data );
    }

    /**
     * GLOBAL API CALL
     * HTTP POST a specific task with the supplied data
     */
    private function http_post( $data )
    {
        $data['u']   = $this->email;
        $data['tkn'] = $this->api_key;

        $response = wp_remote_post( $this->api_endpoint, array(
			'timeout'     => self::TIMEOUT,
			'headers'     => array(),
			'body'        => $data,
			'cookies'     => array()
		));

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        } else {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }
    }
    
    // Reporting Spam IP to CloudFlare
    public function reporting_spam_ip( $payload ) {
		$response = wp_remote_get(
			sprintf( '%s?evnt_v=%s&u=%s&tkn=%s&evnt_t=%s', $this->spam_endpoint, $payload, $this->email, $this->api_key, 'WP_SPAM' ), 
			array(
				'method'		=> 'GET',
				'timeout' 		=> self::TIMEOUT,
				'sslverify'		=> true,
				'user-agent'	=> 'CloudFlare/WordPress/' . WP_ROCKET_VERSION,
			) 
		);
		
		if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        } else {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }
    }
}