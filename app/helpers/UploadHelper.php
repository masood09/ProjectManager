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

class UploadHelper extends Phalcon\Mvc\User\Plugin
{
    public function __construct($dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    protected function _upcountNameCallback($matches)
    {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function _upcountName($fileName)
    {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, '_upcountNameCallback'),
            $fileName,
            1
        );
    }

    public function getUniqueFileName($fileName, $fileDir)
    {
        while (is_file($fileDir . $fileName)) {
            $fileName = $this->_upcountName($fileName);
        }

        return $fileName;
    }

    public function uploadFile($user_id, $file, $project_id, $task_id = null, $comment_id = null)
    {
        if (!is_dir($this->UploadDir)) {
            mkdir($this->UploadDir);
        }

        $fileDir = $this->UploadDir . $project_id . '/';

        if (!is_dir($fileDir)) {
            mkdir($fileDir);
        }

        $fileName = $this->getUniqueFileName($file->getName(), $fileDir);
        $filePath = $fileDir . $fileName;
        $size = $file->getSize();

        move_uploaded_file($file->getTempName(), $filePath);

        $finfo = new finfo;
        $type = $finfo->file($filePath, FILEINFO_MIME_TYPE);

        $upload = new Upload();
        $upload->filename = $fileName;
        $upload->size = $size;
        $upload->user_id = $user_id;
        $upload->project_id = $project_id;

        if ($task_id) {
            $upload->task_id = $task_id;
        }

        if ($comment_id) {
            $upload->comment_id = $comment_id;
        }

        $upload->created_at = new Phalcon\Db\RawValue('now()');
        $upload->updated_at = new Phalcon\Db\RawValue('now()');

        if ($upload->save() == false) {
            foreach ($upload->getMessages() as $message) {
                $this->flashSession->error((string) $message);
            }
        }

        return;
    }
}
