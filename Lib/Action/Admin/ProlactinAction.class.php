<?php
/**
 +------------------------------------------------------------------------------
 * 催乳师控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class  ProlactinAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示催乳师列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$keyword = $_GET['name'];
		//$M = new Model();
		// $sql = "SELECT parent. Prolactinid,parent.ygbh,parent.updatetime, parent.name,parent.isHidden,sub.fieldA as contact,sub.type,sub.id
			// FROM  `zz_prolactin` AS parent, zz_contact AS sub
			// WHERE parent. ProlactinId = sub.no and parent.isHidden = 1 and parent.name like '%$keyword%' 
			// GROUP BY parent.name 
			// ORDER BY parent.updatetime DESC ,sub.id desc";
		$M = M("zzProlactin");
		if($keyword)
			$data["name"] = array("like","%$keyword%");
		if ($_GET['ygbh']) {
			$data["ygbh"] = array("like","%" . $_GET['ygbh'] . "%");
		}
		
		$count = $M->join("join zz_log on zz_log.tablename='zz_prolactin' and zz_log.no = zz_prolactin.id")
			->where($data)->count();
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->join("join zz_log on zz_log.tablename='zz_prolactin' and zz_log.no = zz_prolactin.id")
			->where($data)->limit($p -> firstRow . " , " . $p -> listRows)
			->order('zz_log.updateTime desc')->select();
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 新建催乳师
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addProlactin() {
		//是否已經存在於本地數據庫
		$prolactin = M('zzProlactin');
		$data = $prolactin -> create();
		$lang = $_POST['lang'];
		$data['languages'] = join(",", $lang);
		//获取POST数据
		$count = $prolactin-> where("name = '" . $_POST["name"] . "'") -> count();
		if ($count) {
			$this -> error("该催乳师名称已存在");
		}

		$key = $prolactin -> data($data) -> add();
		if ($key) {
			$logData["tablename"] = "zz_prolactin";
			$logData["no"] = $key;
			$logData["createUser"] = $_SESSION['loginName'];
			ZZLogModel::addLog($logData);
			$this -> success($key);
		} else {
			$this -> error('增加失敗');
		}
	}

	/**
	 +----------------------------------------------------------
	 * 新增联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addContact() {
		$json = $_POST["json"];
		$json = str_replace("\\","",$json);//apache低版本不会自动处理引号
		$json=json_decode($json);
		$arr = objectToArray($json->data);//若json带引号则无法取到
		if (count($arr) == 0) {
			$this -> error('无联系方式');
		} else {
			foreach ($arr as $value) {
				$contact = M("zz_contact");
				$contact -> no = $json -> no;
				$contact -> tableName = "zz_prolactin";
				$v = arrayToObject($value);
				$contact -> type = $v -> type;
				$contact -> fieldA = $v -> content;
				$key = $contact -> add(); 
				if (!$key) {
					$this -> error('联系方式新增失敗');
				}else{
					SysLogs::log("新增联系方式" . $key);
				}
			}
			$this -> success("新增成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 显示要编辑催乳师信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editProlactin() {
		$prolactinID = $_GET["id"];
		//编辑页面
		if ($prolactinID) {
			$prolactin = M('zzProlactin');
			$prolactinInfo = $prolactin -> where("id = " . $prolactinID) -> find();
			$this -> initOptions($prolactinInfo["jg_province"]);
			$this -> assign('jg_province', $prolactinInfo['jg_province']);
			$this -> assign('jg_city', $prolactinInfo["jg_city"]);
			$this -> assign('remark', $prolactinInfo["remark"]);
			$this -> assign("name", $prolactinInfo["name"]);
			$this -> assign("birthday", $prolactinInfo["birthday"]);
			$this -> assign("whcd", $prolactinInfo["whcd"]);
			$this -> assign("gzjy", $prolactinInfo["gzjy"]);
			$this -> assign("ygbh", $prolactinInfo["ygbh"]);
			$this -> assign("xzdz", $prolactinInfo["xzdz"]);
			$this -> assign("prolactinID", $prolactinID);
			$this -> assign("lang", $prolactinInfo["languages"]);
			
			//显示联系方式
			$contact = M("zz_contact");
			$contactArr = $contact -> where("no = " . $prolactinID . " and tableName = 'zz_prolactin'") -> order("id") -> select();
			
			$this -> assign("contactList", $contactArr);
			
			//显示缩略图
			$data['pid'] = $prolactinInfo["id"];
			$data['tablename'] = "zz_prolactin";
			$picList = D("Upload")->getFiles($data);
			$this->assign("picList",$picList);
			$this->assign("imageCount", count($picList));
			$this -> display();
		} else {
			$this -> initOptions();
			$no = $this->createYgbh();
			$this->assign("ygbh",$no);
			$this -> assign('jg_province', '20');//默认广东
			$this -> display();
		}
	}

	/**
	 +----------------------------------------------------------
	 * 初始化下拉选项
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	private function initOptions($pid) {
		//显示省份
		$provs = D('Provincial') -> getProvinces($pid);
		$this -> assign('provList', $provs);
		//显示城市 20代表广东pid
		if(!$pid){
			$pid = 20;
		}
		$cities = D('City') -> getCityByProv($pid);
		$this -> assign('cityList', $cities);
		//语言
		$M = M('zz_languages');
		$langAttr = $M -> getField("itemid,itemname");
		$this -> assign('langList', $langAttr);
		//文化程度
		$whcdList = M('zz_degree')->order("degreeID asc") -> getField("degreeID,degree");
		$this->assign('whcdList',$whcdList);
	}
    /**
	 +----------------------------------------------------------
	 * 根据省份查询城市
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCityByProv()
	{
		if($_POST['pid']){//页面切换省份
			$cities = D('City') -> getCityByProv($_POST['pid']);
			$this->success($cities);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 保存编辑的催乳师信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveProlactin() {
		//是否已經存在於本地數據庫
		$prolactin = M('zzProlactin');
		if ($_POST['name']) {//保存编辑信息
			$data = $prolactin->create();
			$data['languages'] = join(",", $_POST['lang']);
			$pathArr = explode($data['images'], ",");
			$result = $prolactin -> where("id='" . $_POST['prolactinID'] . "'") -> save($data);
			
			SysLogs::log("更新催乳师" . $POST["name"] );
			$logData["tablename"] = "zz_prolactin";
			$logData["no"] = $_POST['prolactinID'];
			$logData["operate"] = "update";
			$logData["updateUser"] = $_SESSION['loginName'];
			ZZLogModel::updateLog($logData);
		}
		//保存成功返回影响的记录数不成功返回false。有可能返回0
		if (is_int($result)) {
			$this -> success('保存成功');
		} else {
			$this -> error('保存失敗');
		}
	}

	//新增上传 
	public function addImage()
	{
		if($_POST["prolactinID"]){
			$value['tablename'] = "zz_prolactin";
			$value['type'] = "pic";
			$value['pid'] = $_POST["prolactinID"];
			$value['path'] = $_POST["path"];
			$result = D("Upload")->addFile($value);
			if ($result) {
				$this->success("upload success");
			} else {
				$this->error("upload failed");
			}
			
		}else{
			$this->error("no params");
		}
	}
	
	//删除上传
	public function removeImage()
	{
		if($_POST){
			$data['tablename'] = "zz_prolactin";
			$data['id'] = $_POST['id'];
			$result = D('Upload')->removeProlactinImage($data);
			if($result === TRUE){
				$this->success("图片删除成功");
			}else{
				$this->error($result);
			}
		}else{
			$this->error("no params");
		}
	}
	//更新上传文件简介
	public function updateTip()
	{
		if($_POST){
			$data['id'] = $_POST['id'];
			$data['tip'] = $_POST['tip'];
			$result = D('Upload')->updateFile($data);
			if($result){
				$this->success("成功保存图片说明");
			}else{
				$this->error("保存图片说明失败");
			}
		}else{
			$this->error("no params");
		}
	}
	
	
	//更新联系方式
	public function saveContact() {
		$json = $_POST["json"];
		$json = str_replace("\\","",$json);
		$json=json_decode($json);
		$arr = objectToArray($json->data);//若json带引号则无法取到
		if (count($arr) == 0) {
			$this -> error('无联系方式');
		} else {
			$contact = M("zz_contact");
			$contact -> where('no= ' . $json -> no) -> delete();
			SysLogs::log("删除联系方式,no=" . $json->no);
			//删除已有记录
			//新增编辑的记录
			foreach ($arr as $value) {
				$v = arrayToObject($value);
				$data['no'] = $json -> no;
				$data['type'] = $v -> type;
				$data['fieldA'] = $v -> content;
				$data['tableName'] = "zz_prolactin";
				if (!($contact -> add($data))) {
					$this -> error('联系方式保存失敗');
				}
			}
			SysLogs::log("新增联系方式,no=" . $json->no);
			$this -> success("保存成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除催乳师
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeProlactin() {
		$prolactinID = $_POST["id"];
		if ($prolactinID) {
			$data['pid'] = M('zz_prolactin') ->field('ygbh')-> where(" Prolactinid=" . $prolactinID) -> find();
			M('zz_prolactin') -> where("id=" . $prolactinID) -> delete();
			SysLogs::log("删除催乳师, Prolactinid=" . $prolactinID);
			$result = $this -> delContact($prolactinID);
			if (is_int($result)) {
				$picData['tablename'] = 'zz_prolactin';
				$picData['pic'] = $data['pid'];
				$result = D("Upload")->getFiles($picData);
				if($result){
					foreach ($result as $key => $value) {
						$fileData['id'] = $value['id'];
						$result = D('Upload')->removeProlactinImage($fileData);
						if ($result !== TRUE){
							$this -> error("图片删除失败");
						}
					}
					$this -> success('刪除成功');
				}else{
					$this -> success('刪除成功');//没有图片
				}
			} else {
				$this -> error('联系方式刪除失败');
			}
		} else {
			$this -> error('无参数');
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function delContact($no) {
		$result = M('zz_contact') -> where("no=" . $no . " and tableName = 'zz_prolactin'") -> delete();
		SysLogs::log("删除联系方式,no=" . no);
		return $result;
	}

	/**
	 +----------------------------------------------------------
	 * 删除图片
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeThumb() {
		$json = $_POST["json"];
		$json = str_replace("\\","",$json);
		$json=json_decode($json);
		$postData = objectToArray($json);//若json带引号则无法取到
		$s_thumb = $postData["thumbUrl"];
		$s_thumb = "Public/Uploads/ Prolactin/" . $s_thumb;
		$m_thumb = str_replace("s_", "m_", $s_thumb);
		if(unlink($s_thumb) && unlink($m_thumb)){
			$this -> success($postData["thumbUrl"]);
		}else{
			$this -> error("删除失败");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 上传图片
	 * 上传规则：
	 * 1 点击上传按钮上传图片并保存到zz_upload
	 * 2 点击删除按钮删除图片路径并从zz_upload删除对应记录
	 * 3 点击图片列表保存按钮，保存zz_upload的tip字段
	 * 4 点击图片列表up按钮，保存zz_upload的index字段
	 * 5 点击图片列表down按钮，保存zz_upload的index字段
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function upload() {
		if (!empty($_FILES)) {
			//如果有文件上传 上传附件
			$this -> _upload();
		} else {
			$this -> ajaxUploadResult("没有上传文件");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 上传图片
	 +----------------------------------------------------------
	 * @access protected
	 +----------------------------------------------------------
	 */
	protected function _upload() {
		import("ORG.Net.UploadFile");
		//导入上传类
		$upload = new UploadFile();
		//设置上传文件大小
		$upload -> maxSize = 3292200;
		//设置上传文件类型
		$upload -> allowExts = explode(',', 'jpg,gif,png,jpeg');
		//设置附件上传目录
		$upload -> savePath = 'Public/Uploads/ Prolactin/';
		//设置需要生成缩略图，仅对图像文件有效
		$upload -> thumb = true;
		// 设置引用图片类库包路径
		$upload -> imageClassPath = 'ORG.Util.Image';
		//设置需要生成缩略图的文件前缀
		$upload -> thumbPrefix = 'm_,s_';
		//生产2张缩略图 m是middle s是small
		//设置缩略图最大宽度
		$upload -> thumbMaxWidth = '400,100';
		//设置缩略图最大高度
		$upload -> thumbMaxHeight = '400,100';
		//设置上传文件规则
		$upload -> saveRule = uniqid;
		//删除原图
		$upload -> thumbRemoveOrigin = true;
		//执行上传操作
		if (!$upload -> upload()) {
			//ajax上传失败
			if ($this -> isAjax() && isset($_POST['_uploadFileResult'])) {
				$uploadSuccess = false;
				$ajaxMsg = $upload -> getErrorMsg();
			} else {
				//捕获上传异常
				$this -> error($upload -> getErrorMsg());
			}
		} else {
			//取得成功上传的文件信息
			$uploadList = $upload -> getUploadFileInfo();
			//添加水印
			foreach ($uploadList as $key => $file) {
				$savename[] = "s_" . $file['savename'];
				//获取生成的small缩略图文件名
				Image::water($file['savepath'] . 'm_' . $file['savename'], 'Public/style/img/zzlogo.png');
			}
			$uploadSuccess = true;
			$ajaxMsg = '上传成功';
			SysLogs::log("上传图片,filename=s_" . $file['savename']);
		}
		// 判断是否有Ajax方式上传附件
		// 并且设置了结果显示Html元素
		if ($this -> isAjax() && isset($_POST['_uploadFileResult'])) {
			// Ajax方式上传参数信息
			$info = Array();
			$info['success'] = $uploadSuccess;
			$info['message'] = $ajaxMsg;
			//设置Ajax上传返回元素Id
			$info['uploadResult'] = $_POST['_uploadFileResult'];
			if (isset($_POST['_uploadFormId'])) {
				//设置Ajax上传表单Id
				$info['uploadFormId'] = $_POST['_uploadFormId'];
			}
			if (isset($_POST['_uploadResponse'])) {
				//设置Ajax上传响应方法名称
				$info['uploadResponse'] = $_POST['_uploadResponse'];
			}
			if (!empty($uploadId)) {
				$info['uploadId'] = implode(',', $uploadId);
			}
			$info['savename'] = implode(',', $savename);

			$this -> ajaxUploadResult($info);
		}
		return;
	}

	/**
	 +----------------------------------------------------------
	 * Ajax上传页面返回信息
	 +----------------------------------------------------------
	 * @access protected
	 +----------------------------------------------------------
	 * @param array $info 附件信息
	 +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
	 * @throws ThinkExecption
	 +----------------------------------------------------------
	 */
	protected function ajaxUploadResult($info) {
		// Ajax方式附件上传提示信息设置
		// 默认使用mootools opacity效果
		//alert($info);		
		$show = '<script language="JavaScript" src="' . __ROOT__ . '/Public/js/mootools.js"></script><script language="JavaScript" type="text/javascript">' . "\n";
		$show .= ' var parDoc = window.parent.document;';
		$show .= ' var result = parDoc.getElementById("' . $info['uploadResult'] . '");';
		if (isset($info['uploadFormId'])) {
			$show .= ' parDoc.getElementById("' . $info['uploadFormId'] . '").reset();';
		}
		$show .= ' result.style.display = "block";';
		$show .= " var myFx = new Fx.Style(result, 'opacity',{duration:600}).custom(0.1,1);";
		if ($info['success']) {
			// 提示上传成功
			$show .= 'result.innerHTML = "<div style=\"color:#3333FF\"> 文件上传成功！</div>";';
			// 如果定义了成功响应方法，执行客户端方法
			// 参数为上传的附件id，多个以逗号分割
			if (isset($info['uploadResponse'])) {
				$show .= 'window.parent.' . $info['uploadResponse'] . '("' . $info['savename'] . '");';
			}
		} else {
			// 上传失败
			// 提示上传失败
			$show .= 'result.innerHTML = "<div style=\"color:#FF0000\"> 上传失败：' . $info['message'] . '</div>";';
		}
		$show .= "\n" . '</script>';
		//$this->assign('_ajax_upload_',$show);
		header("Content-Type:text/html; charset=utf-8");

		exit($show);
		return;
	}

	/**
	 +----------------------------------------------------------
	 * 根据ID返回指定ID记录
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getProlactinById() {
		$id = $_POST["id"];
		//编辑页面
		if ($id) {
			$M = M('zz_prolactin');
			$info = $M -> where(" Prolactinid = " . $id) -> find();
			$this->success(json_encode($info));
		}else{
			$this->error("查询失败");
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 选择查询列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function searchProlactin()
	{
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$M = M("zz_prolactin");
		//获取查询参数
		if($_GET['name']){
			$name = $_GET['name'];
			$data['name'] = array('like',"%$name%");
		}
		$count = $M->where($data)->count();
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M->where($data)->limit($p -> firstRow.','.$p -> listRows)->order("updatetime desc")->select();

		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}
	
	/**
     +----------------------------------------------------------
     * 生成編號
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	private function createYgbh()
	{
		//当前年份
		$date = getdate();
		$maxNo = M("zzProlactin")->max("ygbh");//获取最近的编号
		if($maxNo){//若存在
			$num = (int)substr($maxNo,13) + 1;
			if(strlen($num) == 1){
				$num = "00" . $num;
			}elseif(strlen($num) == 2){
				$num = "0" . $num;				
			}
			$maxNo = substr($maxNo,0,13) . $num;
		}else{
			$maxNo = "ZZ_Prolactin_"."001";
		}
		return $maxNo;
	}
	
	public function uploadImage()
	{
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			
			// Validate the file type
			$fileTypes = array('png','jpg','gif'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			$targetPath =  APP_PATH . 'Public/' . C('STORAGE_ROOT');
			$newName = Date("YmdHis") . substr($_FILES['Filedata']['name'], -4, 4);
			$targetFile = $targetPath . $newName;
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				$result = move_uploaded_file($tempFile, $targetFile);
				if ($result) {
					$file['path'] = C('STORAGE_ROOT') . $newName;
					$file['status'] = true;
					echo json_encode($file);
				}else 
					echo 'Upload Image Failed!';
			} else {
				echo 'Invalid Image type.';
			}
		}else {
			echo false;
		}
	}
}
?>