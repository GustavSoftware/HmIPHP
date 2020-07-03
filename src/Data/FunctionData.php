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
use Gustav\HmIPHP\Utils\Container;
use Psr\Cache\InvalidArgumentException;

/**
 * This class represents the data from some function.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class FunctionData extends AData
{
    /**
     * The identifier of this function.
     *
     * @var int
     */
    private int $_id;

    /**
     * Constructor of this class.
     *
     * @param Container $container
     *   The container
     * @param int $id
     *   The identifier
     */
    public function __construct(Container $container, int $id)
    {
        parent::__construct($container);
        $this->_id = $id;
    }

    /**
     * Returns the identifier of this function.
     *
     * @return int
     *   The identifier
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * Returns the name of this function.
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
        return $this->_container->getConfiguration()->getTranslator()->translate(
            $this->_container->getMapping()->getFunctionData($this->_id)['name']
        );
    }

    /**
     * Returns a description of this function.
     *
     * @return string
     *   The description
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getDescription(): string
    {
        return $this->_container->getMapping()->getFunctionData($this->_id)['description'];
    }

    /**
     * Returns the channels associated to this function.
     *
     * @return ChannelData[]
     *   The channels
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getChannels(): iterable
    {
        return $this->_container->getMapping()->getChannelsOfFunction($this->_id);
    }
}