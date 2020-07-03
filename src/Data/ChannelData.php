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

use Gustav\Cache\CacheException;
use Gustav\HmIPHP\Connection\ConnectionException;
use Gustav\HmIPHP\HmIpException;
use Gustav\HmIPHP\Utils\Container;
use Psr\Cache\InvalidArgumentException;

/**
 * This class represents the data of some device channel.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class ChannelData extends AData
{
    /**
     * The identifier of this channel.
     *
     * @var string
     */
    private string $_id;

    /**
     * The device containing this channel.
     *
     * @var DeviceData
     */
    private DeviceData $_device;

    /**
     * Constructor of this class.
     *
     * @param Container $container
     *   The container
     * @param string $id
     *   The identifier
     * @param DeviceData $device
     *   The containing device
     */
    public function __construct(Container $container, string $id, DeviceData $device)
    {
        parent::__construct($container);
        $this->_id = $id;
        $this->_device = $device;
    }

    /**
     * Returns the identifier of this channel.
     *
     * @return string
     *   The identifier
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * Returns the device containing this channel.
     *
     * @return DeviceData
     *   The containing device
     */
    public function getDevice(): DeviceData
    {
        return $this->_device;
    }

    /**
     * Returns the name of this channel.
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
        return $this->_container->getMapping()->getChannelData($this->_id)['name'];
    }

    /**
     * Returns a list of all associated rooms of this channel.
     *
     * @return RoomData[]
     *   The rooms
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRooms(): iterable
    {
        yield from $this->_container->getMapping()->getRoomsOfChannel($this->_id);
    }

    /**
     * Returns a list of all associated functions of this channel.
     *
     * @return FunctionData[]
     *   The functions
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunctions(): iterable
    {
        yield from $this->_container->getMapping()->getFunctionsOfChannel($this->_id);
    }

    /**
     * Returns a list of all parameters of this channel.
     *
     * @return ParameterData[]
     *   The parameters
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown parameter name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getParameters(): iterable
    {
        yield from $this->_container->getMapping()->getParameters($this->_id);
    }

    /**
     * Returns the parameter of this channel with the given name.
     *
     * @param string $name
     *   The parameter's name
     * @return ParameterData
     *   The parameter
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown parameter name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getParameter(string $name): ParameterData
    {
        return $this->_container->getMapping()->getParameter($this->_id . "/" . $name);
    }
}