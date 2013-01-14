<?php

class UpdateHelper
{
	static function updateAppPre($version)
	{
		switch ($version) {
			case '1.0.0':
				$tasks = Task::find();

				foreach ($tasks AS $task) {
					$task_id = $task->id;
					$user_id = $task->created_by;
					$message = $task->description;
					$created_at = $task->created_at;

					if (!is_null($message) || trim($message != '')) {
						$comment = new Comment();
						$comment->user_id = $user_id;
						$comment->task_id = $task_id;
						$comment->comment = $message;
						$comment->created_at = $created_at;
						$comment->save();

						$task->description = null;
						$task->save();
					}
				}
				break;
			default:
				break;
		}
	}

	static function updateAppPost($version)
	{
		switch ($version) {
			case '1.0.0':
				Config::setValue('core/version', $version);
				break;
			default:
				# code...
				break;
		}
	}

	static function updateData($fileName, $authDetails = null)
	{
		if (is_null($authDetails)) {
			$configFile = __DIR__ . '/../../app/config/config.xml';
			$config = simplexml_load_file($configFile, NULL, LIBXML_NOCDATA);

			$host = $config->database->host;
			$username = $config->database->username;
			$password = $config->database->password;
			$dbname = $config->database->dbname;

		}
		else {
			$host = $authDetails['host'];
			$username = $authDetails['username'];
			$password = $authDetails['password'];
			$dbname = $authDetails['dbname'];
		}

		$connection = new Phalcon\Db\Adapter\Pdo\Mysql(array(
			'host' => $host,
			'username' => $username,
			'password' => $password,
			'dbname' => $dbname,
		));

		$success = $connection->execute(file_get_contents($fileName));

		$connection->close();
	}

	static function updateVersion($currentVersion, $targetVersion, $authDetails = null)
	{
		$sqlDir = __DIR__ . '/../../install/sql/';
		$currentVersionArray = explode('.', $currentVersion);

		while (!($currentVersionArray[0] == $targetVersion['major'] &&
            $currentVersionArray[1] == $targetVersion['minor'] &&
            $currentVersionArray[2] == $targetVersion['patch']))
        {
        	if ($currentVersionArray[2] == 9) {
                $currentVersionArray[1]++;
                $currentVersionArray[2] = 0;
            }
            else {
                $currentVersionArray[2]++;
            }

            if ($currentVersionArray[1] > 9) {
                $currentVersionArray[0]++;
                $currentVersionArray[1] = 0;
            }

            $updateVersion = $currentVersionArray[0] . '.' . $currentVersionArray[1] . '.' . $currentVersionArray[2];
            $sqlFileName = $sqlDir . $updateVersion . '.sql';

            UpdateHelper::updateAppPre($updateVersion);

            if (file_exists($sqlFileName)) {
	    	   	UpdateHelper::updateData($sqlFileName, $authDetails);
            }

            UpdateHelper::updateAppPost($updateVersion);
        }
	}
}
