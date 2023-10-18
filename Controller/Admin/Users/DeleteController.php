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
use BaksDev\Users\Profile\Group\Repository\ProfileGroupCurrentEvent\ProfileGroupCurrentEventInterface;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Delete\ProfileGroupUsersDeleteDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Delete\ProfileGroupUsersDeleteForm;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Delete\ProfileGroupUsersDeleteHandler;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[AsController]
#[RoleSecurity('ROLE_PROFILE_GROUP_USERS_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/profile/group/users/delete/{profile}', name: 'admin.users.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[ParamConverter(UserProfileUid::class)] $profile,
        ProfileGroupUsersDeleteHandler $ProfileGroupUsersDeleteHandler,
        EntityManagerInterface $entityManager,
    ): Response
    {

        $findOneBy['profile'] = $profile;

        if($this->getAdminFilterProfile())
        {
            /* Если пользователь не админ - только собственные группы */
            $findOneBy['authority'] = $this->getProfileUid();
        }

        $ProfileGroupUsers = $entityManager
            ->getRepository(ProfileGroupUsers::class)
            ->findOneBy($findOneBy);

        if(!$ProfileGroupUsers)
        {
            throw new RouteNotFoundException('Page not found');
        }

        $ProfileGroupUsersDeleteDTO = new ProfileGroupUsersDeleteDTO();
        $ProfileGroupUsers?->getDto($ProfileGroupUsersDeleteDTO);

        $form = $this->createForm(ProfileGroupUsersDeleteForm::class, $ProfileGroupUsersDeleteDTO, [
            'action' => $this->generateUrl('users-profile-group:admin.users.delete',
                ['profile' => $profile]
            )]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('profile_group_users_delete'))
        {
            $ProfileGroupUsers = $ProfileGroupUsersDeleteHandler->handle($ProfileGroupUsersDeleteDTO, !$this->getAdminFilterProfile());

            if($ProfileGroupUsers instanceof ProfileGroupUsers)
            {
                $this->addFlash('admin.page.users', 'admin.success.delete', 'admin.profile.group');
                return $this->redirectToRoute('users-profile-group:admin.users.index');
            }

            $this->addFlash(
                'admin.page.users',
                'admin.danger.delete',
                'admin.profile.group',
                $ProfileGroupUsers
            );

            return $this->redirectToRoute('users-profile-group:admin.users.index', status: 400);
        }

        return $this->render(['form' => $form->createView()]);
    }
}
