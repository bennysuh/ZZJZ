<?php

class IndexAction extends Action {
    // 首页
    public function index() {
    	$pageConent = D('PageContent')->getPageContent('index');
		$notices = D('Notice')->getNoticeList(0, 10);
		$bibles = D('Article')->getArticleList(0, 10);
		$ysList = D("Staff")->getRecommendStaffList(0, 10);
		$jnpList = D("Jnp")->getRecentJnp(0, 10);		$this->assign("pageContent", $pageConent['content']);
		$this->assign("ysList", $ysList);
		$this->assign("jnpList", $jnpList);
		$this->assign("notices", $notices);
		$this->assign("bibles", $bibles);
    	$this->display();
    }

}

?>