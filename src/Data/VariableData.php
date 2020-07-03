<?php
/**
 * Gustav Display
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

use DateInterval;
use DateTime;
use Gustav\Cache\CacheException;
use Gustav\Cache\CacheItem;
use Gustav\HmIPHP\Connection\ConnectionException;
use Gustav\HmIPHP\Utils\Container;
use Psr\Cache\InvalidArgumentException;
use StdClass;

/**
 * This class represents the data of some system variable.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
class VariableData extends AData
{
    /**
     * The identifier of this variable.
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
     * Returns the identifier of this variable.
     *
     * @return int
     *   The identifier
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * Returns the name of this variable.
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
        return $this->_container->getMapping()->getVariableData($this->_id)['name'];
    }

    /**
     * Returns a description of this variable.
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
        return $this->_container->getMapping()->getVariableData($this->_id)['description'];
    }

    /**
     * Returns the data type of this variable.
     *
     * @return string
     *   The data type
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getType(): string
    {
        return $this->_container->getMapping()->getVariableData($this->_id)['type'];
    }

    /**
     * Returns the unit of the value of this variable.
     *
     * @return string
     *   The unit
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getUnit(): string
    {
        return $this->_container->getMapping()->getVariableData($this->_id)['unit'];
    }

    /**
     * Returns the time of the last update of this variable's value. Note that this method returns the update time from
     * the cache if this one is available.
     *
     * @return DateTime
     *   The time of the last update
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getLastUpdate(): DateTime
    {
        $this->getState(false);
        $cache = $this->_container->getCacheManager()->getItemPool("variables");
        $date = new DateTime();
        $date->setTimestamp($cache->getItem($this->_id)->get()['dateline']);
        return $date;
    }

    /**
     * Returns the value of this variable.
     *
     * @param bool $forceReload
     *   true (default) if the value should be loaded from the CCU, false if we can return the value saved in the cache.
     *   Note that the value is stored for one hour.
     * @return mixed
     *   The value
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     * @throws InvalidArgumentException
     *   Some invalid argument was given to the cache
     */
    public function getState(bool $forceReload = true)
    {
        $cache = $this->_container->getCacheManager()->getItemPool("variables");
        if(!$forceReload) {
            if($cache->hasItem($this->_id)) {
                return $cache->getItem($this->_id)->get()['value'];
            }
        }
        $result = $this->_container->getConnection()->getData(["sysvar", $this->_id, "~pv"]);
        $data = [
            'value' => $result->v,
            'dateline' => (int) $result->ts
        ];
        $item = new CacheItem(
            $this->_id, $data, true, $cache, (new DateTime())->add(new DateInterval("PT1H"))
        );
        $cache->saveDeferred($item);

        return $data['value'];
    }

    /**
     * Sets the value of this variable. This value will be stored in cache for one hour.
     *
     * @param mixed $value
     *   The value
     * @return bool
     *   true, if sending value was successful, false otherwise
     * @throws CacheException
     *   Some error occurred while handling the cached data
     * @throws ConnectionException
     *   Some HTTP error code occurred
     */
    public function setState($value): bool
    {
        $obj = new StdClass();
        $obj->v = $value;

        if($this->_container->getConnection()->setData($obj, ["sysvar", $this->_id, "~pv"])) {
            $cache = $this->_container->getCacheManager()->getItemPool("variables");
            $data = [
                'value' => $value,
                'dateline' => (int) time()
            ];
            $item = new CacheItem(
                $this->_id, $data, true, $cache, (new DateTime())->add(new DateInterval("PT1H"))
            );
            $cache->saveDeferred($item);
            return true;
        }

        return false;
    }
}