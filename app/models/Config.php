<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

class Config extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    static function getValue($path)
    {
        $return = null;

        $result = Config::findFirst('path="' . $path . '"');

        if ($result) {
            $return = $result->value;
        }

        return $return;
    }

    static function setValue($path, $value)
    {
        $node = Config::findFirst('path="' . $path . '"');

        if ($node) {
            $node->value = $value;
        }
        else {
            $node = new Config();
            $node->path = $path;
            $node->value = $value;
        }

        if ($node->save() == true) {
            return true;
        }

        return false;
    }

    static function keyExists($key)
    {
        $result = Config::findFirst('path = "' . $key . '"');

        if ($result) {
            return true;
        }
        else {
            return false;
        }
    }
}
