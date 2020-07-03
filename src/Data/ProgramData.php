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

namespace Gustav\HmIPHP\Data;

use DateTime;
use Gustav\Cache\CacheException;
use Gustav\HmIPHP\Connection\ConnectionException;
use Gustav\HmIPHP\Utils\Container;
use Psr\Cache\InvalidArgumentException;
use StdClass;

/**
 * This class represents the data from some program.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class ProgramData extends AData
{
    /**
     * The identifier of this program.
     *
     * @var int
     */
    private int $_id;

    /**
     * Constructor of this class.
     *
     * @param Container $container
     *   The container
     * @param int $id
     *   The identifier
     */
    public function __construct(Container $container, int $id)
    {
        parent::__construct($container);
        $this->_id = $id;
    }

    /**
     * Returns the identifier of this program.
     *
     * @return int
     *   The identifier
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * Returns the name of this program.
     *
     * @return string
     *   The name
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getName(): string
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['name'];
    }

    /**
     * Returns a description of this program.
     *
     * @return string
     *   The description
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDescription(): string
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['description'];
    }

    /**
     * Indicates whether this program is currently activated.
     *
     * @return bool
     *   true if activated, false otherwise
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function isActive(): bool
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['active'];
    }

    /**
     * Indicates whether this program is visible on the CCU.
     *
     * @return bool
     *   true if visible, false otherwise
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function isVisible(): bool
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['visible'];
    }

    /**
     * Returns the time of this last execution of this program.
     *
     * @return DateTime
     *   The time of last execution
     * @throws ConnectionException
     *   Some HTTP error code occurred
     */
    public function getLastUpdate(): DateTime
    {
        $result = $this->_container->getConnection()->getData(["program", $this->_id, "~pv"]);
        $date = new DateTime();
        $date->setTimestamp($result->ts);
        return $date;
    }

    /**
     * Executes this program.
     *
     * @return bool
     *   true if execution was successful, false otherwise
     * @throws ConnectionException
     *   Some HTTP error code occurred
     */
    public function execute(): bool
    {
        $obj = new StdClass();
        $obj->v = true;

        return $this->_container->getConnection()->setData($obj, ["program", $this->_id, "~pv"]);
    }
}