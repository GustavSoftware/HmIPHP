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

namespace Gustav\HmIPHP\Translation;

/**
 * This class is used for translation of some internal strings.
 *
 * @author Chris Köcher <ckone@fieselschweif.de>
 * @link   https://gustav.fieselschweif.de
 * @since  1.0.0
 */
abstract class ATranslator
{
    /**
     * The map of translations.
     *
     * @var string[]
     */
    protected array $_translations;

    /**
     * Translates the given string.
     *
     * @param string $key
     *   The string to translate
     * @return string
     *   The translated string (or the key if no translation is available)
     */
    public function translate(string $key): string
    {
        $lKey = strtolower($key);
        if(isset($this->_translations[$lKey])) {
            return $this->_translations[$lKey];
        }
        return $key;
    }

    /**
     * Translates the given string back to Homematic's representation.
     *
     * @param string $value
     *   The value to translate back
     * @return string
     *   The corresponding key (or the value if inverse translation is not possible)
     */
    public function inverseTranslate(string $value): string
    {
        $key = array_search($value, $this->_translations);
        if($key !== false) {
            return $key;
        }
        return $value;
    }
}