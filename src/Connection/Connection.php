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
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * The connection to the CCU.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 * @internal
 */
class Connection
{
    /**
     * The configuration of the controller and this connection.
     *
     * @var Configuration
     */
    private Configuration $_config;

    /**
     * Constructor of this class.
     *
     * @param Configuration $config
     *   The configuration
     */
    public function __construct(Configuration $config)
    {
        $this->_config = $config;
    }

    /**
     * Returns the data from the CCU on the given path.
     *
     * @param string[] $path
     *   The path
     * @return mixed
     *   The returned data
     * @throws ConnectionException
     *   HTTP Error occurred
     */
    public function getData(array $path)
    {
        $url = $this->_config->getBaseUrl() . "/" . implode("/", $path);
        $request = new Request("GET", $url);

        return json_decode($this->_sendRequest($url, $request));
    }

    /**
     * Sets the data on some path on the CCU.
     *
     * @param mixed $data
     *   The new data
     * @param string[] $path
     *   The path
     * @return bool
     *   true, if connection was successful, false otherwise
     * @throws ConnectionException
     *   HTTP error occurred
     */
    public function setData($data, array $path): bool
    {
        $url = $this->_config->getBaseUrl() . "/" . implode("/", $path);
        $json = json_encode($data);
        $request = new Request(
            "PUT", $url, ['Content-Type: application/json','Content-Length: '.strlen($json)], $json
        );

        $this->_sendRequest($url, $request);
        return true;
    }

    /**
     * Sends a request to the given URL.
     *
     * @param string $url
     *   The URL
     * @param Request $request
     *   The request data
     * @return mixed
     *   The returned data
     * @throws ConnectionException
     *   HTTP error occurred
     */
    private function _sendRequest(string $url, Request $request)
    {
        try {
            $response = $this->_config->getHttpClient()->sendRequest($request);
        } catch(ClientExceptionInterface $e) {
            throw ConnectionException::requestError($url, $e);
        }

        if($response->getStatusCode() !== 200) {
            throw ConnectionException::errorCode($response->getStatusCode(), $url);
        }
        return $response->getBody();
    }
}