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

use Exception;
use Gustav\Utils\GustavException;

/**
 * This class represents exceptions when loading data from the CCU.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class HmIpException extends GustavException
{
    /**
     * The error codes.
     *
     * @var int
     */
    public const INVALID_ROOM = 1;
    public const INVALID_DEVICE = 2;
    public const INVALID_CHANNEL = 3;
    public const INVALID_PARAMETER = 4;
    public const INVALID_FUNCTION = 5;
    public const INVALID_PROGRAM = 6;
    public const INVALID_VARIABLE = 7;

    /**
     * Creates an exception when the room with the given name does not exist.
     *
     * @param string $room
     *   The room's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidRoom(string $room, ?Exception $exception = null): self
    {
        return new self("invalid room \"{$room}\"", self::INVALID_ROOM, $exception);
    }

    /**
     * Creates an exception when the device with the given name does not exist.
     *
     * @param string $device
     *   The device's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidDevice(string $device, ?Exception $exception = null): self
    {
        return new self("invalid device \"{$device}\"", self::INVALID_DEVICE, $exception);
    }

    /**
     * Creates an exception when the device channel with the given name does not exist.
     *
     * @param string $channel
     *   The channel's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidChannel(string $channel, ?Exception $exception = null): self
    {
        return new self("invalid channel \"{$channel}\"", self::INVALID_CHANNEL, $exception);
    }

    /**
     * Creates an exception when the channel parameter with the given name does not exist.
     *
     * @param string $parameter
     *   The parameter's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidParameter(string $parameter, ?Exception $exception = null): self
    {
        return new self("invalid parameter \"{$parameter}\"", self::INVALID_PARAMETER, $exception);
    }

    /**
     * Creates an exception when the function with the given name does not exist.
     *
     * @param string $function
     *   The function's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidFunction(string $function, ?Exception $exception = null): self
    {
        return new self("invalid function \"{$function}\"", self::INVALID_FUNCTION, $exception);
    }

    /**
     * Creates an exception when the program with the given name does not exist.
     *
     * @param string $program
     *   The program's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidProgram(string $program, ?Exception $exception = null): self
    {
        return new self("invalid program \"{$program}\"", self::INVALID_PROGRAM, $exception);
    }

    /**
     * Creates an exception when the variable with the given name does not exist.
     *
     * @param string $variable
     *   The variable's name
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function invalidVariable(string $variable, ?Exception $exception = null): self
    {
        return new self("invalid variable \"{$variable}\"", self::INVALID_VARIABLE, $exception);
    }
}