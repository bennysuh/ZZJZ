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
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示催乳师列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		$M = M("zz_jnp");
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)
			->join("join zz_log on zz_log.tablename='zz_jnp' and zz_log.no = zz_jnp.id")
			->join("join zz_upload ON zz_upload.pid = zz_jnp.id AND zz_upload.tablename =  'zz_jnp' ")
			->limit($p -> firstRow . " , " . $p -> listRows)->order('zz_log.updatetime desc')->select();

		foreach ($list as $key => $value) {
			$list[$key]['path'] = D("Jnp")->host . $value['path'];
		}
		$this -> assign('page', $page);
		$this->assign("list", $list);
		$this->assign("typeList", D("Jnp")->typeList);
		$this->display();
	}
	
	/**
	 +----------------------------------------------------------
	 * 详细资料
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function view()
	{
		if (!$_GET['jnpID']) {
			$this->error("no ID to view");
		}
		$jnp = D("Jnp")->getJnpByID($_GET['jnpID']);
		$this->assign("jnpType", $jnp['jnpType']);
		$this->assign("title", $jnp['title']);
		$this->assign("description", $jnp['description']);
		$this->assign("years", $jnp['years']);
		$this->assign("photoList", $jnp['photos']);
		$this->assign("updateTime", $jnp['updateTime']);
		
		$this->display();
	}
	
	public function gallery()
	{
		$jnpType = $_GET['jnpType'];
		if (!$jnpType) {
			$jnpType = "胎毛笔";
		}
		$M = M("zz_jnp");
		$data['jnpType'] = $jnpType;
		$list = $M->where($data)
			->join("join zz_log on zz_log.tablename='zz_jnp' and zz_log.no = zz_jnp.id")
			->join("join zz_upload ON zz_upload.pid = zz_jnp.id AND zz_upload.tablename =  'zz_jnp' ")
			->order('zz_log.updatetime desc')->select();
		foreach ($list as $key => $value) {
			$list[$key]['path'] = D("Jnp")->host . $value['path'];
		}
		$this -> assign('page', $page);
		$this->assign("list", $list);
		$this->assign("typeList", D("Jnp")->typeList);
		$this->display();
	}
}
?>