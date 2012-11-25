<?php
class UploadModel extends Model {
	public function addFile($data)
	{
		if($data){
			$result = M("zz_upload")->data($data)->add();
			SysLogs::log("上传文件,id=" . $result );
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
	public function updateFile($data,$cond)
	{
		if($data){
			$result = M("zz_upload")->where($cond)->data($data)->save();
			SysLogs::log("更新上传文件,id=" . $data["id"] );
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
			if($result){
				return $result;
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
		$result = M("zz_upload")->where($data)->delete();
		SysLogs::log("删除文件,id=" . $data["id"]);
		return $result;
	}
}
?>