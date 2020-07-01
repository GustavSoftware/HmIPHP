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
use Gustav\HmIPHP\Connection\Connection;
use Gustav\HmIPHP\Data\ChannelData;
use Gustav\HmIPHP\Data\DeviceData;
use Gustav\HmIPHP\Data\FunctionData;
use Gustav\HmIPHP\Data\ProgramData;
use Gustav\HmIPHP\Data\RoomData;
use Gustav\HmIPHP\Data\VariableData;
use Gustav\HmIPHP\Utils\Container;
use Gustav\HmIPHP\Utils\Mapping;

class Controller
{
    private Container $_container;

    public function __construct(Configuration $config)
    {
        $this->_container = new Container(
            $config, new Connection($config), $this, ACacheManager::getInstance($config->getCacheConfig()), new Mapping()
        );
        $this->_container->getMapping()->setContainer($this->_container);
    }

    public function getConfiguration(): Configuration
    {
        return $this->_container->getConfiguration();
    }

    public function getDevices(): iterable
    {
        yield from $this->_container->getMapping()->getDevices();
    }

    public function getDevice(string $deviceId): DeviceData
    {
        return $this->_container->getMapping()->getDevice($deviceId);
    }

    public function getDeviceByName(string $deviceName): DeviceData
    {
        return $this->_container->getMapping()->getDevice($deviceName);
    }

    public function getChannel(string $channelId): ChannelData
    {
        return $this->_container->getMapping()->getChannel($channelId);
    }

    public function getChannelByName(string $channelName): ChannelData
    {
        return $this->_container->getMapping()->getChannel($channelName);
    }

    public function getRooms(): iterable
    {
        yield from $this->_container->getMapping()->getRooms();
    }

    public function getRoom(int $roomId): RoomData
    {
        return $this->_container->getMapping()->getRoom($roomId);
    }

    public function getRoomByName(string $roomName): RoomData
    {
        return $this->_container->getMapping()->getRoomByName($roomName);
    }

    public function getFunctions(): iterable
    {
        yield from $this->_container->getMapping()->getFunctions();
    }

    public function getFunction(int $functionId): FunctionData
    {
        return $this->_container->getMapping()->getFunction($functionId);
    }

    public function getFunctionByName(string $functionName): FunctionData
    {
        return $this->_container->getMapping()->getFunctionByName($functionName);
    }

    public function getPrograms(): iterable
    {
        yield from $this->_container->getMapping()->getPrograms();
    }

    public function getProgram(int $programId): ProgramData
    {
        return $this->_container->getMapping()->getProgram($programId);
    }

    public function getProgramByName(string $programName): ProgramData
    {
        return $this->_container->getMapping()->getProgramByName($programName);
    }

    public function getVariables(): iterable
    {
        yield from $this->_container->getMapping()->getVariables();
    }

    public function getVariable(int $variableId): VariableData
    {
        return $this->_container->getMapping()->getVariable($variableId);
    }

    public function getVariableByName(string $variableName): VariableData
    {
        return $this->_container->getMapping()->getVariableByName($variableName);
    }

    public function __destruct()
    {
        $cacheManager = $this->_container->getCacheManager();
        $cacheManager->getItemPool("parameters")->commit();
        $cacheManager->getItemPool("variables")->commit();
        $cacheManager->getItemPool("parameterData")->commit();
        $cacheManager->getItemPool("variableData")->commit();
        $cacheManager->getItemPool("deviceData")->commit();
        $cacheManager->getItemPool("channelData")->commit();
        $cacheManager->getItemPool("roomData")->commit();
    }
}