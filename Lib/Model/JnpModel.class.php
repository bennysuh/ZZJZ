<?php
class JnpModel extends Model {
	protected $trueTableName = "zz_jnp";
	public $host;
	
	public function __construct() {
		parent::__construct();
		$this->host =  APP_NAME . "/Public/Uploads/jnp/"; 
	}
	
	public $typeList = array(
		"胎毛笔",
		"胎毛绣",
		"胎毛画",
		"胎毛印章",
		"手足印",
		"挂坠",
		"其它",
	);
	
	public function getYears()
	{
		$currYear = date("Y");
		$years = array();
		$years[] = $currYear+1;
		$years[] = $currYear;
		while ($currYear > 2010) {
			$years[] = --$currYear;
		}
		return $years;
	}
	
	public function removeJnp($id)
	{
		$data['id'] = $id;
		$jnp = $this->where($data)->find();
		$photos = $this->getPhotosByID($id);
		foreach ($photos as $key => $value) {
			D("Upload")->removeFileByID($value['id']);
		}
		return $this->where($data)->delete();
	}
	
	public function getJnpByID($id)
	{
		$data['id'] = $id;
		$jnp = $this->where($data)->find();	
		$jnp['photos'] = $this->getPhotosByID($id);
		return $jnp;
	}
	
	private function getPhotosByID($id)
	{
		$photoData['tablename'] = $this->trueTableName;
		$photoData['pid'] = $id;
		$photoData['tip'] = "thumb";
		$photos = D("Upload")->getFiles($photoData);
		foreach ($photos as $key => $value) {
			$photos[$key]['thumb'] = $this->host . $value['path'];
		}
		return $photos;
	}
	
	public function checkBh($bh)
	{
		$data['bh'] = trim($bh);
		$result = $this->where($data)->count();
		return $result > 0 ? true : false;
	}
	
	public function getRecentJnp($first, $length, $cond)
	{
		$jnpList = $this->field("id, title, description")->where($cond)->limit($first . ',' . $length)->select();
		$ids = array();
		foreach ($jnpList as $key => $value) {
			$ids[] = $value['id'];
		}
		$uploadData['tablename'] = "zz_jnp";
		$uploadData['pid'] = array("in", $ids);
		$uploadData['tip'] = "big";
		$list = M("zz_upload")->where($uploadData)
			->order('zz_upload.updatetime desc')->select();
		$jnpInfo;
		foreach ($list as $key => $value) {
			$list[$key]['path'] = D("Jnp")->host . $value['path'];
			$jnpInfo = D("Jnp")->where("id={$value['pid']}")->find();
			$list[$key]['title'] = $jnpInfo['title'];
			$description =   $jnpInfo['title'] . "<br/>编号:" . $jnpInfo['bh'] . "<br/>" . ($jnpInfo['cz'] ? "材质:" . $jnpInfo['cz'] . "<br/>" : "")
				.  ($jnpInfo['color'] ? "颜色:" . $jnpInfo['color'] . "<br/>" : "") . ($jnpInfo['size'] ? "尺寸:" . $jnpInfo['size'] . "<br/>" : "");
			$list[$key]['description'] = $description;
			$fileName = substr(strrchr($value['path'],"/"), 1);
			$folder = substr($value['path'], 0, strlen($value['path']) - strlen($fileName));
			$list[$key]['thumb'] = D("Jnp")->host . $folder. "s_" . $fileName;
		}
		return $list;
	}
}
?>