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

namespace BaksDev\Users\Profile\Group\Entity\Role\Voter;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Перевод ProfileGroupVoter */

#[ORM\Entity]
#[ORM\Table(name: 'profile_group_voter')]
class ProfileVoter extends EntityEvent
{
    public const TABLE = 'profile_group_voter';

    /** Связь на роль */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ProfileRole::class, inversedBy: "voter")]
    #[ORM\JoinColumn(name: 'role', referencedColumnName: "id")]
    private ProfileRole $role;

    /**
     * Префикс правила
     */
    #[ORM\Id]
    #[Assert\NotBlank]
    #[ORM\Column(type: RoleVoterPrefix::TYPE)]
    private RoleVoterPrefix $prefix;

    public function __construct(ProfileRole $role)
    {
        $this->role = $role;
    }

    public function getDto($dto): mixed
    {
        if($dto instanceof ProfileVoterInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {

        if($dto instanceof ProfileVoterInterface)
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