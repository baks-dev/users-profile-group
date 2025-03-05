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

namespace BaksDev\Users\Profile\Group\Entity;

use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/* ProfileGroup */

#[ORM\Entity]
#[ORM\Table(name: 'profile_group')]
class ProfileGroup
{
    /**
     * Префикс группы сущности
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: GroupPrefix::TYPE)]
    private GroupPrefix $prefix;


    /**
     * Идентификатор профиля пользователя
     * кто создал группу
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profile;


    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProfileGroupEventUid::TYPE, unique: true)]
    private ProfileGroupEventUid $event;

    public function __construct(UserProfileUid $profile)
    {
        $prefix = uniqid('ROLE_', false);
        $this->prefix = new GroupPrefix($prefix);
        $this->profile = $profile;
    }

    public function __toString(): string
    {
        return (string) $this->prefix;
    }

    /**
     * Идентификатор
     */
    public function getPrefix(): GroupPrefix
    {
        return $this->prefix;
    }

    /**
     * Идентификатор События
     */
    public function getEvent(): ProfileGroupEventUid
    {
        return $this->event;
    }

    public function setEvent(ProfileGroupEventUid|ProfileGroupEvent $event): void
    {
        $this->event = $event instanceof ProfileGroupEvent ? $event->getId() : $event;
    }
}