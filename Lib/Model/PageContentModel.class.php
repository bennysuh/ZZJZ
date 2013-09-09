<?php
class PageContentModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 获取pagecontent
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getPageContent($page) {
		if (!$page) {
			return false;
		}
		$result = M("zz_configpage")->field('id,title,content')->where("page='" . $page . "'")->find();
		return $result ? $result : FALSE;
	}
	
}

?>