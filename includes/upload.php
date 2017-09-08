<?php
if (!defined('ROOT_PATH')) {
  die("Security violation");
}

if (!function_exists("is_uploaded_file")) {
  function is_uploaded_file($file_name) {
    if (!$tmp_file = @get_cfg_var('upload_tmp_dir')) {
      $tmp_file = tempnam('','');
      $deleted = @unlink($tmp_file);
      $tmp_file = dirname($tmp_file);
    }
    $tmp_file .= '/'.get_basefile($file_name);
    return (ereg_replace('/+', '/', $tmp_file) == $file_name) ? 1 : 0;
  }

  function move_uploaded_file($file_name, $destination) {
    return (is_uploaded_file($file_name)) ? ((copy($file_name, $destination)) ? 1 : 0) : 0;
  }
}

class Upload {

  var $upload_errors = array();
  var $accepted_mime_types = array();
  var $accepted_extensions = array();
  var $upload_mode = 3;

  var $image_type = "";
  var $max_width = array();
  var $max_height = array();
  var $max_size = array();
  var $upload_path = array();

  var $field_name;
  var $file_name;
  var $extension;

  var $image_size = 0;
  var $image_size_ok = 0;
  var $lang = array();

  function Upload() {
    global $config, $lang;
	/*
	* Medidas máximas para las imagenes
	*/
    $this->max_width['thumb'] = $config['max_thumb_width'];
    $this->max_width['media'] = 1320;
    $this->max_height['thumb'] = $config['max_thumb_height'];
    $this->max_height['media'] = 1020;

    $this->max_size['thumb'] = $config['max_thumb_size'] * 1024;
    $this->max_size['media'] = $config['max_media_size'] * 1024;

    $this->upload_mode = $config['upload_mode'];
    $this->lang = $lang;

    $this->set_allowed_filetypes();
  }

  function check_image_size() {
    $this->image_size = @getimagesize($this->upload_file);
    $ok = 1;
    if ($this->image_size[0] > $this->max_width[$this->image_type]) {
      $ok = 0;
      $this->set_error($this->lang['invalid_image_width']);
    }

    if ($this->image_size[1] > $this->max_height[$this->image_type]) {
      $ok = 0;
      $this->set_error($this->lang['invalid_image_height']);
    }
    return $ok;
  }

  function copy_file() {
    switch ($this->upload_mode) {
    case 1: // overwrite mode
      if (file_exists($this->upload_path[$this->image_type]."/".$this->file_name)) {
        @unlink($this->upload_path[$this->image_type]."/".$this->file_name);
      }
      $ok = move_uploaded_file($this->upload_file, $this->upload_path[$this->image_type]."/".$this->file_name);
      break;
    case 2: // create new with incremental extention
      $n = 2;
      $copy = "";
      while (file_exists($this->upload_path[$this->image_type]."/".$this->name.$copy.".".$this->extension)) {
        $copy = "_".$n;
        $n++;
      }
      $this->file_name = $this->name.$copy.".".$this->extension;
      //Verificar si existe el directorio
      if(!is_dir($this->upload_path[$this->image_type])){
          mkdir($this->upload_path[$this->image_type]);
      }
      $ok = move_uploaded_file($this->upload_file, $this->upload_path[$this->image_type]."/".$this->file_name);
    
      
      break;
    case 3: // do nothing if exists, highest protection
    default:
      if (file_exists($this->upload_path[$this->image_type]."/".$this->file_name)) {
       $this->set_error($this->lang['file_already_exists']);
       $ok = 0;
      }
      else {
        $ok = move_uploaded_file($this->upload_file, $this->upload_path[$this->image_type]."/".$this->file_name);
      }
      break;
    }
    @chmod($this->upload_path[$this->image_type]."/".$this->file_name, CHMOD_FILES);
    return $ok;
  }

  function check_max_filesize() {
    if ($this->_FILES[$this->field_name]['size'] > $this->max_size[$this->image_type]) {
      return false;
    }
    else {
      return true;
    }
  }

  function save_file() {
    global $user_info;

    $this->upload_file = $this->_FILES[$this->field_name]['tmp_name'];
    $ok = 1;
    if (empty($this->upload_file) || $this->upload_file == "none") {
      $this->set_error($this->lang['no_image_file']);
      $ok = 0;
    }

    if ($user_info['user_level'] != ADMIN) {
      if (!$this->check_max_filesize()) {
        $this->set_error($this->lang['invalid_file_size']);
        $ok = 0;
      }
      if (eregi("image", $this->_FILES[$this->field_name]['type'])) {
        if (!$this->check_image_size()) {
          $ok = 0;
        }
      }
    }

    if (!$this->check_file_extension() || !$this->check_mime_type()) {
      $this->set_error($this->lang['invalid_file_type']. " (".$this->extension.", ".$this->mime_type.")");
      $ok = 0;
    }
    if ($ok) {
      if (!$this->copy_file()) {
        if (isset($this->lang['file_copy_error'])) {
           
          $this->set_error($this->lang['file_copy_error']);
        }
        $ok = 0;
      }
    }
    return $ok;
  }

  function upload_file($field_name, $image_type, $cat_id = 0, $file_name = "") {
    global $HTTP_COOKIE_VARS, $HTTP_POST_VARS, $HTTP_GET_VARS, $_FILES;

    // Bugfix for: http://www.securityfocus.com/archive/1/80106
    if (isset($HTTP_COOKIE_VARS[$field_name]) || isset($HTTP_POST_VARS  [$field_name]) || isset($HTTP_GET_VARS   [$field_name])) {
      die("Security violation");
    }

    $this->_FILES = $_FILES;
    $this->image_type = $image_type;
    $this->field_name = $field_name;

    if ($cat_id) {
      $this->upload_path['thumb'] = THUMB_PATH."/".$cat_id;
      $this->upload_path['media'] = MEDIA_PATH."/".$cat_id;
    }
    else {
      $this->upload_path['thumb'] = THUMB_TEMP_PATH;
      $this->upload_path['media'] = MEDIA_TEMP_PATH;
    }

    if ($file_name != "") {
      preg_match("/(.+)\.(.+)/", $file_name, $regs);
      $this->name = $regs[1];
      preg_match("/(.+)\.(.+)/", $this->_FILES[$this->field_name]['name'], $regs);
      $this->extension = $regs[2];
      $this->file_name = $this->name.".".$this->extension ;
    }
    else {
      $this->file_name = $this->_FILES[$this->field_name]['name'];
      $this->file_name = str_replace(" ", "_", $this->file_name);
      $this->file_name = str_replace("%20", "_", $this->file_name);
      $this->file_name = preg_replace("/[^-\._a-zA-Z0-9]/", "", $this->file_name);

      preg_match('/(.+)\.(.+)/', $this->file_name, $regs);
      $this->name = $regs[1];
      $this->extension = $regs[2];
    }

    $this->mime_type = $this->_FILES[$this->field_name]['type'];
    preg_match("/([a-z]+\/[a-z\-]+)/", $this->mime_type, $this->mime_type);
    $this->mime_type = $this->mime_type[1];

    if ($this->save_file()) {
      return $this->file_name;
    }
    else {
      return false;
    }
  }

  function check_file_extension($extension = "") {
    if ($extension == "") {
      $extension = $this->extension;
    }
    if (!in_array(strtolower($extension), $this->accepted_extensions[$this->image_type])) {
      return false;
    }
    else {
      return true;
    }
  }

  function check_mime_type() {
    if (!isset($this->accepted_mime_types[$this->image_type])) {
      return true;
    }
    if (!in_array($this->mime_type, $this->accepted_mime_types[$this->image_type])) {
      return false;
    }
    else {
      return true;
    }
  }

  function set_allowed_filetypes() {
    global $config;
    //Thumbnails
    $this->accepted_mime_types['thumb'] = array(
      "image/jpg",
      "image/jpeg",
      "image/pjpeg",
      "image/gif",
      "image/x-png",
      "image/png"
    );
    $this->accepted_extensions['thumb'] = array(
      "jpg",
      "jpeg",
      "gif",
      "png",
      "png"
    );

    //Media
    $this->accepted_extensions['media'] = $config['allowed_mediatypes_array'];

    $mime_type_match = array();
    include(ROOT_PATH.'includes/upload_definitions.php');

    foreach ($mime_type_match as $key => $val) {
      if (in_array($key, $this->accepted_extensions['media'])) {
        if (is_array($val)) {
          foreach ($val as $key2 => $val2) {
            $this->accepted_mime_types['media'][] = $val2;
          }
        }
        else {
          $this->accepted_mime_types['media'][] = $val;
        }
      }
    }
  }

  function get_upload_errors() {
    if (empty($this->upload_errors[$this->file_name])) {
      return "";
    }
    $error_msg = "";
    foreach ($this->upload_errors[$this->file_name] as $msg) {
      $error_msg .= "<b>".$this->file_name.":</b> ".$msg."<br />";
    }
    return $error_msg;
  }

  function set_error($error_msg) {
    $this->upload_errors[$this->file_name][] = $error_msg;
  }

  function create_thumb($src,$cat_id, $desired_width = false, $desired_height = false)
{
/*If no dimenstion for thumbnail given, return false */
if (!$desired_height&&!$desired_width) return false;
$src = MEDIA_PATH.'/'.$cat_id.'/'.$src;
$dest = THUMB_PATH.'/'.$cat_id.'/';
$fparts = pathinfo($src);
$ext = strtolower($fparts['extension']);
/* if its not an image return false */
if (!in_array($ext,array('gif','jpg','png','jpeg'))) return false;

/* read the source image */
if ($ext == 'gif')
$resource = imagecreatefromgif($src);
else if ($ext == 'png')
$resource = imagecreatefrompng($src);
else if ($ext == 'jpg' || $ext == 'jpeg')
$resource = imagecreatefromjpeg($src);

$width = imagesx($resource);
$height = imagesy($resource);
/* find the “desired height” or “desired width” of this thumbnail, relative to each other, if one of them is not given */
if(!$desired_height) $desired_height = floor($height*($desired_width/$width));
if(!$desired_width) $desired_width = floor($width*($desired_height/$height));

/* create a new, “virtual” image */
$virtual_image = imagecreatetruecolor($desired_width,$desired_height);

/* copy source image at a resized size */
imagecopyresampled($virtual_image,$resource,0,0,0,0,$desired_width,$desired_height,$width,$height);

/* create the physical thumbnail image to its destination */
/* Use correct function based on the desired image type from $dest thumbnail source */
$fparts = pathinfo($src);
$ext = strtolower($fparts['extension']);
/* if dest is not an image type, default to jpg */
if (!in_array($ext,array('gif','jpg','png','jpeg'))) $ext = 'jpg';
//$dest = $fparts['dirname'].'/'.$fparts['filename'].'.'.$ext;

$image_name = $fparts['filename'].'.'.$ext;
$dest = $dest.$image_name;
if ($ext == 'gif')
imagegif($virtual_image,$dest);
else if ($ext == 'png')
imagepng($virtual_image,$dest,1);
else if ($ext == 'jpg' || $ext == 'jpeg')
imagejpeg($virtual_image,$dest,100);

return array(
'width' => $width,
'height' => $height,
'new_width' => $desired_width,
'new_height'=> $desired_height,
'dest' => $dest,
'image_name' => $image_name
);
}
} //end of class
?>
