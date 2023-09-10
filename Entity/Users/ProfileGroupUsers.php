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

namespace BaksDev\Users\Profile\Group\Entity\Users;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Профили пользователей в группе */

#[ORM\Entity]
#[ORM\Table(name: 'profile_group_users')]
#[ORM\Index(columns: ['profile', 'authority'])]
class ProfileGroupUsers extends EntityState
{
    public const TABLE = 'profile_group_users';

    /**
     * Префикс группы
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: GroupPrefix::TYPE)]
    private readonly GroupPrefix $prefix;

    /**
     * Профиль пользователя в группе
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private readonly UserProfileUid $profile;

    /**
     * Доверенность профиля пользователя
     */
    #[ORM\Column(type: UserProfileUid::TYPE, nullable: true)]
    private ?UserProfileUid $authority = null;

    /**
     * Prefix
     */
    public function getPrefix(): GroupPrefix
    {
        return $this->prefix;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof ProfileGroupUsersInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {

        if($dto instanceof ProfileGroupUsersInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
}