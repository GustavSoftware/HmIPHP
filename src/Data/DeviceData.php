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

class DeviceData extends AData
{
    private string $_id;

    public function __construct(Container $container, string $id)
    {
        parent::__construct($container);
        $this->_id = $id;
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getType(): string
    {
        return $this->_container->getMapping()->getDeviceData($this->_id)['type'];
    }

    public function getName(): string
    {
        return $this->_container->getMapping()->getDeviceData($this->_id)['name'];
    }

    public function getFirmware(): string
    {
        return $this->_container->getMapping()->getDeviceData($this->_id)['firmware'];
    }

    public function isSecured(): bool
    {
        return $this->_container->getMapping()->getDeviceData($this->_id)['secured'];
    }

    public function getChannels(): iterable
    {
        yield from $this->_container->getMapping()->getChannels($this->_id);
    }

    public function getChannel(int $id): ChannelData
    {
        return $this->_container->getMapping()->getChannel($this->_id . "/" . $id);
    }
}