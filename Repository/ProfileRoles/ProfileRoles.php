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

namespace BaksDev\Users\Profile\Group\Repository\ProfileRoles;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Repository\ExistProfileGroup\ExistProfileGroupInterface;
use BaksDev\Users\Profile\Group\Type\Prefix\Group\GroupPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

final class ProfileRoles implements ProfileRolesInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;
    private ExistProfileGroupInterface $existProfileGroup;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        ExistProfileGroupInterface $existProfileGroup,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->existProfileGroup = $existProfileGroup;
    }

    public function fetchAllRoleUser(UserProfileUid $profile) : ?array
    {
        //$session = $this->requestStack->getSession();
        //$authority = $session->get($usr->getProfile(), null);

        /** Тумблер профилей пользователя */
        $ApcuAdapter = new ApcuAdapter('Authority');
        $authority = $ApcuAdapter->getItem((string) $profile)->get();


        /** Проверяем, имеется ли у пользователя группа либо доверенность */
        $existGroup = $this->existProfileGroup->isExistsProfileGroup($profile);

        if($existGroup)
        {
            /** Получаем префикс группы  */
            $group = $this->profileGroupByUserProfile->findProfileGroupByUserProfile($profile, $authority);
            //$ApcuAdapter->delete((string) $profile);

            if($group)
            {
                if($group->equals('ROLE_ADMIN'))
                {
                    $roles = null;
                    $roles[] = 'ROLE_ADMINISTRATION';
                    $roles[] = 'ROLE_ADMIN';
                    $roles[] = 'ROLE_USER';

                }
                else
                {


                    /** Получаем список ролей и правил группы */


                    $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

                    $qb->select("
                       ARRAY(SELECT DISTINCT UNNEST(
                            ARRAY_AGG(profile_role.prefix) || 
                            ARRAY_AGG(profile_voter.prefix)
                        )) AS roles
                    ");

                    $qb->from(ProfileGroup::TABLE, 'profile_group');

                    //                    $qb->join(
                    //                        'profile_group',
                    //                        ProfileGroupEvent::TABLE,
                    //                        'profile_group_event',
                    //                        'profile_group_event.id = profile_group.event'
                    //                    );


                    $qb->leftJoin(
                        'profile_group',
                        ProfileRole::TABLE,
                        'profile_role',
                        'profile_role.event = profile_group.event'
                    );

                    $qb->leftJoin(
                        'profile_role',
                        ProfileVoter::TABLE,
                        'profile_voter',
                        'profile_voter.role = profile_role.id'
                    );

                    $qb->andWhere('profile_group.prefix = :prefix')
                        ->setParameter('prefix', $group, GroupPrefix::TYPE)
                    ;

                    $qb->andWhere('profile_group.profile = :authority')
                        ->setParameter('authority', $authority, UserProfileUid::TYPE)
                    ;

                    $roles = $qb->enableCache('UserGroup', 86400)->fetchOne();

                    if($roles)
                    {
                        $roles = trim($roles, "{}");

                        if(empty($roles))
                        {
                            return null;
                        }

                        $roles = explode(",", $roles);
                        $roles[] = 'ROLE_USER';

                        if($roles)
                        {
                            $roles[] = 'ROLE_ADMINISTRATION';
                        }
                    }
                }
            }
            else
            {
                $roles = null;
                $roles[] = 'ROLE_ADMINISTRATION';
                $roles[] = 'ROLE_USER';

                $authority && $ApcuAdapter->delete((string) $profile);
            }

        }
        else
        {
            $roles = null;
            $roles[] = 'ROLE_USER';
        }

        //        $roles = null;
        //        $roles[] = 'ROLE_ADMIN';

        //dump($roles);

        return $roles;
    }
}