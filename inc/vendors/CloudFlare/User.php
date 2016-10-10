<?php

namespace Cloudflare;

/**
 * CloudFlare API wrapper
 *
 * User
 * The currently logged in/authenticated User
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class User extends Api
{
    /**
     * User details
     */
    public function user()
    {
        return $this->get('user');
    }

    /**
     * Update user
     * Update part of your user details
     *
     * @param string|null $first_name User's first name
     * @param string|null $last_name  User's last name
     * @param string|null $telephone  User's telephone number
     * @param string|null $country    The country in which the user lives. (Full list is here: http://en.wikipedia.org/wiki/List_of_country_calling_codes)
     * @param string|null $zipcode    The zipcode or postal code where the user lives.
     */
    public function update($first_name = null, $last_name = null, $telephone = null, $country = null, $zipcode = null)
    {
        $data = [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'telephone'  => $telephone,
            'country'    => $country,
            'zipcode'    => $zipcode,
        ];

        return $this->patch('user', $data);
    }

    /**
     * Change your email address. Note: You must provide your current password.
     *
     * @param string $email         Your contact email address
     * @param string $confirm_email Your contact email address, repeated
     * @param string $password      Your current password
     */
    public function change_email($email, $confirm_email, $password)
    {
        $data = [
            'email'         => $email,
            'confirm_email' => $confirm_email,
            'password'      => $password,
        ];

        return $this->put('user/email', $data);
    }

    /**
     * Change your password
     *
     * @param string $old_password         Your current password
     * @param string $new_password         Your new password
     * @param string $new_password_confirm Your new password, repeated
     */
    public function change_password($old_password, $new_password, $new_password_confirm)
    {
        $data = [
            'old_password'         => $old_password,
            'new_password'         => $new_password,
            'new_password_confirm' => $new_password_confirm,
        ];

        return $this->put('user/password', $data);
    }

    /**
     * Change your username. Note: You must provide your current password.
     *
     * @param string $username A username used to access other cloudflare services, like support
     * @param string $password Your current password
     */
    public function change_username($username, $password)
    {
        $data = [
            'username' => $username,
            'password' => $password,
        ];

        return $this->put('user/username', $data);
    }

    /**
     * Begin setting up CloudFlare two-factor authentication with a given telephone number
     *
     * @param int    $country_code        The country code of your mobile phone number
     * @param string $mobile_phone_number Your mobile phone number
     * @param string $current_password    Your current CloudFlare password
     */
    public function initialize_two_factor_authentication($country_code, $mobile_phone_number, $current_password)
    {
        $data = [
            'country_code'        => $country_code,
            'mobile_phone_number' => $mobile_phone_number,
            'current_password'    => $current_password,
        ];

        return $this->post('/user/two_factor_authentication', $data);
    }

    /**
     * Finish setting up CloudFlare two-factor authentication with a given telephone number
     *
     * @param int $auth_token The token provided by the two-factor authenticator
     */
    public function finalize_two_factor_authentication($auth_token)
    {
        $data = [
            'auth_token' => $auth_token,
        ];

        return $this->put('user/two_factor_authentication', $data);
    }

    /**
     * Disable two-factor authentication for your CloudFlare user account
     *
     * @param int The token provided by the two-factor authenticator
     */
    public function disable_two_factor_authentication($auth_token)
    {
        $data = [
            'auth_token' => $auth_token,
        ];

        return $this->delete('user/two_factor_authentication', $data);
    }
}
