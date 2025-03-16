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

namespace BaksDev\Users\Profile\Group\Security\Switcher;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserByUserProfile\UserByUserProfileInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


final class SwitchUserProvider implements UserProviderInterface
{

    private GetUserByIdInterface $getUserById;
    private TokenStorageInterface $tokenStorage;
    private CurrentUserProfileInterface $currentUserProfile;
    private UserByUserProfileInterface $userByUserProfile;
    private AppCacheInterface $cache;

    public function __construct(
        AppCacheInterface $cache,
        TokenStorageInterface $tokenStorage,
        GetUserByIdInterface $getUserById,
        CurrentUserProfileInterface $currentUserProfile,
        UserByUserProfileInterface $userByUserProfile,
        private readonly RequestStack $request
    )
    {

        $this->getUserById = $getUserById;
        $this->tokenStorage = $tokenStorage;
        $this->currentUserProfile = $currentUserProfile;
        $this->userByUserProfile = $userByUserProfile;
        $this->cache = $cache;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }


    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        /* Идентификатор профиля авторизации */
        $authority = new UserProfileUid($identifier);

        $token = $this->tokenStorage->getToken();
        $current = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : $token?->getUser();


        if(!$current || !$current instanceof UserInterface)
        {
            throw new InvalidArgumentException('User not found');
        }

        /** Получаем активный профиль текущего пользователя */
        $profile = $this->currentUserProfile->fetchProfileAssociative($current->getId(), false);
        $profile = new UserProfileUid($profile['user_profile_id']);


        /** Получаем пользователя, в которого авторизуемся  */
        $user = $this->userByUserProfile
            ->forProfile($authority)
            ->find();

        if(!$user)
        {
            throw new InvalidArgumentException('Switch User not found');
        }

        /**
         * Если не авторизован в пользователя и (Администратор ресурса или профиль принадлежит пользователю)
         * - не проверяем доверенность и сразу авторизуем
         */
        $ADMIN = !$token instanceof SwitchUserToken && in_array('ROLE_ADMIN', $current->getRoles());


        /** Роли профиля авторизации  */
        if($ADMIN || $user->getId()->equals($current->getId()))
        {
            /** Если пользователь Администратор ресурса и авторизуется в собственный профиль - присваиваем родительские роли */
            if($ADMIN && $user->getId()->equals($current->getId()))
            {
                $roles = $current->getRoles();
            }
            else
            {
                $roles = $this->getUserById->fetchAllRoleUser($authority);
            }
        }
        else
        {
            /** Применяем только доступные самозванцу роли */
            $roles = $this->getUserById->fetchAllRoleUser($profile, $authority);
        }


        $user->setRole($roles);
        $user->setProfile($authority);

        /** Тумблер профилей активного пользователя */
        $Session = $this->request->getSession();
        $Session->set('Authority', $authority);
        $Session->save();

        //        $AppCache = $this->cache->init('Authority', 0);
        //        $save = $AppCache->getItem($current->getUserIdentifier());
        //        $save->set($authority);
        //        $save->expiresAfter(DateInterval::createFromDateString('1 weeks'));
        //        $AppCache->save($save);

        return $user;
    }
}