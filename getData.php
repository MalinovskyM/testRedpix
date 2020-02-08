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
function get_last_insert_id($multiq){
    $load = linksql();
    $multiq .=";SELECT last_insert_id();";   

    mysqli_multi_query($load,$multiq);
    $res1=mysqli_store_result($load);
    $resp1=mysqli_fetch_array($res1);
    mysqli_free_result($res1);
    mysqli_more_results($load);
    mysqli_next_result($load);
    $res2=mysqli_store_result($load);
    $resp2=mysqli_fetch_array($res2);
    return $resp2["last_insert_id()"];
    mysqli_free_result('$res2');
}

function addComment2Db($comment){
	$sql = "INSERT INTO `comment`(`comment`) VALUES ('$comment')";
	$res = get_last_insert_id($sql);
	return $res;
}

function addFile2Db($name,$id_comment){
	$sql = "INSERT INTO `files`(`name`, `id_comment`) VALUES ('$name',$id_comment)";
	$res = mysqli_query(linksql(),$sql);
}

function cropImage($aInitialImageFilePath, $aNewImageFilePath, $aNewImageWidth, $aNewImageHeight) {
    if (($aNewImageWidth < 0) || ($aNewImageHeight < 0)) {
        return false;
    }

    // Массив с поддерживаемыми типами изображений
    $lAllowedExtensions = array(1 => "gif", 2 => "jpeg", 3 => "png"); 
    
    // Получаем размеры и тип изображения в виде числа
    list($lInitialImageWidth, $lInitialImageHeight, $lImageExtensionId) = getimagesize($aInitialImageFilePath); 
    
    if (!array_key_exists($lImageExtensionId, $lAllowedExtensions)) {
        return false;
    }
    $lImageExtension = $lAllowedExtensions[$lImageExtensionId];
    
    // Получаем название функции, соответствующую типу, для создания изображения
    $func = 'imagecreatefrom' . $lImageExtension; 
    // Создаём дескриптор исходного изображения
    $lInitialImageDescriptor = $func($aInitialImageFilePath);

    // Определяем отображаемую область
    $lCroppedImageWidth = 0;
    $lCroppedImageHeight = 0;
    $lInitialImageCroppingX = 0;
    $lInitialImageCroppingY = 0;
    if ($aNewImageWidth / $aNewImageHeight > $lInitialImageWidth / $lInitialImageHeight) {
        $lCroppedImageWidth = floor($lInitialImageWidth);
        $lCroppedImageHeight = floor($lInitialImageWidth * $aNewImageHeight / $aNewImageWidth);
        $lInitialImageCroppingY = floor(($lInitialImageHeight - $lCroppedImageHeight) / 2);
    } else {
        $lCroppedImageWidth = floor($lInitialImageHeight * $aNewImageWidth / $aNewImageHeight);
        $lCroppedImageHeight = floor($lInitialImageHeight);
        $lInitialImageCroppingX = floor(($lInitialImageWidth - $lCroppedImageWidth) / 2);
    }
    
    // Создаём дескриптор для выходного изображения
    $lNewImageDescriptor = imagecreatetruecolor($aNewImageWidth, $aNewImageHeight);
    imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $lInitialImageCroppingX, $lInitialImageCroppingY, $aNewImageWidth, $aNewImageHeight, $lCroppedImageWidth, $lCroppedImageHeight);
    $func = 'image' . $lImageExtension;
    
    // сохраняем полученное изображение в указанный файл
    return $func($lNewImageDescriptor, $aNewImageFilePath);
}


if( isset( $_POST['my_file_upload'] ) ){  
  $idComment = addComment2Db($_POST['comment']);
  $uploaddir = './uploads'; 
  
  if( ! is_dir( $uploaddir ) ) mkdir( $uploaddir, 0777 );

  $files      = $_FILES; 
  $done_files = array();

  
  foreach( $files as $file ){
    $file_name = $file['name'];
    if( move_uploaded_file($file['tmp_name'], "$uploaddir/$file_name" ) ){
      $name_new = "uploads/".rand(0,1000000)."_cropped.jpg";
      $done_files[] = $name_new;
      
      $old_name = $uploaddir."/".$file_name;
      cropImage($old_name, $name_new, 450, 450);
      
      
      addFile2Db($name_new,$idComment);
      // unlink($old_name);
    }
  }

  $data = $done_files ? array('files' => $done_files,"comment"=>$_POST['comment'] ) : array('error' => 'Ошибка загрузки файлов.');

  die( json_encode( $data ) );
}