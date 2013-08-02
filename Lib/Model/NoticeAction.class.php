<?php
/**
 +------------------------------------------------------------------------------
 * 通知控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class NoticeAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		if($_GET['title'])
		{
			$title = $_GET['title'];
			$where['content']  = array('like',"%$title%");
			$where['title']  = array('like',"%$title%");
			$where['_logic'] = 'or';
			$data['_complex'] = $where;
		}
		$M = M("zz_notice");
		import("@.ORG.Page");
		$count = $M->field("title,content,isPublish,id")->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = D("NoticeView")->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
		
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
	public function addNotice() {
		//是否已經存在於本地數據庫
		$M = M("zz_notice");
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增NOTICE,id=" . $key);
			$logData["tablename"] = "zz_notice";
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
	public function editNotice()
	{
		$id = $_GET['id'];//已有记录
		if($id){//assess列表过来的
			$M = M("zz_notice");
			$result = $M->where("id=" . $id)->find();
			if($result){
				$this->assign("id",$id);
				$this->assign("title",$result['title']);
				$this->assign("content",$result['content']);
				$this->assign("isPublish",$result['isPublish']);
				$this->display();
			}else{
				$this->error("无此NOTICE");
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
	public function saveNotice(){
		$M = M('zz_notice');
		$data = $M->create();
		if($data){
			$result = $M ->data($data)->save();
			if(is_int($result)){
				SysLogs::log("更新NOTICE,id=" . $data["id"]);
				$logData["tablename"] = "zz_notice";
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
	public function removeNotice() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_notice') -> where("id=" . $id) -> delete();
			SysLogs::log("删除notice,id=" . $id);
			$logData["tablename"] = "zz_notice";
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