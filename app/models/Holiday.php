<?php

class Holiday extends Phalcon\Mvc\Model
{
	public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }
}
