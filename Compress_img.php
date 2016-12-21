
<?php


  include('SimpleImage.php');
             $img = new SimpleImage();
 

function search_file($folderName){
    global $img;
    // открываем текущую папку 
    $dir = opendir($folderName); 
    // перебираем папку 
    while (($file = readdir($dir)) !== false){ // перебираем пока есть файлы
        if($file != "." && $file != ".."){ // если это не папка
            if(is_file($folderName."/".$file)){ // если файл проверяем имя
              if ((strpos($file, '.jpg') > 0 ) or
                 (strpos($file, '.jpeg') > 0 ) or
                 (strpos($file, '.gif') > 0 ) or
                 (strpos($file, '.png') > 0 )) {
                echo $folderName."/".$file.' '.filesize($folderName."/".$file).' -> ';
                $img->load($folderName."/".$file);
                if (($img->getWidth() > $_POST['maxwidth']) or ($img->getHeight() > $_POST['maxheight'])) {
                  $img->resizeInTo($_POST['maxwidth'], $_POST['maxheight']);
                }
                $img->save($folderName."/".$file);
                echo filesize($folderName."/".$file).' <br /> ';
                
              }
            } 
            // если папка, то рекурсивно вызываем search_file
            if(is_dir($folderName."/".$file)) {
              search_file($folderName."/".$file);
            }
        } 
    }
    // закрываем папку
    closedir($dir);
}


if ($_POST['folder'] != '') {
 if ($_POST['maxwidth'] == 0) {
   $_POST['maxwidth'] = 99999;
 }
 if ($_POST['maxheight'] == 0) {
   $_POST['maxheight'] = 99999;
 }
 if {$_POST['folder'] == ''} {
  $_POST['folder'] = '.';
 } else {
  $_POST['folder'] = "./".$_POST['folder'];
 }
 search_file($_POST['folder']);
 echo 'ok';
} else {
  echo '<h1>Compress images</h1>';
  echo '<form action="" method="POST">';
  echo ' Folder:<input type="text" name="folder">';
  echo ' Max width:<input type="text" name="maxwidth">';
  echo ' Max height:<input type="text" name="maxheight">';
  echo '<input type="submit" value="Compress">';
  echo '</form>';
}
?>