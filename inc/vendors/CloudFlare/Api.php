<?php

namespace Cloudflare;

use Cloudflare\Exception\AuthenticationException;
use Cloudflare\Exception\UnauthorizedException;

/**
 * CloudFlare API wrapper
 *
 * A work in progress library for the Cloudflare API. The documentation for the API can be found at https://www.cloudflare.com/docs/.
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Api
{
    /**
     * Holds the provided email address for API authentication
     *
     * @var string
     */
    public $email;

    /**
     * Holds the provided auth_key for API authentication
     *
     * @var string
     */
    public $auth_key;

    /**
     * Holds the curl options
     *
     * @var array
     */
    public $curl_options;

    /**
     * Make a new instance of the API client
     * This can be done via providing the email address and api key as seperate parameters
     * or by passing in an already instantiated object from which the details will be extracted
     */
    public function __construct()
    {
        $num_args = func_num_args();
        if ($num_args === 1) {
            $parameters = func_get_args();
            $client = $parameters[0];
            $this->email = $client->email;
            $this->auth_key = $client->auth_key;
            $this->curl_options = $client->curl_options;
        } elseif ($num_args === 2) {
            $parameters = func_get_args();
            $this->email = $parameters[0];
            $this->auth_key = $parameters[1];
        }
    }

    /**
     * Setter to allow the setting of the email address
     *
     * @param string $email The email address associated with the Cloudflare account
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Setter to allow the setting of the Authentication Key
     *
     * @param string $token Authentication key, this can be retrieve from the 'My Account' section of the Cloudflare account
     */
    public function setAuthKey($token)
    {
        $this->auth_key = $token;
    }

    /**
     * Setter to allow the adding / changing of the Curl options that will be used within the HTTP requests
     *
     * @param int   $key   The CURLOPT_XXX option to set e.g. CURLOPT_TIMEOUT
     * @param mixed $value The value to be set on option e.g. 10
     */
    public function setCurlOption($key, $value)
    {
        $this->curl_options[$key] = $value;
    }

    /**
     * API call method for sending requests using GET
     *
     * @param string     $path Path of the endpoint
     * @param array|null $data Data to be sent along with the request
     *
     * @return mixed
     */
    public function get($path, array $data = null)
    {
        return $this->request($path, $data, 'get');
    }

    /**
     * API call method for sending requests using POST
     *
     * @param string     $path Path of the endpoint
     * @param array|null $data Data to be sent along with the request
     *
     * @return mixed
     */
    public function post($path, array $data = null)
    {
        return $this->request($path, $data, 'post');
    }

    /**
     * API call method for sending requests using PUT
     *
     * @param string     $path Path of the endpoint
     * @param array|null $data Data to be sent along with the request
     *
     * @return mixed
     */
    public function put($path, array $data = null)
    {
        return $this->request($path, $data, 'put');
    }

    /**
     * API call method for sending requests using DELETE
     *
     * @param string     $path Path of the endpoint
     * @param array|null $data Data to be sent along with the request
     *
     * @return mixed
     */
    public function delete($path, array $data = null)
    {
        return $this->request($path, $data, 'delete');
    }

    /**
     * API call method for sending requests using PATCH
     *
     * @param string     $path Path of the endpoint
     * @param array|null $data Data to be sent along with the request
     *
     * @return mixed
     */
    public function patch($path, array $data = null)
    {
        return $this->request($path, $data, 'patch');
    }

    /**
     * @codeCoverageIgnore
     *
     * API call method for sending requests using GET, POST, PUT, DELETE OR PATCH
     *
     * @param string      $path   Path of the endpoint
     * @param array|null  $data   Data to be sent along with the request
     * @param string|null $method Type of method that should be used ('GET', 'POST', 'PUT', 'DELETE', 'PATCH')
     *
     * @return mixed
     */
    protected function request($path, array $data = null, $method = null)
    {
        // Check if php-curl is enabled
        if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
			return false;
		}
		
        if (!isset($this->email, $this->auth_key) || false === filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new AuthenticationException('Authentication information must be provided');
        }

        $data = (is_null($data) ? [] : $data);
        $method = (is_null($method) ? 'get' : $method);

        //Removes null entries
        $data = array_filter($data, function ($val) {
            return !is_null($val);
        });

        $url = 'https://api.cloudflare.com/client/v4/'.$path;

        $default_curl_options = [
            CURLOPT_VERBOSE        => false,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ];

        $curl_options = $default_curl_options;
        if (isset($this->curl_options) && is_array($this->curl_options)) {
            $curl_options = array_replace($default_curl_options, $this->curl_options);
        }

        $wp_rocket_version = WP_ROCKET_VERSION;
        $headers = ["X-Auth-Email: {$this->email}", "X-Auth-Key: {$this->auth_key}", "User-Agent: wp-rocket/{$wp_rocket_version}"];

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);

        $headers[] = 'Content-type: application/json';
        $json_data = json_encode($data);

        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        } elseif ($method === 'put') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($method === 'delete') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } elseif ($method === 'patch') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        } else {
            $url .= '?'.http_build_query($data);
        }
		
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);

        $http_result = curl_exec($ch);
        $error = curl_error($ch);
        $information = curl_getinfo($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (in_array($http_code, [401, 403])) {
            throw new UnauthorizedException('You do not have permission to perform this request');
        }

        $response = json_decode($http_result);

        curl_close($ch);
        if (isset($response->success) && $response->success !== true) {
            $response->error = $error;
            $response->http_code = $http_code;
            $response->method = $method;
            $response->information = $information;
        }

        return $response;
    }
}
