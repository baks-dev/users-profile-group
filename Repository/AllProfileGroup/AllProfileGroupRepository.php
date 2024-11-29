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

namespace BaksDev\Users\Profile\Group\Repository\AllProfileGroup;


use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Translate\ProfileGroupTranslate;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllProfileGroupRepository implements AllProfileGroupInterface
{
    private PaginatorInterface $paginator;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    )
    {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    private ?SearchDTO $search = null;

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }


    /** Метод возвращает пагинатор ProfileGroup */
    public function fetchAllProfileGroupAssociative(
        ?UserProfileUid $profile
    ): PaginatorInterface
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->addSelect('profile_group.event');
        $dbal->from(ProfileGroup::class, 'profile_group');

        if($profile)
        {
            $dbal->where('profile_group.profile = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE);
        }

        $dbal
            ->addSelect('trans.name')
            ->leftJoin(
                'profile_group',
                ProfileGroupTranslate::class,
                'trans',
                'trans.event = profile_group.event AND trans.local = :local'
            );


        /** Ответственное лицо (Профиль пользователя) */

        $dbal
            ->addSelect('users_profile.id as users_profile_id')
            ->leftJoin(
                'profile_group',
                UserProfile::class,
                'users_profile',
                'users_profile.id = profile_group.profile'
            );

        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event'
            );

        /* Поиск */
        if($this->search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('trans.name');

        }

        return $this->paginator->fetchAllAssociative($dbal);
    }
}
