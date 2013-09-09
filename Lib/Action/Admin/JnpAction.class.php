<?php
/**
 +------------------------------------------------------------------------------
 * 纪念品控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class JnpAction extends EntryAction {

	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
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
		if ($_GET['years']) {
			$data["years"] = $_GET['years'];
		}
		$M = M("zz_jnp");
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)
			->join("join zz_log on zz_log.tablename='zz_jnp' and zz_log.no = zz_jnp.id")
			->limit($p -> firstRow . " , " . $p -> listRows)->order('zz_log.updatetime desc')->select();
		$this -> assign('page', $page);
		$this->assign("list",$list);
		$this->assign("yearList", D("Jnp")->getYears());
		$this->assign("typeList", D("Jnp")->typeList);
		$this->display();
	}

	
	/**
	 +----------------------------------------------------------
	 * 显示新增/编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editJnp()
	{
		$jnpID = $_GET["id"];
		$this->assign("typeList", D("Jnp")->typeList);
		$this->assign("yearList", D("Jnp")->getYears());
		$this->assign("years", date("Y"));
		if ($jnpID) {
			$jnpInfo =  D("Jnp")->getJnpByID($jnpID);
			$this->assign("imageCount", count($jnpInfo['photos']));
			$this->assign("jnpID", $jnpInfo['id']);
			$this->assign("jnpType", $jnpInfo['jnpType']);
			$this->assign("years", $jnpInfo['years']);
			$this->assign("cz", $jnpInfo['cz']);
			$this->assign("size", $jnpInfo['size']);
			$this->assign("color", $jnpInfo['color']);
			$this->assign("bh", $jnpInfo['bh']);
			$this->assign("title", $jnpInfo['title']);
			$this->assign("description", $jnpInfo['description']);
			$this->assign("photoList", $jnpInfo['photos']);
		}
		$this->display();
	}


	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveJnp(){
		$M = M('zz_jnp');
		$data = $M->create();
		
		if (!$data) $this -> error('保存失敗');
		if ((D("Jnp")->checkBh($data['bh']))) {
			$this->error("已存在此编号");
		}
		$data['updateTime'] = date('Y-m-d H:i:s');
		if ($data['id']) {
			$M ->data($data)->save();
			SysLogs::log("更新纪念品,id=" . $data["id"]);
			$logData["tablename"] = "zz_jnp";
			$logData["no"] = $data["id"];
			$logData["operate"] = "update";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			$this->success("更新成功");
		} else {
			$result = $M ->data($data)->add();
			Log::write(M()->getLastSql());
			SysLogs::log("新增纪念品,id=" . $result);
			$logData["tablename"] = "zz_jnp";
			$logData["no"] = $result;
			$result = ZZLogModel::addLog($logData);
			if (!$result) $this->error("add log error");
			$this->success("新增成功");
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function delJnp() {
		$id = $_POST["id"];
		if ($id) {
			$tablename = "zz_jnp";
			// 删除图片
			D("Upload")->removeImage($tablename, $id);
			$data['id'] = $id;
			$result = D("Jnp")->where($data)->delete();
			SysLogs::log("删除纪念品,id=" . $id);
			$logData["tablename"] = $tablename;
			$logData["no"] = $id;
			$logData["operate"] = "delete";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			if(is_int($result))
				$this -> success("删除成功");
			else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
}
?>