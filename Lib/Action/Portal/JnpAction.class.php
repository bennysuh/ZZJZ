<?php
 /**
 +------------------------------------------------------------------------------
 * Jnp控制類
 +------------------------------------------------------------------------------
 * @author    david <lhdst@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class JnpAction extends Action {
	public function gallery()
	{
		if($_GET['keyword']) {
			$title = trim($_GET['keyword']);
			$where['description']  = array('like',"%$title%");
			$where['title']  = array('like',"%$title%");
			$where['bh']  = array('like',"%$title%");
			$where['color']  = array('like',"%$title%");
			$where['size']  = array('like',"%$title%");
			$where['cz']  = array('like',"%$title%");
			$where['_logic'] = 'or';
			$data['_complex'] = $where;
		}
		if ($_GET['jnpType']) {
			$data["jnpType"] = $_GET['jnpType'];
		}
		$jnpList = D("Jnp")->where($data)->field("id, title, description")->select();
		$ids = array();
		foreach ($jnpList as $key => $value) {
			$ids[] = $value['id'];
		}
		$uploadData['tablename'] = "zz_jnp";
		$uploadData['pid'] = array("in", $ids);
		$list = M("zz_upload")->where($uploadData)
			->order('zz_upload.updatetime desc')->select();
		$jnpInfo;
		foreach ($list as $key => $value) {
			$list[$key]['path'] = D("Jnp")->host . $value['path'];
			$jnpInfo = D("Jnp")->where("id={$value['pid']}")->find();
			$list[$key]['title'] = $jnpInfo['title'];
			$description =   $jnpInfo['title'] . "<br/>编号:" . $jnpInfo['bh'] . "<br/>" . ($jnpInfo['cz'] ? "材质:" . $jnpInfo['cz'] . "<br/>" : "")
				.  ($jnpInfo['color'] ? "颜色:" . $jnpInfo['color'] . "<br/>" : "") . ($jnpInfo['size'] ? "尺寸:" . $jnpInfo['size'] . "<br/>" : "");
			$list[$key]['description'] = $description;
			$list[$key]['thumb'] = D("Jnp")->host . "s_". $value['path'];
		}
		$this->assign("list", $list);
		$this->assign("typeList", D("Jnp")->typeList);
		$this->display();
	}
}
?>