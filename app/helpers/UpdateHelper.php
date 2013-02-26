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

class UpdateHelper
{
    static function updateAppPre($version)
    {
        switch ($version) {
            case '1.0.0':
                $comments = Comment::find();

                foreach ($comments AS $comment) {
                    $message = preg_replace('{\r\n?}', "\n", $comment->comment);
                    $message = str_replace("\n", "<br>", htmlspecialchars($message));
                    $comment->comment = $message;
                    $comment->save();
                }

                $notes = Note::find();

                foreach ($notes AS $note) {
                    $message = preg_replace('{\r\n?}', "\n", $note->content);
                    $message = str_replace("\n", "<br>", htmlspecialchars($message));
                    $note->content = $message;
                    $note->save();
                }

                $projects = Project::find();

                foreach ($projects AS $project) {
                    $description = preg_replace('{\r\n?}', "\n", $project->description);
                    $description = str_replace("\n", "<br>", htmlspecialchars($description));
                    $project->description = $description;
                    $project->save();
                }

                $tasks = Task::find();

                foreach ($tasks AS $task) {
                    $task_id = $task->id;
                    $user_id = $task->created_by;
                    $message = preg_replace('{\r\n?}', "\n", $task->description);
                    $message = str_replace("\n", "<br>", htmlspecialchars($message));
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
                Config::setValue('attendance/days_target_time', '8');
                Config::setValue('attendance/leaves_per_month', '2');
                Config::setValue('attendance/leaves_per_quarter', '6');
                Config::setValue('attendance/leaves_per_year', '24');
                Config::setValue('attendance/leaves_method', 'quarter');
                Config::setValue('attendance/leaves_carries', '1');
                Config::setValue('attendance/weekoffs', '6,7');

                $users = User::find();

                foreach ($users AS $user) {
                    $user->leaves = $user->getAllocatedLeavesCount();
                    $user->leaves_assigned_on = new Phalcon\Db\RawValue('now()');
                    $user->save();
                }

                $projectUsers = ProjectUser::find();

                foreach ($projectUsers AS $projectUser) {
                    if ($projectUser->user_id != $projectUser->getProject()->created_by) {
                        $notification = new Notification();

                        $notification->user_id = $projectUser->user_id;
                        $notification->type = 'project';
                        $notification->type_id = $projectUser->project_id;
                        $notification->message = '<strong>' . $projectUser->getProject()->getUser()->full_name . '</strong> has added you to the project <strong>' . $projectUser->getProject()->name . '</strong>';
                        $notification->read = 1;
                        $notification->created_by = $projectUser->getProject()->getUser()->id;
                        $notification->created_at = $projectUser->created_at;

                        $notification->save();
                    }
                }

                $tasks = Task::find();

                foreach ($tasks AS $task) {
                    if ($task->assigned_to != $task->created_by) {
                        $notification = new Notification();

                        $notification->user_id = $task->assigned_to;
                        $notification->type = 'task';
                        $notification->type_id = $task->id;
                        $notification->message = '<strong>' . $task->getCreatedBy()->full_name . '</strong> has assigned the task <strong>' . $task->title . '</strong> of the project <strong>' . $task->getProject()->name . '</strong> to you';
                        $notification->read = 1;
                        $notification->created_by = $task->getCreatedBy()->id;
                        $notification->created_at = $task->created_at;

                        $notification->save();
                    }

                    $task->hours_spent = $task->calculateTotalTimeSpent();

                    $task->comments = count($task->getComments());

                    if ($task->status == 1) {
                        $task->closed_by = $task->assigned_to;
                    }
                    else {
                        $task->completed_on = null;
                    }

                    $task->save();
                }

                $comments = Comment::find();

                foreach ($comments AS $comment) {
                    foreach ($comment->getTask()->getTaskUser() AS $taskUser) {
                        if ($taskUser->user_id != $comment->user_id) {
                            $notification = new Notification();

                            $notification->user_id = $taskUser->user_id;
                            $notification->type = 'comment';
                            $notification->type_id = $comment->id;
                            $notification->message = '<strong>' . $comment->getUser()->full_name . '</strong> commented on your task <strong>' . $comment->getTask()->title . '</strong> : "' . substr(strip_tags($comment->comment), 0, 200) . '..."';
                            $notification->read = 1;
                            $notification->created_by = $comment->getUser()->id;
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
                            $notification->type = 'note';
                            $notification->type_id = $note->id;
                            $notification->message = '<strong>' . $note->getUser()->full_name . '</strong> added a new note to the project <strong>' . $note->getProject()->name . '</strong> : "' . substr(strip_tags($note->content), 0, 200) . '..."';
                            $notification->read = 1;
                            $notification->created_by = $note->getUser()->id;
                            $notification->created_at = $note->created_at;

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

    static function updateVersion($currentVersion, $targetVersion, $metadata, $authDetails = null)
    {
        $sqlDir = __DIR__ . '/../../install/sql/';
        $currentVersionArray = explode('.', $currentVersion);
        $maintainanceFile = __DIR__ . '/../../public/maintainance.flag';

        while (!($currentVersionArray[0] == $targetVersion['major'] &&
            $currentVersionArray[1] == $targetVersion['minor'] &&
            $currentVersionArray[2] == $targetVersion['patch']))
        {
            file_put_contents($maintainanceFile, 'maintainance mode');

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

            $metadata->reset();

            if (file_exists($sqlFileName)) {
                UpdateHelper::updateData($sqlFileName, $authDetails);
            }

            $metadata->reset();

            UpdateHelper::updateAppPost($updateVersion);
        }

        unlink($maintainanceFile);
    }
}
