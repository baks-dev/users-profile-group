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

namespace BaksDev\Users\Profile\Group\Controller\Admin\Users;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group\ProfileGroupUsersDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group\ProfileGroupUsersForm;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group\ProfileGroupUsersHandler;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_PROFILE_GROUP_USERS_ADD')]
final class AddController extends AbstractController
{
    /**
     * Добавить (изменить) группу пользователя
     */
    #[Route('/admin/profile/group/user/{profile}', name: 'admin.users.add', methods: ['GET', 'POST'])]
    public function news(
        Request $request,
        ProfileGroupUsersHandler $ProfileGroupUsersHandler,
        EntityManagerInterface $entityManager,
        #[ParamConverter(UserProfileUid::class)] $profile = null,
    ): Response
    {

        $isAdminProfile = $this->isGranted('ROLE_ADMIN') ? null : $this->getProfileUid();

        $ProfileGroupUsersDTO = new ProfileGroupUsersDTO();

        if($profile)
        {
            $ProfileGroupUsers = $entityManager->getRepository(ProfileGroupUsers::class)->findOneBy(['profile' => $profile, 'authority' => $isAdminProfile]);
            $ProfileGroupUsers ? $ProfileGroupUsers->getDto($ProfileGroupUsersDTO) : null;
        }


        // Форма
        $form = $this->createForm(ProfileGroupUsersForm::class, $ProfileGroupUsersDTO, [
            'action' => $this->generateUrl('users-profile-group:admin.users.add'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('profile_group_users'))
        {
            $this->refreshTokenForm($form);

            if($isAdminProfile)
            {
                $ProfileGroupUsersDTO->setAuthority($isAdminProfile);
            }

            $ProfileGroupUsers = $ProfileGroupUsersHandler->handle($ProfileGroupUsersDTO);

            if($ProfileGroupUsers instanceof ProfileGroupUsers)
            {
                $this->addFlash(
                    'admin.page.users',
                    'admin.success.new',
                    'admin.profile.group'
                );

                return $this->redirectToRoute('users-profile-group:admin.users.index');
            }

            $this->addFlash(
                'admin.page.users',
                'admin.danger.new',
                'admin.profile.group',
                $ProfileGroupUsers
            );

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}