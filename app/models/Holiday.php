<?php

class Holiday extends Phalcon\Mvc\Model
{
	public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    static function getFutureHolidays()
    {
    	$holidays = Holiday::find(array(
    		'conditions' => 'date >= now()',
    		'order' => 'date ASC',
    	));

    	return $holidays;
    }
}
