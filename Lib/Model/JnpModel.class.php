<?php
class JnpModel extends Model {
	protected $trueTableName = "zz_jnp";
	public $host = "/ZZJZ/Public/Uploads/jnp/";
	
	public $typeList = array(
		"胎毛笔",
		"胎毛绣",
		"手足印",
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
		$photos = D("Upload")->getFiles($photoData);
		foreach ($photos as $key => $value) {
			$photos[$key]['path'] = $this->host . $value['path'];
			$photos[$key]['thumb'] = $this->host . "s_" . $value['path'];
		}
		return $photos;
	}
	
	public function checkBh($bh)
	{
		$data['bh'] = trim($bh);
		$result = $this->where($data)->count();
		return $result > 0 ? true : false;
	}
}
?>