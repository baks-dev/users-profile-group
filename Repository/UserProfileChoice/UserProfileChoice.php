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

namespace BaksDev\Users\Profile\Group\Repository\UserProfileChoice;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use Generator;

final class UserProfileChoice implements UserProfileChoiceInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Метод возвращает коллекцию профилей пользователя и всех доверенных профилей
     */
    public function getCollection(UserProfileUid $profile): Generator
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->select('profiles.profile AS value');
        $qb->addSelect('personal.username AS attr');


        $qb->from(ProfileGroupUsers::TABLE, 'profiles');

        $qb->where('profiles.authority = :profile');
        $qb->orWhere('profiles.profile = :profile');

        $qb->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            'profiles',
            UserProfile::TABLE,
            'profile',
            'profile.id = profiles.profile');


        $qb->join(
            'profiles',
            UserProfileInfo::TABLE,
            'info',
            'info.profile = profiles.profile AND info.status = :status')
            ->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);


        $qb->leftJoin(
            'profile',
            UserProfilePersonal::TABLE,
            'personal',
            'personal.event = profile.event');


        $qb->join(
            'info',
            Account::TABLE,
            'account',
            'account.id = info.usr');

        $qb->join(
            'account',
            \BaksDev\Auth\Email\Entity\Status\AccountStatus::TABLE,
            'status',
            'status.event = account.event AND status.status = :account_status')

        ->setParameter('account_status', new AccountStatus(AccountStatusEnum::ACTIVE), AccountStatus::TYPE);


        $qb->allGroupByExclude();

        return $qb
            // ->enableCache('Namespace', 3600)
            ->fetchAllHydrate(UserProfileUid::class);
    }
}