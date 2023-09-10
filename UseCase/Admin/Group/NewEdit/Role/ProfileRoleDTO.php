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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Role;

use BaksDev\Users\Profile\Group\Entity\Role\ProfileRoleInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see ProfileRole */
final class ProfileRoleDTO implements ProfileRoleInterface
{
    /**
     * Префикс роли
     */
    #[Assert\NotBlank]
    private GroupRolePrefix $prefix;


    private bool $checked = true;

    /**
     * Правила роли
     */
    #[Assert\Valid]
    private ArrayCollection $voter;

    public function __construct()
    {
        $this->voter = new ArrayCollection();
    }

    /**
     * Prefix
     */
    public function getPrefix(): ?GroupRolePrefix
    {
        return $this->prefix;
    }

    /**
     * Prefix
     */
    public function getRolePrefix(): ?GroupRolePrefix
    {
        return $this->prefix;
    }


    public function setPrefix(GroupRolePrefix $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Voter
     */
    public function getVoter(): ArrayCollection
    {
        return $this->voter;
    }

    public function setVoter(ArrayCollection $voter): self
    {
        $this->voter = $voter;
        return $this;
    }


    public function addVoter(Voter\ProfileVoterDTO $voter): void
    {
        $filter = $this->voter->filter(function(Voter\ProfileVoterDTO $element) use ($voter) {
            return $voter->getPrefix()?->equals($element->getPrefix());
        });

        if($filter->isEmpty())
        {
            $this->voter->add($voter);
        }
    }

    public function removeVoter(Voter\ProfileVoterDTO $voter): void
    {
        $filter = $this->voter->filter(function(Voter\ProfileVoterDTO $element) use ($voter) {
            return $voter->getPrefix()?->equals($element->getPrefix());
        });

        if(!$filter->isEmpty())
        {
            $this->voter->removeElement($filter->current());
        }
    }


    /**
     * Checked
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;
        return $this;
    }

}