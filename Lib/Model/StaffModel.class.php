<?php
class StaffModel extends Model {
	private $host = "http://www.gzzzjz.com/ZZJZ/Public/Uploads/Staff/";
	/**
	 +----------------------------------------------------------
	 * Portal获取推荐月嫂列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getRecommendStaffList($firstRow, $listRows) {
		$M = M('zz_staff');
		$data['isHidden'] = 1;
		$data['ysLevel'] = array(array('like','%高级月嫂%'), array('like','%特级月嫂%'), array('like','%星级月嫂%'),'or'); 
		
		$list = $M->field("zz_staff.staffID,zz_staff.ygbh,zz_staff.name,zz_staff.ysLevel,zz_staff.birthday,zz_upload.path,city.city")
		->where($data)
		->join('RIGHT JOIN city ON zz_staff.jg_province = city.pid and zz_staff.jg_city = city.cid 
		right join zz_upload on zz_upload.tablename="zz_staff" and zz_upload.pid = zz_staff.ygbh 
			and zz_upload.tip like "%网照%" ')
			->order('zz_staff.updatetime desc')
		->limit($firstRow.','.$listRows)->select();
		return $list ? $list : array();
	}
	
	public function getLastStaffList($firstRow,$listRows) {
		$M = D("StaffView");
		$result = $M->where("isHidden=1")->limit("$firstRow,$listRows")->order("updatetime")->select();
		return $result;
	}
	
	/**
	 +----------------------------------------------------------
	 *获取月嫂总数
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCount()
	{
		return M("zz_staff")->where("isHidden=1")->count();
	}
	
	public function getStaffInfo($staffId)
	{
		$staffInfo = D('StaffView') -> where("staffid = " . $staffId) -> find();
		return $staffInfo;
	}
	
	/**
	 +----------------------------------------------------------
	 *获取月嫂列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getStafflist()
	{
		$result = M("zz_staff")->field("staffid,name")->order("name asc")->select();
		return $result;
	}
	
	/****start api function ****/
	
	public function getYueSaoList_Api($start=0, $end=0, $cond)
	{
		$M = D("StaffView");
		$data['isHidden'] = 1;
		if ($cond) {
			$data = array_merge($data, $cond);
		}
		
		if ($start === 0 && $end === 0) {
			$result = M("zzStaff")->field("zz_staff.staffID, zz_staff.ygbh, zz_staff.languages, zz_degree.degree, zz_staff.address, 
						zz_staff.gzjy, zz_staff.ysLevel,zz_staff.updatetime, zz_staff.birthday, zz_staff.jg_province,
						zz_staff.jg_city, zz_upload.path, city.city, provincial.provincial")
						->join("zz_upload on zz_upload.pid=zz_staff.ygbh
						and zz_upload.tablename = 'zz_staff' and zz_upload.tip = '网照'")
						->join("provincial on provincial.pid=zz_staff.jg_province")
						->join("city on city.pid=provincial.pid and city.cid = zz_staff.jg_city")
						->join("zz_degree on zz_degree.degreeID=zz_staff.whcd")
						->where($data)->order("zz_staff.updateTime desc")->select(); 	
		} else {
			$result = M("zzStaff")->field("zz_staff.staffID, zz_staff.ygbh, zz_staff.languages, zz_degree.degree, zz_staff.address, 
						zz_staff.gzjy, zz_staff.ysLevel,zz_staff.updatetime, zz_staff.birthday, zz_staff.jg_province,
						zz_staff.jg_city, zz_upload.path, city.city, provincial.provincial")
						->join("zz_upload on zz_upload.pid=zz_staff.ygbh
						and zz_upload.tablename = 'zz_staff' and zz_upload.tip = '网照'")
						->join("provincial on provincial.pid=zz_staff.jg_province")
						->join("city on city.pid=provincial.pid and city.cid = zz_staff.jg_city")
						->join("zz_degree on zz_degree.degreeID=zz_staff.whcd")
						->limit($start . "," . $end)
						->where($data)->order("zz_staff.updateTime desc")->select(); 	
		}
		
		foreach ($result as $key => $item) {
			$result[$key]['ygbh'] = substr($item['ygbh'], -4);
			$result[$key]['path'] = $item['path'] ? ($this->host . $item['path']) : '';
			$result[$key]['age'] = $this->changeBirthdayToAge($item['birthday']) . '岁';
			$result[$key]['languages'] = $this->getLanguages($item['languages']);
		}
		return $result ? $result : array();
	}

	public function getInfoByID_Api($id)
	{
		if (!$id) return FALSE;
		$data['isHidden'] = 1;
		$data['staffid'] = $id;
		$result = M("zzStaff")->field("zz_staff.staffID, zz_staff.ygbh, zz_staff.languages, zz_degree.degree, zz_staff.address, 
						zz_staff.gzjy, zz_staff.ysLevel, date_format(zz_staff.updatetime, '%Y-%c-%d') as updateTime, zz_staff.birthday, zz_staff.jg_province,
						zz_staff.jg_city, zz_upload.path as path, city.city, provincial.provincial")
						->join("zz_upload on zz_upload.pid=zz_staff.ygbh
						and zz_upload.tablename = 'zz_staff' and zz_upload.tip = '网照'")
						->join("provincial on provincial.pid=zz_staff.jg_province")
						->join("city on city.pid=provincial.pid and city.cid = zz_staff.jg_city")
						->join("zz_degree on zz_degree.degreeID=zz_staff.whcd")
						->where($data)->find(); 
		if ($result) {
			//$result['updatetime'] = substr($result['updatetime'], -9);
			$result['ygbh'] = substr($result['ygbh'], -4);
			$result['path'] = $result['path'] ? ($this->host . $result['path']) : '';
			$result['age'] = $this->changeBirthdayToAge($result['birthday']) . '岁';
			$result['languages'] = $this->getLanguages($result['languages']);
			return $result;
		} else {
			return FALSE;
		}
	}
	
	public function getInfoListByID_Api($id)
	{
		if (!$id) return FALSE;
		
		$frontList = $this->getFrontListByID_Api($id);
		$backList = $this->getBackListByID_Api($id);
		
		if ($frontList) {
			if ($backList) {
				$result = array_merge(array(), $frontList, $backList);
			} else {
				$result = $frontList;
			}
		} else if ($backList){
			$result = $backList;
		} else {
			return FALSE;
		}
		
		return $result;
	}
	
	public function getFrontListByID_Api($id)
	{
		$data['isHidden'] = 1;
		$data['_string'] = " staffID < " . $id;
		$frontList = M("zzStaff")->field("zz_staff.staffID, zz_staff.ygbh, zz_staff.languages, zz_degree.degree, zz_staff.address, 
						zz_staff.gzjy, zz_staff.ysLevel, date_format(zz_staff.updatetime, '%Y-%c-%d'), zz_staff.birthday, zz_staff.jg_province,
						zz_staff.jg_city, zz_upload.path, city.city, provincial.provincial")
						->join("zz_upload on zz_upload.pid=zz_staff.ygbh
						and zz_upload.tablename = 'zz_staff' and zz_upload.tip = '网照'")
						->join("provincial on provincial.pid=zz_staff.jg_province")
						->join("city on city.pid=provincial.pid and city.cid = zz_staff.jg_city")
						->join("zz_degree on zz_degree.degreeID=zz_staff.whcd")
						->where($data)
						->order("zz_staff.staffID desc")->limit("10")->select(); 
		foreach ($frontList as $key => $item) {
			$frontList[$key]['ygbh'] = substr($item['ygbh'], -4);
			$frontList[$key]['path'] = $item['path'] ? ($this->host . $item['path']) : '';
			$frontList[$key]['age'] = $this->changeBirthdayToAge($item['birthday']) . '岁';
			$frontList[$key]['languages'] = $this->getLanguages($item['languages']);
		}
		return $frontList ? $frontList : "";
	}
	
	public function getBackListByID_Api($id, $hasSelf = TRUE)
	{
		$data['isHidden'] = 1;
		if ($hasSelf) {
			$data['_string'] = " zz_staff.staffID >= $id";
		} else {
			$data['_string'] = " zz_staff.staffID > $id";
		}
		
		$backList = M("zz_staff")->field("zz_staff.staffID, zz_staff.ygbh, zz_staff.languages, zz_degree.degree, zz_staff.address, 
						zz_staff.gzjy, zz_staff.ysLevel,zz_staff.updatetime, zz_staff.birthday, zz_staff.jg_province,
						zz_staff.jg_city, zz_upload.path, city.city, provincial.provincial")
						->join("zz_upload on zz_upload.pid=zz_staff.ygbh
						and zz_upload.tablename = 'zz_staff' and zz_upload.tip = '网照'")
						->join("provincial on provincial.pid=zz_staff.jg_province")
						->join("city on city.pid=provincial.pid and city.cid = zz_staff.jg_city")
						->join("zz_degree on zz_degree.degreeID=zz_staff.whcd")
						->where($data)->order("zz_staff.staffID asc")->limit("10")->select(); 
		foreach ($backList as $key => $item) {
			$backList[$key]['ygbh'] = substr($item['ygbh'], -4);
			$backList[$key]['path'] = $item['path'] ? ($this->host . $item['path']) : '';
			$backList[$key]['age'] = $this->changeBirthdayToAge($item['birthday']) . '岁';
			$backList[$key]['languages'] = $this->getLanguages($item['languages']);
		}
		return $backList ? $backList : "";
	}
	
	public function getLanguages($languages)
	{
		$langs = explode(",", $languages);
		if (count($langs) > 1) {
			foreach ($langs as $key => $value) {
				$langArr[] = M("zz_languages")->getFieldByItemid($value, "itemname");
			}
		} else {
			$langArr[] = M("zz_languages")->getFieldByItemid($languages, "itemname");
		}
		return implode(",", $langArr);
	}
	
	public function changeBirthdayToAge($birthday)
	{
		$interval = date(time() - strtotime($birthday));
		return intval($interval / (365 * 60 * 60 * 24 )) + 1;
	}
	
	private function getPath($ygbh)
	{
		$data['tablename'] = "zz_staff";
		$data['pid'] = $ygbh;
		$data['tip'] = "网照";
		$result = M("zz_upload")->where($data)->find();
		return $result ? $result['path'] : '';
	}
}
?>