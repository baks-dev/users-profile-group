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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Users\Delete;


use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Messenger\ProfileGroupMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ProfileGroupUsersDeleteHandler
{
    public function __construct(
        #[Target('usersProfileGroupLogger')] private LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        MessageDispatchInterface $messageDispatch
    ) {}

    /** @see ProfileGroupUsers */
    public function handle(ProfileGroupUsersDeleteDTO $command, bool $isAdmin): string|ProfileGroupUsers
    {
        /**
         *  Валидация ProfileGroupUsersDeleteDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }


        $findOneBy['prefix'] = $command->getPrefix();
        $findOneBy['profile'] = $command->getProfile();

        if($isAdmin === false)
        {
            $findOneBy['authority'] = $command->getAuthority();
        }

        $ProfileGroupUsers = $this->entityManager
            ->getRepository(ProfileGroupUsers::class)
            ->findOneBy($findOneBy);


        if(!$ProfileGroupUsers)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by prefix: %s',
                ProfileGroupUsers::class,
                $command->getPrefix()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        $this->entityManager->remove($ProfileGroupUsers);
        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this
            ->messageDispatch
            ->addClearCacheOther('users-profile-group')
            ->dispatch(
                message: new ProfileGroupMessage($ProfileGroupUsers->getPrefix()),
                transport: 'profile-group-users'
            );

        // 'profile-group-users_high'
        return $ProfileGroupUsers;
    }
}