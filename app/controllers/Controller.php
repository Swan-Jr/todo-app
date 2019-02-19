<?php

namespace Controllers;

abstract class Controller
{
    public $twig;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->setupTwig();
    }

    private function setupTwig()
    {
//        Debug settings now
//
//        At prod debug option should be set to false and debug extension shouldn't be loaded
        $loader = new \Twig_Loader_Filesystem('../app/views');
        $this->twig = new \Twig_Environment($loader, array(
            'cache' => '../tmp/cache',
            'debug' => true,
        ));
//        Allowing the use of dump() function
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $this->twig->addGlobal('root_prefix', ROOT_PREFIX);
    }
}