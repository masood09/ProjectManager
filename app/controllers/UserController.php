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

class UserController extends ControllerBase
{
    public function loginAction()
    {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email', 'string');
            $password = $this->request->getPost('password');

            $user = User::findFirst('email="' . $email . '" AND is_active="1"');

            if ($user) {
                $Bcrypt = new Bcrypt();

                if ($Bcrypt->verify($password, $user->password)) {
                    $this->session->set('user_id', $user->id);
                    $this->flashSession->success('Welcome ' . $user->full_name . '!');

                    $this->response->redirect('dashboard/index');
                    $this->view->disable();
                    return;
                }
            }

            $this->flashSession->error('Email and/or password incorrect.');
        }

        Phalcon\Tag::setTitle('Log in');
    }

    public function logoutAction()
    {
        $this->session->remove('user_id');
        $this->flashSession->success('You have been successfully logged out.');
        $this->response->redirect('user/login');
        $this->view->disable();
        return;
    }

    public function accountAction()
    {
        $this->view->setVar('body_id', 'user_account');
        Phalcon\Tag::setTitle('My Account');
    }

    public function leavesAction()
    {
        $this->view->setVar('availLeaves', $this->currentUser->getAvailLeavesCount());
        $this->view->setVar('body_id', 'user_leaves');
        Phalcon\Tag::setTitle('My Leaves');
    }

    public function getallleavesajaxAction()
    {
        $start = $this->request->getQuery('start');
        $end = $this->request->getQuery('end');
        $leavesArray = array();

        $leaves = Leaves::find(array(
            'conditions' => 'date >= "' . date('Y-m-d', $start) . '" AND date <= "' . date('Y-m-d', $end) . '" AND user_id = "' . $this->currentUser->id . '"',
            'order' => 'date DESC',
        ));

        foreach ($leaves AS $leave) {
            $temp = array();
            $temp['id'] = 'leave_' . $leave->id;
            $temp['title'] = $leave->getUser()->full_name;
            $temp['start'] = $leave->date;
            $temp['allDay'] = true;

            if (is_null($leave->approved)) {
                $temp['color'] = '#fcf8e3';
            }
            else if ($leave->approved == 1) {
                $temp['color'] = '#dff0d8';
            }
            else if ($leave->approved == 0) {
                $temp['color'] = '#f2dede';
            }

            $temp['textColor'] = '#333333';

            $temp['eventType'] = 'leave';

            $temp['leaveId'] = $leave->id;
            $temp['leaveDate'] = $leave->date;
            $temp['leaveReason'] = $leave->reason;
            $temp['leaveApproved'] = $leave->approved;

            $leavesArray[] = $temp;
        }

        $holidays = Holiday::find(array(
            'conditions' => 'date >= "' . date('Y-m-d', $start) . '" AND date <= "' . date('Y-m-d', $end) . '"',
            'order' => 'date DESC',
        ));

        foreach ($holidays AS $holiday) {
            $temp = array();
            $temp['id'] = 'holiday_' . $holiday->id;
            $temp['title'] = $holiday->name;
            $temp['start'] = $holiday->date;
            $temp['allDay'] = true;
            $temp['color'] = '#d9edf7';
            $temp['textColor'] = '#333333';

            $temp['eventType'] = 'holiday';

            $temp['holidayId'] = $holiday->id;
            $temp['holidayName'] = $holiday->name;
            $temp['holidayDate'] = $holiday->date;

            $leavesArray[] = $temp;
        }

        echo json_encode($leavesArray);

        $this->view->disable();
        return;
    }

    public function applyleaveAction()
    {
        if ($this->request->isPost()) {
            $from = $this->request->getPost('leavesFrom');
            $to = $this->request->getPost('leavesTo');
            $reason = $this->request->getPost('leavesReason');
            $user = $this->currentUser;

            if (!$user) {
                $this->view->disable();
                return;
            }

            if (!$reason) {
                $reason = '';
            }

            $appliedLeaves = array();
            $Bcrypt = new Bcrypt();
            $holidays = AttendanceHelper::getHolidays($from, $to);

            $counterFrom = strtotime($from);
            $counterTo = strtotime($to);

            while(strtotime('+1 day', $counterFrom) <= strtotime('+1 day', $counterTo)) {
                echo date('Y-m-d', $counterFrom) . "<br>";
                if (
                    !in_array(date('N', $counterFrom), $user->getWeekOffs())
                    && !in_array(date('Y-m-d', $counterFrom), $holidays)
                ) {
                    $appliedLeaves[] = date('Y-m-d', $counterFrom);
                }

                $counterFrom = strtotime('+1 day', $counterFrom);
            }

            foreach ($appliedLeaves AS $appliedLeave) {
                $leave = Leaves::findFirst('user_id = "' . $user->id . '" AND date = ' . $appliedLeave);

                if (!$leave) {
                    $leave = new Leaves();
                    $leave->user_id = $user->id;
                    $leave->date = $appliedLeave;
                    $leave->reason = $reason;
                    $leave->approved_by = $this->currentUser->id;
                    $leave->created_at = new Phalcon\Db\RawValue('now()');
                    $leave->save();
                }
            }

            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

    public function saveAction()
    {
        if ($this->request->isPost()) {
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'user';
                $action = 'account';
            }

            if ($this->request->hasFiles() == true) {
                foreach ($this->request->getUploadedFiles() as $file) {
                    if (!$file->getName() || !$file->getTempName() || $file->getSize() == 0) {
                        continue;
                    }

                    $extension = pathinfo($file->getName(), PATHINFO_EXTENSION);
                    $extension = strtolower($extension);

                    if (!in_array($extension, array('jpg', 'jpeg', 'png'))) {
                        $this->flashSession->error('You can only upload PNG or JPG files');
                        $this->response->redirect($controller . '/' . $action);
                        $this->view->disable();
                        return;
                    }

                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                            $ProfileImageOrig = imagecreatefromjpeg($file->getTempName());
                            break;
                        case 'png':
                            $ProfileImageOrig = imagecreatefrompng($file->getTempName());
                            break;
                    }

                    list($ProfileImageOrigWidth, $ProfileImageOrigHeight) = getimagesize($file->getTempName());

                    $ProfileImage = imagecreatetruecolor(60, 60);
                    imagecopyresampled($ProfileImage, $ProfileImageOrig, 0, 0, 0, 0, 60, 60, $ProfileImageOrigWidth, $ProfileImageOrigHeight);
                    $ProfileImagePath = __DIR__ . '/../../public/profile/' . $this->currentUser->id . '.jpg';
                    imagejpeg($ProfileImage, $ProfileImagePath, 90);
                    imagedestroy($ProfileImageOrig);
                    imagedestroy($ProfileImage);
                }
            }

            $full_name = $this->request->getPost('full_name');
            $email = $this->request->getPost('email');

            $this->currentUser->full_name = $full_name;
            $this->currentUser->email = $email;

            if (!$this->currentUser->save()) {
                foreach ($this->currentUser->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            $this->flashSession->success('Changes have been saved successfully!');

            $this->response->redirect($controller . '/' . $action);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

    public function changepasswordAction()
    {
        if ($this->request->isPost()) {
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'user';
                $action = 'account';
            }

            $old_password = $this->request->getPost('old_password');
            $new_password1 = $this->request->getPost('new_password1');
            $new_password2 = $this->request->getPost('new_password2');

            $Bcrypt = new Bcrypt();

            if (!$Bcrypt->verify($old_password, $this->currentUser->password)) {
                $this->flashSession->error('You did not enter the correct password');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            if ($new_password1 !== $new_password2) {
                $this->flashSession->error('Passwords do not match.');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $this->currentUser->password = $Bcrypt->hash($new_password1);

            if (!$this->currentUser->save()) {
                foreach ($this->currentUser->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            $this->flashSession->success('Password has been changed successfully!');

            $this->response->redirect($controller . '/' . $action);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }
}
