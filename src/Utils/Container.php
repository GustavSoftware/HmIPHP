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

/**
 * This class contains some important objects.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 * @internal
 */
class Container
{
    /**
     * The configuration of the controller.
     *
     * @var Configuration
     */
    private Configuration $_config;

    /**
     * The connection to the CCU.
     *
     * @var Connection
     */
    private Connection $_connection;

    /**
     * The controller.
     *
     * @var Controller
     */
    private Controller $_controller;

    /**
     * The cache manager.
     *
     * @var ACacheManager
     */
    private ACacheManager $_cacheManager;

    /**
     * The mapping of the data.
     *
     * @var Mapping
     */
    private Mapping $_mapping;

    /**
     * Constructor of this class.
     *
     * @param Configuration $config
     *   The configuration of the controller
     * @param Connection $connection
     *   The connection to the CCU
     * @param Controller $controller
     *   The controller
     * @param ACacheManager $cacheManager
     *   The cache manager
     * @param Mapping $mapping
     *   The data mapping
     */
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

    /**
     * Returns the configuration of the controller.
     *
     * @return Configuration
     *   The configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->_config;
    }

    /**
     * Returns the connection to the CCU.
     *
     * @return Connection
     *   The connection
     */
    public function getConnection(): Connection
    {
        return $this->_connection;
    }

    /**
     * Returns the controller.
     *
     * @return Controller
     *   The controller
     */
    public function getController(): Controller
    {
        return $this->_controller;
    }

    /**
     * Returns the cache manager.
     *
     * @return ACacheManager
     *   The cache manager
     */
    public function getCacheManager(): ACacheManager
    {
        return $this->_cacheManager;
    }

    /**
     * Returns the data mapping.
     *
     * @return Mapping
     *   The data mapping
     */
    public function getMapping(): Mapping
    {
        return $this->_mapping;
    }
}