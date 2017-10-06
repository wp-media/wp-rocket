<?php

namespace Intervention\Httpauth;

class DigestUser implements UserInterface
{
    /**
     * Nonce to discourage cryptanalysis
     *
     * @var string
     */
    private $nonce;

    /**
     * nc
     * @var string
     */
    private $nc;

    /**
     * cnonce
     * @var string
     */
    private $cnonce;

    /**
     * quality of protection
     *
     * @var string
     */
    private $qop;

    /**
     * Name of user
     *
     * @var string
     */
    private $username;

    /**
     * Requested uri
     *
     * @var string
     */
    private $uri;

    /**
     * Response
     *
     * @var string
     */
    private $response;

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
    public function isValid($name, $password, $realm)
    {
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $u1 = md5(sprintf('%s:%s:%s', $this->username, $realm, $password));
        $u2 = md5(sprintf('%s:%s', $request_method, $this->uri));
        $response = md5(sprintf('%s:%s:%s:%s:%s:%s', $u1, $this->nonce, $this->nc, $this->cnonce, $this->qop, $u2));

        return ($response == $this->response) && ($name == $this->username);
    }

    /**
     * Parses the User Information from server variables

     * @return void
     */
    public function parse()
    {
        $digest = $this->getDigest();
        $user = array();
        $required = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);

        preg_match_all('@(\w+)=(?:(?:")([^"]+)"|([^\s,$]+))@', $digest, $matches, PREG_SET_ORDER);

        if (is_array($matches)) {
            foreach ($matches as $m) {
                $key = $m[1];
                $user[$key] = $m[2] ? $m[2] : $m[3];
                unset($required[$key]);
            }

            if (count($required) == 0) {
                $this->nonce = $user['nonce'];
                $this->nc = $user['nc'];
                $this->cnonce = $user['cnonce'];
                $this->qop = $user['qop'];
                $this->username = $user['username'];
                $this->uri = $user['uri'];
                $this->response = $user['response'];
            }
        }
    }

    /**
     * Fetch digest data from environment information
     *
     * @return string
     */
    public function getDigest()
    {
        $digest = null;

        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {

            $digest = $_SERVER['PHP_AUTH_DIGEST'];

        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

            if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'digest') === 0) {

                $digest = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
            }
        }

        return $digest;
    }
}
