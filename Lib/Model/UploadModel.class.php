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
	//更新tip 和isshow
	public function updateFile($data,$cond)
	{
		if($data){
			$result = M("zz_upload")->where($cond)->data($data)->save();
			Log::write(M()->getLastSql());
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
		return M("zz_upload")->where($data)->delete();
	}
}
?>