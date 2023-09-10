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

namespace BaksDev\Users\Profile\Group\Command\Upgrade;

use BaksDev\Core\Command\Update\ProjectUpgradeInterface;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Repository\ExistAdminProfile\ExistAdminProfileInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group\ProfileGroupUsersDTO;
use BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group\ProfileGroupUsersHandler;
use BaksDev\Users\Profile\UserProfile\Repository\AdminUserProfile\AdminUserProfileInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AsCommand(
    name: 'baks:users-profile-group:admin',
    description: 'Добавляет в группу "Администратор" профиль администратора ресурса',
)]
#[AutoconfigureTag('baks.project.upgrade')]
class UpgradeUserProfileAdminGroupCommand extends Command implements ProjectUpgradeInterface
{

    private ExistAdminProfileInterface $existAdminProfile;
    private AdminUserProfileInterface $adminUserProfile;
    private ProfileGroupUsersHandler $profileGroupUsersHandler;

    public function __construct(
        ExistAdminProfileInterface $existAdminProfile,
        AdminUserProfileInterface $adminUserProfile,
        ProfileGroupUsersHandler $profileGroupUsersHandler,
    )
    {
        parent::__construct();

        $this->existAdminProfile = $existAdminProfile;
        $this->adminUserProfile = $adminUserProfile;
        $this->profileGroupUsersHandler = $profileGroupUsersHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** Проверяем, была ли добавлена группа профилю */
        $exists = $this->existAdminProfile->isExistsAdminProfile();

        if(!$exists)
        {
            $io = new SymfonyStyle($input, $output);
            $io->text('Обновляем группу профиля администратора ресурса');

            /** Получаем профиль пользователя администратора ресурса */
            $UserProfileUid = $this->adminUserProfile->fetchUserProfile();

            if(!$UserProfileUid)
            {
                $io->warning('Профиль администратора не найден');
                return Command::INVALID;
            }

            $ProfileGroupUsersDTO = new ProfileGroupUsersDTO();
            $ProfileGroupUsersDTO
                ->setProfile($UserProfileUid)
                ->setPrefix(new  GroupPrefix('ROLE_ADMIN'));

            $handle = $this->profileGroupUsersHandler->handle($ProfileGroupUsersDTO);

            if(!$handle instanceof ProfileGroupUsers)
            {
                $io->success(
                    sprintf('Ошибка %s при добавлении профиля в группу администратора ресурса', $handle)
                );

                return Command::FAILURE;
            }

        }

        return Command::SUCCESS;
    }

    /** Чам выше число - тем первым в итерации будет значение */
    public static function priority(): int
    {
        return 10;
    }
}
