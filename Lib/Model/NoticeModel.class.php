<?php
class NoticeModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 获取article列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getNoticeList($firstRow,$listRows) {
		$result = M("zz_notice")->field("id,title,content")->limit("$firstRow,$listRows")->order("updatetime")->select();
		return $result;
	}
	/**
	 +----------------------------------------------------------
	 *获取总数
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCount()
	{
		return M("zz_notice")->count();
	}
}

?>