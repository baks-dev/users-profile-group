<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\Delete\Tests;

use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Repository\ProfileGroupCurrentEvent\ProfileGroupCurrentEventInterface;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\Delete\ProfileGroupDeleteDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\Delete\ProfileGroupDeleteHandler;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Role\ProfileRoleDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Trans\ProfileGroupTranslateDTO;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-group
 *
 * @depends BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Tests\ProfileGroupEditTest::class
 *
 * @see     ProfileGroupEditTest
 */
#[When(env: 'test')]
final class ProfileGroupDeleteTest extends KernelTestCase
{

    public function testUseCase(): void
    {
        self::bootKernel();
        $container = self::getContainer();


        /** @var ProfileGroupCurrentEventInterface $ProfileGroupCurrentEvent */
        $ProfileGroupCurrentEvent = $container->get(ProfileGroupCurrentEventInterface::class);
        $ProfileGroupEvent = $ProfileGroupCurrentEvent->findProfileGroupEvent(new GroupPrefix());


        /** @var ProfileGroupDTO $ProfileGroupDTO */
        $ProfileGroupDTO = new ProfileGroupDTO(new UserProfileUid());
        $ProfileGroupEvent->getDto($ProfileGroupDTO);
        self::assertNotEquals(ProfileGroupEventUid::TEST, (string) $ProfileGroupDTO->getEvent());


        /** @var ProfileRoleDTO $ProfileRoleDTO */
        self::assertCount(0, $ProfileGroupDTO->getRole());

        $ProfileRoleDTO = $ProfileGroupDTO->getRole()->current();
        self::assertFalse($ProfileRoleDTO);


        /** @var ProfileGroupTranslateDTO $ProfileGroupTranslateDTO */

        $ProfileGroupTranslate = $ProfileGroupDTO->getTranslate();
        foreach($ProfileGroupTranslate as $ProfileGroupTranslateDTO)
        {
            self::assertEquals('okwdtYvwom', $ProfileGroupTranslateDTO->getName());
        }


        $ProfileGroupDeleteDTO = new ProfileGroupDeleteDTO(new UserProfileUid());
        $ProfileGroupEvent->getDto($ProfileGroupDeleteDTO);

        /** DELETE */

        /** @var ProfileGroupDeleteHandler $ProfileGroupDeleteHandler */
        $ProfileGroupDeleteHandler = self::getContainer()->get(ProfileGroupDeleteHandler::class);
        $handle = $ProfileGroupDeleteHandler->handle($ProfileGroupDeleteDTO);

        self::assertTrue(($handle instanceof ProfileGroup), $handle.': Ошибка ProfileGroup');

    }

    /**
     * @depends testUseCase
     */
    public function testComplete(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);


        $ProfileGroup = $em->getRepository(ProfileGroup::class)
            ->find(GroupPrefix::TEST);

        if($ProfileGroup)
        {
            $em->remove($ProfileGroup);
        }

        $WbProductSettingsEventCollection = $em->getRepository(ProfileGroupEvent::class)
            ->findBy(['prefix' => GroupPrefix::TEST]);

        foreach($WbProductSettingsEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        self::assertNull($ProfileGroup);

        $em->clear();
        //$em->close();

    }
}