<?php
/**
 * Gustav HmIPHP - An interface for communication with a Homematic CCU
 * Copyright (C) since 2020  Gustav Software
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Gustav\HmIPHP;

use Gustav\Utils\GustavException;

class HmIpException extends GustavException
{
    public const INVALID_CALL = 1;
    public const INVALID_ROOM = 2;
    public const INVALID_DEVICE = 3;
    public const INVALID_CHANNEL = 4;
    public const INVALID_PARAMETER = 5;
    public const INVALID_FUNCTION = 6;
    public const INVALID_PROGRAM = 7;
    public const INVALID_VARIABLE = 8;

    public static function invalidCall(string $func, ?\Exception $exception = null): self
    {
        return new self("invalid call of {$func}", self::INVALID_CALL, $exception);
    }

    public static function invalidRoom(string $room, ?\Exception $exception = null): self
    {
        return new self("invalid room \"{$room}\"", self::INVALID_ROOM, $exception);
    }

    public static function invalidDevice(string $device, ?\Exception $exception = null): self
    {
        return new self("invalid device \"{$device}\"", self::INVALID_DEVICE, $exception);
    }

    public static function invalidChannel(string $channel, ?\Exception $exception = null): self
    {
        return new self("invalid channel \"{$channel}\"", self::INVALID_CHANNEL, $exception);
    }

    public static function invalidParameter(string $parameter, ?\Exception $exception = null): self
    {
        return new self("invalid parameter \"{$parameter}\"", self::INVALID_PARAMETER, $exception);
    }

    public static function invalidFunction(string $function, ?\Exception $exception = null): self
    {
        return new self("invalid function \"{$function}\"", self::INVALID_FUNCTION, $exception);
    }

    public static function invalidProgram(string $program, ?\Exception $exception = null): self
    {
        return new self("invalid program \"{$program}\"", self::INVALID_PROGRAM, $exception);
    }

    public static function invalidVariable(string $variable, ?\Exception $exception = null): self
    {
        return new self("invalid variable \"{$variable}\"", self::INVALID_VARIABLE, $exception);
    }
    //TODO!
}