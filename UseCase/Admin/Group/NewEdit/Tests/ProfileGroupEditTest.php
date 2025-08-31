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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Tests;

use BaksDev\Support\Answer\UseCase\Admin\NewEdit\Tests\SupportAnswerNewTest;
use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\ProfileGroupHandler;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Role\ProfileRoleDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Role\Voter\ProfileVoterDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Trans\ProfileGroupTranslateDTO;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-group')]
#[When(env: 'test')]
final class ProfileGroupEditTest extends KernelTestCase
{
    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testUseCase(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $ProfileGroupEvent = $em->getRepository(ProfileGroupEvent::class)->find(ProfileGroupEventUid::TEST);
        self::assertNotNull($ProfileGroupEvent);

        /** @var ProfileGroupDTO $ProfileGroupDTO */
        $ProfileGroupDTO = new ProfileGroupDTO(new UserProfileUid());
        $ProfileGroupEvent->getDto($ProfileGroupDTO);
        self::assertEquals(ProfileGroupEventUid::TEST, (string) $ProfileGroupDTO->getEvent());


        /** @var ProfileRoleDTO $ProfileRoleDTO */
        self::assertCount(1, $ProfileGroupDTO->getRole());

        $ProfileRoleDTO = $ProfileGroupDTO->getRole()->current();
        self::assertEquals(GroupRolePrefix::TEST, (string) $ProfileRoleDTO->getPrefix());

        self::assertTrue($ProfileRoleDTO->isChecked());
        $ProfileRoleDTO->setChecked(false);


        /** @var ProfileVoterDTO $ProfileVoterDTO */

        self::assertCount(1, $ProfileRoleDTO->getVoter());

        $ProfileVoterDTO = $ProfileRoleDTO->getVoter()->current();
        self::assertEquals(RoleVoterPrefix::TEST, (string) $ProfileVoterDTO->getPrefix());

        self::assertTrue($ProfileVoterDTO->isChecked());
        $ProfileVoterDTO->setChecked(false);


        /** @var ProfileGroupTranslateDTO $ProfileGroupTranslateDTO */

        $ProfileGroupTranslate = $ProfileGroupDTO->getTranslate();
        foreach($ProfileGroupTranslate as $ProfileGroupTranslateDTO)
        {
            self::assertEquals('QVzVblOllN', $ProfileGroupTranslateDTO->getName());
            $ProfileGroupTranslateDTO->setName('okwdtYvwom');
        }

        /** UPDATE */

        self::bootKernel();

        /** @var ProfileGroupHandler $ProfileGroupHandler */
        $ProfileGroupHandler = self::getContainer()->get(ProfileGroupHandler::class);
        $handle = $ProfileGroupHandler->handle($ProfileGroupDTO);

        self::assertTrue(($handle instanceof ProfileGroup), $handle.': Ошибка ProfileGroup');

        $em->clear();
        //$em->close();

    }

    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testComplete(): void
    {
        //self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $ProfileGroupEvent = $em->getRepository(ProfileGroupEvent::class)->findBy(['prefix' => GroupPrefix::TEST]);
        self::assertNotNull($ProfileGroupEvent);
        self::assertCount(2, $ProfileGroupEvent);

        $em->clear();
        //$em->close();
    }
}