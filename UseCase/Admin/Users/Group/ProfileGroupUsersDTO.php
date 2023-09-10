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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group;

use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsersInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProfileGroupUsers */
final class ProfileGroupUsersDTO implements ProfileGroupUsersInterface
{

    /**
     * Префикс группы
     */
    #[Assert\NotBlank]
    private GroupPrefix $prefix;

    /**
     * Профиль пользователя в группе
     */
    #[Assert\NotBlank]
    private readonly UserProfileUid $profile;


    /**
     * Доверенность профиля пользователя
     */
    private ?UserProfileUid $authority = null;

    /**
     * Префикс группы
     */
    public function getPrefix(): GroupPrefix
    {
        return $this->prefix;
    }

    public function setPrefix(GroupPrefix $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Профиль пользователя в группе
     */
    public function getProfile(): ?UserProfileUid
    {

        if (!(new ReflectionProperty($this::class, 'profile'))->isInitialized($this)) {
           return null;
        }

        return $this->profile;
    }

    public function setProfile(UserProfileUid $profile): self
    {
        if (!(new ReflectionProperty($this::class, 'profile'))->isInitialized($this)) {
            $this->profile = $profile;
        }

        return $this;
    }

    /**
     * Доверенность профиля пользователя
     */
    public function getAuthority(): ?UserProfileUid
    {
        return $this->authority;
    }

    public function setAuthority(?UserProfileUid $authority): self
    {
        $this->authority = $authority;
        return $this;
    }


}