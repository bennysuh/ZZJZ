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
			->limit($p -> firstRow . " , " . $p -> listRows)
			->order('updateTime desc')->select();
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
		
		$data['updateTime'] = date('Y-m-d H:i:s');
		if ($data['id']) {
			unset($data['bh']);
			$M ->data($data)->save();
			SysLogs::log("更新纪念品,id=" . $data["id"]);
			$logData["tablename"] = "zz_jnp";
			$logData["no"] = $data["id"];
			$logData["operate"] = "update";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
			$this->success("更新成功");
		} else {
			if ((D("Jnp")->checkBh($data['bh']))) {
				$this->error("已存在此编号");
			}
			$result = $M ->data($data)->add();
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
	
	public function autoUpload()
	{
		$path =  "./Public/Uploads/jnp/source/test/";
		$subFolder = "target/tmx/"; // 修改的地方
		$targetPath = "./Public/Uploads/jnp/" . $subFolder;
		$handle = opendir($path);	// 打开路径
		if (!$handle) {
			return FALSE; 
		}
		$data['bh'] = array("like", "tmx_%"); // 修改地方
		$lastBh = D("Jnp")->where($data)->order("id desc")->getField("bh");
		$lastBh_id = substr($lastBh, -2);
		$key = $lastBh_id ? ($lastBh_id+1) : 1;
		while (false !== ($file = readdir($handle)))	// 循环读取目录中的文件名并赋值给$file
		{
			if ($file != "." && $file != "..")	// 排除当前路径和前一路径
			{
				unset($uploadData);
				unset($jnpData);
				$fileNameArr = explode(" ", $file);
				// 新增jnp log
				$jnpData['jnpType'] = D("Jnp")->typeList[2];
				$jnpData['years'] = '2013';
				$jnpData['bh'] = 'tmx_2013_' . $key++;// 修改地方
				$jnpData['cz'] = '参考样品';// 修改地方
				$jnpData['color'] = '样色';
				//$size = substr($fileNameArr[1], 0, -4);
				$jnpData['size'] = $size ? $size : '参考样品';
				$jnpData['title'] = $fileNameArr[0];
				$jnpData['description'] = $fileNameArr[0];
				if (!mb_check_encoding($jnpData['title'] , 'utf-8')){
					if (mb_check_encoding($jnpData['title'] , 'gbk')) {
						$jnpData['title'] = iconv('gbk', 'utf-8//IGNORE', $jnpData['title'] );
						$jnpData['description'] = iconv('gbk', 'utf-8//IGNORE', $jnpData['description'] );
					} else {
						dump("unknow code");
					}
		        }
		        $jnpData['description'] .= "-胎毛绣系列";// 修改地方
				$jnpData['updateTime'] = date('Y-m-d H:i:s');
				$jnpID = D("Jnp")->data($jnpData)->add();
				if (!$jnpID) {
					dump(M()->getLastSql());
					return false;
				}
				// copy image
				$sourcePath = $path . $file;
				$newName = $this->generalFileName() . ".jpg";
				if (!$this->createDir($targetPath)) {
					print_r("create dir failed:" . $targetPath);
					return FALSE;
				}
				$targetFile = $targetPath . $newName;
				
				if (!copy($sourcePath, $targetFile)) {
					echo "copy failed:" . $sourcePath . ',' . $targetFile;
					return false ;
				}
				
				// 创建缩略图
				$thumbResult = D("Upload")->createThumb($targetPath, $newName);
				if (!$thumbResult) {
					echo "create thumb failed!";
					return false;
				}
				
				// 新增upload log
				$uploadData['tablename'] = "zz_jnp";
				$uploadData['pid'] = $jnpID;
				$uploadData['tip'] = "big";
				$uploadData['path'] = $subFolder . $newName;
				$uploadData['type'] = "pic";
				$uploadData['sortIndex'] = 0;
				$uploadData['updateTime'] = date('Y-m-d H:i:s');
				$result = M("zz_upload")->add($uploadData);
				if (!$result) {
					echo "create upload error";
					return FALSE;
				}
				
				// 新增thumb log
				$uploadData['tip'] = "thumb";
				$uploadData['path'] = $subFolder . 's_' . $newName;
				$result = M("zz_upload")->add($uploadData);
				if (!$result) {
					echo "create upload error";
					return FALSE;
				}
			}
		}
		closedir($handle);
	}
	
	public function generalFileName()
	{
		$now = microtime_format();
		return date('YmdHis', $now[0]) . $now[1];
	}
	
	public function createDir($dir)
	{
		// 創建目錄
		if (!is_dir($dir)) {
			return mkdir($dir, 0755, true);
		}
		return TRUE;
	}
}
?>