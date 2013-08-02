<?php
/**
 +------------------------------------------------------------------------------
 * EMAIL model控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class EmailModelAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['modelTitle'])
		{
			$title = $_GET['modelTitle'];
			$where['modelContent']  = array('like',"%$title%");
			$where['modelTitle']  = array('like',"%$title%");
			$where['_logic'] = 'or';
			$data['_complex'] = $where;
		}
		
		$M = M("zz_emailmodel");
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updateTime desc")->select();
		
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
	public function addEmailModel() {
		//是否已經存在於本地數據庫
		$M = M("zz_emailmodel");
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增email模板,id=" . $key);
			$logData["tablename"] = "zz_emailmodel";
			$logData["no"] = $key;
			ZZLogModel::addLog($logData);
			$this -> success("新增成功");
		} else {
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
	public function editEmailModel()
	{
		$id = $_GET['id'];//已有记录
		
		if($id){//assess列表过来的
			$M = M("zz_emailmodel");
			$result = $M->where("id=" . $id)->find();
			if($result){
				$this->assign("id",$id);
				$this->assign("modelTitle",$result['modelTitle']);
				$this->assign("modelContent",$result['modelContent']);
				$this->assign("modelType",$result['modelType']);
				$this->display();
			}else{
				$this->error("无此Email模板");
			}
		}else{
			$this->display();
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveEmailModel(){
		$M = M('zz_emailmodel');
		$data = $M->create();
		if($data){
			$result = $M ->data($data)->save();
			if(is_int($result)){
				SysLogs::log("更新email model,id=" . $data["id"]);
				$logData["tablename"] = "zz_emailmodel";
				$logData["no"] = $data["id"];
				$logData["operate"] = "update";
				$logData["updateUser"] = $_SESSION['loginName'];
				ZZLogModel::updateLog($logData);
				$this->success("更新成功");
			}else{
				$this->error("保存失败！");
			}
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
	public function removeEmailModel() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_emailmodel') -> where("id=" . $id) -> delete();
			SysLogs::log("删除zz_emailmodel,id=" . $id);
			$logData["tablename"] = "zz_email";
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

	/**
	 +----------------------------------------------------------
	 * 获取ajax emailmodel信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getEmailModelByID()
	{
		$id = $_POST['id'];
		if($id){
			$result = M("zz_emailmodel")->where("id=" . $id)->find();
			if($result){
				$this->success($result);
			}else{
				$this->error("无此Email模板");
			}
		}else{
			$this->error("no params!");
		}
	}
	 
}
?>