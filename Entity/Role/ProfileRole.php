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

namespace BaksDev\Users\Profile\Group\Entity\Role;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Type\Id\ProfileRoleUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Перевод ProfileGroupRole */

#[ORM\Entity]
#[ORM\Table(name: 'profile_group_role')]
class ProfileRole extends EntityEvent
{
    public const TABLE = 'profile_group_role';

    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: ProfileRoleUid::TYPE)]
    private ProfileRoleUid $id;

    /**
     * Связь на событие
     */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: ProfileGroupEvent::class, inversedBy: "role")]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: "id")]
    private ProfileGroupEvent $event;

    /**
     * Префикс роли
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: GroupRolePrefix::TYPE)]
    private GroupRolePrefix $prefix;

    /**
     * Правила роли
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: ProfileVoter::class, cascade: ['all'])]
    private Collection $voter;

    public function __construct(ProfileGroupEvent $event)
    {
        $this->id = new ProfileRoleUid();
        $this->event = $event;
    }

    public function __clone(): void
    {
        //$this->id = new ProfileRoleUid();
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof ProfileRoleInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {

        if($dto instanceof ProfileRoleInterface)
        {
            if(!$dto->isChecked())
            {
                return false;
            }

            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


}