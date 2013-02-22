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

class ProjectController extends ControllerBase
{
    public function viewAction($id=null, $task_id=null)
    {
        if (is_null($id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $project = Project::findFirst('id = "' . $id . '"');

        if (!$project) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($this->currentUser)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $allProjectTasks = $project->getAllTasks();

        if (count($allProjectTasks) > 0) {
            if (is_null($task_id)) {
                $currentTask = $allProjectTasks[0];
            }
            else {
                $currentTask = Task::findFirst('id = "' . $task_id . '"');

                if (!$currentTask) {
                    $this->response->redirect('project/view/' . $project->id);
                    $this->view->disable();
                    return;
                }
            }
        }

        NotificationHelper::markProjectRead($this->currentUser->id, $project->id);

        if ($currentTask) {
            NotificationHelper::markTaskRead($this->currentUser->id, $currentTask->id);
        }

        $this->view->setVar('currentProject', $project);
        $this->view->setVar('allProjectTasks', $allProjectTasks);
        $this->view->setVar('currentTask', $currentTask);
        $this->view->setVar('body_id', 'project_tasks');

        if ($currentTask) {
            $this->view->setVar('url_params', $project->id . '/' . $currentTask->id);
        }
        else {
            $this->view->setVar('url_params', $project->id);
        }

        if ($currentTask) {
            Phalcon\Tag::setTitle($project->name . ' | ' . $currentTask->title);
        }
        else {
            Phalcon\Tag::setTitle($project->name);
        }
    }

    public function notesAction($project_id, $note_id = null)
    {
        if (is_null($project_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $project = Project::findFirst('id = "' . $project_id . '"');

        if (!$project) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($this->currentUser)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $allProjectNotes = $project->getAllNotes();

        if (count($allProjectNotes) > 0) {
            if (is_null($note_id)) {
                $currentNote = $allProjectNotes[0];
            }
            else {
                $currentNote = Note::findFirst('id = "' . $note_id . '"');

                if (!$currentNote) {
                    $this->response->redirect('project/notes/' . $project->id);
                    $this->view->disable();
                    return;
                }
            }
        }

        NotificationHelper::markProjectRead($this->currentUser->id, $project->id);

        if ($currentNote) {
            NotificationHelper::markNoteRead($this->currentUser->id, $currentNote->id);
        }

        $this->view->setVar('currentProject', $project);
        $this->view->setVar('allProjectNotes', $allProjectNotes);
        $this->view->setVar('currentNote', $currentNote);
        $this->view->setVar('body_id', 'project_notes');

        if ($currentNote) {
            $this->view->setVar('url_params', $project->id . '/' . $currentNote->id);
        }
        else {
            $this->view->setVar('url_params', $project->id);
        }

        if ($currentNote) {
            Phalcon\Tag::setTitle($project->name . ' | ' . $currentNote->title);
        }
        else {
            Phalcon\Tag::setTitle($project->name);
        }
    }

    public function getusersajaxAction($id=null)
    {
        $return = array();

        if (is_null($id)) {
            echo json_encode($return);
            $this->view->disable();
            return;
        }

        $project = Project::findFirst('id = "' . $id . '"');

        if (!$project) {
            echo json_encode($return);
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($this->currentUser)) {
            echo json_encode($return);
            $this->view->disable();
            return;
        }

        $projectUsers = $project->getProjectUsers();

        foreach($projectUsers AS $projectUser) {
            $temp = array();
            $temp['value'] = $projectUser->id;
            $temp['text'] = $projectUser->full_name;

            $return[] = $temp;
        }

        echo json_encode($return);
        $this->view->disable();
    }

    public function createprojectAction()
    {
        if ($this->request->isPost()) {
            if (!$this->currentUser->isAdmin()) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            $name = $this->request->getPost('name');
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if (!$name) {
                $this->flashSession->error('Project name should be specified');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $project = new Project();
            $project->name = htmlspecialchars($name);
            $project->created_by = $this->currentUser->id;
            $project->created_at = new Phalcon\Db\RawValue('now()');
            $project->status = 1;

            if (!$project->save()) {
                foreach ($project->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            // Let's add all admin users to this project.
            $admins = User::find('role_id = "1"');

            foreach ($admins AS $admin) {
                $projectUser = new ProjectUser();
                $projectUser->user_id = $admin->id;
                $projectUser->project_id = $project->id;
                $projectUser->created_at = new Phalcon\Db\RawValue('now()');

                $projectUser->save();
            }

            NotificationHelper::newProjectNotification($project, $this->currentUser);

            $this->response->redirect('project/view/' . $project->id);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }

    public function newtaskAction($project_id = null)
    {
        if ($this->request->isPost()) {
            $project = Project::findFirst('id = "' . $project_id . '"');

            if (!$project) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            if (!$project->isInProject($this->currentUser)) {
                $this->response->redirect('dashboard/index');
                $this->view->disable();
                return;
            }

            $title = $this->request->getPost('title');
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if (!$title) {
                $this->flashSession->error('Task title should be specified');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $task = new Task();
            $task->title = htmlspecialchars($title);
            $task->project_id = $project_id;
            $task->created_by = $this->currentUser->id;
            $task->created_at = new Phalcon\Db\RawValue('now()');
            $task->assigned_to = $this->currentUser->id;
            $task->status = 0;

            if (!$task->save()) {
                foreach ($task->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            $this->response->redirect('project/view/' . $project->id . '/' . $task->id);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }

    public function newnoteAction($project_id = null)
    {
        if ($this->request->isPost()) {
            $project = Project::findFirst('id = "' . $project_id . '"');

            if (!$project) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            if (!$project->isInProject($this->currentUser)) {
                $this->response->redirect('dashboard/index');
                $this->view->disable();
                return;
            }

            $title = $this->request->getPost('title');
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if (!$title) {
                $this->flashSession->error('Note title should be specified');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $note = new Note();
            $note->title = htmlspecialchars($title);
            $note->project_id = $project_id;
            $note->user_id = $this->currentUser->id;
            $note->created_at = new Phalcon\Db\RawValue('now()');

            if (!$note->save()) {
                foreach ($note->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            NotificationHelper::newNoteNotification($project, $note);

            $this->response->redirect('project/notes/' . $project->id . '/' . $note->id);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }

    public function filesAction($project_id)
    {
        if (is_null($project_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $project = Project::findFirst('id = "' . $project_id . '"');

        if (!$project) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($this->currentUser)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $allProjectFiles = $project->getProjectFiles(false);
        $allTasksFiles = $project->getTaskFiles();

        NotificationHelper::markProjectRead($this->currentUser->id, $project->id);

        $this->view->setVar('currentProject', $project);
        $this->view->setVar('allProjectFiles', $allProjectFiles);
        $this->view->setVar('allTasksFiles', $allTasksFiles);
        $this->view->setVar('body_id', 'project_files');

        $this->view->setVar('url_params', $project->id);

        Phalcon\Tag::setTitle($project->name . ' | Files');
    }

    public function newfileAction($project_id)
    {
        if (is_null($project_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if ($this->request->isPost()) {
            $project = Project::findFirst('id = "' . $project_id . '"');

            if (!$project) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            if (!$project->isInProject($this->currentUser)) {
                $this->response->redirect('dashboard/index');
                $this->view->disable();
                return;
            }

            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if ($this->request->hasFiles() == true) {
                foreach ($this->request->getUploadedFiles() as $file) {
                    if (!$file->getName() || !$file->getTempName() || $file->getSize() == 0) {
                        continue;
                    }

                    $this->uploadHelper->uploadFile($this->currentUser->id, $file, $project->id);
                }

                $this->flashSession->success('File uploaded successfully');
            }

            $this->response->redirect($controller . '/' . $action . '#comment-' . $comment->id);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }
}
