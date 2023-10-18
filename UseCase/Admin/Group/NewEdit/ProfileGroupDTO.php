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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit;

use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEventInterface;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProfileGroupEvent */
final class ProfileGroupDTO implements ProfileGroupEventInterface
{

    private readonly UserProfileUid $profile;

    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?ProfileGroupEventUid $id = null;

    /**
     * Роли группы
     */
    #[Assert\Valid]
    private ArrayCollection $role;

    /**
     * Переводы
     */
    #[Assert\Valid]
    private ArrayCollection $translate;


    public function __construct(UserProfileUid $profile)
    {
        $this->role = new ArrayCollection();
        $this->translate = new ArrayCollection();
        $this->profile = $profile;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }


    /**
     * Идентификатор события
     */
    public function getEvent(): ?ProfileGroupEventUid
    {
        return $this->id;
    }


    /** Настройки локали */

    public function getTranslate(): ArrayCollection
    {
        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->translate) as $locale)
        {
            $TransFormDTO = new Trans\ProfileGroupTranslateDTO();
            $TransFormDTO->setLocal($locale);
            $this->addTranslate($TransFormDTO);
        }

        return $this->translate;
    }

    public function addTranslate(Trans\ProfileGroupTranslateDTO $trans): void
    {
        if(empty($trans->getLocal()->getLocalValue()))
        {
            return;
        }

        if(!$this->translate->contains($trans))
        {
            $this->translate->add($trans);
        }
    }


    public function removeTranslate(Trans\ProfileGroupTranslateDTO $trans): void
    {
        $this->translate->removeElement($trans);
    }

    /**
     * Role
     */
    public function getRole(): ArrayCollection
    {
        return $this->role;
    }

    public function setRole(ArrayCollection $role): self
    {
        $this->role = $role;
        return $this;
    }


    public function addRole(Role\ProfileRoleDTO $role): Role\ProfileRoleDTO
    {
        $filter = $this->role->filter(function(Role\ProfileRoleDTO $element) use ($role) {
            return $role->getPrefix()?->equals($element->getPrefix());
        });

        if($filter->isEmpty())
        {
            $this->role->add($role);
            return $role;
        }

        return $filter->current();
    }

    public function removeRole(Role\ProfileRoleDTO $role): void
    {
        $filter = $this->role->filter(function(Role\ProfileRoleDTO $element) use ($role) {
            return $role->getPrefix()?->equals($element->getPrefix());
        });

        if(!$filter->isEmpty())
        {
            $this->role->removeElement($filter->current());
        }
    }

}