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

namespace Gustav\HmIPHP\Connection;

use Gustav\HmIPHP\Configuration;

class Connection
{
    private Configuration $_config;

    public function __construct(Configuration $config)
    {
        $this->_config = $config;
    }

    public function getData(array $path)
    {
        $url = $this->_config->getBaseUrl() . "/" . implode("/", $path);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);

        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($code !== 200) {
            throw ConnectionException::errorCode($code, $url);
        }
        return json_decode($data);
    }

    public function setData($data, array $path): bool
    {
        $url = $this->_config->getBaseUrl() . "/" . implode("/", $path);
        $json = json_encode($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($json)]);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $return = curl_exec($curl);

        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($code !== 200) {
            throw ConnectionException::errorCode($code, $url);
        }

        return $return;
    }
}