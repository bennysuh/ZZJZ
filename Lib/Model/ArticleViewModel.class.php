<?php
class ArticleViewModel extends ViewModel {
	/**
	 +----------------------------------------------------------
	 * contract組合staff、customer的視圖模型
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public $viewFields = array(
		'zz_article' => array('articleid'=>'id','title','content','articleType'),
		'zz_log' => array('createUser','createTime','updateUser','updateTime','_on' => 'zz_log.tablename="zz_article" and zz_article.articleid=zz_log.no'),
		'config_type'=>array('value'=>'articleType','_on'=>'zz_article.articletype=config_type.id'),
	);
}
?>