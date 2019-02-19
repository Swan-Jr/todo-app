<?php

$startScript = 'index.php';

// Saving permanent part of URI
$rootPrefix = resolvePrefix($startScript) ?? '/';
define('ROOT_PREFIX', $rootPrefix);

// Resolving valid URI in case the '/public' folder is not located at the root of host
$requestPattern = '~' . ROOT_PREFIX . '(' . $startScript . ')?(.*)~';
$pregMatches = [];
preg_match($requestPattern, $_SERVER['REQUEST_URI'], $pregMatches);

// casting URI to corresponding pattern: [/(index.php/)(any(/possible(/route)))]
$validRequestURI = ($pregMatches[1] === '' && $pregMatches[2] !== '' ? '/' : '') . ($pregMatches[2] === '' ? '/' : $pregMatches[2]);

function resolvePrefix(string $scriptName): string
{
    $scriptLocation = strstr($_SERVER['SCRIPT_NAME'], $scriptName, true);
    $Uri = $_SERVER['REQUEST_URI'];

    $i = 0;
    $result = '';
    while ( isset($scriptLocation[$i], $Uri[$i]) && ($scriptLocation[$i] === $Uri[$i]) ) {
        $result .= $scriptLocation[$i];
        $i++;
    }

    return $result;
}
