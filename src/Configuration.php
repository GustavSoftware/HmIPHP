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
use Gustav\HmIPHP\Translation\ATranslation;
use Gustav\HmIPHP\Translation\EnglishTranslation;

class Configuration
{
    private CacheConfiguration $_cacheConfig;
    private string $_baseUrl = "https://ccu3-webui:2122";
    private ATranslation $_translation;

    public function __construct()
    {
        $this->_cacheConfig = new CacheConfiguration();
        $this->_translation = new EnglishTranslation();
    }

    public function setCacheConfig(CacheConfiguration $cacheConfig): self
    {
        $this->_cacheConfig = $cacheConfig;
        return $this;
    }

    public function getCacheConfig(): CacheConfiguration
    {
        return $this->_cacheConfig;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->_baseUrl = $baseUrl;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->_baseUrl;
    }

    public function setTranslation(ATranslation $translation): self
    {
        $this->_translation = $translation;
        return $this;
    }

    public function getTranslation(): ATranslation
    {
        return $this->_translation;
    }
}