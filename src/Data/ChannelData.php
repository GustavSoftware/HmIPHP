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

use Gustav\HmIPHP\Utils\Container;

class ChannelData extends AData
{
    private string $_id;
    private DeviceData $_device;

    public function __construct(Container $container, string $id, DeviceData $device)
    {
        parent::__construct($container);
        $this->_id = $id;
        $this->_device = $device;
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getDevice(): DeviceData
    {
        return $this->_device;
    }

    public function getName(): string
    {
        return $this->_container->getMapping()->getChannelData($this->_id)['name'];
    }

    public function getRooms(): iterable
    {
        yield from $this->_container->getMapping()->getRoomsOfChannel($this->_id);
    }

    public function getFunctions(): iterable
    {
        yield from $this->_container->getMapping()->getFunctionsOfChannel($this->_id);
    }

    public function getParameters(): iterable
    {
        yield from $this->_container->getMapping()->getParameters($this->_id);
    }

    public function getParameter(string $name): ParameterData
    {
        return $this->_container->getMapping()->getParameter($this->_id . "/" . $name);
    }
}