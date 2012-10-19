<?php

class IndexAction extends Action {
    // 首页
    public function index() {
    	$this->getRecommendStaffList();
		$this->getTop10Articles();
		$this->getNotices();
		$this->display();
    }
	public function getRecommendStaffList()
	{
		$list = D("Staff")->getStaffList(1,10);
		$year = date("Y");
		dump($year);
		foreach ($list as $key => $value) {
			$value["age"] = $year - substr($value["birthday"], 0,4);
		}
		$this -> assign('recommendStaffList', $staffList);
	}
	public function getNotices()
	{
		$result = D("Notice")->getNoticeList(1,10);
		$this -> assign('noticeList', $result);
	}
	//获取前10个记录
	public function getTop10Articles()
	{
		$list = D("Article")->getArticleList(1,10);
		$this -> assign('articleList', $list);
	}
	
}

?>