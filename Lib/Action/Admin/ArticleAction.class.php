<?php
/**
 +------------------------------------------------------------------------------
 * 文章控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ArticleAction extends EntryAction {
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
		$typeList = M("configType")->order('orderID asc')->getField('id,value');
		
		$this -> assign('typeList', $typeList);
		$M = M("zz_article");
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = D("ArticleView")->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();
		
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
	public function addArticle() {
		//是否已經存在於本地數據庫
		$M = M("zz_article");
		$data = $M->create();
		$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
		if ($key) {
			SysLogs::log("新增article,id=" . $key);
			$logData["tablename"] = "zz_article";
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
	public function editArticle()
	{
		$id = $_GET['id'];//已有记录
		$typeList = M("configType")->order('orderID asc')->getField('id,value');
		$this -> assign('typeList', $typeList);
		if($id){//assess列表过来的
			$M = M("zz_article");
			$result = $M->where("articleid=" . $id)->find();
			if($result){
				$this->assign("id",$id);
				$this->assign("title",$result['title']);
				$this->assign("content",$result['content']);
				$this->assign("articleType",$result['articletype']);
				$this->display();
			}else{
				$this->error("无此article");
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
	public function saveArticle(){
		$M = M('zz_article');
		$data = $M->create();
		if($data){
			$result = $M ->data($data)->save();
			if(is_int($result)){
				SysLogs::log("更新article,id=" . $data["id"]);
				$logData["tablename"] = "zz_article";
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
	public function removeArticle() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_article') -> where("articleid=" . $id) -> delete();
			SysLogs::log("删除article,id=" . $id);
			$logData["tablename"] = "zz_article";
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