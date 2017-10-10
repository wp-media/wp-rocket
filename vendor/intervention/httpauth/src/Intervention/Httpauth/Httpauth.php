<?php

namespace Intervention\Httpauth;

use Exception;

class Httpauth
{
    /**
     * Type of HTTP Authentication
     *
     * @var string
     */
    public $type = 'basic';

    /**
     * Realm of HTTP Authentication
     *
     * @var string
     */
    public $realm = 'Secured resource';

    /**
     * Username of HTTP Authentication
     *
     * @var string
     */
    private $username;

    /**
     * Password of HTTP Authentication
     *
     * @var string
     */
    private $password;

    /**
     * Creates new instance of Httpauth
     *
     * @param array $parameters set realm, username and/or password as key
     */

    public function __construct($parameters = null)
    {
        // overwrite settings with runtime parameters (optional)
        if (is_array($parameters)) {

            if (array_key_exists('type', $parameters)) {
                $this->type = $parameters['type'];
            }

            if (array_key_exists('realm', $parameters)) {
                $this->realm = $parameters['realm'];
            }

            if (array_key_exists('username', $parameters)) {
                $this->username = $parameters['username'];
            }

            if (array_key_exists('password', $parameters)) {
                $this->password = $parameters['password'];
            }
        }

        // check if at leat username and password is set
        if ( ! $this->username || ! $this->password) {
            throw new Exception('No username or password set for HttpAuthentication.');
        }
    }

    /**
     * Creates new instance of Httpaccess with given parameters
     *
     * @param  array  $parameters   set realm, username and/or password
     * @return Intervention\Httpauth\Httpauth
     */
    public static function make($parameters = null)
    {
        return new Httpauth($parameters);
    }

    /**
     * Denies access for not-authenticated users
     *
     * @return void
     */
    public function secure()
    {
        if ( ! $this->validateUser($this->getUser())) {
            $this->denyAccess();
        }
    }

    /**
     * Checks for valid user
     *
     * @param  User $user
     * @return bool
     */
    private function validateUser(UserInterface $user)
    {
        return $user->isValid($this->username, $this->password, $this->realm);
    }

    /**
     * Checks if username/password combination matches
     *
     * @param  string  $username
     * @param  string  $password
     * @return boolean
     */
    public function isValid($username, $password)
    {
        return ($username == $this->username) && ($password == $this->password);
    }

    /**
     * Sends HTTP 401 Header
     *
     * @return void
     */
    private function denyAccess()
    {
        header('HTTP/1.0 401 Unauthorized');

        switch (strtolower($this->type)) {
            
            case 'digest':
                header('WWW-Authenticate: Digest realm="' . $this->realm .'",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->realm) . '"');
                break;

            default:
                header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
                break;
        }

        die('<strong>HTTP/1.0 401 Unauthorized</strong>');
    }

    /**
     * Get User according to current auth type
     *
     * @return Intervention\Httpauth\UserInterface
     */
    private function getUser()
    {
        // set user based on authentication type
        switch (strtolower($this->type)) {

            case 'digest':
                return new DigestUser;
                break;

            default:
                return new BasicUser;
                break;
        }
    }
}
