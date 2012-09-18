<?php
/**
 +------------------------------------------------------------------------------
 * 员工控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class StaffAction extends EntryAction {
	/**
	 +----------------------------------------------------------
	 * 根据查询条件显示员工列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function index() {
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$keyword = $_GET['name'];
		$M = new Model();
		$sql = "SELECT parent.staffid, parent.name,parent.isHidden,sub.fieldA as contact,sub.type,sub.id
			FROM  `zz_staff` AS parent, zz_contact AS sub
			WHERE parent.staffId = sub.no and parent.isHidden = 1 and parent.name like '%$keyword%' 
			GROUP BY parent.name 
			ORDER BY parent.updatetime DESC ,sub.id desc";
		$count = count($M -> query($sql));
		//分页
		$p = new Page($count, 10);
		$page = $p -> show();
		$list = $M -> query($sql . " LIMIT " . $p -> firstRow . " , " . $p -> listRows);
		$this -> assign('page', $page);
		$this -> assign('list', $list);
		$this -> display();
	}

	/**
	 +----------------------------------------------------------
	 * 新建员工
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function addStaff() {
		//是否已經存在於本地數據庫
		$staff = M('zz_staff');
		$data = $staff -> create();
		$lang = $_POST['lang'];
		$data['languages'] = join(",", $lang);
		//获取POST数据
		$count = $staff -> where("name = '" . $_POST["name"] . "'") -> count();
		if ($count) {
			$this -> error("员工名已存在");
		}

		$key = $staff -> data($data) -> add();
		if ($key) {
			//保存图片到zz_upload
			if($data["images"]){
				$imgArr = explode(",", $data['images']);
				$this->saveImages($imgArr);
			}
			$this -> success($key);
		} else {
			$this -> error('增加失敗');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 新增图片
	 +----------------------------------------------------------
	 * @access public
	 * @param $imgArr array 图片路径数组
	 +----------------------------------------------------------
	 */
	private function saveImages($imgArr)
	{
		$M = M("zz_upload");
		foreach ($imgArr as $key => $value) {
			$data["tablename"] = "zz_staff";
			$M->data($data)->add();	
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
				$contact -> tableName = "zz_staff";
				$v = arrayToObject($value);
				$contact -> type = $v -> type;
				$contact -> fieldA = $v -> content;
				if (!($contact -> add())) {
					$this -> error('联系方式新增失敗');
				}
			}
			$this -> success("新增成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 显示要编辑员工信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function editStaff() {
		$staffId = $_GET["staffId"];
		//编辑页面
		if ($staffId) {
			$staff = M('zz_staff');
			$staffInfo = $staff -> where("staffid = " . $staffId) -> find();
			$this -> initOptions($staffInfo["jg_province"]);
			$this -> assign('jg_province', $staffInfo['jg_province']);
			$this -> assign('jg_city', $staffInfo["jg_city"]);
			$this -> assign('address', $staffInfo["address"]);
			$this -> assign('remark', $staffInfo["remark"]);
			$this -> assign('ysLevel', $staffInfo["ysLevel"]);
			$this -> assign("name", $staffInfo["name"]);
			$this -> assign("birthday", $staffInfo["birthday"]);
			$this -> assign("whcd", $staffInfo["whcd"]);
			$this -> assign("gzjy", $staffInfo["gzjy"]);
			// $this -> assign("phone", $staffInfo["phone"]);
			$this -> assign("staffId", $staffId);
			$this -> assign("images", $staffInfo["images"]);
			$this -> assign("imgShowIndex", $staffInfo["imgShowIndex"]);
			$this -> assign("lang", $staffInfo["languages"]);
			if ($staffInfo["images"]) {
				$vo = explode(",", $staffInfo["images"]);
				$this -> assign("list", $vo);
			}
			//查询级别的enum。并转换为数组
			$M = M('zz_stafflevel');
			$levelAttr = $M -> getField("id,level");
			$this -> assign('levelVo', $levelAttr);
			//显示联系方式
			$contact = M("zz_contact");
			$contactArr = $contact -> where("no = " . $staffId . " and tableName = 'zz_staff'") -> order("id") -> select();
			$this -> assign("contactList", $contactArr);
			$this -> display();
			//不要显示2次display。
		} else {
			$this -> initOptions();
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
		//级别
		$M = M('zz_stafflevel');
		$levelAttr = $M -> getField("id,level");
		$this -> assign('levelVo', $levelAttr);
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
		$whcdList = array("小学","初中","高中","中专","大专","本科","硕士","其它");
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
	 * 保存编辑的员工信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveStaff() {
		//是否已經存在於本地數據庫
		$staff = M('zz_staff');
		if ($_POST['name']) {//保存编辑信息
			$data = $staff->create();
			$data['languages'] = join(",", $_POST['lang']);
			//david保存
		} else {
			$data["isHidden"] = $_POST['isHidden'];
			//列表中操作显示和隐藏员工状态
		}
		$result = $staff -> where("staffid='" . $_POST['staffId'] . "'") -> save($data);
		//保存成功返回影响的记录数不成功返回false。有可能返回0
		if (is_int($result)) {
			$this -> success('保存成功');
		} else {
			$this -> error('保存失敗');
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
			//删除已有记录
			//新增编辑的记录
			foreach ($arr as $value) {
				$v = arrayToObject($value);
				$data['no'] = $json -> no;
				$data['type'] = $v -> type;
				$data['fieldA'] = $v -> content;
				$data['tableName'] = "zz_staff";
				if (!($contact -> add($data))) {
					$this -> error('联系方式保存失敗');
				}
			}
			$this -> success("保存成功");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除员工
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function removeStaff() {
		$staffId = $_POST["staffId"];
		if ($staffId) {
			M('zz_staff') -> where("staffid=" . $staffId) -> delete();
			$result = $this -> delContact($staffId);
			if (is_int($result)) {
				$this -> success('刪除成功');
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
		$result = M('zz_contact') -> where("no=" . $no . " and tableName = 'zz_staff'") -> delete();
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
		$s_thumb = substr($s_thumb, strpos($s_thumb, "/")+1);
		$m_thumb = str_replace("s_", "m_", $s_thumb);
		Log::write($s_thumb . "," . $m_thumb);
		if(unlink($s_thumb) && unlink($m_thumb)){
			$this -> success($postData["index"]);
		}else{
			$this -> error("删除失败");
		}
	}

	/**
	 +----------------------------------------------------------
	 * 上传图片
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
		$upload -> savePath = 'Public/Uploads/Staff/';
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
			Log::write($upload -> getErrorMsg());
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
	public function getStaffById() {
		$id = $_POST["id"];
		//编辑页面
		if ($id) {
			$M = M('zz_staff');
			$info = $M -> where("staffid = " . $id) -> find();
			echo json_encode($info);
		}
	}
	
	/**
	 +----------------------------------------------------------
	 * 选择查询列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function searchStaff()
	{
		//根据URL参数查询用户
		import("@.ORG.Page");
		//导入分页类
		$M = M("zz_staff");
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
}
?>