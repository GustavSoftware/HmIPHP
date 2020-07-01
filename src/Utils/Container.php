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


use Gustav\Cache\ACacheManager;
use Gustav\HmIPHP\Configuration;
use Gustav\HmIPHP\Connection\Connection;
use Gustav\HmIPHP\Controller;

class Container
{
    private Configuration $_config;
    private Connection $_connection;
    private Controller $_controller;
    private ACacheManager $_cacheManager;
    private Mapping $_mapping;

    public function __construct(
        Configuration $config, Connection $connection, Controller $controller, ACacheManager $cacheManager,
        Mapping $mapping
    ) {
        $this->_config = $config;
        $this->_connection = $connection;
        $this->_controller = $controller;
        $this->_cacheManager = $cacheManager;
        $this->_mapping = $mapping;
    }

    public function getConfiguration(): Configuration
    {
        return $this->_config;
    }

    public function getConnection(): Connection
    {
        return $this->_connection;
    }

    public function getController(): Controller
    {
        return $this->_controller;
    }

    public function getCacheManager(): ACacheManager
    {
        return $this->_cacheManager;
    }

    public function getMapping(): Mapping
    {
        return $this->_mapping;
    }
}