<?php
class ContactModel extends Model {
	/**
	 +----------------------------------------------------------
	 * 获取联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getContact($id, $tablename) {
		//显示联系方式
		$contact = M("zz_contact");
		$contactArr = $contact->where("no =" . $id . " and tableName = '$tablename'")->order("id")->select();
		return $contactArr;
	}
	
	/**
	 +----------------------------------------------------------
	 * 新增联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	// public function addContact($arr,$tablename){
		// if(count($arr) == 0){
			// return false;
		// }else{
			// foreach ($arr as $value) {
				// $contact = M("zz_contact");
				// $contact->no = $json->no;
				// $contact->tableName = $tablename;
				// $v = arrayToObject($value);
				// $contact->type = $v->type;
				// $contact->fieldA = $v->content;
				// if(!($contact -> add())){
					// return false;
				// }
			// }
			// return true;
		// }
	// }
// 	
	/**
	 +----------------------------------------------------------
	 * 新增/更新联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function saveContact($id,$arr,$tablename){
		if(count($arr) == 0){
			return false;
		}else{
			$contact = M("zz_contact");
			$contact->where('no= ' . $id)->delete();//删除已有记录
			
			SysLogs::log("删除客户联系方式,no=" . $id);
			//新增编辑的记录
			foreach ($arr as $value) {
				$v = arrayToObject($value);
				$data['no'] = $id;
				$data['type'] = $v->type;
				$data['fieldA'] = $v->content;
				$data['tableName'] = $tablename;
				if(!($contact->add($data))){
					return false;
				}
			}
			SysLogs::log("新增客户联系方式,no=" . $id);
			return  true;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 刪除联系方式
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function delContact($no,$tablename){
		$result = M('zz_contact') -> where("no=" . $no . " and tableName = '$tablename'") -> delete();
		SysLogs::log("删除客户联系方式,no=" . $no);
		return $result;
	}
}
?>