<?php
/* 
---------------------------------------------------
Functions
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!defined('ROOT_PATH')) die("Hijo, que estás haciendo");

//-----------------------------------------------------
// Calcula la edad
//------------------------------------------------------
function calcular_edad($fecha){
    $dias = explode("-", $fecha, 3);
    $dias = mktime(0,0,0,$dias[1],$dias[0],$dias[2]);
    $edad = (int)((time()-$dias)/31556926 );
    return $edad;
}

//-----------------------------------------------------
// Función que envia correo electrónicos
//------------------------------------------------------
function enviar_email($para, $nombre, $de, $remitente, $asunto, $mensaje){
    require 'includes/PHPMailerAutoload.php';
   
    $message = file_get_contents('template-email.html'); 

    $message = str_replace('%email%', $email, $message);
    $message = str_replace('%name%', $name, $message); 
    $message = str_replace('%tipo%', $type, $message); 
    $message = str_replace('%mensaje%', $mensaje, $message);

    $message = str_replace('%phone%', $phone, $message); 


    $mail = new PHPMailer;
    $mail->isSMTP();                                      // Set mailer to use SMTP
    //$mail->SMTPDebug = 2;
     $mail->Host = '';                    // Specify main and backup server
    $mail->SMTPAuth = true;
    $mail->Port = 26;
    $mail->Username = '';                // SMTP username
    $mail->Password = '';                 // SMTP password
    //$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

    $mail->From = $de;
    $mail->FromName = $remitente;
    $mail->AddAddress($para, $nombre);  // Add a recipient   
    $mail->CharSet = 'UTF-8';
    $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $asunto;
    $mail->Body    = $message;
    $mail->AltBody = $message;

   //send the message, check for errors
    if (!$mail->send()) { $resp = "ERROR"; } else { $resp = "SUCCESS"; }

    return $resp;
}

//----------------------------------------------------
// Obtiene la URL del servidor
// ---------------------------------------------------
function obtenURL() {
    $url = "http://" . $_SERVER['HTTP_HOST'];
    $whitelist = array('127.0.0.1', "::1");
    if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
         $url = "http://" . $_SERVER['HTTP_HOST']."/logoscda/";
    }
    return $url;
}



function get_basename($path) {
    $path = str_replace("\\", "/", $path);
    $name = substr(strrchr($path, "/"), 1);
    return $name ? $name : $path;
}

function get_basefile($path) {
    $basename = get_basename($path);
    preg_match('/(.+)\?(.+)/', $basename, $regs);
    return isset($regs[1]) ? $regs[1] : $basename;
}

function redirect($url) {
    global $script_url, $site_sess;
    if (strpos($url, '://') === false) {
        $url = $script_url . '/' . $url;
    }
    $location = @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ? 'Refresh: 0; URL=' : 'Location: ';
    if (is_object($site_sess)) {
        $url = $site_sess->url($url, "&");
    }
    header($location . $url);
    exit;
}

function is_remote($file_name) {
    return strpos($file_name, '://') > 0 ? 1 : 0;
}

function is_remote_file($file_name) {
    return is_remote($file_name) && preg_match("#\.[a-zA-Z0-9]{1,4}$#", $file_name) ? 1 : 0;
}

function is_local_file($file_name) {
    return!is_remote($file_name) && get_basefile($file_name) != $file_name && preg_match("#\.[a-zA-Z0-9]{1,4}$#", $file_name) ? 1 : 0;
}

function check_remote_media($remote_media_file) {
    global $config;
    return is_remote($remote_media_file) && preg_match("#\.[" . $config['allowed_mediatypes_match'] . "]+$#i", $remote_media_file) ? 1 : 0;
}

function check_local_media($local_media_file) {
    global $config;
    return!is_remote($local_media_file) && get_basefile($local_media_file) != $local_media_file && preg_match("#\.[" . $config['allowed_mediatypes_match'] . "]+$#i", $local_media_file) ? 1 : 0;
}

function check_remote_thumb($remote_thumb_file) {
    return is_remote($remote_thumb_file) && preg_match("#\.[gif|jpg|jpeg|png]+$#is", $remote_thumb_file) ? 1 : 0;
}

function check_executable($file_name) {
    if (substr(PHP_OS, 0, 3) == "WIN" && !eregi("\.exe$", $file_name)) {
        $file_name .= ".exe";
    } elseif (substr(PHP_OS, 0, 3) != "WIN") {
        $file_name = eregi_replace("\.exe$", "", $file_name);
    }
    return $file_name;
}



function safe_htmlspecialchars($chars) {
    // Translate all non-unicode entities
    $chars = preg_replace(
            '/&(?!(#[0-9]+|[a-z]+);)/si', '&amp;', $chars
    );

    $chars = str_replace(">", "&gt;", $chars);
    $chars = str_replace("<", "&lt;", $chars);
    $chars = str_replace('"', "&quot;", $chars);
    return $chars;
}

function un_htmlspecialchars($text) {
    $text = str_replace(
            array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), $text
    );

    return $text;
}

function get_iptc_info($info) {
    $iptc_match = array();
    $iptc_match['2#120'] = "caption";
    $iptc_match['2#122'] = "caption_writer";
    $iptc_match['2#105'] = "headline";
    $iptc_match['2#040'] = "special_instructions";
    $iptc_match['2#080'] = "byline";
    $iptc_match['2#085'] = "byline_title";
    $iptc_match['2#110'] = "credit";
    $iptc_match['2#115'] = "source";
    $iptc_match['2#005'] = "object_name";
    $iptc_match['2#055'] = "date_created";
    $iptc_match['2#090'] = "city";
    $iptc_match['2#095'] = "state";
    $iptc_match['2#101'] = "country";
    $iptc_match['2#103'] = "original_transmission_reference";
    $iptc_match['2#015'] = "category";
    $iptc_match['2#020'] = "supplemental_category";
    $iptc_match['2#025'] = "keyword";
    $iptc_match['2#116'] = "copyright_notice";

    $iptc = iptcparse($info);
    $iptc_array = array();
    if (is_array($iptc)) {
        foreach ($iptc as $key => $val) {
            if (isset($iptc_match[$key])) {
                $iptc_info = "";
                foreach ($val as $val2) {
                    $iptc_info .= ( ($iptc_info != "" ) ? ", " : "") . $val2;
                }
                if ($key == "2#055") {
                    $iptc_array[$iptc_match[$key]] = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\3.\\2.\\1", $iptc_info);
                } else {
                    $iptc_array[$iptc_match[$key]] = $iptc_info;
                }
            }
        }
    }
    return $iptc_array;
}

function get_exif_info($exif) {
    $exif_match = array();
    $exif_match['Make'] = "make";
    $exif_match['Model'] = "model";
    $exif_match['DateTimeOriginal'] = "datetime";
    $exif_match['ISOSpeedRatings'] = "isospeed";
    $exif_match['ExposureTime'] = "exposure";
    $exif_match['FNumber'] = "aperture";
    $exif_match['FocalLength'] = "focallen";

    $exif_array = array();
    if (is_array($exif)) {
        foreach ($exif as $key => $val) {
            if (isset($exif_match[$key])) {
                $exif_info = $val;
                if ($key == "DateTimeOriginal") {
                    $exif_array[$exif_match[$key]] = preg_replace("/([0-9]{4}):([0-9]{2}):([0-9]{2})/", "\\3.\\2.\\1", $exif_info);
                } elseif ($key == "ExposureTime") {
                    $exposure = explode("/", $exif_info);
                    $exif_array[$exif_match[$key]] = "1/" . ($exposure[1] / $exposure[0]);
                } elseif ($key == "FNumber") {
                    $aperture = explode("/", $exif_info);
                    $exif_array[$exif_match[$key]] = "F/" . ($aperture[0] / $aperture[1]);
                } elseif ($key == "FocalLength") {
                    $focalLen = explode("/", $exif_info);
                    $exif_array[$exif_match[$key]] = ($focalLen[0] / $focalLen[1]) . "mm";
                } else {
                    $exif_array[$exif_match[$key]] = $exif_info;
                }
            }
        }
    }
    return $exif_array;
}


//Funciones para obtener el dominio actual de la pagina
function strleft($s1, $s2) {
    return substr($s1, 0, strpos($s1, $s2));
}

function selfURL() {
    $servlocal = false;
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
    $URL = $protocol . "://" . $_SERVER['SERVER_NAME'] . "/";

    $pos = strpos($URL, "www.");
    if ($pos === false) {
        $pos = strlen("http://");
        $URL = substr($URL, 0, $pos) . 'www.' . substr($URL, $pos);
    }


    return $URL;
}

function format_file_size($file_size = 0) {
    //$file_size = intval($file_size);
    if (!$file_size) {
        return "n/a";
    }
    if (strlen($file_size) <= 9 && strlen($file_size) >= 7) {
        $file_size = number_format($file_size / 1048576, 1);
        return $file_size . "&nbsp;MB";
    } elseif (strlen($file_size) >= 10) {
        $file_size = number_format($file_size / 1073741824, 1);
        return $file_size . "&nbsp;GB";
    } else {
        $file_size = number_format($file_size / 1024, 1);
        return $file_size . "&nbsp;KB";
    }
}

function get_remote_file_size($file_path) {
    if (!CHECK_REMOTE_FILES) {
        return 'n/a';
    }
    ob_start();
    @readfile($file_path);
    $file_data = ob_get_contents();
    ob_end_clean();
    return format_file_size(strlen($file_data));
}


function check_email($email) {
    return (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $email)) ? 1 : 0;
}

function format_date($format, $timestamp) {
    global $user_info;
    $timezone_offset = (defined("TIME_OFFSET")) ? TIME_OFFSET : 0;
    return date($format, $timestamp + (3600 * $timezone_offset));
}

function format_url($url) {
    if (empty($url)) {
        return '';
    }

    if (!preg_match("/^https?:\/\//i", $url)) {
        $url = "http://" . $url;
    }

    return $url;
}

function replace_url($text) {
    $text = " " . $text . " ";
    $url_search_array = array(
        "#([^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[^, \(\)<>\n\r]*)?)#si",
        "#([^]_a-z0-9-=\"'\/])([a-z]+?)://([^, \(\)<>\n\r]+)#si"
    );

    $url_replace_array = array(
        "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\" rel=\"nofollow\">www.\\2.\\3\\4</a>",
        "\\1<a href=\"\\2://\\3\" target=\"_blank\" rel=\"nofollow\">\\2://\\3</a>",
    );
    $text = preg_replace($url_search_array, $url_replace_array, $text);

    if (strpos($text, "@")) {
        $text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
    }
    return substr($text, 1, -1);
}

function format_text($text, $html = 1, $word_wrap = 0) {
    if ($word_wrap && $text != "") {
        $text = preg_replace("/([^\n\r ?&\.\/<>\"\\-]{" . $word_wrap . "})/i", " \\1\n", $text);
    }

    if ($html == 0 || $html == 2) {
        $text = safe_htmlspecialchars($text);
    }

    if ($html !== 2) {
        //$text = nl2br(trim($text)); 
        $text = replace_url($text);
    }

    $text = str_replace("\\'", "'", $text);

    return $text;
}

function utf8_to_htmlentities($source) {
    // array used to figure what number to decrement from character order
    // value
    // according to number of characters used to map unicode to ascii by
    // utf-8
    $decrement = array();
    $decrement[4] = 240;
    $decrement[3] = 224;
    $decrement[2] = 192;
    $decrement[1] = 0;

    // the number of bits to shift each charNum by
    $shift = array();
    $shift[1][0] = 0;
    $shift[2][0] = 6;
    $shift[2][1] = 0;
    $shift[3][0] = 12;
    $shift[3][1] = 6;
    $shift[3][2] = 0;
    $shift[4][0] = 18;
    $shift[4][1] = 12;
    $shift[4][2] = 6;
    $shift[4][3] = 0;

    $pos = 0;
    $len = strlen($source);
    $str = '';
    while ($pos < $len) {
        $asciiPos = ord(substr($source, $pos, 1));
        if (($asciiPos >= 240) && ($asciiPos <= 255)) {
            // 4 chars representing one unicode character
            $thisLetter = substr($source, $pos, 4);
            $pos += 4;
        } elseif (($asciiPos >= 224) && ($asciiPos <= 239)) {
            // 3 chars representing one unicode character
            $thisLetter = substr($source, $pos, 3);
            $pos += 3;
        } else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
            // 2 chars representing one unicode character
            $thisLetter = substr($source, $pos, 2);
            $pos += 2;
        } else {
            // 1 char (lower ascii)
            $thisLetter = substr($source, $pos, 1);
            $pos += 1;
        }

        // process the string representing the letter to a unicode entity
        $thisLen = strlen($thisLetter);
        $thisPos = 0;
        $decimalCode = 0;

        while ($thisPos < $thisLen) {
            $thisCharOrd = ord(substr($thisLetter, $thisPos, 1));
            if ($thisPos == 0) {
                $charNum = intval($thisCharOrd - $decrement[$thisLen]);
                $decimalCode += ( $charNum << $shift[$thisLen][$thisPos]);
            } else {
                $charNum = intval($thisCharOrd - 128);
                $decimalCode += ( $charNum << $shift[$thisLen][$thisPos]);
            }
            $thisPos++;
        }
        if (($thisLen == 1) && ($decimalCode <= 128)) {
            $encodedLetter = $thisLetter;
        } else {
            $encodedLetter = '&#' . $decimalCode . ';';
        }
        $str .= $encodedLetter;
    }
    return $str;
}

function uni_to_utf8($char) {
    $char = intval($char);

    switch ($char) {
        case ($char < 128) :
            // its an ASCII char no encoding needed
            return chr($char);

        case ($char < 1 << 11) :
            // its a 2 byte UTF-8 char
            return chr(192 + ($char >> 6)) .
            chr(128 + ($char & 63));

        case ($char < 1 << 16) :
            // its a 3 byte UTF-8 char
            return chr(224 + ($char >> 12)) .
            chr(128 + (($char >> 6) & 63)) .
            chr(128 + ($char & 63));

        case ($char < 1 << 21) :
            // its a 4 byte UTF-8 char
            return chr(240 + ($char >> 18)) .
            chr(128 + (($char >> 12) & 63)) .
            chr(128 + (($char >> 6) & 63)) .
            chr(128 + ($char & 63));

        case ($char < 1 << 26) :
            // its a 5 byte UTF-8 char
            return chr(248 + ($char >> 24)) .
            chr(128 + (($char >> 18) & 63)) .
            chr(128 + (($char >> 12) & 63)) .
            chr(128 + (($char >> 6) & 63)) .
            chr(128 + ($char & 63));
        default:
            // its a 6 byte UTF-8 char
            return chr(252 + ($char >> 30)) .
            chr(128 + (($char >> 24) & 63)) .
            chr(128 + (($char >> 18) & 63)) .
            chr(128 + (($char >> 12) & 63)) .
            chr(128 + (($char >> 6) & 63)) .
            chr(128 + ($char & 63));
    }
}

function get_user_info($user_name = "") {
    global $db, $user_table_fields, $user_id;
    $user_info = 0;
    if ($user_name != "" && $user_id != GUEST) {
        $sql = "SELECT * FROM clientes WHERE usuario = '$user_name'";
        if ($user_info = $db->run($sql,"", true)) {

            foreach ($user_table_fields as $key => $val) {
                if (isset($user_info[$val])) {
                    $user_info[$key] = $user_info[$val];
                } elseif (!isset($user_info[$key])) {
                    $user_info[$key] = "";
                }
            }
        }
    }
    return $user_info;
}

function get_random_key($db_table = "", $db_column = "") {
    global $conectar_db;
    $key = md5(uniqid(microtime()));
    if ($db_table != "" && $db_column != "") {
        $i = 0;
        while ($i == 0) {
            $sql = "SELECT " . $db_column . "
              FROM " . $db_table . "
              WHERE " . $db_column . " = '$key'";
            if ($conectar_db->is_empty($sql)) {
                $i = 1;
            } else {
                $i = 0;
                $key = md5(uniqid(microtime()));
            }
        }
    }
    return $key;
}

function show_error_pages($error_msg, $clickstream = "") {
    global $site_template, $site_sess, $lang, $config;
    $site_template->register_vars(array(
        "error_msg" => $error_msg,
        "lang_error" => $lang['error'],
        "clickstream" => $clickstream,
        "random_image" => ""
    ));
    header("HTTP/1.0 404 Not Found");
    header("Status: 404 Not Found");
    exit();
}


function get_document_root() {
    global $global_info, $DOCUMENT_ROOT, $HTTP_SERVER_VARS;
    if (!empty($global_info['document_root'])) {
        return $global_info['document_root'];
    }
    if (!empty($HTTP_SERVER_VARS['DOCUMENT_ROOT'])) {
        $DOCUMENT_ROOT = $HTTP_SERVER_VARS['DOCUMENT_ROOT'];
    } elseif (getenv("DOCUMENT_ROOT")) {
        $DOCUMENT_ROOT = getenv("DOCUMENT_ROOT");
    } elseif (empty($DOCUMENT_ROOT)) {
        $DOCUMENT_ROOT = "";
    }
    return $global_info['document_root'] = $DOCUMENT_ROOT;
}

function remote_file_exists($url, $check_remote = CHECK_REMOTE_FILES) { // similar to file_exists(), checks existence of remote files
    if (!$check_remote || !CHECK_REMOTE_FILES) {
        return true;
    }
    $url = trim($url);
    if (!preg_match("=://=", $url))
        $url = "http://$url";
    if (!($url = @parse_url($url))) {
        return false;
    }
    if (!eregi("http", $url['scheme'])) {
        return false;
    }
    $url['port'] = (!isset($url['port'])) ? 80 : $url['port'];
    $url['path'] = (!isset($url['path'])) ? "/" : $url['path'];
    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
    if (!$fp) {
        return false;
    } else {
        $head = "";
        $httpRequest = "HEAD " . $url['path'] . " HTTP/1.1\r\n"
                . "HOST: " . $url['host'] . "\r\n"
                . "Connection: close\r\n\r\n";
        fputs($fp, $httpRequest);
        while (!feof($fp)) {
            $head .= fgets($fp, 1024);
        }
        fclose($fp);

        preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
        if ($matches[2] == 200) {
            return true;
        }
    }
}

if (!function_exists('is_executable')) {

    function is_executable($file) {
        return is_file($file);
    }

}

if (!function_exists('session_regenerate_id')) {

    function session_regenerate_id() {
        $id = md5(uniqid(microtime()));
        if (session_id($id)) {
            return true;
        } else {
            return false;
        }
    }

}

function get_mime_content_type($file) {
    if (function_exists('mime_content_type')) {
        $type = mime_content_type($file);
        if ($type) {
            return $type;
        }
    }

    $info = @getimagesize($file);

    if (isset($info['mime'])) {
        return $info['mime'];
    }

    $type = @exec(trim('file -bi ' . escapeshellarg($file)));

    if (strpos($type, ';') !== false) {
        list($type) = explode(';', $type);
    }

    if ($type) {
        return $type;
    }

    static $types = array(
'ai' => 'application/postscript',
 'aif' => 'audio/x-aiff',
 'aifc' => 'audio/x-aiff',
 'aiff' => 'audio/x-aiff',
 'asc' => 'text/plain',
 'au' => 'audio/basic',
 'avi' => 'video/x-msvideo',
 'bcpio' => 'application/x-bcpio',
 'bin' => 'application/octet-stream',
 'c' => 'text/plain',
 'cc' => 'text/plain',
 'ccad' => 'application/clariscad',
 'cdf' => 'application/x-netcdf',
 'class' => 'application/octet-stream',
 'cpio' => 'application/x-cpio',
 'cpt' => 'application/mac-compactpro',
 'csh' => 'application/x-csh',
 'css' => 'text/css',
 'dcr' => 'application/x-director',
 'dir' => 'application/x-director',
 'dms' => 'application/octet-stream',
 'doc' => 'application/msword',
 'drw' => 'application/drafting',
 'dvi' => 'application/x-dvi',
 'dwg' => 'application/acad',
 'dxf' => 'application/dxf',
 'dxr' => 'application/x-director',
 'eps' => 'application/postscript',
 'etx' => 'text/x-setext',
 'exe' => 'application/octet-stream',
 'ez' => 'application/andrew-inset',
 'f' => 'text/plain',
 'f90' => 'text/plain',
 'fli' => 'video/x-fli',
 'gif' => 'image/gif',
 'gtar' => 'application/x-gtar',
 'gz' => 'application/x-gzip',
 'h' => 'text/plain',
 'hdf' => 'application/x-hdf',
 'hh' => 'text/plain',
 'hqx' => 'application/mac-binhex40',
 'htm' => 'text/html',
 'html' => 'text/html',
 'ice' => 'x-conference/x-cooltalk',
 'ief' => 'image/ief',
 'iges' => 'model/iges',
 'igs' => 'model/iges',
 'ips' => 'application/x-ipscript',
 'ipx' => 'application/x-ipix',
 'jpe' => 'image/jpeg',
 'jpeg' => 'image/jpeg',
 'jpg' => 'image/jpeg',
 'js' => 'application/x-javascript',
 'kar' => 'audio/midi',
 'latex' => 'application/x-latex',
 'lha' => 'application/octet-stream',
 'lsp' => 'application/x-lisp',
 'lzh' => 'application/octet-stream',
 'm' => 'text/plain',
 'man' => 'application/x-troff-man',
 'me' => 'application/x-troff-me',
 'mesh' => 'model/mesh',
 'mid' => 'audio/midi',
 'midi' => 'audio/midi',
 'mif' => 'application/vnd.mif',
 'mime' => 'www/mime',
 'mov' => 'video/quicktime',
 'movie' => 'video/x-sgi-movie',
 'mp2' => 'audio/mpeg',
 'mp3' => 'audio/mpeg',
 'mpe' => 'video/mpeg',
 'mpeg' => 'video/mpeg',
 'mpg' => 'video/mpeg',
 'mpga' => 'audio/mpeg',
 'ms' => 'application/x-troff-ms',
 'msh' => 'model/mesh',
 'nc' => 'application/x-netcdf',
 'oda' => 'application/oda',
 'pbm' => 'image/x-portable-bitmap',
 'pdb' => 'chemical/x-pdb',
 'pdf' => 'application/pdf',
 'pgm' => 'image/x-portable-graymap',
 'pgn' => 'application/x-chess-pgn',
 'png' => 'image/png',
 'pnm' => 'image/x-portable-anymap',
 'pot' => 'application/mspowerpoint',
 'ppm' => 'image/x-portable-pixmap',
 'pps' => 'application/mspowerpoint',
 'ppt' => 'application/mspowerpoint',
 'ppz' => 'application/mspowerpoint',
 'pre' => 'application/x-freelance',
 'prt' => 'application/pro_eng',
 'ps' => 'application/postscript',
 'qt' => 'video/quicktime',
 'ra' => 'audio/x-realaudio',
 'ram' => 'audio/x-pn-realaudio',
 'ras' => 'image/cmu-raster',
 'rgb' => 'image/x-rgb',
 'rm' => 'audio/x-pn-realaudio',
 'roff' => 'application/x-troff',
 'rpm' => 'audio/x-pn-realaudio-plugin',
 'rtf' => 'text/rtf',
 'rtx' => 'text/richtext',
 'scm' => 'application/x-lotusscreencam',
 'set' => 'application/set',
 'sgm' => 'text/sgml',
 'sgml' => 'text/sgml',
 'sh' => 'application/x-sh',
 'shar' => 'application/x-shar',
 'silo' => 'model/mesh',
 'sit' => 'application/x-stuffit',
 'skd' => 'application/x-koan',
 'skm' => 'application/x-koan',
 'skp' => 'application/x-koan',
 'skt' => 'application/x-koan',
 'smi' => 'application/smil',
 'smil' => 'application/smil',
 'snd' => 'audio/basic',
 'sol' => 'application/solids',
 'spl' => 'application/x-futuresplash',
 'src' => 'application/x-wais-source',
 'step' => 'application/STEP',
 'stl' => 'application/SLA',
 'stp' => 'application/STEP',
 'sv4cpio' => 'application/x-sv4cpio',
 'sv4crc' => 'application/x-sv4crc',
 'swf' => 'application/x-shockwave-flash',
 't' => 'application/x-troff',
 'tar' => 'application/x-tar',
 'tcl' => 'application/x-tcl',
 'tex' => 'application/x-tex',
 'texi' => 'application/x-texinfo',
 'texinfo -  application/x-texinfo',
 'tif' => 'image/tiff',
 'tiff' => 'image/tiff',
 'tr' => 'application/x-troff',
 'tsi' => 'audio/TSP-audio',
 'tsp' => 'application/dsptype',
 'tsv' => 'text/tab-separated-values',
 'txt' => 'text/plain',
 'unv' => 'application/i-deas',
 'ustar' => 'application/x-ustar',
 'vcd' => 'application/x-cdlink',
 'vda' => 'application/vda',
 'viv' => 'video/vnd.vivo',
 'vivo' => 'video/vnd.vivo',
 'vrml' => 'model/vrml',
 'wav' => 'audio/x-wav',
 'wrl' => 'model/vrml',
 'xbm' => 'image/x-xbitmap',
 'xlc' => 'application/vnd.ms-excel',
 'xll' => 'application/vnd.ms-excel',
 'xlm' => 'application/vnd.ms-excel',
 'xls' => 'application/vnd.ms-excel',
 'xlw' => 'application/vnd.ms-excel',
 'xml' => 'text/xml',
 'xpm' => 'image/x-xpixmap',
 'xwd' => 'image/x-xwindowdump',
 'xyz' => 'chemical/x-pdb',
 'zip' => 'application/zip',
    );

    $ext = get_file_extension($file);

    if (isset($types[$ext])) {
        return $types[$ext];
    }

    return 'application/octet-stream';
}

?>