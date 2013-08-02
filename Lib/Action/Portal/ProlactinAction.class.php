<?php
 /**
 +------------------------------------------------------------------------------
 * prolactin控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ProlactinAction extends Action {
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
		//导入分页类
		$M = M('zz_prolactin');
		$data['turnon'] = 1;
		$count = $M->where($data)->count();
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->field("zz_prolactin.id,zz_prolactin.ygbh,zz_prolactin.name,zz_prolactin.birthday,zz_upload.path,city.city")->where($data)
		->join('RIGHT JOIN city ON zz_prolactin.jg_province = city.pid and zz_prolactin.jg_city = city.cid 
		right join zz_upload on zz_upload.tablename="zz_prolactin" and zz_upload.pid = zz_prolactin.id 
			and zz_upload.tip like "%网照%" ')
		->limit($p -> firstRow.','.$p -> listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['age'] = date('Y') - substr($value['birthday'], 0, 4);
		}
		
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}
	/**
	 +----------------------------------------------------------
	 * 催乳师详细资料
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function viewProlactin()
	{
		if (!$_GET['prolactinID']) {
			$this->error("no prolactin ID to view");
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