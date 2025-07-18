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

namespace BaksDev\Users\Profile\Group\Repository\AllProfileGroupUsers;


use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Translate\ProfileGroupTranslate;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllProfileGroupUsersRepository implements AllProfileGroupUsersInterface
{

    private ?SearchDTO $search = null;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Profile
     */
    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }


    /** Метод возвращает пагинатор ProfileGroupUsers */
    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class)->bindLocal();

        $dbal
            ->addSelect('users.profile as profile_id')
            ->from(ProfileGroupUsers::class, 'users');

        $dbal
            ->join(
                'users',
                ProfileGroup::class,
                'groups',
                'groups.prefix = users.prefix'
            );

        $dbal
            ->addSelect('trans.name as group_name')
            ->leftJoin(
                'groups',
                ProfileGroupTranslate::class,
                'trans',
                'trans.event = groups.event AND trans.local = :local'
            );


        /** Профиль создателя группы */

        // UserProfile
        $dbal->leftJoin(
            'groups',
            UserProfile::class,
            'group_profile',
            'group_profile.id = groups.profile'
        );

        $dbal
            ->addSelect('group_profile_personal.username AS group_profile_username')
            ->leftJoin(
                'group_profile',
                UserProfilePersonal::class,
                'group_profile_personal',
                'group_profile_personal.event = group_profile.event'
            );


        if($this->profile)
        {
            $dbal->where('users.authority = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }


        // ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ

        // UserProfile
        $dbal->leftJoin(
            'users',
            UserProfile::class,
            'users_profile',
            'users_profile.id = users.profile'
        );


        $dbal
            ->addSelect('users_profile_info.usr AS usr')
            ->leftJoin(
                'users',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = users.profile'
            );


        // Personal
        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event'
            );

        // Avatar
        $dbal
            ->addSelect("CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name) AS users_profile_avatar")
            ->addSelect("CASE WHEN users_profile_avatar.cdn THEN  CONCAT ( 'small.', users_profile_avatar.ext) ELSE users_profile_avatar.ext END AS users_profile_avatar_ext")
            ->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn')
            ->leftJoin(
                'users_profile',
                UserProfileAvatar::class,
                'users_profile_avatar',
                'users_profile_avatar.event = users_profile.event'
            );


        /* Поиск */
        if($this->search?->getQuery())
        {
            $this->DBALQueryBuilder
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('users.profile')
                ->addSearchLike('trans.name')
                ->addSearchLike('users_profile_personal.username');

        }

        return $this->paginator->fetchAllAssociative($dbal);
    }
}
