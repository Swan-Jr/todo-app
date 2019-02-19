<?php

namespace Controllers;


use Models\Auth;

class AuthController extends Controller
{
    private $authInstance;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        Auth::Authenticate();

        $this->authInstance = new Auth();
    }

    /**
     * Login page
     * Valid URI: /login
     *
     * @return mixed
     */
    public function index()
    {
        if ( Auth::isLoggedIn() ) {
            header('Location: ' . ($_SESSION['backRef'] ?? ROOT_PREFIX));
        }

        return $this->twig->render('login.html.twig', [
            'backRef' => $_SESSION['backRef'] ?? ROOT_PREFIX,
            'formAction' => $_SERVER['REQUEST_URI'],
        ]);
    }

    /**
     * Login action
     * Valid POST URI: /login
     *
     * @return mixed
     */
    public function login()
    {
        if ( Auth::isLoggedIn() ) {
            header('Location: ' . ($_SESSION['backRef'] ?? ROOT_PREFIX));
        }

        if ( !Auth::isValidInput() ) {

            return $this->twig->render('login.html.twig', [
                'username' => $_POST['username'],
                'formAction' => $_SERVER['REQUEST_URI'],
                'backRef' => $_SESSION['backRef'] ?? ROOT_PREFIX,
                'errors' => true,
            ]);
        }

        if ( !$this->authInstance->loginUser() ) {
           return $this->index();
        }

        header('Location: ' . ($_SESSION['backRef'] ?? ROOT_PREFIX));
    }

    public function logout()
    {
        if ( Auth::isLoggedIn() ) {
            Auth::logOut();
            header('Location: ' . ($_SESSION['backRef'] ?? ROOT_PREFIX));
        } else {
            header('Location: ' . ROOT_PREFIX . 'login');
        }
    }
}