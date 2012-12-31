<?php

class Role extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
    	$this->hasMany('id', 'User', 'role_id');
    }
}
