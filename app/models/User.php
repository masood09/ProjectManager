<?php

use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class User extends Phalcon\Mvc\Model
{
    public function validation()
    {
        $this->validate(new UniquenessValidator(array(
            'field' => 'email',
            'message' => 'The email is already registered. Try to log in using the email address.',
        )));

        if ($this->validationHasFailed() == true) {
            return false;
        }
    }
}
