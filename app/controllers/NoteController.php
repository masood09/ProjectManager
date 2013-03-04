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

class NoteController extends ControllerBase
{
    public function updateajaxAction()
    {
        if ($this->request->isPost()) {
            $note_id = $this->request->getPost('pk');

            $note = Note::findFirst('id = "' . $note_id . '"');

            if (!$note) {
                $this->view->disable();
                return;
            }

            if (!$note->getProject()->isInProject($this->currentUser)) {
                $this->view->disable();
                return;
            }

            $data_name = $this->request->getPost('name');
            $value = $this->request->getPost('value');

            if ($data_name == 'title') {
                $note->title = $value;
                $note->save();
            }

            if ($data_name == 'content') {
                $note->content = $value;
                $note->updated_at = new Phalcon\Db\RawValue('now()');
                $note->save();
            }
        }

        $this->view->disable();
        return;
    }
}
