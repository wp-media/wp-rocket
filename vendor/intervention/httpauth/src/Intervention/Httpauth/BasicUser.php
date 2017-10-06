<?php

namespace Intervention\Httpauth;

class BasicUser implements UserInterface
{
    /**
     * The loginname of the user
     *
     * @var string
     */
    private $name;

    /**
     * The password of the user
     *
     * @var password
     */
    private $password;

    /**
     * Creates a new instance
     */
    public function __construct()
    {
        $this->parse();
    }

    /**
     * Checks for valid username & password
     *
     * @param  string  $name
     * @param  string  $password
     * @return boolean
     */
    public function isValid($name, $password, $realm = null)
    {
        return ($name == $this->name) && ($password == $this->password);
    }

    /**
     * Parses the User Information from server variables

     * @return void
     */
    public function parse()
    {
        if(array_key_exists('PHP_AUTH_USER', $_SERVER)) { // mod_php

            $this->name = $_SERVER['PHP_AUTH_USER'];
            $this->password = array_key_exists('PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : null;

        } elseif(array_key_exists('HTTP_AUTHENTICATION', $_SERVER)) { // most other servers

            if(strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']), 'basic') === 0) {

                $userdata = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHENTICATION'], 6)));
                list($this->name, $this->password) = $userdata;

            }
        }
    }
}
