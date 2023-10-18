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


use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DateInterval;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[AsController]
#[RoleSecurity('ROLE_ADMINISTRATION')]
final class AuthorityController extends AbstractController
{
    #[Route('/admin/profile/group/user/authority/{profile}', name: 'admin.authority', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        RouterInterface $router,
        AppCacheInterface $cache,
        #[ParamConverter(UserProfileUid::class)] $profile,
    ): Response
    {

        if(!$this->getProfileUid())
        {
            return $this->redirectToReferer();
        }

        /** Сохраняем идентификатор профиля */
        $AppCache = $cache->init('Authority', 0);
        $authority = $AppCache->getItem((string) $this->getProfileUid());
        $authority->set($profile);
        $authority->expiresAfter(DateInterval::createFromDateString('1 weeks'));
        $AppCache->save($authority);

        if($request->headers->get('referer'))
        {
            /** Получаем информацию о контроллере */
            $path = parse_url($request->headers->get('referer'), PHP_URL_PATH);
            $routeInfo = $router->match($path);

            /** @var \Symfony\Component\Routing\Route $controller */
            foreach($router->getRouteCollection()->all() as $controller)
            {
                if($controller->getDefault('_canonical_route') === $routeInfo['_route'])
                {
                    $roles = $this->getUsr()?->getRoles();

                    foreach($roles as $role)
                    {
                        // Проверяем, имеется ли доступ к контроллеру
                        $access = $this->isGranted($role, $controller);
                        if($access)
                        {
                            return $this->redirectToReferer();
                        }
                    }
                }
            }
        }


        if($this->isGranted('ROLE_ADMINISTRATION'))
        {
            return $this->redirectToRoute('core:admin.homepage');
        }

        return $this->redirectToRoute('Pages:user.homepage');
    }
}
