<?php

class FilesController extends ControllerBase
{
	public function getAction($projectId=null)
	{
		if (is_null($projectId)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = Project::findFirst('id="' . $projectId . '"');

		if (!$project) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if (!$project->isInProject($this->currentUser)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$return = array();

		$uploads = Upload::find('project_id = "' . $projectId . '"');

		if (count($uploads) > 0) {
			foreach($uploads AS $upload) {
				$fileName = $upload->filename;
				$fileUrl = 'uploads/' . $projectId . '/' . $fileName;

				$temp = array();

				$temp['name'] = $upload->filename;
				$temp['size'] = (int)$upload->size;
				$temp['type'] = $upload->type;
				$temp['url'] = $this->url->get($fileUrl);

				if (in_array($upload->type, array('image/jpeg', 'image/png', 'image/gif'))) {
					$temp['thumbnail_url'] = $this->url->get($fileUrl);
				}

				$temp['uploaded_by'] = $upload->getUser()->full_name;
				$temp['uploaded_at'] = $upload->uploaded_at;

				if ($upload->user_id == $this->currentUser->id || $this->currentUser->id == 1) {
					$temp['delete_url'] = $this->url->get('files/delete/');
					$temp['delete_type'] = 'POST';
				}

				$return['files'][] = $temp;
			}
		}

		echo json_encode($return);
		$this->view->disable();
		return;
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

	protected function _getUniqueFileName($fileName, $dir)
	{
		while (is_file($dir . $fileName)) {
			$fileName = $this->_upcountName($fileName);
		}

		return $fileName;
	}

	public function postAction($projectId=null)
	{
		if (!$this->request->isPost()) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if (is_null($projectId)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = Project::findFirst('id="' . $projectId . '"');

		if (!$project) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if (!$project->isInProject($this->currentUser)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if ($this->request->hasFiles() == true) {
			foreach ($this->request->getUploadedFiles() as $file) {
				$projectDir = $this->UploadDir . $projectId . '/';

				if (!is_dir($projectDir)) {
					mkdir($projectDir);
				}

				$fileName = $this->_getUniqueFileName(current($file->getName()), $projectDir);
				$filePath = $projectDir . $fileName;
				$fileUrl = 'uploads/' . $projectId . '/' . $fileName;
				$size = current($file->getSize());

				move_uploaded_file(current($file->getTempName()), $filePath);

				$finfo = new finfo;
				$type = $finfo->file($filePath, FILEINFO_MIME_TYPE);

				$upload = new Upload();
				$upload->filename = $fileName;
				$upload->filepath = $filePath;
				$upload->type = $type;
				$upload->size = $size;
				$upload->user_id = $this->currentUser->id;
				$upload->project_id = $projectId;
				$upload->uploaded_at = new Phalcon\Db\RawValue('now()');

				if ($upload->save() == true) {
					$temp = array();
					$temp['name'] = $upload->filename;
					$temp['size'] = (int)$upload->size;
					$temp['type'] = $upload->type;
					$temp['url'] = $this->url->get($fileUrl);

					if (in_array($upload->type, array('image/jpeg', 'image/png', 'image/gif'))) {
						$temp['thumbnail_url'] = $this->url->get($fileUrl);
					}

					$temp['delete_url'] = $this->url->get('files/delete/');
					$temp['delete_type'] = 'POST';

					$return['files'][] = $temp;

					echo json_encode($return);
				}
			}

			$this->view->disable();
			return;
		}
	}
}
