<?php
/**
 +------------------------------------------------------------------------------
 * 空檔期控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class FreeTimeAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['staffId'])
		{
			$data["staffId"] = $_GET["staffId"];
		}
		if($_GET['startDate'])
		{
			$data["startDate"] = $_GET["startDate"];
		}
		if($_GET['endDate'])
		{
			$data["endDate"] = $_GET["endDate"];
		}
		$M = M("zz_freetime");
		$data['staffId'] = $_GET['staffId'];
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
		$this -> assign('page', $page);
		$this->assign("list",$result);
		$this->display();
	}

	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addFreetime() {
		//是否已經存在於本地數據庫
		$M = M('zz_freetime');
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增空檔期,id=" . $key);
			$this -> success($key);
		} else {
			Log::write($M->getError());
			$this -> error('增加失敗');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 显示新增/编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editFreetime()
	{
		$freetimeId = $_GET['id'];//已有记录
		if($freetimeId){//assess列表过来的
			$M = D('AssessView');
			$result = $M->where("zz_assess.id=" . $assessId)->find();
			if($result){
				$this->assign("ygbh",$result['ygbh']);
				$this->assign("staffName",$result['staffName']);
				$this->assign("startDate",$result['startDate']);
				$this->assign("endDate",$result['endDate']);
				$this->display();
			}else{
				Log::write(M()->getLastSql());
				$this->error("无此空檔期");
			}
		}else{
			$this->error("缺少订单号");
		}
	}
	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveFreetime(){
		$M = M('zz_freetime');
		$data = $M->create();
		if($data){
			$M ->data($data)->save();
			SysLogs::log("更新空檔期,id=" . $data["id"]);
			$this->success("更新成功");
		}else{
			$this -> error('保存失敗');
		}
	}
	
	
	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeFreeTime() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_freetime') -> where("id=" . $id) -> delete();
			SysLogs::log("删除空檔期,id=" . $id);
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