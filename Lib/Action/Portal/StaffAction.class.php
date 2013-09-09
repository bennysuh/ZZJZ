<?php
 /**
 +------------------------------------------------------------------------------
 * STAFF控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class StaffAction extends Action {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示月嫂列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$M = M('zz_staff');
		if ($_GET['level']) {
			$data['ysLevel'] = $_GET['level'];
		}
		$data['isHidden'] = 1;
		$count = $M->where($data)
				->join('RIGHT JOIN city ON zz_staff.jg_province = city.pid and zz_staff.jg_city = city.cid 
				right join zz_upload on zz_upload.tablename="zz_staff" and zz_upload.pid = zz_staff.ygbh 
					and zz_upload.tip like "%网照%" ')->count();
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->field("zz_staff.staffID,zz_staff.ygbh,zz_staff.name,zz_staff.ysLevel,zz_staff.birthday,zz_upload.path,city.city")
				->where($data)
				->join('RIGHT JOIN city ON zz_staff.jg_province = city.pid and zz_staff.jg_city = city.cid 
				right join zz_upload on zz_upload.tablename="zz_staff" and zz_upload.pid = zz_staff.ygbh 
					and zz_upload.tip like "%网照%" ')
		->limit($p -> firstRow.','.$p -> listRows)->select();
		foreach ($list as $key => $value) {
			$list[$key]['age'] = D("Staff")->changeBirthdayToAge($value['birthday']) . '岁';
			$list[$key]['ygbh'] = substr($value['ygbh'], -4);
		}
		$levelList = M("zz_stafflevel")->order('id desc')->select();
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this->assign("levelList", $levelList);
		$this -> display();
	}
	/**
	 +----------------------------------------------------------
	 * 月嫂详细资料
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function viewStaff()
	{
		if (!$_GET['staffID']) {
			$this->error("no staff ID to view");
		}
		$data['staffid'] = $_GET['staffID'];
		$staff = M("zz_staff")->join("right join zz_degree on zz_degree.degreeID = zz_staff.whcd 
			RIGHT JOIN city ON zz_staff.jg_province = city.pid and 
			zz_staff.jg_city = city.cid 
			right join zz_upload on zz_upload.tablename='zz_staff' 
			and zz_upload.pid = zz_staff.ygbh and zz_upload.tip like '%网照%' ")->where($data)->find();
		$this->assign("ygbh", $staff['ygbh']);
		$this->assign("gzjy", $staff['gzjy']);
		$this->assign("path", $staff['path']);
		$this->assign("city", $staff['city']);
		$this->assign("age", D("Staff")->changeBirthdayToAge($staff['birthday']) . '岁');
		$this->assign("whcd",  $staff['degree']);
		$this->assign("ysLevel", $staff['ysLevel']);
		$data['itemid'] = array("in", $staff["languages"]);
		$langs = D("Staff")->getLanguages($staff['languages']);
		$this -> assign("lang", $langs);
		$this -> assign("zhpj", $staff['remark']);
		$this->display();
	}
	
	public function selectYs()
	{
		$selectList = array();
		while ($a < 10) {
			$a++;
			$selectList[] = APP_NAME . "/Public/selectYs/" . $a . ".jpg";
		}
		$this->assign("pptList", $selectList);
		$this->display();
	}
}
?>