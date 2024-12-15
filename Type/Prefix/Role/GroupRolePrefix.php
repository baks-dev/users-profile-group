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

namespace BaksDev\Users\Profile\Group\Type\Prefix\Role;

use App\Kernel;
use InvalidArgumentException;

final class GroupRolePrefix
{
    public const string TYPE = 'prefix_role';

    public const string TEST = 'ROLE_PREFIX_TEST';

    private $value;

    public function __construct(?string $value = null)
    {
        if(empty($value) && Kernel::isTestEnvironment())
        {
            $this->value = mb_strtoupper(self::TEST);
            return;
        }

        if(empty($value))
        {
            throw new InvalidArgumentException('You need to pass a value Role Prefix');
        }

        if(!preg_match('/ROLE_(\w{1,10})/', $value))
        {
            throw new InvalidArgumentException('Incorrect Role Prefix.');
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