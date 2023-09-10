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

namespace BaksDev\Users\Profile\Group\Security\Switcher;

use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserByUserProfile\UserByUserProfileInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
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

    public function __construct(
        TokenStorageInterface $tokenStorage,
        GetUserByIdInterface $getUserById,
        CurrentUserProfileInterface $currentUserProfile,
        UserByUserProfileInterface $userByUserProfile
    )
    {

        $this->getUserById = $getUserById;
        $this->tokenStorage = $tokenStorage;
        $this->currentUserProfile = $currentUserProfile;
        $this->userByUserProfile = $userByUserProfile;
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class)
    {
        return User::class === $class;
    }


    public function loadUserByIdentifier(string $identifier): UserInterface
    {

        /* Идентификатор профиля авторизации */
        $authority = new UserProfileUid($identifier);

        /* Идентификатор пользователя */
        //$current = $this->tokenStorage->getToken()?->getUser();

        $token = $this->tokenStorage->getToken();
        $current = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : $token?->getUser();

        //dump($current);

        if(!$current)
        {
            throw new InvalidArgumentException('User not found');
        }

        /** Получаем активный профиль текущего пользователя */
        $profile = $this->currentUserProfile->fetchProfileAssociative($current->getId(), false);
        $profile = new UserProfileUid($profile['user_profile_id']);
       

        /** Получаем пользователя, в которого авторизуемся  */
        $user = $this->userByUserProfile->findUserByProfile($authority);

        if(!$user)
        {
            throw new InvalidArgumentException('Switch User not found');
        }

        /**
         * Если не авторизован в пользователя и (Администратор ресурса или профиль принадлежит пользователю)
         * - не проверяем доверенность и сразу авторизуем
         */
        $ADMIN = $token instanceof SwitchUserToken ? false : in_array('ROLE_ADMIN', $current->getRoles());


        /** Роли профиля авторизации  */
        if($ADMIN || $user->getId()->equals($current->getId()))
        {
            if($ADMIN)
            {
                /** Тумблер профилей авторизации пользователя */
                $ApcuAdapter = new ApcuAdapter('Authority');
                $save = $ApcuAdapter->getItem($user->getUserIdentifier());
                $save->set($authority);
                $save->expiresAfter(86400);
                $ApcuAdapter->save($save);
            }

            $roles = $this->getUserById->fetchAllRoleUser($authority);
        }
        else
        {
            /** Применяем только доступные самозванцу роли */
            $roles = $this->getUserById->fetchAllRoleUser($profile, $authority);
        }


        $user->setRole($roles);
        $user->setProfile($authority);

        //dump($user);

        /** Тумблер профилей активного пользователя */
        $ApcuAdapter = new ApcuAdapter('Authority');
        $save = $ApcuAdapter->getItem($current->getUserIdentifier());
        $save->set($authority);
        $save->expiresAfter(86400);
        $ApcuAdapter->save($save);


        return $user;
    }
}