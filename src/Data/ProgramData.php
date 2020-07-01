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

class ProgramData extends AData
{
    private int $_id;

    public function __construct(Container $container, int $id)
    {
        parent::__construct($container);
        $this->_id = $id;
    }

    public function getId(): int
    {
        return $this->_id;
    }

    public function getName(): string
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['name'];
    }

    public function getDescription(): string
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['description'];
    }

    public function isActive(): bool
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['active'];
    }

    public function isVisible(): bool
    {
        return $this->_container->getMapping()->getProgramData($this->_id)['visible'];
    }

    public function getLastUpdate(): \DateTime
    {
        $result = $this->_container->getConnection()->getData(["program", $this->_id, "~pv"]);
        $date = new \DateTime();
        $date->setTimestamp($result->ts);
        return $date;
    }

    public function execute(): bool
    {
        $obj = new \StdClass();
        $obj->v = true;

        return $this->_container->getConnection()->setData($obj, ["program", $this->_id, "~pv"]);
    }
}