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


use BaksDev\Users\Profile\Group\Repository\ProfileGroupsChoice\ProfileGroupsChoiceInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProfileGroupUsersForm extends AbstractType
{

    private AuthorizationCheckerInterface $authorizationChecker;
    private TokenStorageInterface $tokenStorage;
    private ProfileGroupsChoiceInterface $profileGroupsChoice;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        ProfileGroupsChoiceInterface $profileGroupsChoice,
    )
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->profileGroupsChoice = $profileGroupsChoice;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {




        /** @var User $usr */
        $usr = $this->tokenStorage->getToken()?->getUser();

        if(!$usr)
        {
            throw new InvalidArgumentException('User not found');
        }

        $profile = $usr->getProfile();

        if(!$profile)
        {
            throw new InvalidArgumentException('Profile not found');
        }


        $builder
            ->add('prefix', ChoiceType::class, [
                'choices' => $this->profileGroupsChoice->findProfileGroupsChoiceByProfile($profile),
                'choice_value' => function (?GroupPrefix $prefix) {
                    return $prefix?->getValue();
                },
                'choice_label' => function (GroupPrefix $prefix) {
                    return $prefix->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'placeholder' => 'Select options ...',
            ]);

        //$builder->add('prefix', TextType::class);

        $builder->add('profile', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {

            /** @var ProfileGroupUsersDTO $data */
            $data = $event->getData();

            if($data->getProfile())
            {
                $builder = $event->getForm();
                $builder->add('profile', HiddenType::class, ['disabled' => true]);
            }

        });




        $builder->get('profile')->addModelTransformer(
            new CallbackTransformer(
                function ($profile) {
                    return (string) $profile;
                },
                function ($profile) {
                    return new UserProfileUid($profile);
                }
            )
        );



        /* Сохранить ******************************************************/
        $builder->add(
            'profile_group_users',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProfileGroupUsersDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}