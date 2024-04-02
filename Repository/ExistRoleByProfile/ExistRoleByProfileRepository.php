<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\Group\Repository\ExistRoleByProfile;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class ExistRoleByProfileRepository implements ExistRoleByProfileInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод проверяет, имеется ли у профиля хотя бы у одной группы роль
     */
    public function isExistRole(UserProfileUid $profile, string $voter): bool
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(ProfileGroupUsers::TABLE, 'group_user')
            ->where('group_user.profile = :profile OR group_user.authority = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            'group_user',
            ProfileGroup::TABLE,
            'profile_group',
            'profile_group.prefix = group_user.prefix'
        );

        $qb->join(
            'profile_group',
            ProfileRole::TABLE,
            'profile_role',
            'profile_role.event = profile_group.event'
        );

        $qb->join(
            'profile_role',
            ProfileVoter::TABLE,
            'group_voter',
            'group_voter.role = profile_role.id AND group_voter.prefix = :voter'
        )
            ->setParameter('voter', $voter);

        return $qb->fetchExist();
    }
}