<?php
// +----------------------------------------------------------------------
// | Elibrary [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://elibrary.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ccxopen <ccxopen@gmail.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * 應用入口基類，用於強制用戶必須登錄
 +------------------------------------------------------------------------------
 * @author    ccxopen <ccxopen@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class EntryAction extends Action {

    public function _initialize()
    {		
		import('@.ORG.SysLogs');
		if(!$_SESSION[C('USER_AUTH_KEY')] && MODULE_NAME != 'Auth') {
			redirect(__ROOT__.'/Admin/Auth/');
			return;
		}
		
		import("@.ORG.Auth");
		if (!Auth::AccessDecision()){
            $this->error('沒有權限');
        }

		//記錄系統日誌		
		SysLogs::log();
    }

}

?>