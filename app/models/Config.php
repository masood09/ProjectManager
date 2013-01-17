<?php

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
