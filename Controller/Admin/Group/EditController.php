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

namespace BaksDev\Users\Profile\Group\Controller\Admin\Group;


use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupForm;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_PROFILE_GROUP_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/profile/group/edit/{id}', name: 'admin.group.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] ProfileGroupEvent $ProfileGroupEvent,
        ProfileGroupHandler $ProfileGroupHandler,
    ): Response
    {
        $ProfileGroupDTO = new ProfileGroupDTO($this->getProfileUid());
        $ProfileGroupEvent->getDto($ProfileGroupDTO);

        // Форма
        $form = $this
            ->createForm(
                type: ProfileGroupForm::class,
                data: $ProfileGroupDTO,
                options: ['action' => $this->generateUrl('users-profile-group:admin.group.newedit.edit', ['id' => $ProfileGroupDTO->getEvent()]),]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('profile_group'))
        {
            $this->refreshTokenForm($form);

            $ProfileGroup = $ProfileGroupHandler->handle($ProfileGroupDTO);

            if($ProfileGroup instanceof ProfileGroup)
            {
                $this->addFlash(
                    'admin.page.edit',
                    'admin.success.edit',
                    'admin.profile.group'
                );

                return $this->redirectToRoute('users-profile-group:admin.group.index');
            }

            $this->addFlash(
                'admin.page.edit',
                'admin.danger.edit',
                'admin.profile.group',
                $ProfileGroup);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
