<?php

class SessionHelper
{
	static function generateSessionId($len = 32, $md5 = true)
    {
        $chars = array(
            'Q', '@', '8', 'y', '%', '^', '5', 'Z', '(', 'G', '_', 'O', '`',
            'S', '-', 'N', '<', 'D', '{', '}', '[', ']', 'h', ';', 'W', '.',
            '/', '|', ':', '1', 'E', 'L', '4', '&', '6', '7', '#', '9', 'a',
            'A', 'b', 'B', '~', 'C', 'd', '>', 'e', '2', 'f', 'P', 'g', ')',
            '?', 'H', 'i', 'X', 'U', 'J', 'k', 'r', 'l', '3', 't', 'M', 'n',
            '=', 'o', '+', 'p', 'F', 'q', '!', 'K', 'R', 's', 'c', 'm', 'T',
            'v', 'j', 'u', 'V', 'w', ',', 'x', 'I', '$', 'Y', 'z', '*'
        );

        $numChars = count($chars) - 1;
        $token = '';

        for ( $i=0; $i<$len; $i++ ) {
            $token .= $chars[ mt_rand(0, $numChars) ];
        }

        if ( $md5 ) {
            # Number of 32 char chunks
            $chunks = ceil( strlen($token) / 32 ); $md5token = '';

            # Run each chunk through md5
            for ( $i=1; $i<=$chunks; $i++ )
                $md5token .= md5( substr($token, $i * 32 - 32, 32) );

            # Trim the token
            $token = substr($md5token, 0, $len);
        }

        $session = Session::findFirst('session_id="' . $token . '"');

        if ($session) {
            SessionHelper::generateSessionId();
        }

        return $token;
    }

    static function registerSession($user)
	{
		// Let's fetch all the session for this user first and delete them.
		$sessions = $user->getSession();

		foreach($sessions AS $session) {
			$session->delete();
		}

		$session = new Session();
		$session->session_id = SessionHelper::generateSessionId();
		$session->user_id = $user->id;
		$session->created_at = new Phalcon\Db\RawValue('now()');
		$session->expiring_at = new Phalcon\Db\RawValue('ADDTIME(NOW(), "8:00:00")');

		if ($session->save() == true) {
			return $session->session_id;
		}

		return null;
	}

    static function destroySession($session_id)
    {
        $session = Session::findFirst('session_id = "' . $session_id . '" AND created_at <= ' . new Phalcon\Db\RawValue('now()') . ' AND expiring_at >= ' . new Phalcon\Db\RawValue('now()'));

        if ($session) {
            $sessions = $session->getUser()->getSession();

            foreach ($sessions AS $session) {
                $session->delete();
            }
        }
    }

	static function isLoggedIn($session_id)
    {
        $session = Session::findFirst('session_id = "' . $session_id . '" AND created_at <= ' . new Phalcon\Db\RawValue('now()') . ' AND expiring_at >= ' . new Phalcon\Db\RawValue('now()'));

        if ($session) {
            return true;
        }

        return false;
    }

    static function getUser($session_id)
    {
        $session = Session::findFirst('session_id = "' . $session_id . '" AND created_at <= ' . new Phalcon\Db\RawValue('now()') . ' AND expiring_at >= ' . new Phalcon\Db\RawValue('now()'));

        if ($session) {
            $user = $session->getUser();

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    static function getUserRole($session_id)
    {
        $user = SessionHelper::getUser($session_id);

        if ($user) {
            $role = $user->getRole();
            return $role;
        }

        return null;
    }

    static function isAdmin($session_id, $role=null)
    {
        if (is_null($role)) {
            $role = SessionHelper::getUserRole($session_id);
        }

        if (!is_null($role)) {
            if ($role->code === 'admin') {
                return true;
            }
        }

        return false;
    }
}
