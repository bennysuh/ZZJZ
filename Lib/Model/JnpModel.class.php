<?php
class JnpModel extends Model {
	protected $trueTableName = "zz_jnp";
	private $host = "/ZZJZ/Public/Uploads/jnp/";
	private $showField = "id, jnpType, description, date_format(`updateTime`, '%Y-%m-%d') as updateTime, zz_uploads.path, 
				zz_upload.tip, zz_upload.type, zz_upload.sortIndex";
	
	public $typeList = array(
		"胎毛笔",
		"胎毛绣",
		"手足印",
		"其它",
	);
	
	public function getYears()
	{
		$currYear = date("Y");
		$minYear = $this->min("years");
		$maxYear = $this->max("years");
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
		}
		return $photos;
	}
}
?>