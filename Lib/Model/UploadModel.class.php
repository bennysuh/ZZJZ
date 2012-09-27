<?php
class UploadModel extends Model {
	public function addFile($data)
	{
		if($data){
			$result = M("zz_upload")->data($data)->add();
			Log::write(M()->getLastSql());
			if($result){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public function updateFile($data)
	{
		if($data){
			$result = M("zz_upload")->data($data)->save();
			if($result){
				return true;
			}else{
				return false;
			}
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
}
?>