<?php
/**
 +------------------------------------------------------------------------------
 * 上传通用控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class  UploadAction extends EntryAction {
	private function getParentFolder($model)
	{
		switch ($model) {
			case "jnp":
				$folder = "jnp";
				break;
			case "staff":
				$folder = "Staff";
				break;
			case "prolactin":
				$folder = "prolactin";
				break;
			case "zdg":
				$folder = "zdg";
				break;
			default:
				$folder = "unknow";
				break;
		}
		return $folder;
	}
	
	
	public function uploadImage()
	{
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			
			// Validate the file type
			$fileTypes = array('png','jpg','gif'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$parentFolder = $this->getParentFolder($_POST['model']);
			$targetPath =  C("PHOTO_PATH") . $parentFolder . "/";
			$newName = Date("YmdHis") . substr($_FILES['Filedata']['name'], -4, 4);
			$targetFile = $targetPath . $newName;
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				$result = move_uploaded_file($tempFile, $targetFile);
				if ($result) {
					$file['path'] = $newName;
					$file['status'] = true;
					echo json_encode($file);
				}else 
					echo 'Upload Image Failed!';
			} else {
				echo 'Invalid Image type.';
			}
		}else {
			echo false;
		}
	}
	
	public function addImage()
	{
		if($_POST["pid"]){
			$value['tablename'] = $_POST["tablename"];
			$value['type'] = $_POST["type"];
			$value['pid'] = $_POST["pid"];
			$value['path'] = $_POST["path"];
			$sortIndex = D("Upload")->getLastIndex($_POST["tablename"], $_POST["pid"]);
			$value['sortIndex'] = ++$sortIndex;
			$result = D("Upload")->addFile($value);
			if ($result) {
				$this->success("upload success");
			} else {
				$this->error("upload failed");
			}
		}else{
			$this->error("no params");
		}
	}
	
	public function removeImage()
	{
		if($_POST){
			$result = D('Upload')->removeFileByID($_POST['id']);
			if($result){
				$this->success("图片删除成功");
			}else{
				$this->error($result);
			}
		}else{
			$this->error("no params");
		}
	}
}
?>