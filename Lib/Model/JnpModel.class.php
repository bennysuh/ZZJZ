<?php
class JnpModel extends Model {
	protected $trueTableName = "zz_jnp";
	private $parentFolder = "jnp/";
	private $showField = "id, jnpType, description, date_format(`updateTime`, '%Y-%m-%d') as updateTime, zz_uploads.path, 
				zz_upload.tip, zz_upload.type, zz_upload.sortIndex";
	
	
	public function getJnpList($keyword)
	{
		import("@.ORG.Page"); //导入分页类
		//创建查询条件SQL
		$where = $this->buildCondition($keyword);	
		
		//分页器
		$count = $this->where($where)->count(); //计算总数
		$p = new Page ($count, 10);
		$page = $p->show();
		$list = $this->field($this->showField)->where($where)
				->join("zz_upload on zz_jnp.id = zz_upload.pid and zz_upload.tablename ='zz_jnp' and zz_upload.isShow=1")
				->limit($p->firstRow.','.$p->listRows)
				->order("updateTime")->select();
		
		$result['page'] = $page;
		$result['list'] = $list;
		$result['total'] = $count; //搜索的记录数，搜索日志用到
		return $result;
	}
	
	/**
     +----------------------------------------------------------
     * 创建查询条件SQL
     +----------------------------------------------------------
	 * @access private
     +----------------------------------------------------------
     */
	private function buildCondition($keyword) {
		if ($keyword) {
			$where .= " (title like('%$keyword%') or description like('%$keyword%') 
						or jnpType like('%$keyword%')) ";
		}
		return $where;
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

		return $photos;
	}
}
?>