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

namespace Gustav\HmIPHP\Connection;

use Exception;
use Gustav\HmIPHP\HmIpException;

/**
 * This class represents exceptions when connecting to the Homematic CCU.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class ConnectionException extends HmIpException
{
    /**
     * The error codes.
     *
     * @var int
     */
    public const ERROR_CODE = 1;
    public const REQUEST_ERROR = 2;

    /**
     * Creates an exception when the connection to the Homematic CCU returns a page with an HTTP code different from
     * 200.
     *
     * @param int $code
     *   The error code
     * @param string $url
     *   The called url
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function errorCode(int $code, string $url, ?Exception $exception = null): self
    {
        return new self(
            "error with no. {$code} occurred on call of \"{$url}\"",
            self::ERROR_CODE,
            $exception
        );
    }

    /**
     * Creates an exception when some error occurred on HTTP request.
     *
     * @param string $url
     *   The called url
     * @param Exception|null $exception
     *   Previous exception
     * @return self
     *   The exception
     */
    public static function requestError(string $url, ?Exception $exception = null): self
    {
        return new self("error on call of \"{$url}\"", self::REQUEST_ERROR, $exception);
    }
}