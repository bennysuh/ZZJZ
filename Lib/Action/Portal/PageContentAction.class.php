<?php

class PageContentAction extends Action {
    // 首页
    public function index() {
    	if (!$_GET['page']) {
			$this->error("no page param!");
		}
    	$pageConent = D('PageContent')->getPageContent($_GET['page']);		$this->assign("pageContent", $pageConent);
    	$this->display();
    }

}

?>