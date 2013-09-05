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
			->limit($p -> firstRow . " , " . $p -> listRows)->order('zz_log.updatetime desc')->select();
		$result = array();
		foreach ($list as $key => $value) {
			$result[] = D("Jnp")->getJnpByID($value['id']);
		}
		$this -> assign('page', $page);
		$this->assign("list",$result);
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
		$prolactin = M("zz_prolactin")->join("right join zz_degree on zz_degree.degreeID = zz_prolactin.whcd 
			RIGHT JOIN city ON zz_prolactin.jg_province = city.pid and 
			zz_prolactin.jg_city = city.cid 
			right join zz_upload on zz_upload.tablename='zz_prolactin' 
			and zz_upload.pid = zz_prolactin.id and zz_upload.tip like '%网照%' ")
			->where('zz_prolactin.id = ' . $_GET['prolactinID'])->find();
		$this->assign("ygbh", $prolactin['ygbh']);
		$this->assign("gzjy", $prolactin['gzjy']);
		$this->assign("path", $prolactin['path']);
		$this->assign("city", $prolactin['city']);
		$this->assign("age", date('Y') - substr($prolactin['birthday'], 0, 4));
		$this->assign("whcd",  $prolactin['degree']);
		$data['itemid'] = array("in", $prolactin["languages"]);
		$langs = M('zz_languages')->where($data)->select();
		foreach ($langs as $key => $value) {
			$langArr[] = $value['itemname'];	
		}
		$this -> assign("lang", implode($langArr, ','));
		$this -> assign("zhpj", $prolactin['remark']);
		$this->display();
	}
}
?>