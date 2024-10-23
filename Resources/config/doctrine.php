<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

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

return static function(DoctrineConfig $doctrine) {


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
