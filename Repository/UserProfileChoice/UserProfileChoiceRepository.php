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

namespace BaksDev\Users\Profile\Group\Repository\UserProfileChoice;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Generator;

final readonly class UserProfileChoiceRepository implements UserProfileChoiceInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает коллекцию профилей пользователя и всех доверенных профилей
     */
    public function getCollection(UserProfileUid $profile): Generator
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(ProfileGroupUsers::class, 'profiles')
            ->where('profiles.authority = :profile')
            ->orWhere('profiles.profile = :profile');

        $dbal->setParameter('profile', $profile, UserProfileUid::TYPE);

        $dbal->join(
            'profiles',
            UserProfile::class,
            'profile',
            'profile.id = profiles.profile'
        );


        $dbal
            ->join(
                'profiles',
                UserProfileInfo::class,
                'info',
                'info.profile = profiles.profile AND info.status = :status'
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );


        $dbal->leftJoin(
            'profile',
            UserProfilePersonal::class,
            'personal',
            'personal.event = profile.event'
        );


        $dbal->join(
            'info',
            Account::class,
            'account',
            'account.id = info.usr'
        );

        $dbal
            ->join(
                'account',
                AccountStatus::class,
                'status',
                'status.event = account.event AND status.status = :account_status'
            )
            ->setParameter(
                'account_status',
                EmailStatusActive::class,
                EmailStatus::TYPE
            );


        /** Свойства конструктора объекта гидрации */
        $dbal->select('profiles.profile AS value');
        $dbal->addSelect('personal.username AS attr');

        $dbal->allGroupByExclude();

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchAllHydrate(UserProfileUid::class);
    }
}
