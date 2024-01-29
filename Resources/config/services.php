<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

return static function(ContainerConfigurator $configurator) {

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $NAMESPACE = 'BaksDev\Users\Profile\Group\\';

    $MODULE = substr(__DIR__, 0, strpos(__DIR__, "Resources"));

    $services->load($NAMESPACE, $MODULE)
        ->exclude([
            $MODULE.'{Entity,Resources,Type}',
            $MODULE.'**/*Message.php',
            $MODULE.'**/*DTO.php',
        ])
    ;


    /** Резолверы */

    $services->set(UserProfileUid::class)->class(UserProfileUid::class);
};
