<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination
//$targetFolder = '../upload'; // Relative to the root

//$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = '../../Uploads/zdg';
	
	$targetFileName = date("YmdHis") .  strrchr($_FILES['Filedata']['name'], ".");
	$targetFile = rtrim($targetPath,'/') . '/' . $targetFileName;
	// Validate the file type
	$fileTypes = array('jpg','png','gif','jpeg','JPG','PNG','GIF','JPEG'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		echo $targetFileName;
	} else {
		echo false;
	}
}
?>