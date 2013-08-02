<?php

class IndexAction extends Action {
    // 首页
    public function index() {
    	$pageConent = D('PageContent')->getPageContent('index');
		$notices = D('Notice')->getNoticeList(0, 10);
		$bibles = D('Article')->getArticleList(0, 10);		$this->assign("pageContent", $pageConent['content']);
		$this->assign("notices", $notices);
		$this->assign("bibles", $bibles);
    	$this->display();
    }

}

?>