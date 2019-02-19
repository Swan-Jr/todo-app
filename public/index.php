<?php

session_start();

require_once '../vendor/autoload.php';

require_once '../app/config.php';

require_once '../app/uri_resolver.php';

// Controller name shortcut
$cn = '';
// Calling class instance
$app = null;
$pregMatches = [];
try {
    foreach ($routes[$_SERVER['REQUEST_METHOD']] as $key => $value) {

        // resolving received URI and calling corresponding class instance and method if one is matched
        if (preg_match($key, $validRequestURI, $pregMatches) === 1) {
            $targetAction = preg_split('/@/', $value);
            $cn = $controllerNamespace . $targetAction[0];

            $app = new $cn();
            $output = isset($pregMatches[1])
                ? $app->{$targetAction[1]}(...(array_slice($pregMatches, 1)))
                : $app->{$targetAction[1]}();
            echo $output;

            break;
        }
    }
} catch (Throwable $e) {
    echo $e->getMessage();
} finally {
    if ($app === null)
        die('404 ERROR: PAGE NOT FOUND');
}
