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

namespace BaksDev\Users\Profile\Group\Repository\ProfileGroupCurrentEvent;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Event\ProfileGroupEvent;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;

final readonly class ProfileGroupCurrentEventRepository implements ProfileGroupCurrentEventInterface
{
    public function __construct(private ORMQueryBuilder $ORMQueryBuilder) {}

    /**
     * Получает активное событие группы
     */
    public function findProfileGroupEvent(GroupPrefix $prefix): ?ProfileGroupEvent
    {
        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm->select('event');

        $orm
            ->from(ProfileGroup::class, 'profile_group')
            ->where('profile_group.prefix = :prefix')
            ->setParameter(
                key: 'prefix',
                value: $prefix,
                type: GroupPrefix::TYPE
            );

        $orm->join(
            ProfileGroupEvent::class,
            'event',
            'WITH',
            'event.id = profile_group.event'
        );

        return $orm->getOneOrNullResult();
    }
}