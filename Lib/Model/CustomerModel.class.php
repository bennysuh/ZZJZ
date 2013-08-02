<?php
class CustomerModel extends Model {
	/**
	 +----------------------------------------------------------
	 *获取customer列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCustomerlist()
	{
		$result = M("zz_customer")->field("id,name")->order("name asc")->select();
		return $result;
	}	
	
	/**
	 +----------------------------------------------------------
	 *获取所有存在email的customer列表。用于发送email
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function getCustomersForEmail()
	{
		$result = M("zz_customer,zz_contact")->field("zz_customer.id,name,fieldA as email")->where("zz_contact.tableName='zz_customer' and zz_contact.type='email' and zz_contact.no=zz_customer.id ")->order("name asc")->select();
		return $result;
	}
	
	public function getCustomerByPhone($phone)
	{
		$result = M()->query("select zz_customer.id from zz_customer,zz_contact where zz_contact.tableName='zz_customer' and zz_contact.type='电话' 
			and zz_contact.fieldA =" . $phone . " and zz_contact.no = zz_customer.id");
		
		return $result[0]['id'];
	}		
	
	public function getCustomerNameAndPhone()
	{
		$result = M("zz_customer,zz_contact")->field("zz_customer.id,zz_contact.fieldA as phone,zz_customer.name")
			->where("zz_contact.tableName='zz_customer' and zz_contact.type='电话' and zz_contact.no = zz_customer.id")->select();
		return $result;
	}
}
	