<?php

class IndexAction extends Action {
    // 首页
    public function index() {
    	$this->getRecommendStaffList();
		$this->getLastStaffList();
		$this->getTop10Articles();
		$this->getNotices();
		$this->display();
    }
	public function getRecommendStaffList()
	{
		$staffList = D("Staff")->getRecommendStaffList(1,10);
		foreach ($staffList as $key => $value) {
			$staffList[$key]["age"] = parseBirthdayToAge($value["birthday"]);
		}
		$this -> assign('recommendStaffList', $staffList);
	}
	public function getLastStaffList()
	{
		$staffList = D("Staff")->getLastStaffList(1,10);
		foreach ($staffList as $key => $value) {
			$staffList[$key]["age"] = parseBirthdayToAge($value["birthday"]);
		}
		$this -> assign('lastStaffList', $staffList);
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