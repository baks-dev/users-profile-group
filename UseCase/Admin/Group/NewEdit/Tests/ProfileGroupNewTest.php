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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Tests;

use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-group
 */
#[When(env: 'test')]
final class ProfileGroupNewTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
    {

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $ProfileGroup = $em->getRepository(ProfileGroup::class)
            ->find(GroupPrefix::TEST)
            //->find('ROLE_660083B81EE76')
        ;

        if($ProfileGroup)
        {
            $em->remove($ProfileGroup);
        }

        $WbProductSettingsEventCollection = $em->getRepository(ProfileGroupEvent::class)
            ->findBy(['prefix' => GroupPrefix::TEST])
            //->findBy(['prefix' => 'ROLE_660083B81EE76'])
    ;


        foreach($WbProductSettingsEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();



        $em->clear();
        //$em->close();
    }


    public function testUseCase(): void
    {
        /**
         * WbProductCardDTO
         */

        $UserProfileUid = new UserProfileUid();
        $ProfileGroupDTO = new ProfileGroupDTO($UserProfileUid);
        self::assertSame($UserProfileUid, $ProfileGroupDTO->getProfile());

        /** @var ProfileRoleDTO $ProfileRoleDTO */

        $ProfileRoleDTO = new ProfileRoleDTO();
        $ProfileRoleDTO->setPrefix(new GroupRolePrefix(GroupRolePrefix::TEST));
        self::assertEquals(GroupRolePrefix::TEST, (string) $ProfileRoleDTO->getPrefix());

        $ProfileRoleDTO->setChecked(true);
        self::assertTrue($ProfileRoleDTO->isChecked());

        $ProfileGroupDTO->addRole($ProfileRoleDTO);
        self::assertTrue($ProfileGroupDTO->getRole()->contains($ProfileRoleDTO));

        /** @var ProfileVoterDTO $ProfileVoterDTO */

        $ProfileVoterDTO = new ProfileVoterDTO();
        $ProfileVoterDTO->setPrefix(new RoleVoterPrefix(RoleVoterPrefix::TEST));
        self::assertEquals(RoleVoterPrefix::TEST, (string) $ProfileVoterDTO->getPrefix());

        $ProfileVoterDTO->setChecked(true);
        self::assertTrue($ProfileVoterDTO->isChecked());

        $ProfileRoleDTO->addVoter($ProfileVoterDTO);
        self::assertTrue($ProfileRoleDTO->getVoter()->contains($ProfileVoterDTO));


        /** @var ProfileGroupTranslateDTO $ProfileGroupTranslateDTO */

        $ProfileGroupTranslate = $ProfileGroupDTO->getTranslate();
        foreach($ProfileGroupTranslate as $ProfileGroupTranslateDTO)
        {
            $ProfileGroupTranslateDTO->setName('QVzVblOllN');
            self::assertEquals('QVzVblOllN', $ProfileGroupTranslateDTO->getName());
        }

        /** PERSIST */

        self::bootKernel();

        /** @var ProfileGroupHandler $ProfileGroupHandler */
        $ProfileGroupHandler = self::getContainer()->get(ProfileGroupHandler::class);
        $handle = $ProfileGroupHandler->handle($ProfileGroupDTO);

        self::assertTrue(($handle instanceof ProfileGroup), $handle.': Ошибка ProfileGroup');

    }

}