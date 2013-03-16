<?php
// +----------------------------------------------------------------------
// | Common CMS
// +----------------------------------------------------------------------
// | Copyright (c) 2013  All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang <mchuang1140@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 分类
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1104@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class ArticleTypeAction extends EntryAction
{
	public function index()
    {
		$Model = M('config_type');
		$result = $Model->field('id,value,orderID,updateTime')->order('orderID asc')->select();
		$this->assign("list",$result);
		$this->display();
	}
	
	public function saveType()
	{
		$M = M('config_type');
		$data = $M->create();
		if($data){
			$result = $M ->data($data)->save();
			if(is_int($result)){
				SysLogs::log("更新分类,id=" . $data["id"]);
				$this->success("更新成功");
			}else{
				$this -> error('update error');
			}
			
		}else{
			$this -> error('保存失敗');
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 新增
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addType() {
		$M = M('config_type');
		$data = $M->create();
		$count = $M->where("value='" . $_POST['value'] . "'")->count();
		if($count != 0){
			$this -> error('已存在该分类');
		}else{
			$key = $M ->data($data)-> add();//获取新增返回的id值用于添加到联系方式表中
			if ($key) {
				SysLogs::log("新增行业分类,id=" . $key);
				$this -> success("新增成功");
			} else {
				$this -> error('增加失敗');
			}
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function delType() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('config_type') -> where("id=" . $id) -> delete();
			SysLogs::log("删除分类,id=" . $id);
			if(is_int($result))
				$this -> success("删除成功");
			else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无参数');
		}
	}
	
	
}
	