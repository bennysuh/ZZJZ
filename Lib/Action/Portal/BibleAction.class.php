<?php
 /**
 +------------------------------------------------------------------------------
 * Bible控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class BibleAction extends Action {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询
		import("@.ORG.Page");
		//导入分页类
		$M = M('zz_article');
		if ($_GET['title']) {
			$title = htmlspecialchars($_GET['title']);
			$where['title'] = array("like", "%$title%");
			$where['content'] = array("like", "%$title%");
			$where['_logic']  = "or";
			$data['_complex'] = $where;
		}
		
		$count = $M->where($data)->count();
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->select();
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}
	/**
	 +----------------------------------------------------------
	 * 月嫂详细资料
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function view()
	{
		if (!$_GET['id']) {
			$this->error("no bible ID to view");
		}
		$data['articleid'] = $_GET['id'];
		$article = M("zz_article")->where($data)->find();
		
		$this->assign("title", $article['title']);
		$this->assign("content", $article['content']);
		$this->assign("updateTime", $staff['updatetime']);
		$this->display();
	}
}
?>