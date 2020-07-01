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


use Gustav\Cache\CacheItem;
use Gustav\HmIPHP\Utils\Container;

class ParameterData extends AData
{
    private string $_name;
    private ChannelData $_channel;
    private string $_id;

    public function __construct(Container $container, string $name, ChannelData $channel)
    {
        parent::__construct($container);
        $this->_name = $name;
        $this->_channel = $channel;
        $this->_id = $channel->getId() . "/" . $this->_name;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function getChannel(): ChannelData
    {
        return $this->_channel;
    }

    public function getType(): string
    {
        return $this->_container->getMapping()->getParameterData($this->_id)['type'];
    }

    public function getUnit(): string
    {
        return $this->_container->getMapping()->getParameterData($this->_id)['unit'];
    }

    public function getLastUpdate(): \DateTime
    {
        $this->getState(false);
        $cache = $this->_container->getCacheManager()->getItemPool("parameters");
        $date = new \DateTime();
        $date->setTimestamp($cache->getItem($this->_id)->get()['dateline']);
        return $date;
    }

    public function getState(bool $forceReload = true)
    {
        $cache = $this->_container->getCacheManager()->getItemPool("parameters");
        if(!$forceReload) {
            if($cache->hasItem($this->_id)) {
                return $cache->getItem($this->_id)->get()['value'];
            }
        }
        $result = $this->_container->getConnection()->getData(["device", $this->_channel->getId(), $this->_name, "~pv"]);
        $data = [
            'value' => $result->v,
            'dateline' => (int) $result->ts
        ];
        $item = new CacheItem(
            $this->_id, $data, true, $cache, (new \DateTime())->add(new \DateInterval("PT1H"))
        );
        $cache->saveDeferred($item);

        return $data['value'];
    }

    public function setState($value): bool
    {
        $obj = new \StdClass();
        $obj->v = $value;

        if($this->_container->getConnection()->setData($obj, ["device", $this->_channel->getId(), $this->_name, "~pv"])) {
            $cache = $this->_container->getCacheManager()->getItemPool("parameters");
            $data = [
                'value' => $value,
                'dateline' => (int) time()
            ];
            $item = new CacheItem(
                $this->_id, $data, true, $cache, (new \DateTime())->add(new \DateInterval("PT1H"))
            );
            $cache->saveDeferred($item);
        }

        return false;
    }
}