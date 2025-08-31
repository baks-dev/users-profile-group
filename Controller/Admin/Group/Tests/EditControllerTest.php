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

namespace BaksDev\Users\Profile\Group\Controller\Admin\Group\Tests;

use BaksDev\Users\Profile\Group\Type\Event\ProfileGroupEventUid;
use BaksDev\Users\Profile\Group\UseCase\Admin\Group\NewEdit\Tests\ProfileGroupNewTest;
use BaksDev\Users\User\Tests\TestUserAccount;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-group')]
#[When(env: 'test')]
final class EditControllerTest extends WebTestCase
{
    private const string URL = '/admin/profile/group/edit/%s';

    private const string ROLE = 'ROLE_PROFILE_GROUP_EDIT';

    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testRoleSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getModer(self::ROLE);

            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, ProfileGroupEventUid::TEST));

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /**
     * Доступ по роли ROLE_ADMIN
     */
    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testRoleAdminSuccessful(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getAdmin();

            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, ProfileGroupEventUid::TEST));

            self::assertResponseIsSuccessful();
        }

        self::assertTrue(true);
    }

    /**
     * Доступ по роли ROLE_USER
     */
    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testRoleUserDeny(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');
            $client->request('GET', sprintf(self::URL, ProfileGroupEventUid::TEST));

            self::assertResponseStatusCodeSame(403);
        }

        self::assertTrue(true);
    }

    /** Доступ по без роли */
    #[DependsOnClass(ProfileGroupNewTest::class)]
    public function testGuestFiled(): void
    {

        self::ensureKernelShutdown();
        $client = static::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $client->request('GET', sprintf(self::URL, ProfileGroupEventUid::TEST));

            // Full authentication is required to access this resource
            self::assertResponseStatusCodeSame(401);
        }

        self::assertTrue(true);
    }

}
