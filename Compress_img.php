<?php
    class SimpleImage {
       
         var $image;
         var $image_type;
       
         function load($filename) {
            $image_info = getimagesize($filename);
            $this->image_type = $image_info[2];
            if( $this->image_type == IMAGETYPE_JPEG ) {
               $this->image = imagecreatefromjpeg($filename);
            } elseif( $this->image_type == IMAGETYPE_GIF ) {
               $this->image = imagecreatefromgif($filename);
            } elseif( $this->image_type == IMAGETYPE_PNG ) {
               $this->image = imagecreatefrompng($filename);
               imagealphablending($this->image, false);
               imagesavealpha($this->image, true);               
            } else {
               $this->image_type = false;
               return false;
            }
            return true;               
         }
         function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
            if( $image_type == IMAGETYPE_JPEG ) {
               imageinterlace ($this->image ,1);
               imagejpeg($this->image,$filename,$compression);
            } elseif( $image_type == IMAGETYPE_GIF ) {
               imagegif($this->image,$filename);
            } elseif( $image_type == IMAGETYPE_PNG ) {
               imagepng($this->image,$filename);
            }
            if( $permissions != null) {
               chmod($filename,$permissions);
            }
         }
         function output($image_type=IMAGETYPE_JPEG) {

            if( $image_type == IMAGETYPE_JPEG ) {
               imageinterlace ($this->image ,1);
               imagejpeg($this->image);
            } elseif( $image_type == IMAGETYPE_GIF ) {
               imagegif($this->image);
            } elseif( $image_type == IMAGETYPE_PNG ) {
               imagepng($this->image);
            }
         }
         function getWidth() {
            return imagesx($this->image);
         }
         function getHeight() {
            return imagesy($this->image);
         }
         function resizeToHeight($height) {
            $ratio = $height / $this->getHeight();
            $width = $this->getWidth() * $ratio;
            $this->resize($width,$height);
         }
         function resizeToWidth($width) {
            $ratio = $width / $this->getWidth();
            $height = $this->getheight() * $ratio;
            $this->resize($width,$height);
         }
         function scale($scale) {
            $width = $this->getWidth() * $scale/100;
            $height = $this->getheight() * $scale/100;
            $this->resize($width,$height);
         }
         
         function resize($width,$height) {
            $new_image = imagecreatetruecolor($width, $height);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
            $this->image = $new_image;
         }
         function cover ($width,$height) {
            /* Заполнить область */
            $w = $this->getWidth();
            if ($width != $w) {
              $this->resizeToWidth($width);
            }
            $h = $this->getHeight();
            if ($height > $h) {
              $this->resizeToHeight($height);
            }
            $this->wrapInTo ($width,$height);
         }
         
         function wrapInTo ($width,$height) {
            /* Обрезает все что не вмещается в область */
            $new_image = imagecreatetruecolor($width, $height);
            $w = $this->getWidth();
            $h = $this->getHeight();
            if ($width > $w) {
              $dst_x = round(($width - $w) / 2);
              $src_x = 0;
              $dst_w = $w;
              $src_w = $w;
            } else {
              $dst_x = 0;
              $src_x = round(($w - $width) / 2);
              $dst_w = $width;
              $src_w = $width;
            }
            if ($height > $h) {
              $dst_y = round(($height - $h) / 2);
              $src_y = 0;
              $dst_h = $h;
              $src_h = $h;
            } else {
              $dst_y = 0;
              $src_y = round(($h - $height) / 2);
              $dst_h = $height;
              $src_h = $height;
            }
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparentindex = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefill($new_image, 0, 0, $transparentindex);
            imagecopyresampled($new_image, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            $this->image = $new_image;
         }
         
         function resizeInTo($width,$height) {
            /* Масштабюировать чтобы изображение влезло в рамки */
            $ratiow = $width / $this->getWidth()*100;
            $ratioh = $height / $this->getHeight()*100;
            $ratio = min($ratiow, $ratioh);
            $this->scale($ratio);
         }   
         function crop($x1,$y1,$x2,$y2) {
            /* Вырезать кусок */
            $w = abs($x2 - $x1);
            $h = abs($y2 - $y1);
            $x = min($x1,$x2);
            $y = min($y1,$y2);
           	$new_image = imagecreatetruecolor($w, $h);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopy($new_image, $this->image, 0, 0, $x, $y, $w, $h);
            $this->image = $new_image;
         }
      };

$img = new SimpleImage();
$files_size = 0;
/***********************************************************/
/***********************************************************/

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/***********************************************************/ 

       function FileListinfile($directory, $outputfile) {
          if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
              if (is_file($directory.$file)) {
                  if ((strpos($file, '.jpg') > 0 ) or
                     (strpos($file, '.jpeg') > 0 ) or
                     (strpos($file, '.gif') > 0 ) or
                     (strpos($file, '.png') > 0 )) {
                         file_put_contents($outputfile, $directory.$file."\n", FILE_APPEND);
                  }
              } elseif ($file != '.' and $file != '..' and is_dir($directory.$file)) {
                FileListinfile($directory.$file.'/', $outputfile);
              }
            }
          }
          closedir($handle);
        }

/***********************************************************/ 

if ($_GET['step'] == 'start') {
  if ($_POST['folder'] != '') {
    if (!is_numeric($_POST['maxwidth'])) {$_POST['maxwidth'] = 99999;};
    if (!is_numeric($_POST['maxheight'])) {$_POST['maxheight'] = 99999;};
    @unlink('Compress_img.txt');
    FileListinfile($_POST['folder'], 'Compress_img.txt');
    file_put_contents('Compress_img_options.txt' ,$_POST['folder'].":".$_POST['maxwidth'].":".$_POST['maxheight'].":".$files_size."\n", FILE_APPEND);
    echo '<script>location.replace("?step=go&n=0&folder='.$_POST['folder'].'&maxwidth='.$_POST['maxwidth'].'&maxheight='.$_POST['maxheight'].'"); </script>';
  } else {
    echo '<p>Error: Folder not set</p>';
  }
} else if ($_GET['step'] == 'go') {

  $starttime = microtime_float();
  $n=$_GET['n']+0;
  $files = file('Compress_img.txt');
  $folder = $_GET['folder'];
  $maxwidth = $_GET['maxwidth'];
  $maxheight = $_GET['maxheight'];

  $folderName = $folder;
  $curtime=microtime_float();
  $runtime=$curtime-$starttime ;  
  $curfiles = 0;
  while (($runtime < 5) and ($n < count($files)) and ($curfiles < 1000)) {
    $file = trim($files[$n]);
    $img->load('./'.$file);
    if ($maxwidth + $maxheight > 0) {
      if (($img->getWidth() > $maxwidth) or ($img->getHeight() > $maxheight)) {
        $img->resizeInTo($maxwidth, $maxheight);
      }
    }
    $img->save($file);
    $curtime=microtime_float();
    $runtime=$curtime-$starttime ;
    $n++;
    $curfiles ++;
  };

  echo  'Current session worktime '.$runtime.'sec. Compressed '.$curfiles.' files. Last file is '.$n.'/'.count($files).' '.$files[$n - 1].'<br />';
  if ($n < count($files)) {
      $were = '?step=go&n='.$n.
             '&folder='.$_GET['folder'].
             '&maxwidth='.$_GET['maxwidth'].
             '&maxheight='.$_GET['maxheight'].'';
      echo 'Wait <span id="counter">10</span> second<br />';
      echo '<p><a href="'.$were.'">I dont want wait. GO GO GO.</a></p>';
      echo '<script type="text/javascript">
      function TimeOut () {
      var timec = parseInt(document.getElementById("counter").innerHTML, 10);
       timec--;
       document.getElementById("counter").innerHTML = timec;
       if (timec <= 0){
         location.replace("'.$were.'");
         clearInterval(idtimer);
        }
      }
      var idtimer = setInterval("TimeOut()", 1000);
      </script>';
  }


} else {
  echo '<h1>Compress images / Progressive JPEG</h1>';
  echo '<form action="?step=start" method="POST">';
  echo ' Folder (/ at end):<input type="text" name="folder">';
  echo ' Max width:<input type="text" name="maxwidth">';
  echo ' Max height:<input type="text" name="maxheight">';
  echo '<input type="submit" value="Compress">';
  echo '</form>';
}
?>