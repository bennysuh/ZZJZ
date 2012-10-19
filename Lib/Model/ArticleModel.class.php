<?php
class ArticleModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 获取article列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getArticleList($firstRow,$listRows) {
		$result = M("zz_article")->limit("$firstRow,$listRows")->order("updatetime")->select();
		return $result;
	}
	
}

?>