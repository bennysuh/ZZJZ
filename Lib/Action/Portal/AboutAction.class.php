<?php
class AboutAction extends Action {
    // 
    public function index() {
    	$data["page"] = "about";
    	$result = M("zz_configpage")->where($data)->find();
		$this->assign("title",$result["title"]);
		$this->assign("content",$result["content"]);
		$this->display();
    }
}