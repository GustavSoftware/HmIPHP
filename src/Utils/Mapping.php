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

use Gustav\Cache\CacheItem;
use Gustav\HmIPHP\Data\ChannelData;
use Gustav\HmIPHP\Data\DeviceData;
use Gustav\HmIPHP\Data\FunctionData;
use Gustav\HmIPHP\Data\ParameterData;
use Gustav\HmIPHP\Data\ProgramData;
use Gustav\HmIPHP\Data\RoomData;
use Gustav\HmIPHP\Data\VariableData;
use Gustav\HmIPHP\HmIpException;

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
    private Container $_container;

    private array $_devices = [];
    private array $_channels = [];
    private array $_parameters = [];
    private array $_rooms = [];
    private array $_functions = [];
    private array $_programs = [];
    private array $_variables = [];

    private array $_devicesToChannels = [];
    private array $_channelsToParameters = [];

    private array $_roomsToChannels;
    private array $_channelsToRooms;
    private array $_channelsToFunctions;
    private array $_functionsToChannels;

    private array $_deviceNamesToIds;
    private array $_channelNamesToIds;
    private array $_roomNamesToIds;
    private array $_functionNamesToIds;
    private array $_programNamesToIds;
    private array $_variableNamesToIds;



    public function __construct()
    {
        //TODO?
    }

    public function setContainer(Container $container): void
    {
        $this->_container = $container;
    }

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

    public function getRooms(): iterable
    {
        $this->_fetchRoomNames();
        foreach($this->_roomNamesToIds as $roomId) {
            yield $roomId => $this->getRoom($roomId, false);
        }
    }

    public function getRoomByName(string $roomName): RoomData
    {
        $this->_fetchRoomNames();
        $roomName = trim(strtolower($roomName));
        if(!isset($this->_roomNamesToIds[$roomName])) {
            $roomName = $this->_container->getConfiguration()->getTranslation()->inverseTranslate($roomName);
            if(!isset($this->_roomNamesToIds[$roomName])) {
                throw HmIpException::invalidRoom($roomName);
            }
        }

        return $this->getRoom($this->_roomNamesToIds[$roomName], false);
    }

    public function getRoomName(int $roomId): string
    {
        $data = $this->getRoomData($roomId);
        return $this->_container->getConfiguration()->getTranslation()->translate($data['name']);
    }

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

    public function getDevices(): iterable
    {
        $this->_fetchDeviceNames();
        foreach($this->_deviceNamesToIds as $deviceId) {
            yield $deviceId => $this->getDevice($deviceId, false);
        }
    }

    public function getDeviceByName(string $deviceName): DeviceData
    {
        $this->_fetchDeviceNames();
        $deviceName = trim(strtolower($deviceName));
        if(!isset($this->_deviceNamesToIds[$deviceName])) {
            throw HmIpException::invalidDevice($deviceName);
        }
        return $this->getDevice($this->_deviceNamesToIds[$deviceName], false);
    }

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

    public function getChannelByName(string $channelName): ChannelData
    {
        $this->_fetchChannelNames();
        $channelName = trim(strtolower($channelName));
        if(!isset($this->_channelNamesToIds[$channelName])) {
            throw HmIpException::invalidChannel($channelName);
        }
        return $this->getChannel($this->_channelNamesToIds[$channelName], false);
    }

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

    public function getFunctions(): iterable
    {
        $this->_fetchFunctionNames();
        foreach($this->_functionNamesToIds as $functionId) {
            yield $functionId => $this->getFunction($functionId, false);
        }
    }

    public function getFunctionByName(string $functionName): FunctionData
    {
        $this->_fetchFunctionNames();
        $functionName = trim(strtolower($functionName));
        if(!isset($this->_functionNamesToIds[$functionName])) {
            $functionName = $this->_container->getConfiguration()->getTranslation()->inverseTranslate($functionName);
            if(!isset($this->_functionNamesToIds[$functionName])) {
                throw HmIpException::invalidFunction($functionName);
            }
        }

        return $this->getFunction($this->_functionNamesToIds[$functionName], false);
    }

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

    public function getPrograms(): iterable
    {
        $this->_fetchProgramNames();
        foreach($this->_programNamesToIds as $programId) {
            yield $programId => $this->getProgram($programId, false);
        }
    }

    public function getProgramByName(string $programName): ProgramData
    {
        $this->_fetchProgramNames();
        $programName = trim(strtolower($programName));
        if(!isset($this->_programNamesToIds[$programName])) {
            throw HmIpException::invalidProgram($programName);
        }

        return $this->getProgram($this->_programNamesToIds[$programName], false);
    }

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

    public function getVariables(): iterable
    {
        $this->_fetchVariableNames();
        foreach($this->_variableNamesToIds as $variableId) {
            yield $variableId => $this->getVariable($variableId, false);
        }
    }

    public function getVariableByName(string $variableName): VariableData
    {
        $this->_fetchVariableNames();
        $variableName = trim(strtolower($variableName));
        if(!isset($this->_variableNamesToIds[$variableName])) {
            throw HmIpException::invalidVariable($variableName);
        }

        return $this->getVariable($this->_variableNamesToIds[$variableName], false);
    }

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
                new CacheItem($deviceId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );

            return $data;
        }
    }

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
                new CacheItem($channelId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );

            return $data;
        }
    }

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
                new CacheItem($roomId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );
            return $data;
        }
    }

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
                new CacheItem($functionId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );
            return $data;
        }
    }

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
                new CacheItem($parameterId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );
            return $data;
        }
    }

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
                new CacheItem($programId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );
            return $data;
        }
    }

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
                new CacheItem($variableId, $data, true, $cache, (new \DateTime())->add(new \DateInterval("P1M")))
            );
            return $data;
        }
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_roomNamesToIds = $cache->getItem("namesToIds")->get();
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_deviceNamesToIds = $cache->getItem("namesToIds")->get();
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_channelNamesToIds = $cache->getItem("namesToIds")->get();
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_functionNamesToIds = $cache->getItem("namesToIds")->get();
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_programNamesToIds = $cache->getItem("namesToIds")->get();
    }

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

            $duration = (new \DateTime())->add(new \DateInterval("P1M"));
            $cache->save(new CacheItem("namesToIds", $namesToIds, true, $cache, $duration));
        }

        $this->_variableNamesToIds = $cache->getItem("namesToIds")->get();
    }
}