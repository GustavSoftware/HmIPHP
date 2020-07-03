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

namespace Gustav\HmIPHP\Utils;

use DateInterval;
use DateTime;
use Gustav\Cache\CacheException;
use Gustav\Cache\CacheItem;
use Gustav\HmIPHP\Connection\ConnectionException;
use Gustav\HmIPHP\Data\ChannelData;
use Gustav\HmIPHP\Data\DeviceData;
use Gustav\HmIPHP\Data\FunctionData;
use Gustav\HmIPHP\Data\ParameterData;
use Gustav\HmIPHP\Data\ProgramData;
use Gustav\HmIPHP\Data\RoomData;
use Gustav\HmIPHP\Data\VariableData;
use Gustav\HmIPHP\HmIpException;
use Psr\Cache\InvalidArgumentException;

/**
 * This class manages the mappings between all of our data objects.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link https://gustav.fieselschweif.de
 * @since 1.0.0
 * @internal
 */
class Mapping
{
    /**
     * The container.
     *
     * @var Container
     */
    private Container $_container;

    /**
     * The devices we have already constructed.
     *
     * @var DeviceData[]
     */
    private array $_devices = [];

    /**
     * The channels we have already constructed.
     *
     * @var ChannelData[]
     */
    private array $_channels = [];

    /**
     * The channel parameters we have already constructed.
     *
     * @var ParameterData[]
     */
    private array $_parameters = [];

    /**
     * The rooms we have already constructed.
     *
     * @var RoomData[]
     */
    private array $_rooms = [];

    /**
     * The functions we have already constructed.
     *
     * @var FunctionData[]
     */
    private array $_functions = [];

    /**
     * The programs we have already constructed.
     *
     * @var ProgramData[]
     */
    private array $_programs = [];

    /**
     * The system variables we have already constructed.
     *
     * @var VariableData[]
     */
    private array $_variables = [];

    /**
     * The map from devices to their channels.
     *
     * @var array
     */
    private array $_devicesToChannels = [];

    /**
     * The map from channels to their parameters.
     *
     * @var array
     */
    private array $_channelsToParameters = [];

    /**
     * The map from rooms to their associated channels.
     *
     * @var array
     */
    private array $_roomsToChannels;

    /**
     * The map from channels to their associated rooms.
     *
     * @var array
     */
    private array $_channelsToRooms;

    /**
     * The map from channels to their associated functions.
     *
     * @var array
     */
    private array $_channelsToFunctions;

    /**
     * The map from functions to their associated channels.
     *
     * @var array
     */
    private array $_functionsToChannels;

    /**
     * The map from device names to their identifiers.
     *
     * @var string[]
     */
    private array $_deviceNamesToIds;

    /**
     * The map from channel names to their identifiers.
     *
     * @var string[]
     */
    private array $_channelNamesToIds;

    /**
     * The map from room names to their identifiers.
     *
     * @var int[]
     */
    private array $_roomNamesToIds;

    /**
     * The map from function names to their identifiers.
     *
     * @var int[]
     */
    private array $_functionNamesToIds;

    /**
     * The map from program names to their identifiers.
     *
     * @var int[]
     */
    private array $_programNamesToIds;

    /**
     * The map from system variable names to their identifiers.
     *
     * @var int[]
     */
    private array $_variableNamesToIds;

    /**
     * Sets the container.
     *
     * @param Container $container
     *   The container
     */
    public function setContainer(Container $container): void
    {
        $this->_container = $container;
    }

    /**
     * Returns the room with the given identifier.
     *
     * @param int $roomId
     *   The room's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return RoomData
     *   The room
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRoom(int $roomId, bool $checkExistence = true): RoomData
    {
        if(!isset($this->_rooms[$roomId])) {
            if($checkExistence) {
                $this->getRoomData($roomId); //includes a check whether this room exists
            }
            $this->_rooms[$roomId] = new RoomData($this->_container, $roomId);
        }
        return $this->_rooms[$roomId];
    }

    /**
     * Returns a list of all rooms.
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
        $this->_fetchRoomNames();
        foreach($this->_roomNamesToIds as $roomId) {
            yield $roomId => $this->getRoom($roomId, false);
        }
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
        $this->_fetchRoomNames();
        $roomName = trim(strtolower($roomName));
        if(!isset($this->_roomNamesToIds[$roomName])) {
            $roomName = $this->_container->getConfiguration()->getTranslator()->inverseTranslate($roomName);
            if(!isset($this->_roomNamesToIds[$roomName])) {
                throw HmIpException::invalidRoom($roomName);
            }
        }

        return $this->getRoom($this->_roomNamesToIds[$roomName], false);
    }

    /**
     * Returns the device with the given identifier.
     *
     * @param string $deviceId
     *   The device's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return DeviceData
     *   The device
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDevice(string $deviceId, bool $checkExistence = true): DeviceData
    {
        if(!isset($this->_devices[$deviceId])) {
            if($checkExistence) {
                $this->getDeviceData($deviceId); //includes a check whether this device exists
            }
            $this->_devices[$deviceId] = new DeviceData($this->_container, $deviceId);
        }
        return $this->_devices[$deviceId];
    }

    /**
     * Returns a list of all devices.
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
        $this->_fetchDeviceNames();
        foreach($this->_deviceNamesToIds as $deviceId) {
            yield $deviceId => $this->getDevice($deviceId, false);
        }
    }

    /**
     * Returns the device with the given name.
     *
     * @param string $deviceName
     *   The devices's name
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
        $this->_fetchDeviceNames();
        $deviceName = trim(strtolower($deviceName));
        if(!isset($this->_deviceNamesToIds[$deviceName])) {
            throw HmIpException::invalidDevice($deviceName);
        }
        return $this->getDevice($this->_deviceNamesToIds[$deviceName], false);
    }

    /**
     * Returns the device channel with the given identifier.
     *
     * @param string $channelId
     *   The channel's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return ChannelData
     *   The channel
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannel(string $channelId, bool $checkExistence = true): ChannelData
    {
        if(!isset($this->_channels[$channelId])) {
            if($checkExistence) {
                $this->getChannelData($channelId);
            }
            [$deviceId, ] = explode("/", $channelId);
            $this->_channels[$channelId] = new ChannelData($this->_container, $channelId, $this->getDevice($deviceId, false));
        }
        return $this->_channels[$channelId];
    }

    /**
     * Returns the channels of some device.
     *
     * @param string $deviceId
     *   The device's id
     * @return ChannelData[]
     *   The channels
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannels(string $deviceId): iterable
    {
        if(!isset($this->_devicesToChannels[$deviceId])) {
            $data = $this->getDeviceData($deviceId);
            $map = [];
            foreach($data['channels'] as $channelNum => $channel) {
                $channel = $this->getChannel($channel['id'], false);
                $map[$channelNum] = $channel;
                yield $channelNum => $channel;
            }
            $this->_devicesToChannels[$deviceId] = $map;
        } else {
            yield from $this->_devicesToChannels[$deviceId];
        }
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
        $this->_fetchChannelNames();
        $channelName = trim(strtolower($channelName));
        if(!isset($this->_channelNamesToIds[$channelName])) {
            throw HmIpException::invalidChannel($channelName);
        }
        return $this->getChannel($this->_channelNamesToIds[$channelName], false);
    }

    /**
     * Returns the parameters of some device channel.
     *
     * @param string $channelId
     *   The channel's id
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
    public function getParameters(string $channelId): iterable
    {
        if(!isset($this->_channelsToParameters[$channelId])) {
            $data = $this->getChannelData($channelId);
            $map = [];
            foreach($data['parameters'] as $parameterName) {
                $parameter = $this->getParameter("{$channelId}/{$parameterName}", false);
                $map[$parameterName] = $parameter;
                yield $parameterName => $parameter;
            }
            $this->_channelsToParameters[$channelId] = $map;
        } else {
            yield from $this->_channelsToParameters[$channelId];
        }
    }

    /**
     * Returns the channel parameter with the given identifier.
     *
     * @param string $parameterId
     *   The parameter's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
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
    public function getParameter(string $parameterId, bool $checkExistence = true): ParameterData
    {
        if(!isset($this->_parameters[$parameterId])) {
            [$deviceId, $channelId, $parameterName] = explode("/", $parameterId);
            if($checkExistence) {
                $data = $this->getChannelData("{$deviceId}/{$channelId}");
                if(!isset($data['parameters'][$parameterName])) {
                    throw HmIpException::invalidParameter($parameterId);
                }
            }
            $this->_parameters[$parameterId] = new ParameterData(
                $this->_container, $parameterName, $this->getChannel("{$deviceId}/{$channelId}", false)
            );
        }
        return $this->_parameters[$parameterId];
    }

    /**
     * Returns the rooms of some device channel.
     *
     * @param string $channelId
     *   The channel's id
     * @return RoomData[]
     *   The rooms
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRoomsOfChannel(string $channelId): iterable
    {
        if(!isset($this->_channelsToRooms[$channelId])) {
            $data = $this->getChannelData($channelId);
            $map = [];
            foreach($data['rooms'] as $roomId) {
                $room = $this->getRoom($roomId, false);
                $map[$roomId] = $room;
                yield $roomId => $room;
            }
            $this->_channelsToRooms[$channelId] = $map;
        } else {
            yield from $this->_channelsToRooms[$channelId];
        }
    }

    /**
     * Returns the device channels of some room.
     *
     * @param int $roomId
     *   The room'S id
     * @return ChannelData[]
     *   The channels
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannelsOfRoom(int $roomId): iterable
    {
        if(!isset($this->_roomsToChannels[$roomId])) {
            $data = $this->getRoomData($roomId);
            $map = [];
            foreach($data['channels'] as $channelId) {
                $channel = $this->getChannel($channelId, false);
                $map[$channelId] = $channel;
                yield $channelId => $channel;
            }
            $this->_roomsToChannels[$roomId] = $map;
        } else {
            yield from $this->_roomsToChannels[$roomId];
        }
    }

    /**
     * Returns the function with the given identifier.
     *
     * @param int $functionId
     *   The function's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return FunctionData
     *   The function
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunction(int $functionId, bool $checkExistence = true): FunctionData
    {
        if(!isset($this->_functions[$functionId])) {
            if($checkExistence) {
                $this->getFunctionData($functionId);
            }
            $this->_functions[$functionId] = new FunctionData($this->_container, $functionId);
        }
        return $this->_functions[$functionId];
    }

    /**
     * Returns a list of all functions.
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
        $this->_fetchFunctionNames();
        foreach($this->_functionNamesToIds as $functionId) {
            yield $functionId => $this->getFunction($functionId, false);
        }
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
        $this->_fetchFunctionNames();
        $functionName = trim(strtolower($functionName));
        if(!isset($this->_functionNamesToIds[$functionName])) {
            $functionName = $this->_container->getConfiguration()->getTranslator()->inverseTranslate($functionName);
            if(!isset($this->_functionNamesToIds[$functionName])) {
                throw HmIpException::invalidFunction($functionName);
            }
        }

        return $this->getFunction($this->_functionNamesToIds[$functionName], false);
    }

    /**
     * Returns the device channels of some function.
     *
     * @param int $functionId
     *   The function's id
     * @return ChannelData[]
     *   The channel data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannelsOfFunction(int $functionId): iterable
    {
        if(!isset($this->_functionsToChannels[$functionId])) {
            $data = $this->getFunctionData($functionId);
            $map = [];
            foreach($data['channels'] as $channelId) {
                $channel = $this->getChannel($channelId, false);
                $map[$channelId] = $channel;
                yield $channelId => $channel;
            }
            $this->_functionsToChannels[$functionId] = $map;
        } else {
            yield from $this->_functionsToChannels[$functionId];
        }
    }

    /**
     * Returns the functions of some channel.
     *
     * @param string $channelId
     *   The channel'S id
     * @return FunctionData[]
     *   The functions
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunctionsOfChannel(string $channelId): iterable
    {
        if(!isset($this->_channelsToFunctions[$channelId])) {
            $data = $this->getChannelData($channelId);
            $map = [];
            foreach($data['functions'] as $functionId) {
                $function = $this->getFunction($functionId, false);
                $map[$functionId] = $function;
                yield $functionId => $function;
            }
            $this->_channelsToFunctions[$channelId] = $map;
        } else {
            yield from $this->_channelsToFunctions[$channelId];
        }
    }

    /**
     * Returns the program with the given identifier.
     *
     * @param int $programId
     *   The program's id
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return ProgramData
     *   The program
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getProgram(int $programId, bool $checkExistence = true): ProgramData
    {
        if(!isset($this->_programs[$programId])) {
            if($checkExistence) {
                $this->getProgramData($programId);
            }
            $this->_programs[$programId] = new ProgramData($this->_container, $programId);
        }
        return $this->_programs[$programId];
    }

    /**
     * Returns a list of all programs.
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
        $this->_fetchProgramNames();
        foreach($this->_programNamesToIds as $programId) {
            yield $programId => $this->getProgram($programId, false);
        }
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
        $this->_fetchProgramNames();
        $programName = trim(strtolower($programName));
        if(!isset($this->_programNamesToIds[$programName])) {
            throw HmIpException::invalidProgram($programName);
        }

        return $this->getProgram($this->_programNamesToIds[$programName], false);
    }

    /**
     * Returns the system variable with the given identifier.
     *
     * @param int $variableId
     *   The identifier of the variable
     * @param bool $checkExistence
     *   true, if another check of existence is needed, false otherwise
     * @return VariableData
     *   The variable
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getVariable(int $variableId, bool $checkExistence = true): VariableData
    {
        if(!isset($this->_variables[$variableId])) {
            if($checkExistence) {
                $this->getVariableData($variableId);
            }
            $this->_variables[$variableId] = new VariableData($this->_container, $variableId);
        }
        return $this->_variables[$variableId];
    }

    /**
     * Returns a list of all system variables.
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
        $this->_fetchVariableNames();
        foreach($this->_variableNamesToIds as $variableId) {
            yield $variableId => $this->getVariable($variableId, false);
        }
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
        $this->_fetchVariableNames();
        $variableName = trim(strtolower($variableName));
        if(!isset($this->_variableNamesToIds[$variableName])) {
            throw HmIpException::invalidVariable($variableName);
        }

        return $this->getVariable($this->_variableNamesToIds[$variableName], false);
    }

    /**
     * Returns the data of some device.
     *
     * @param string $deviceId
     *   The identifier of the device
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDeviceData(string $deviceId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("deviceData");
        if($cache->hasItem($deviceId)) {
            return $cache->getItem($deviceId)->get();
        } else {
            $connection = $this->_container->getConnection();
            $result = $connection->getData(["device", $deviceId]);
            $data = [
                'name' => trim($result->title),
                'type' => trim($result->type),
                'secured' => (bool) $result->aesActive,
                'firmware' => trim($result->firmware),
                'channels' => []
            ];

            foreach($result->{"~links"} as $channel) {
                if($channel->rel == "channel") {
                    $data['channels'][$channel->href] = [
                        'id' => $deviceId . "/" . $channel->href,
                        'name' => $channel->title
                    ];
                }
            }

            $cache->saveDeferred(
                new CacheItem($deviceId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );

            return $data;
        }
    }

    /**
     * Returns the data of some device's channel.
     *
     * @param string $channelId
     *   The identifier of the channel
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannelData(string $channelId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("channelData");
        if($cache->hasItem($channelId)) {
            return $cache->getItem($channelId)->get();
        } else {
            $connection = $this->_container->getConnection();
            $result = $connection->getData(["device", $channelId]);
            $data = [
                'name' => trim($result->title),
                'rooms' => [],
                'functions' => [],
                'parameters' => []
            ];

            foreach($result->{"~links"} as $link) {
                if(in_array($link->href, ["..", "\$MASTER"])) {
                    continue;
                }
                if($link->rel == "room") {
                    $linkId = explode("/", $link->href)[2];
                    $data['rooms'][$linkId] = $linkId;
                } elseif($link->rel == "function") {
                    $linkId = explode("/", $link->href)[2];
                    $data['functions'][$linkId] = $linkId;
                } elseif($link->rel == "parameter") {
                    $data['parameters'][$link->href] = $link->href;
                }
            }

            $cache->saveDeferred(
                new CacheItem($channelId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );

            return $data;
        }
    }

    /**
     * Returns the data of some room.
     *
     * @param int $roomId
     *   The identifier of the room
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getRoomData(int $roomId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("roomData");
        if($cache->hasItem($roomId)) {
            return $cache->getItem($roomId)->get();
        } else {
            $result = $this->_container->getConnection()->getData(["room", $roomId]);
            $data = [
                'name' => trim($result->title),
                'description' => trim($result->description),
                'channels' => []
            ];
            foreach($result->{"~links"} as $channel) {
                if($channel->rel != "channel") {
                    continue;
                }
                $data['channels'][] = substr($channel->href, 8); //|/device/|=8
            }
            $cache->saveDeferred(
                new CacheItem($roomId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );
            return $data;
        }
    }

    /**
     * Returns the data of some function.
     *
     * @param int $functionId
     *   The identifier of the function
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getFunctionData(int $functionId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("functionData");
        if($cache->hasItem($functionId)) {
            return $cache->getItem($functionId)->get();
        } else {
            $result = $this->_container->getConnection()->getData(["function", $functionId]);
            $data = [
                'name' => trim($result->title),
                'description' => trim($result->description),
                'channels' => []
            ];
            foreach($result->{"~links"} as $channel) {
                if($channel->rel != "channel") {
                    continue;
                }
                $data['channels'][] = substr($channel->href, 8); //|/device/|=8
            }
            $cache->saveDeferred(
                new CacheItem($functionId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );
            return $data;
        }
    }

    /**
     * Returns the data of some channel parameter.
     *
     * @param int $parameterId
     *   The identifier of the parameter
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getParameterData($parameterId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("parameterData");
        if($cache->hasItem($parameterId)) {
            return $cache->getItem($parameterId)->get();
        } else {
            $result = $this->_container->getConnection()->getData(["device", $parameterId]);
            $data = [
                'name' => trim($result->title),
                'type' => trim($result->type),
                'unit' => trim($result->unit)
            ];
            $cache->saveDeferred(
                new CacheItem($parameterId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );
            return $data;
        }
    }

    /**
     * Returns the data of some program.
     *
     * @param int $programId
     *   The identifier of the program
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getProgramData(int $programId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("programData");
        if($cache->hasItem($programId)) {
            return $cache->getItem($programId)->get();
        } else {
            $result = $this->_container->getConnection()->getData(["program", $programId]);
            $data = [
                'name' => trim($result->title),
                'description' => trim($result->description),
                'active' => (bool) $result->active,
                'visible' => (bool) $result->visible
            ];
            $cache->saveDeferred(
                new CacheItem($programId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );
            return $data;
        }
    }

    /**
     * Returns the data of some system variable.
     *
     * @param int $variableId
     *   The identifier of the variable
     * @return array
     *   The data
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getVariableData(int $variableId): array
    {
        $cache = $this->_container->getCacheManager()->getItemPool("variableData");
        if($cache->hasItem($variableId)) {
            return $cache->getItem($variableId)->get();
        } else {
            $result = $this->_container->getConnection()->getData(["sysvar", $variableId]);
            $data = [
                'name' => trim($result->title),
                'description' => trim($result->description),
                'type' => trim($result->type),
                'unit' => trim($result->unit)
            ];
            $cache->saveDeferred(
                new CacheItem($variableId, $data, true, $cache, (new DateTime())->add(new DateInterval("P1M")))
            );
            return $data;
        }
    }

    /**
     * Fetches the names of rooms from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchRoomNames(): void
    {
        if(isset($this->_roomNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("roomNames");
        if(!$cache->hasItem("namesToIds")) {
            $result = $this->_container->getConnection()->getData(["room"]);
            $namesToIds = [];
            foreach($result->{"~links"} as $room) {
                if($room->rel != "room") {
                    continue;
                }
                $namesToIds[trim(strtolower($room->title))] = (int) $room->href;
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_roomNamesToIds = $cache->getItem("namesToIds")->get();
    }

    /**
     * Fetches the names of devices from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchDeviceNames(): void
    {
        if(isset($this->_deviceNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("deviceNames");
        if(!$cache->hasItem("namesToIds")) {
            $result = $this->_container->getConnection()->getData(["device"]);
            $namesToIds = [];
            foreach($result->{"~links"} as $device) {
                if($device->rel != "device") {
                    continue;
                }
                $namesToIds[trim(strtolower($device->title))] = $device->href;
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_deviceNamesToIds = $cache->getItem("namesToIds")->get();
    }

    /**
     * Fetches the names of channels from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchChannelNames(): void
    {
        if(isset($this->_channelNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("channelNames");
        if(!$cache->hasItem("namesToIds")) {
            $connection = $this->_container->getConnection();
            $devices = $connection->getData(["device"]);
            $namesToIds = [];
            foreach($devices->{"~links"} as $device) {
                if($device->rel != "device") {
                    continue;
                }
                $data = $this->getDeviceData($device->href);
                foreach($data['channels'] as $channel) {
                    $namesToIds[trim(strtolower($channel['name']))] = $channel['id'];
                }
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_channelNamesToIds = $cache->getItem("namesToIds")->get();
    }

    /**
     * Fetches the names of functions from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchFunctionNames(): void
    {
        if(isset($this->_functionNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("functionNames");
        if(!$cache->hasItem("namesToIds")) {
            $result = $this->_container->getConnection()->getData(["function"]);
            $namesToIds = [];
            foreach($result->{"~links"} as $function) {
                if($function->rel != "function") {
                    continue;
                }
                $namesToIds[trim(strtolower($function->title))] = (int) $function->href;
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_functionNamesToIds = $cache->getItem("namesToIds")->get();
    }

    /**
     * Fetches the names of programs from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchProgramNames(): void
    {
        if(isset($this->_programNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("programNames");
        if(!$cache->hasItem("namesToIds")) {
            $result = $this->_container->getConnection()->getData(["program"]);
            $namesToIds = [];
            foreach($result->{"~links"} as $program) {
                if($program->rel != "program") {
                    continue;
                }
                $namesToIds[trim(strtolower($program->title))] = (int) $program->href;
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_programNamesToIds = $cache->getItem("namesToIds")->get();
    }

    /**
     * Fetches the names of system variables from the CCU.
     *
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    private function _fetchVariableNames(): void
    {
        if(isset($this->_variableNamesToIds)) {
            return;
        }

        $cache = $this->_container->getCacheManager()->getItemPool("variableNames");
        if(!$cache->hasItem("namesToIds")) {
            $result = $this->_container->getConnection()->getData(["sysvar"]);
            $namesToIds = [];
            foreach($result->{"~links"} as $variable) {
                if($variable->rel != "sysvar") {
                    continue;
                }
                $namesToIds[trim(strtolower($variable->title))] = (int) $variable->href;
            }

            $duration = (new DateTime())->add(new DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_variableNamesToIds = $cache->getItem("namesToIds")->get();
    }
}