<?php
error_reporting(0);

function linksql(){
$link = mysqli_connect( 
		              'localhost',  
		              'root',       
		              '',   
		              'testRedpix');
$link->set_charset("utf8");
return $link;
};

function getAllfDb(){
	$result = [];
	$sqlComment = "SELECT * FROM `comment`";
	$resComment = mysqli_query(linksql(), $sqlComment);
	foreach ($resComment as $vComment) {
		$idCommetn = $vComment['id'];
		$textComment = $vComment['comment'];
		$sqlFiles = "SELECT * FROM `files` WHERE `id_comment` = '$idCommetn'";
		$resFiles = mysqli_query(linksql(),$sqlFiles);
		$files_res = [];
		foreach ($resFiles as  $value) {
			$nameFile = $value["name"];
			$idFile = $value["id"];
			$files_res[$idFile]=$nameFile;
		}
		$result[$idCommetn]=["files"=>$files_res,"comment"=>$textComment];
	}
	return json_encode($result);
}

echo getAllfDb();