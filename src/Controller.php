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

use Gustav\Cache\ACacheManager;
use Gustav\Cache\CacheException;
use Gustav\HmIPHP\Connection\Connection;
use Gustav\HmIPHP\Connection\ConnectionException;
use Gustav\HmIPHP\Data\ChannelData;
use Gustav\HmIPHP\Data\DeviceData;
use Gustav\HmIPHP\Data\FunctionData;
use Gustav\HmIPHP\Data\ProgramData;
use Gustav\HmIPHP\Data\RoomData;
use Gustav\HmIPHP\Data\VariableData;
use Gustav\HmIPHP\Utils\Container;
use Gustav\HmIPHP\Utils\Mapping;
use Psr\Cache\InvalidArgumentException;

/**
 * The control component for accessing the Homematic IP-Data.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 * @api
 */
class Controller
{
    /**
     * The container.
     *
     * @var Container
     */
    private Container $_container;

    /**
     * Constructor of this class.
     *
     * @param Configuration $config
     *   The configuration of this controller
     */
    public function __construct(Configuration $config)
    {
        $this->_container = new Container(
            $config, new Connection($config), $this, ACacheManager::getInstance($config->getCacheConfig()), new Mapping()
        );
        $this->_container->getMapping()->setContainer($this->_container);
    }

    /**
     * Returns the configuration of this controller.
     *
     * @return Configuration
     *   The controller
     */
    public function getConfiguration(): Configuration
    {
        return $this->_container->getConfiguration();
    }

    /**
     * Returns a list of devices controlled by the connected Homematic CCU.
     *
     * @return DeviceData[]
     *   The devices
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDevices(): iterable
    {
        yield from $this->_container->getMapping()->getDevices();
    }

    /**
     * Returns the device with the given identifier.
     *
     * @param string $deviceId
     *   The device's id
     * @return DeviceData
     *   The device
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDevice(string $deviceId): DeviceData
    {
        return $this->_container->getMapping()->getDevice($deviceId);
    }

    /**
     * Returns the device with the given name.
     *
     * @param string $deviceName
     *   The device's name
     * @return DeviceData
     *   The device
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown device name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDeviceByName(string $deviceName): DeviceData
    {
        return $this->_container->getMapping()->getDeviceByName($deviceName);
    }

    /**
     * Returns the device channel with the given identifier.
     *
     * @param string $channelId
     *   The channel's id
     * @return ChannelData
     *   The channel
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannel(string $channelId): ChannelData
    {
        return $this->_container->getMapping()->getChannel($channelId);
    }

    /**
     * Returns the device channel with the given name.
     *
     * @param string $channelName
     *   The channel's name
     * @return ChannelData
     *   The channel
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown channel name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannelByName(string $channelName): ChannelData
    {
        return $this->_container->getMapping()->getChannelByName($channelName);
    }

    /**
     * Returns a list of all rooms controlled by the CCU.
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
        yield from $this->_container->getMapping()->getRooms();
    }

    /**
     * Returns the room with the given identifier.
     *
     * @param int $roomId
     *   The room's id
     * @return RoomData
     *   The room
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRoom(int $roomId): RoomData
    {
        return $this->_container->getMapping()->getRoom($roomId);
    }

    /**
     * Returns the room with the given name.
     *
     * @param string $roomName
     *   The room's name
     * @return RoomData
     *   The room
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown room name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRoomByName(string $roomName): RoomData
    {
        return $this->_container->getMapping()->getRoomByName($roomName);
    }

    /**
     * Returns the functions controlled by the CCU.
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
        yield from $this->_container->getMapping()->getFunctions();
    }

    /**
     * Returns the function with the given identifier.
     *
     * @param int $functionId
     *   The function's id
     * @return FunctionData
     *   The function
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunction(int $functionId): FunctionData
    {
        return $this->_container->getMapping()->getFunction($functionId);
    }

    /**
     * Returns the function with the given name.
     *
     * @param string $functionName
     *   The function's name
     * @return FunctionData
     *   The function
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown function name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunctionByName(string $functionName): FunctionData
    {
        return $this->_container->getMapping()->getFunctionByName($functionName);
    }

    /**
     * Returns the programs controlled by the CCU.
     *
     * @return ProgramData[]
     *   The programs
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getPrograms(): iterable
    {
        yield from $this->_container->getMapping()->getPrograms();
    }

    /**
     * Returns the program with the given identifier.
     *
     * @param int $programId
     *   The program's id
     * @return ProgramData
     *   The program
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getProgram(int $programId): ProgramData
    {
        return $this->_container->getMapping()->getProgram($programId);
    }

    /**
     * Returns the program with the given name.
     *
     * @param string $programName
     *   The program's name
     * @return ProgramData
     *   The program
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown program name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getProgramByName(string $programName): ProgramData
    {
        return $this->_container->getMapping()->getProgramByName($programName);
    }

    /**
     * Returns the system variables controlled by the CCU.
     *
     * @return VariableData[]
     *   The variables
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getVariables(): iterable
    {
        yield from $this->_container->getMapping()->getVariables();
    }

    /**
     * Returns the system variable with the given identifier.
     *
     * @param int $variableId
     *   The variable's id
     * @return VariableData
     *   The variable
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getVariable(int $variableId): VariableData
    {
        return $this->_container->getMapping()->getVariable($variableId);
    }

    /**
     * Returns the system variable with the given name.
     *
     * @param string $variableName
     *   The variable's name
     * @return VariableData
     *   The variable
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws HmIpException
     *   Unknown variable name
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getVariableByName(string $variableName): VariableData
    {
        return $this->_container->getMapping()->getVariableByName($variableName);
    }

    /**
     * Saves the cache.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     */
    public function __destruct()
    {
        $cacheManager = $this->_container->getCacheManager();

        $caches = [
            "parameters", "variables", "parameterData", "variableData", "deviceData", "channelData", "roomData"
        ];
        foreach($caches as $pool) {
            if($cacheManager->isOpened($pool)) {
                $cacheManager->getItemPool($pool)->commit();
            }
        }
    }
}