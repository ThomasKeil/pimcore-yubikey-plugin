<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 27.12.14
 * Time: 14:06
 */

namespace YubiKey;
use Pimcore\Log;

class RemoteAuthenticator {

    /**
     * @param $username
     * @param $password
     * @return null|\Pimcore\Model\User
     */
    public static function authenticate($username, $password) {
        $user = null;

        return $user;
    }
}