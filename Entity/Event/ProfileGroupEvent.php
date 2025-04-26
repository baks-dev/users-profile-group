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

namespace BaksDev\Users\Profile\Group\Entity\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\Group\Entity\Modify\ProfileGroupModify;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Translate\ProfileGroupTranslate;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\Type\Id\ProfileGroupUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;


/* ProfileGroupEvent */

#[ORM\Entity]
#[ORM\Table(name: 'profile_group_event')]
class ProfileGroupEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProfileGroupEventUid::TYPE)]
    private ProfileGroupEventUid $id;

    /**
     * Идентификатор ProfileGroup
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: GroupPrefix::TYPE, nullable: false)]
    private ?GroupPrefix $prefix = null;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: ProfileGroupModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ProfileGroupModify $modify;

    /**
     * Роли группы
     */
    #[ORM\OneToMany(targetEntity: ProfileRole::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $role;

    /**
     * Переводы
     */
    #[ORM\OneToMany(targetEntity: ProfileGroupTranslate::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $translate;


    public function __construct()
    {
        $this->id = new ProfileGroupEventUid();
        $this->modify = new ProfileGroupModify($this);
    }

    /**
     * Идентификатор События
     */

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ProfileGroupEventUid
    {
        return $this->id;
    }

    /**
     * Prefix
     */
    public function getPrefix(): ?GroupPrefix
    {
        return $this->prefix;
    }

    public function setMain(ProfileGroup|GroupPrefix $prefix): void
    {
        $this->prefix = $prefix instanceof ProfileGroup ? $prefix->getPrefix() : $prefix;
    }

    //    public function setPrefix(GroupPrefix $prefix): self
    //    {
    //        $this->prefix = $prefix;
    //        return $this;
    //    }

    /**
     * Translate
     */
    public function getNameByLocale(Locale $locale): ?string
    {
        $name = null;

        foreach($this->translate as $trans)
        {
            if($name = $trans->name($locale))
            {
                break;
            }
        }

        return $name;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof ProfileGroupEventInterface || $dto instanceof self)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof ProfileGroupEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}