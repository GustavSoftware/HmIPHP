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

namespace Gustav\HmIPHP;

use Gustav\Cache\Configuration as CacheConfiguration;
use Gustav\HmIPHP\Translation\ATranslator;
use Gustav\HmIPHP\Translation\EnglishTranslator;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

/**
 * This class contains the configurations of the controller.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 * @api
 */
class Configuration
{
    /**
     * The configuration of the cache.
     *
     * @var CacheConfiguration
     */
    private CacheConfiguration $_cacheConfig;

    /**
     * The URL to the CCU Jack.
     *
     * @var string
     */
    private string $_baseUrl = "https://ccu3-webui:2122";

    /**
     * The translation of the data. By default this is english.
     *
     * @var ATranslator
     */
    private ATranslator $_translator;

    /**
     * An HTTP client (as in PSR-18). By default this is the client from GuzzleHttp
     *
     * @var ClientInterface
     */
    private ClientInterface $_httpClient;

    /**
     * Constructor of this class.
     */
    public function __construct()
    {
        $this->_cacheConfig = new CacheConfiguration();
        $this->_translator = new EnglishTranslator();
        $this->_httpClient = new Client();
    }

    /**
     * Sets the configuration of the cache. Note that this must be set before the construction of the controller.
     *
     * @param CacheConfiguration $cacheConfig
     *   The configuration
     * @return $this
     *   This object
     */
    public function setCacheConfig(CacheConfiguration $cacheConfig): self
    {
        $this->_cacheConfig = $cacheConfig;
        return $this;
    }

    /**
     * Returns the configuration of the cache.
     *
     * @return CacheConfiguration
     *   The configuration
     */
    public function getCacheConfig(): CacheConfiguration
    {
        return $this->_cacheConfig;
    }

    /**
     * Sets the URL of the CCU Jack.
     *
     * @param string $baseUrl
     *   The URL
     * @return $this
     *   This object
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->_baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Returns the URL of the CCU Jack.
     *
     * @return string
     *   The URL
     */
    public function getBaseUrl(): string
    {
        return $this->_baseUrl;
    }

    /**
     * Sets the translation of the data.
     *
     * @param ATranslator $translation
     *   The translation object
     * @return $this
     *   This object
     */
    public function setTranslator(ATranslator $translation): self
    {
        $this->_translator = $translation;
        return $this;
    }

    /**
     * Returns the translation of the data.
     *
     * @return ATranslator
     *   The translation object
     */
    public function getTranslator(): ATranslator
    {
        return $this->_translator;
    }

    /**
     * Sets the HTTP client to use here.
     *
     * @param ClientInterface $client
     *   The client
     * @return $this
     *   This object
     */
    public function setHttpClient(ClientInterface $client): self
    {
        $this->_httpClient = $client;
        return $this;
    }

    /**
     * Returns the HTTP client to use here.
     *
     * @return ClientInterface
     *   The client
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->_httpClient;
    }
}