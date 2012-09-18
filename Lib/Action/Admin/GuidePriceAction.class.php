<?php
// +----------------------------------------------------------------------
// | ZZJZ [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://pms.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang 
// +----------------------------------------------------------------------
// $Id$
/**
 +------------------------------------------------------------------------------
 * GuidePrice
 +------------------------------------------------------------------------------
 * @author    davidhuang 
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class GuidePriceAction extends EntryAction
{
	/**
     +----------------------------------------------------------
     * 初始化列表頁面
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function index()
    {
    	import("@.ORG.Page");
    	$M =  M('zz_guideprice');
    	$count = $M->count();
    	$p = new Page($count,10);
		$page = $p -> show();
		$list = $M->limit($p->firstRow.','.$p->listRows)->order('year desc,month desc')->select();
		$this->assign("page",$page);
		$this->assign("list",$list);
		$this->display();
	}
	
	/**
     +----------------------------------------------------------
     * 保存編輯信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function saveGuidePrice()
    {
    	$M = M('zz_guideprice');
    	if($_POST['id']){
			if($M->create()){
				$data = $M->create();
				if($this->checkExit($data)){
					$this->error("已存在该年度月份指导价");
				}else{
					$result = $M->save();
				}
			}else{
				$error = $M->getError();
			}
    	}
		if(is_int($result)){
			$this->success("保存成功");
		}else{
			Log::write(M()->getLastSql());
			$this->error($error);
		}
	}
	/**
     +----------------------------------------------------------
     * 检验是否已有该项
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function checkExit($data)
	{
		$M = M('zz_guideprice');
		$isExit = $M->where("year=" . $data['year'] . " and month = " . $data['month'] )->count();
		//编辑状态如果返回的1条记录则说明是同条记录
		if($data['id'] && $isExit == 1){
			return false;
		}
		return $isExit;
	}
	/**
     +----------------------------------------------------------
     * 保存新增信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function addGuidePrice()
    {
    	$M = M('zz_guideprice');
		if($M->create()){
			$data = $M->create();
			if($this->checkExit($data)){
				$this->error("已存在该年度月份指导价");
			}else{
				$result = $M->add();
			}
		}else{
			$error = $M->getError();
			$this->error("数据提交失败");
		}
		if($result){
			$this->success("新增成功");
		}else{
			Log::write(M()->getLastSql());
			$this->error("新增失败");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeGuidePrice() {
		$id = $_POST["id"];
		if ($id) {
			$result = M('zz_guideprice') -> where("id=" . $id) -> delete();
			if(is_int($result))
				$this -> success("删除成功");
			else 
				$this -> error('删除失敗');
		} else {
			$this -> error('无ID参数');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 生成曲线统计图
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function exportChart()
	{
		$data['year'] = array("BETWEEN",$_POST['startYear'].",".$_POST['endYear']);
		$data['month'] = array("BETWEEN",$_POST['startMonth'].",".$_POST['endMonth']);
		$result = M("zz_guideprice")->where($data)->order("year asc,month asc")->select();
		if($result){
			import("@.ORG.Chart");
			$chart = new Chart();
			$xData = array();
			$yData = array();
			foreach ($result as $key => $value) {
				array_push($yData,$value['price']);
				array_push($xData,$value['year']."/".$value['month']);
			}
			$title = $_POST['startYear'] . "年" . $_POST['startMonth'] . "月至" . $_POST['endYear'] . "年" . $_POST['endMonth'] . "月指导价格折线图";
			$exportName = "Public/export/guidePrice.png";
			$chart->createChart($xData,$yData,$title,$exportName);
			$this->success("导出成功！");
		}else{
			Log::write(M()->getLastSql());
			$this->error("导出失败！");
		}
	}
}
?>