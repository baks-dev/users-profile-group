<?php

use BaksDev\Users\Profile\Group\BaksDevUsersProfileGroupBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    $MODULE = BaksDevUsersProfileGroupBundle::PATH;

    $routes->import(
        $MODULE.'Controller',
        'attribute',
        false,
        $MODULE.implode(DIRECTORY_SEPARATOR, ['Controller', '**', '*Test.php'])
    )
        ->prefix(\BaksDev\Core\Type\Locale\Locale::routes())
        ->namePrefix('users-profile-group:');
};
