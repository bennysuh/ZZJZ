<?php
/**
 +------------------------------------------------------------------------------
 * 通知控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class NoticeAction extends Action {
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
			$data["title"] =  array('like',"%$title%");
		}
		$data['isPublish'] = 1;
		$M = M("zz_notice");
		import("@.ORG.Page");
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$result = D("NoticeView")
			->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updateTime desc")->select();
		foreach ($result as $key => $value) {
			$result[$key]['updateTime'] = substr($value['updateTime'], 0, 10);
		}
		$this -> assign('page', $page);
		$this->assign("list",$result);
		$this->display();
	}

	/**
	 +----------------------------------------------------------
	 * 显示新增/编辑信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function view()
	{
		$id = $_GET['id'];//已有记录
		if($id){//assess列表过来的
			$M = M("zz_notice");
			$result = $M->where("id=" . $id)->find();
			if($result){
				$this->assign("id",$id);
				$this->assign("title",$result['title']);
				$this->assign("content",$result['content']);
				$this->assign("updateTime",substr($result['updatetime'], 0, 10));
				$this->display();
			}else{
				$this->error("无此通知");
			}
		}else{
			$this->display();
		}
	}

}
?>