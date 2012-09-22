<?php
/**
 +------------------------------------------------------------------------------
 * 评价控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class AssessAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['staffId']){
			// //查询姓名
			// $M = M('zz_staff');
			// $rel = $M->field('name')->where('staffid='.$_GET['staffId'])->find();
			// $this->assign("staffName",$rel['name']);
			$data['staffId'] = $_GET['staffId'];			$M = M("zz_assess");
			import("@.ORG.Page");
			$count = $M->where($data)->count();
			$p = new Page($count, 10);
			$page = $p -> show();
			
			$M = D("AssessView");
			$result = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
			$this -> assign('page', $page);
			$this->assign("list",$result);
			Log::write(M()->getLastSql());
			$this->display();
		}else{
			$this->error("无STAFFID参数");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addAssess() {
		//是否已經存在於本地數據庫
		$M = M('zz_assess');
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
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
	public function editAssess()
	{
		$assessId = $_GET['assessId'];//已有记录
		$orderId = $_GET['orderId'];
		Log::write($assessId);
		if($assessId){//assess列表过来的
			$M = D('AssessView');
			$result = $M->where("zz_assess.id=" . $assessId)->find();
			if($result){
				$this->assign("staffId",$result['staffId']);
				$this->assign("orderId",$result['orderId']);
				$this->assign("staffName",$result['staffName']);
				$this->assign("wsxgLevel",$result['wsxgLevel']);
				$this->assign("yzczlLevel",$result['yzczlLevel']);
				$this->assign("zyjnLevel",$result['zyjnLevel']);
				$this->assign("fwtdLevel",$result['fwtdLevel']);
				$this->assign("assess",$result['assess']);
				$this->assign("id",$result['id']);
				$this->display();
			}else{
				Log::write(M()->getLastSql());
				$this->error("无此评价");
			}
		}else if($orderId){//contract列表过来的
			$M = M('zz_assess');
			//查询有无此记录
			$staffName = $_GET['staffName'];
			$staffId = $_GET['staffId'];
			$result = $M->where('orderId='. $orderId . " and staffId=" . $staffId)->find();
			Log::write(M()->getLastSql());
			if($result){
				//显示记录
				$this->assign("wsxgLevel",$result['wsxgLevel']);
				$this->assign("yzczlLevel",$result['yzczlLevel']);
				$this->assign("zyjnLevel",$result['zyjnLevel']);
				$this->assign("fwtdLevel",$result['fwtdLevel']);
				$this->assign("assess",$result['assess']);
				$this->assign("id",$result['id']);
			}
			$this->assign("staffName",$staffName);
			$this->assign("staffId",$staffId);
			$this->assign("orderId",$orderId);
			
			$this->display();
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
	public function saveAssess(){
		$M = M('zz_assess');
		$data = $M->create();
		if($data){
			$M ->data($data)->save();
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
	public function removeAssess() {
		$id = $_POST["assessId"];
		if ($id) {
			$result = M('zz_assess') -> where("id=" . $id) -> delete();
			
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