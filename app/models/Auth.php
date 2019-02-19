<?php

namespace Models;


class Auth extends BaseModel
{
    private static $adminPass = 'admin1234';
    private static $adminName = 'admin';
    private static $anonymousUsername = 'anonymous user';
    private static $validUserRoles = [
        0 => 'anon',
        1 => 'admin',
    ];

    /**
     * Authenticating every accessing application user. Should be put at application start.
     */
    public static function Authenticate(): void
    {
        if (
            !isset($_SESSION['authenticated'], $_SESSION['userrole'])
            || !$_SESSION['authenticated']
            || !in_array($_SESSION['userrole'], self::$validUserRoles)
        ) {
            $_SESSION['authenticated'] = true;
            $_SESSION['userrole'] = self::$validUserRoles[0];
            $_SESSION['username'] = self::$anonymousUsername;
        }
    }

    /**
     * Permitting access to any method only to $roleAllowed role
     *
     * @param string $roleAllowed
     */
    public static function restrictAccess(string $roleAllowed)
    {
        if ($_SESSION['userrole'] !== $roleAllowed) {
            header('Refresh: 3; URL = '. ROOT_PREFIX);
            die('You are not allowed to access this page');
        }
    }

    /**
     * @return array
     */
    public static function getValidUserRoles(): array
    {
        return self::$validUserRoles;
    }

    /**
     * Checking if user is already logged in
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        if (
            $_SESSION['userrole'] !== self::$validUserRoles[0]
            && in_array($_SESSION['userrole'], self::$validUserRoles)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Logging user out
     */
    public static function logOut(): void
    {
        $_SESSION['userrole'] = self::$validUserRoles[0];
        $_SESSION['username'] = self::$anonymousUsername;
    }

    /**
     * Validating POST input before login action
     *
     * @return bool
     */
    public static function isValidInput(): bool
    {
        $patterns['usernamePattern'] = '/(^[\w]{3,}$)|(^[\w]+( [\w]+)+$)/';
        $patterns['passwordPattern'] = '/^[\w\d]{8,}$/';

        if (
            !isset($_POST['username'])
            || !isset($_POST['password'])
            || preg_match($patterns['usernamePattern'], $_POST['username']) !== 1
            || preg_match($patterns['passwordPattern'], $_POST['password']) !== 1
        ) {

            return false;
        }

        return true;
    }

    /**
     * Login a user attempt
     *
     * @return bool
     */
    public function loginUser(): bool
    {
        // ordinary this is done via db-request with hashed passwords and other stuff
        // but for now, hardcoded login and pass values inside this class is enough

        if ($_POST['username'] === self::$adminName && $_POST['password'] === self::$adminPass) {
            $_SESSION['userrole'] = self::$validUserRoles[1];
            $_SESSION['username'] = self::$adminName;

            return true;
        }

        return false;
    }
}