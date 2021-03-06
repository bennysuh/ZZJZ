<?php
// +----------------------------------------------------------------------
// | EASY-BOOKMARK [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://easy-bookmark.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: davidhuang <davidhuang@nmg.com.hk>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * backup控制類
 +------------------------------------------------------------------------------
 * @author   davidhuang <davidhuang@nmg.com.hk>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
Header('Content-type:document; charset=utf-8');
header("Content-Disposition:attachment; filename=zzjz_".date( 'YmdHis ').".sql"); 
class BackUpAction extends EntryAction
{
    
    public function index()
    {
    	$table=$this->getTable();
		$struct=$this->bakStruct($table);
		$record=$this->bakRecord($table);
		echo $struct;
		echo $record;
		exit();
	}
	
	/**
	*返回数据库中的数据表
	*/
	protected function getTable(){
		$dbName=C('DB_NAME');
		$result=M()->query('show tables from '.$dbName);
		foreach ($result as $v){
		    $tbArray[]=$v['Tables_in_'.C('DB_NAME')];
		}
		return $tbArray;
	}
	
	/**
	*备份数据表结构
	*/
	protected function bakStruct($array){
		
		foreach ($array as $v){
		
			$tbName=$v;
			
			$result=M()->query('show columns from '.$tbName);

			$sql.="--\r\n";
			$sql.="-- 数据表结构: `$tbName`\r\n";
			$sql.="--\r\n\r\n";
			
			$sql.="create table `$tbName` (\r\n";

			$rsCount=count($result);
			
			foreach ($result as $k=>$v){
			
			        $field  =       $v['Field'];
			        $type   =       $v['Type'];
			        $default=       $v['Default'];
			        $extra  =       $v['Extra'];
			        $null   =       $v['Null'];

					if(!($default=='')){
						if(is_string($default) && $default != 'CURRENT_TIMESTAMP'
						&& $default != '1'  && $default != '0'){
							$default='default "'. $default . '"';
						}else{
							$default='default '. $default ;
						}
					}
			        
			        if($null=='NO'){
			            $null='not null';
			        }else{
			            $null="null";
			        }			        
			        
			        if($v['Key']=='PRI'){
			                $key    =       'primary key';
			        }else{
			                $key    =       '';
			        }
					if($k<($rsCount-1)){
						
						$sql.="`$field` $type $null $default $key $extra ,\r\n";
					}else{
						//最后一条不需要","号
						$sql.="`$field` $type $null $default $key $extra \r\n";
					}


			}
			$sql.=")engine=innodb charset=utf8;\r\n\r\n";
		}
		return str_replace(')',')',$sql);
	}
	
	/**
	*备份数据表数据
	*/
	protected function bakRecord($array){
	
	    foreach ($array as $v){
		
			$tbName=$v;
						
		    $rs=M()->query('select * from '.$tbName);
		    
		    if(count($rs)<=0){
	    	    continue;
	    	}

			$sql.="--\r\n";
			$sql.="-- 数据表中的数据: `$tbName`\r\n";
			$sql.="--\r\n\r\n";

	    	foreach ($rs as $k=>$v){

	    	    $sql.="INSERT INTO `$tbName` VALUES (";
		    	foreach ($v as $key=>$value){
		    	    if($value==''){
		    	        $value='null';
		    	    }
		    	    $type=gettype($value);
		    	    if($type=='string'){
		    	        $value="'".addslashes($value)."'";
		    	    }
		    	    $sql.="$value," ;
		    	}
				$sql = substr($sql,0,-1);
		    	$sql.=");\r\n\r\n";
	        }
		}
		return str_replace(')',')',$sql);
	}

	
}
?>
