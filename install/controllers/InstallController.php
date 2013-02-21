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

class InstallController extends ControllerBase
{
    public function indexAction()
    {
        $this->response->redirect('install/start');
        $this->view->disable();
        return;
    }

    public function startAction()
    {
        $this->view->setVar('body_id', 'project_install_start');
    }

    public function configurationAction()
    {
        if ($this->request->isPost()) {

        }
        else {
            $this->response->redirect('');
            $this->view->disable();
            return;
        }
    }

    protected function _installSql($mysqlConnect, $extraSql = '')
    {
        $installSql = '';
        $sqlDir = __DIR__ . '/../../install/sql/';
        $currentVersionArray = explode('.', '0.0.0');
        $targetVersion = $this->AppVersion;

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

            if (file_exists($sqlFileName)) {
                $installSql .= file_get_contents($sqlFileName) . "\n\n";
            }
        }

        $sqlFileName = $sqlDir . 'install.sql';
        $installSql .= file_get_contents($sqlFileName) . "\n\n";
        $installSql .= $extraSql . "\n\n";

        $mysqlConnect->multi_query($installSql);
    }

    protected function _writeConfig($dbHost, $dbUsername, $dbPassword, $dbName)
    {
        $configFile = __DIR__ . '/../../app/config/config.xml';

        $xml = '
<config>
    <database>
        <host><![CDATA[' . $dbHost . ']]></host>
        <username><![CDATA[' . $dbUsername . ']]></username>
        <password><![CDATA[' . $dbPassword . ']]></password>
        <dbname><![CDATA[' . $dbName . ']]></dbname>
    </database>
</config>
        ';

        if (!file_put_contents($configFile, $xml)) {
            return false;
        }

        return true;
    }

    public function finishAction()
    {
        if ($this->request->isPost()) {
            $fullname = $this->request->getPost('userFullname');
            $email = $this->request->getPost('userEmail');
            $password1 = $this->request->getPost('userPassword1');
            $password2 = $this->request->getPost('userPassword2');

            $appName = $this->request->getPost('appName');
            $appEmail = $this->request->getPost('appEmail');

            if ($password1 != $password2) {
                $this->flashSession->error('Passwords do not match');
                $this->response->redirect('install/configuration');
                $this->view->disable();
                return;
            }

            $Bcrypt = new Bcrypt();
            $passwordHash = $Bcrypt->hash($password1);

            $dbHost = $this->request->getPost('dbHost');
            $dbName = $this->request->getPost('dbName');
            $dbUsername = $this->request->getPost('dbUsername');
            $dbPassword = $this->request->getPost('dbPassword');

            $mysqlConnect = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

            if (mysqli_connect_error()) {
                $this->flashSession->error('Database error: Please check your settings');
                $this->response->redirect('install/configuration');
                $this->view->disable();
                return;
            }

            $extraSql = '
                INSERT INTO `user`
                (
                    `full_name`,
                    `email`,
                    `password`,
                    `role_id`,
                    `is_active`,
                    `created_at`
                )
                VALUES
                (
                    "' . $mysqlConnect->real_escape_string($fullname) . '",
                    "' . $mysqlConnect->real_escape_string($email) . '",
                    "' . $passwordHash . '",
                    "1",
                    "1",
                    NOW()
                );

                INSERT INTO `config`
                (
                    `path`,
                    `value`
                )
                VALUES
                (
                    "core/name",
                    "' . $mysqlConnect->real_escape_string($appName) . '"
                ),
                (
                    "core/email",
                    "' . $mysqlConnect->real_escape_string($appEmail) . '"
                ),
                (
                    "core/baseurl",
                    "' . $this->baseUrl . '"
                );
            ';

            if ($this->_writeConfig($dbHost, $dbUsername, $dbPassword, $dbName)) {
                $this->_installSql($mysqlConnect, $extraSql);
            }

            $mysqlConnect->close();
            sleep(10);
        }

        $this->response->redirect('');
        $this->view->disable();
        return;
    }
}
