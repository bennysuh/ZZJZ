<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

class Page extends Think {
    // 起始行數
    public $firstRow	;
    // 清單每頁顯示行數
    public $listRows	;
    // 頁數跳轉時要帶的參數
    public $parameter  ;
    // 分頁總頁面數
    protected $totalPages  ;
    // 總行數
    protected $totalRows  ;
    // 當前頁數
    protected $nowPage    ;
    // 分頁的欄的總頁數
    protected $coolPages   ;
    // 分頁欄每頁顯示的頁數
    protected $rollPage   ;
	// 分頁顯示定制
    protected $config  =	array('header'=>'<span>條記錄</span>','prev'=>'上一頁','next'=>'下一頁','first'=>'最前頁','last'=>'最後頁','theme'=>' %totalRow% %header% %first% %upPage% %downPage% %end% %pageList% 共%totalPage%頁');

    /**
     +----------------------------------------------------------
     * 架構函數
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  總的記錄數
     * @param array $listRows  每頁顯示記錄數
     * @param array $parameter  分頁跳轉的參數
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->rollPage = C('PAGE_ROLLPAGE');
        $this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //總頁數
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[C('VAR_PAGE')])?$_GET[C('VAR_PAGE')]:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 分頁顯示輸出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show($sizeByUser = false, $ajax = false) {
		if ($sizeByUser) {
            setcookie('pagesize', $this->listRows, time() + 999999999, '/', '');
		}
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
		$url = preg_replace('/%5B\d+%5D/','%5B%5D',$url);

        //上下翻頁字串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
			$upPage="<input type='button' value='".$this->config['prev']."' href='".$url."&".$p."=$upRow' class='pageButton' />";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
			$downPage="<input type='button' value='".$this->config['next']."' href='".$url."&".$p."=$downRow' class='pageButton' />";
        }else{
            $downPage="";
        }
        // << < > >>
        if($this->nowPage == 1){
            $theFirst = "";
        }else{
			$theFirst = "<input type='button' value='".$this->config['first']."' href='".$url."&".$p."=1' class='pageButton' />";
        }
        if($this->totalPages == $this->nowPage){
            $theEnd="";
        }else{            
            $theEndRow = $this->totalPages;
			$theEnd = "<input type='button' value='".$this->config['last']."' href='".$url."&".$p."=$theEndRow' class='pageButton' />";
        }

		$pageList = "<span>第&nbsp;<select name='$p' onchange=\"window.location.href='${url}&${p}='+this.value\"></span>";
		for($i = 1; $i <= $this->totalPages; $i++) {
			if ($i == $this->nowPage) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$pageList .= "<option value='${i}' ${selected} >${i}</option>";
		}
		$pageList .= "</select>&nbsp;頁";
        
        $pageStr	 =	 str_replace(
            array('%header%','%totalRow%','%upPage%','%downPage%','%first%','%end%','%pageList%','%totalPage%'),
            array($this->config['header'],"<span id='totalRecord'>".$this->totalRows."</span>",$upPage,$downPage,$theFirst,$theEnd,$pageList,$this->totalPages),$this->config['theme']);

		if ($sizeByUser) {
			$pageSizeList = "，每頁顯示<select name='pagesize' onchange=\"window.location.href='${url}&pagesize='+this.value\">";
			$sizeOptions = array(20,40,60,80,100);
			foreach ($sizeOptions as $size) {
				if ($size == $this->listRows) {
					$selected = " selected";
				} else {
					$selected = "";
				}
				$pageSizeList .= "<option value='${size}' ${selected} >${size}</option>";
			}
			$pageSizeList .= '</select>';
		}
        
		$pageStr = "<div class='pageRecord'>${pageStr}${pageSizeList}</div>";

		if ($ajax) {
			return array('upPage'=>$upRow > 0 ? $upRow : 1, 'downPage'=>$downRow <= $this->totalPages ? $downRow : $this->totalPages);
		} else {
			return $pageStr;
		}		
    }

	
	//在當前URL附件參數
	public static function parameter($name, $value)
	{
		return  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").'&'.$name.'='.$value;
        
	}

}
?>
