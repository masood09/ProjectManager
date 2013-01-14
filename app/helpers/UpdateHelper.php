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
				$projectUsers = ProjectUser::find();

				foreach ($projectUsers AS $projectUser) {
					if ($projectUser->user_id != $projectUser->getProject()->created_by) {
						$notification = new Notification();

						$notification->user_id = $projectUser->user_id;
						$notification->message = '<strong>' . $projectUser->getProject()->getUser()->full_name . '</strong> has added you to the project <strong>' . $projectUser->getProject()->name . '</strong>';
						$notification->project_id = $projectUser->project_id;
						$notification->read = 1;
						$notification->created_by = $projectUser->getProject()->created_by;
						$notification->created_at = $projectUser->created_at;

						$notification->save();
					}
				}

				$tasks = Task::find();

				foreach ($tasks AS $task) {
					if ($task->assigned_to != $task->created_by) {
						$notification = new Notification();

						$notification->user_id = $task->assigned_to;
						$notification->message = '<strong>' . $task->getProject()->getUser()->full_name . '</strong> has assigned the task <strong>' . $task->title . '</strong> of the project <strong>' . $task->getProject()->name . '</strong> to you';
						$notification->project_id = $task->project_id;
						$notification->task_id = $task->id;
						$notification->read = 1;
						$notification->created_by = $task->getProject()->created_by;
						$notification->created_at = $task->created_at;

						$notification->save();
					}
				}

				$comments = Comment::find();

				foreach ($comments AS $comment) {
					foreach ($comment->getTask()->getTaskUser() AS $taskUser) {
						if ($taskUser->user_id != $comment->user_id) {
							$notification = new Notification();

							$notification->user_id = $taskUser->user_id;
							$notification->message = '<strong>' . $comment->getUser()->full_name . '</strong> commented on your task <strong>' . $comment->getTask()->title . '</strong> : "' . substr(strip_tags(Markdown($comment->comment)), 0, 100) . '..."';
							$notification->project_id = $comment->getTask()->project_id;
							$notification->task_id = $comment->task_id;
							$notification->comment_id = $comment->id;
							$notification->read = 1;
							$notification->created_by = $comment->user_id;
							$notification->created_at = $comment->created_at;

							$notification->save();
						}
					}
				}

				$notes = Note::find();

				foreach ($notes AS $note) {
					foreach ($note->getProject()->getProjectUser() AS $projectUser) {
						if ($note->user_id != $projectUser->user_id) {
							$notification = new Notification();

							$notification->user_id = $projectUser->user_id;
							$notification->message = '<strong>' . $note->getUser()->full_name . '</strong> added a new note to the project <strong>' . $note->getProject()->name . '</strong> : "' . substr(strip_tags(Markdown($note->content)), 0, 100) . '..."';
							$notification->project_id = $note->project_id;
							$notification->note_id = $note->id;
							$notification->read = 1;
							$notification->created_by = $note->user_id;
							$notification->created_at = $note->created_at;

							$notification->save();
						}
					}
				}

				$uploads = Upload::find('task_id IS NULL AND comment_id IS NULL');

				foreach ($uploads AS $upload) {
					foreach ($upload->getProject()->getProjectUser() AS $projectUser) {
						if ($upload->user_id != $projectUser) {
							$notification = new Notification();

							$notification->user_id = $projectUser->user_id;
							$notification->message = '<strong>' . $upload->getUser()->full_name . '</strong> uploaded a new file to the project <strong>' . $upload->getProject()->name . '</strong> : "' . $upload->filename . '"';
							$notification->project_id = $upload->project_id;
							$notification->upload_id = $upload->id;
							$notification->read = 1;
							$notification->created_by = $upload->user_id;
							$notification->created_at = $upload->uploaded_at;

							$notification->save();
						}
					}
				}

				Config::setValue('core/version', $version);
				break;
			default:
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
