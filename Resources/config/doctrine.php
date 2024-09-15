<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\Profile\Group\BaksDevUsersProfileGroupBundle;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventType;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\Type\Id\ProfileRoleType;
use BaksDev\Users\Profile\Group\Type\Id\ProfileRoleUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefixType;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefixType;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefixType;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine) {


    $doctrine->dbal()->type(ProfileGroupEventUid::TYPE)->class(ProfileGroupEventType::class);
    $doctrine->dbal()->type(ProfileRoleUid::TYPE)->class(ProfileRoleType::class);

    $doctrine->dbal()->type(GroupPrefix::TYPE)->class(GroupPrefixType::class);
    $doctrine->dbal()->type(GroupRolePrefix::TYPE)->class(GroupRolePrefixType::class);
    $doctrine->dbal()->type(RoleVoterPrefix::TYPE)->class(RoleVoterPrefixType::class);


    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);


    $emDefault->mapping('users-profile-group')
        ->type('attribute')
        ->dir(BaksDevUsersProfileGroupBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevUsersProfileGroupBundle::NAMESPACE.'\\Entity')
        ->alias('users-profile-group');
};
