<?php

namespace Intervention\Httpauth;

interface UserInterface
{
    /**
     * Checks for valid username & password
     *
     * @param  array  $credentials
     * @return boolean
     */
    public function isValid($name, $password, $realm);

    /**
     * Parses the User Information from server variables

     * @return void
     */
    public function parse();

}
