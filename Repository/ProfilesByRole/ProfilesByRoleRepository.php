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

declare(strict_types=1);

namespace BaksDev\Users\Profile\Group\Repository\ProfilesByRole;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\BaksDevUsersProfileGroupBundle;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class ProfilesByRoleRepository implements ProfilesByRoleInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает массив профилей, имеющих определенную роль
     */
    public function findAll(string $prefix, ?UserProfileUid $profile = null): ?array
    {
        if(!class_exists(BaksDevUsersProfileGroupBundle::class))
        {
            return null;
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(ProfileGroup::class, 'profile_group');

        $dbal->join(
            'profile_group',
            ProfileRole::class,
            'profile_role',
            'profile_role.event = profile_group.event'
        );

        $dbal->join(
            'profile_role',
            ProfileVoter::class,
            'profile_voter',
            'profile_voter.role = profile_role.id'
        );

        $dbal->addSelect('profile_group_users.profile');
        $dbal->addSelect('profile_group_users.authority');
        $dbal->leftJoin(
            'profile_group',
            ProfileGroupUsers::class,
            'profile_group_users',
            'profile_group_users.prefix = profile_group.prefix'
        );

        if($profile)
        {
            /** Если передан профиль пользователя - получаем группы и доверености */
            $dbal
                ->andWhere('profile_group_users.profile = :profile OR profile_group_users.authority = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE);
        }
        else
        {
            /** По умолчанию получаем только роли групп пользователей */
            $dbal
                ->andWhere('profile_group_users.authority IS NULL');
        }

        $dbal
            ->andWhere('(
            profile_group.prefix = :prefix OR
            profile_role.prefix = :prefix OR 
            profile_voter.prefix = :prefix
        )')
            ->setParameter('prefix', $prefix);


        $dbal->groupBy('profile_group_users.profile, profile_group_users.authority');

        $originalArray = $dbal
            ->enableCache('users-profile-group', 3600)
            ->fetchAllAssociative();


        // Получение уникальных значений ключей "profile" и "authority" без значений null
        $uniqueValues = [];

        foreach($originalArray as $item)
        {
            if($item['profile'] && !in_array($item['profile'], $uniqueValues, true))
            {
                $uniqueValues[] = $item['profile'];
            }

            if($item['authority'] && !in_array($item['authority'], $uniqueValues, true))
            {
                $uniqueValues[] = $item['authority'];
            }
        }

        return $uniqueValues;
    }
}