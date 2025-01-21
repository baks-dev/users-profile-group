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

namespace BaksDev\Users\Profile\Group\UseCase\Admin\Users\Group;


use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Messenger\ProfileGroupMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ProfileGroupUsersHandler
{
    public function __construct(
        #[Target('usersProfileGroupLogger')] private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private MessageDispatchInterface $messageDispatch
    ) {}

    /** @see ProfileGroupUsers */
    public function handle(ProfileGroupUsersDTO $command): string|ProfileGroupUsers
    {
        /**
         *  Валидация ProfileGroupUsersDTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }

        $ProfileGroupUsers = $this->entityManager
            ->getRepository(ProfileGroupUsers::class)
            ->findOneBy([
                //'prefix' => $command->getPrefix(),
                'profile' => $command->getProfile(),
                'authority' => $command->getAuthority()
            ]);

        if(!$ProfileGroupUsers)
        {
            $ProfileGroupUsers = new ProfileGroupUsers();
        }

        $this->entityManager->clear();

        $ProfileGroupUsers->setEntity($command);
        $this->entityManager->persist($ProfileGroupUsers);

        /**
         * Валидация ProfileGroupUsers
         */
        $errors = $this->validator->validate($ProfileGroupUsers);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

            return $uniqid;
        }

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch
            ->addClearCacheOther('profile-group-users')
            ->dispatch(
                message: new ProfileGroupMessage($ProfileGroupUsers->getPrefix()),
                transport: 'users-profile-group'
            );

        return $ProfileGroupUsers;
    }
}