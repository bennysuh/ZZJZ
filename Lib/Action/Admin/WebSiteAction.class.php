<?php
/**
 +------------------------------------------------------------------------------
 * 网站内容管理控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class WebSiteAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		$navList = M("zz_nav")->where("navType='top' and hasContent!=2")->order("position")->getField("navId,navName");
		$this->assign("navList",$navList);
		$this->display();
	}
	
	
	/**
	 +----------------------------------------------------------
	 * 更新
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveWebSite(){
		$M = M('zz_configpage');
		$data = $M->create();
		if($data){
			$result = $M ->data($data)->save();
			if(is_int($result)){
				SysLogs::log("更新zz_configpage,id=" . $data["id"]);
				$logData["tablename"] = "zz_configpage";
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
		
	public function getSubNav()
	{
		if($_POST['id']){
			$result = M("zz_nav")->field('navId,navName,hasContent')->where("navType='sub' and parentNav='" . $_POST['id'] . "' and hasContent!=2")->select();
			if(!$result){
				$result = M("zz_configpage")->field("title,content,id")->where("page='" . $_POST['id'] . "'")->find();	
				$result['type'] = "single";
			}
			$this->success($result);
		}else{
			$this->error("no param!");
		}
	}
	
	public function getWebSite()
	{
		if($_POST['subNavId']){
			$result = M("zz_configpage")->field('id,title,content')->where("page='" . $_POST['subNavId'] . "'")->find();
			$this->success($result);
		}else{
			$this->error("no param!");
		}
	}
}
?>