<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\Group\Repository\ProfileGroup;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final readonly class ProfileGroupByUserProfileRepository implements ProfileGroupByUserProfileInterface
{

    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Возвращает префикс группы (GroupPrefix) профиля пользователя
     * $authority = false - если администратор ресурса
     */
    public function findProfileGroupByUserProfile(
        UserProfileUid $profile,
        UserProfileUid|bool|null $authority = null // доверенность
    ): GroupPrefix|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('groups.prefix')
            ->from(ProfileGroupUsers::class, 'groups')
            ->where('groups.authority IS NULL');

        /**
         * Если передана доверительная группа
         */
        if($authority instanceof UserProfileUid)
        {
            $dbal
                ->where('groups.authority = :authority')
                ->setParameter(
                    key: 'authority',
                    value: $authority,
                    type: UserProfileUid::TYPE,
                );
        }

        $dbal
            ->andWhere('groups.profile = :profile')
            ->setParameter(
                key: 'profile',
                value: $profile,
                type: UserProfileUid::TYPE,
            );

        $group = $dbal
            ->enableCache('users-profile-group', '1 hour')
            ->fetchOne();

        return $group ? new GroupPrefix($group) : false;
    }
}