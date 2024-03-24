<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\Group\Type\Prefix\Voter;

use App\Kernel;
use InvalidArgumentException;
use function mb_strtoupper;

final class RoleVoterPrefix
{
	public const TYPE = 'prefix_voter';

	public const TEST = 'ROLE_VOTER_TEST';

	private $value;

	public function __construct(?string $value = null)
	{
        if (empty($value) && Kernel::isTestEnvironment())
        {
            $this->value = mb_strtoupper(self::TEST);
            return;
        }

        if(empty($value))
		{
			throw new InvalidArgumentException('You need to pass a value Voter Prefix');
		}
		
		if(!preg_match('/ROLE_(\w{1,10})/', $value))
		{
			throw new InvalidArgumentException('Incorrect Voter Prefix.');
		}
		
		$this->value = mb_strtoupper($value);
	}
	
	
	public function __toString(): string
	{
		return $this->value;
	}
	
	
	public function getValue(): string
	{
		return $this->value;
	}

    public function equals(string|self $prefix): bool
    {
        return $this->value === (string) $prefix;
    }
}