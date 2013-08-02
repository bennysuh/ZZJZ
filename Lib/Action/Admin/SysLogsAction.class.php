<?php
// +----------------------------------------------------------------------
// | CommonCMS [IT IS LIFE]
// +----------------------------------------------------------------------
// | Copyright (c) 2013  All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang <mchuang1140@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 系统日誌类
 +------------------------------------------------------------------------------
 * @author    davidhuang <mchuang1140@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class SysLogsAction extends EntryAction
{
    
    /**
     +----------------------------------------------------------
     * 分類日誌
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
     */
	public function showList()
    {
		import("@.ORG.Page"); //导入分页类

		$model = M('sysLogs');
		
		//创建查询条件SQL
		switch ($_GET['category']) {
		case 'all':
			$where = "1=1";
		break;
		case 'login':
			$where = "appGroup = 'Admin' and module='Auth' and (action='login' or action='logout')";
		break;
		case 'user':
			$where = "appGroup = 'Admin' and module='User' and (action='addUser' or action='removeUser' or action='saveUserInfo')";
		break;
		case 'group':
			$where = "appGroup = 'Admin' and module='Group' and (action='saveGroups' or action='removeGroups' or action='addMember' or action='removeMember')";
		break;
		case 'function':
			$where = "appGroup = 'Admin' and module='SysAuth' and action='updateSysAccess'";
		break;		
		case 'other':
			$where = "appGroup = 'Admin' and module='Dictionary' and 1=0";
		break;
		}
		
		//分页器
		$count = $model->where($where)->count(); //计算总数
		$p = new Page ($count, 20);
		$page = $p->show();

		$list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->order("dateTime desc")->select();
		
		$this->assign('page', $page);
		$this->assign('list', $list);

		$this->display();
	}

	
    
}
?>