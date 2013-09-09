<?php
class UploadModel extends Model {
	public function addFile($data)
	{
		if($data){
			$result = M("zz_upload")->data($data)->add();
			if($result){
				return $result;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//更新tip 和isshow
	public function updateFile($data, $cond)
	{
		if($data){
			$result = M("zz_upload")->where($cond)->data($data)->save();
			if(is_int($result)){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function getFiles($data)
	{
		if($data){
			$result = M("zz_upload")->where($data)->order("sortIndex asc")->select();
			return $result ? $result : array();
		}else{
			return false;
		}	
	}
	
	/**
	 +----------------------------------------------------------
	 * 删除文件
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeFile($data) {
		return M("zz_upload")->where($data)->delete();
	}
	
	/**
	 +----------------------------------------------------------
	 * 删除文件
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeProlactinImage($data) {
		$file = M("zz_upload")->where($data)->find();
		$result = unlink(APP_PATH . "Public" . $file['path']);
		if ($result) {
			$result = M("zz_upload")->where($data)->delete();
			return $result ? TRUE : FALSE;
		} else {
			return "delete image failed";
		}
	}
	
	public function removeImage($tablename, $id)
	{
		$data['tablename'] = $tablename;
		$data['pid'] = $id;
		$files = M("zz_upload")->where($data)->select();
		foreach ($files as $key => $value) {
			$result = $this->removeFileByID($value['id']);
			if (!$result) {
				return FALSE;
			}
		}
		return TRUE;
	}
	
	//更新tip 和isshow
	public function updateTip($data)
	{
		if (!$data) return FALSE;
		$result = M("zz_upload")->data($data)->save();
		return is_int($result);
	}
	
	private function getParentFolder($tablename) 
	{
		$folder = "";
		switch ($tablename) {
			case "zz_jnp":
				$folder = "jnp";
				break;
			case "zz_staff":
				$folder = "Staff";
				break;
			case "zz_prolactin":
				$folder = "prolactin";
				break;
			case "zz_zdg":
				$folder = "zdg";
				break;
		}
		return $folder;
	}
	
	public function removeFileByID($id)
	{
		$data['id'] = $id;
		$file = M("zz_upload")->where($data)->find();
		$folder = $this->getParentFolder($file['tablename']);
		try{
			unlink(C("PHOTO_PATH") . $folder . "/" . $file['path']);
			unlink(C("PHOTO_PATH") . $folder . "/s_" . $file['path']);
			return M("zz_upload")->where($data)->delete();
		}catch(Exception $e){
			 throw new Exception('Delete File Failed');
		}
	}
	
	public function getLastIndex($tablename, $pid)
	{
		$data['tablename'] = $tablename;
		$data['pid'] = $pid;
		return $this->where($data)->max('sortIndex');
	}
	
	public function createThumb($sourcePath, $sourceName, $targetName, $targetMaxWidth, $targetMaxHeight)
	{
		import("@.ORG.Image");
		$image = new Image();
		$targetName = 's_' . $sourceName;
		$sourceFile= $sourcePath . $sourceName;
		$targetWidth = $targetWidth ? $targetWidth : 150;
		$targetHeight = $targetHeight ? $targetHeight : 150;
		$result = $image::thumb($sourceFile, $sourcePath . $targetName, '', $targetWidth, $targetHeight);
		return $result;
	}
}
?>