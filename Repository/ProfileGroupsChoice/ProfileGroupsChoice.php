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

namespace BaksDev\Users\Profile\Group\Repository\ProfileGroupsChoice;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Translate\ProfileGroupTranslate;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class ProfileGroupsChoice implements ProfileGroupsChoiceInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(DBALQueryBuilder $DBALQueryBuilder,)
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Список идентификаторов групп ролей профиля пользователя
     */
    public function findProfileGroupsChoiceByProfile(UserProfileUid $profile): Generator
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class)->bindLocal();

        $qb->select('groups.prefix AS value');
        $qb->addSelect('trans.name AS attr');
        $qb->from(ProfileGroup::TABLE, 'groups');
        $qb->leftJoin(
            'groups',
            ProfileGroupTranslate::TABLE,
            'trans',
            'trans.event = groups.event AND trans.local = :local'
        );

        $qb->where('groups.profile = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE)
        ;
        
        return $qb
            ->enableCache('users-profile-group', 86400)
            ->fetchAllHydrate(GroupPrefix::class);

    }
}