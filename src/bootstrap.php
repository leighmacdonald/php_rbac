<?php
/**
 * @package php_rbac
 * @author  Leigh MacDonald <leigh.macdonald@gmail.com>
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        /** @noinspection PhpIncludeInspection */
        return include $file;
    }

    return false;
}

if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) &&
    (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))
) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL;
    exit(1);
}

return $loader;
