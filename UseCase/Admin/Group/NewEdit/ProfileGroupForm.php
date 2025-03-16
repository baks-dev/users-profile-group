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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit;


use BaksDev\Users\Profile\Group\Security\RoleInterface;
use BaksDev\Users\Profile\Group\Security\VoterInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProfileGroupForm extends AbstractType
{
    public function __construct(
        #[AutowireIterator('baks.security.role', defaultPriorityMethod: 'getSortMenu')] private readonly iterable $roles,
        #[AutowireIterator('baks.security.voter')] private readonly iterable $voters,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) {

                /** @var ProfileGroupDTO $ProfileGroupDTO */

                $ProfileGroupDTO = $event->getData();


                /** @var RoleInterface $role */
                foreach($this->roles as $role)
                {
                    $ProfileRoleDTO = new Role\ProfileRoleDTO();
                    $ProfileRoleDTO->setChecked(false);
                    $ProfileRoleDTO->setPrefix(new GroupRolePrefix($role->getRole()));

                    if($this->authorization->isGranted('ROLE_ADMIN') || $this->authorization->isGranted($role->getRole()))
                    {
                        $ProfileRoleDTO = $ProfileGroupDTO->addRole($ProfileRoleDTO);

                        /** @var VoterInterface $voter */
                        foreach($this->voters as $voter)
                        {
                            if(!$voter->equals($role))
                            {
                                continue;
                            }

                            $ProfileVoterDTO = new Role\Voter\ProfileVoterDTO();
                            $ProfileVoterDTO->setChecked(false);
                            $ProfileVoterDTO->setPrefix(new RoleVoterPrefix($voter::getVoter()));

                            if($this->authorization->isGranted('ROLE_ADMIN') || $this->authorization->isGranted($voter::getVoter()))
                            {

                                $ProfileRoleDTO->addVoter($ProfileVoterDTO);
                            }
                            else
                            {
                                $ProfileRoleDTO->removeVoter($ProfileVoterDTO);
                            }
                        }
                    }
                    else
                    {
                        $ProfileGroupDTO->removeRole($ProfileRoleDTO);
                    }
                }
            }
        );


        $builder->add('translate', CollectionType::class, [
            'entry_type' => Trans\ProfileGroupTranslateForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__translate__',
        ]);

        $builder->add('role', CollectionType::class, [
            'entry_type' => Role\ProfileRoleForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
        ]);


        /* Сохранить ******************************************************/
        $builder->add(
            'profile_group',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfileGroupDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}