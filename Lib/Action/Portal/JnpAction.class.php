<?php
 /**
 +------------------------------------------------------------------------------
 * Jnp控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class JnpAction extends Action {
	public function gallery()
	{
		if($_GET['keyword']) {
			$title = trim($_GET['keyword']);
			$where['description']  = array('like',"%$title%");
			$where['title']  = array('like',"%$title%");
			$where['bh']  = array('like',"%$title%");
			$where['color']  = array('like',"%$title%");
			$where['size']  = array('like',"%$title%");
			$where['cz']  = array('like',"%$title%");
			$where['_logic'] = 'or';
			$data['_complex'] = $where;
		}
		if ($_GET['jnpType']) {
			$data["jnpType"] = $_GET['jnpType'];
		}
		import("@.ORG.Page");
		$count = D("Jnp")->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = D("Jnp")->getRecentJnp($p -> firstRow, $p -> listRows, $data);
		
		$this -> assign('page', $page);
		$this->assign("list", $list);
		$this->assign("typeList", D("Jnp")->typeList);
		$this->display();
	}
}
?>