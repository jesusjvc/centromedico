<?php
/* 
---------------------------------------------------
Gestor de plantillas
---------------------------------------------------
Consultorio 1.0
Desarrollado por: Esbrillante Estudio Digital
https://esbrillante.mx
---------------------------------------------------
*/

if (!defined('ROOT_PATH')) die("Hijo, que estás haciendo");

class Template {

  var $no_error = 0;
  var $val_cache = array();
  var $missing_val_cache = array();
  var $template_cache = array();
  var $template_path;
  var $template_extension = "html";
  var $start = "{";
  var $end = "}";

  function Template($template_path = "") {
    if (!@is_dir($template_path)) {
      $this->error("Couldn't open Template-Pack ".$template_path, 1);
    }
    $this->template_path = $template_path;
  }

  function register_vars($var_name, $value = "") {
    if (!is_array($var_name)) {
      $this->val_cache[$var_name] = $value;
    }
    else {    
      $this->val_cache = array_merge($this->val_cache, $var_name);
    }
  }

  function un_register_vars($var_list) {
    $vars = explode(",", $var_list);
    foreach ($vars as $val) {
      unset($this->val_cache[trim($val)]);
    }
  }

  function cache_templates($template_list) {
    $template_list = explode(",", $template_list);
    foreach ($template_list as $val) {
      $val = trim($val);
      if (!isset($this->template_cache[$val])) {
        $this->get_template($val);
      }
    }
  }

  function get_template($template) {
    if (!isset($this->template_cache[$template])) {
      $path = $this->template_path."/".$template.".".$this->template_extension;
      $line = @implode("", @file($path));
      if (empty($line)) {
        $this->error("No se puede abrir la plantilla:  ".$path, 1);
      }

      if (defined('EXEC_PHP_CODE') && EXEC_PHP_CODE == 0) {
        $line = preg_replace("/<[\?|%]+(php|=)?(.*)[\?|%]+>/siU", "", $line);
        $line = preg_replace("/<script\s+language\s?=\s?[\"|']?php[\"|']?>(.*)<\/script>/siU", "", $line);
      }
      $line = $this->compile_template($line);
      $this->template_cache[$template] = $line;
    }
    return $this->template_cache[$template];
  }

  function analizar_plantilla($template) {  
    $template = $this->get_template($template);
    // Don't show error notices
    $old = error_reporting(E_ALL ^ E_NOTICE);
    extract($this->val_cache);
    ob_start();
    eval("?>".$template."<?php return 1;");
    $str = ob_get_contents();
    ob_end_clean();
    // Reset error_reporting
    error_reporting($old);
    return $str;
  }

  function compile_template($template)
  {

    // Replace <?xml by printing them via php to avoid error messages when short_open_tags is on
    $template = preg_replace('/<\?xml/i', "<?php echo '<?xml'; ?>", $template);
    // Compile variables in PHP code
    preg_match_all(
        "/<[\?|%]+(php|=)?(.*)[\?|%]+>/siU",
        $template,
        $regs,
        PREG_SET_ORDER
    );
    for ($i = 0; isset($regs[$i]); $i++) {
      // Fix single quotes
      $parsed = preg_replace_callback(
        "/=\s*'(.*)".preg_quote($this->start)."([A-Z0-9-_]+)".preg_quote($this->end)."(.*)';/Usi",
        array(&$this, '_fix_php_quotes'),
        $regs[$i][0]
      );

      $parsed = preg_replace_callback(
        '='.preg_quote($this->start).'([A-Z0-9-_]+)'.preg_quote($this->end).'=Usi',
        array(&$this, '_compile_php_var'),
        $parsed
      );
      $parsed;
      $template = str_replace($regs[$i][0], $parsed, $template);
    }

    // Compile variables
    $template = preg_replace_callback(
        '='.preg_quote($this->start).'([A-Z0-9-_]+)'.preg_quote($this->end).'=Usi',
        array(&$this, '_compile_var'),
        $template
    );

    // Compile condition tags
    $template = preg_replace_callback(
        '='.preg_quote($this->start).'if(not?)?\s+([A-Z0-9-_]+)'.preg_quote($this->end).'=Usi',
        array(&$this, '_compile_condition_start'),
        $template
    );

    $template = preg_replace_callback(
        '='.preg_quote($this->start).'endif(not?)?\s+([A-Z0-9-_]+)'.preg_quote($this->end).'=Usi',
        array(&$this, '_compile_condition_end'),
        $template
    );

    return $template;
  }

  function _compile_php_var(&$matches) {
    return '{$' . trim($matches[1]) . '}';
  }

  function _fix_php_quotes(&$matches) {
    return '= "' . str_replace('"', '\\"', $matches[1])
           . $this->start.$matches[2].$this->end
           . str_replace('"', '\\"', $matches[3]) . '";';
  }

  function _compile_var(&$matches) {
    $name = trim($matches[1]);

    if (!isset($this->val_cache[$name])) {
        return $matches[0];
    }

        // Header and Footer are parsed in print_template()
        if ($name == 'header' || $name == 'footer') {
        return $matches[0];
    }

    return '<?php echo $' . $name . '; ?>';
  }

  function _compile_condition_start(&$matches) {
    $name = trim($matches[2]);

    if (!isset($this->val_cache[$name])) {
        return $matches[0];
    }

    if ($matches[1] == 'not' || $matches[1] == 'no') {
        return '<?php if (empty($' . $name . ') || $' . $name . ' === REPLACE_EMPTY){ ?>';
    }

    return '<?php if (!empty($' . $name . ') && $' . $name . ' !== REPLACE_EMPTY){ ?>';
  }

  function _compile_condition_end(&$matches) {
    $name = trim($matches[2]);

    if (!isset($this->val_cache[$name])) {
        return $matches[0];
    }

        return '<?php } ?>';
  }

  function parse_array($array) {
    static $keys;

    foreach ($array as $key => $val) {
      if (is_array($val)) {
        $array[$key] = $this->parse_array($val);
      }
      else {
        if (!isset($keys) || count($keys) != count($this->val_cache)) {
          $keys = array_keys($this->val_cache);
          array_walk($keys, array(&$this, '_prepare_key'));
        }

        $array[$key] = str_replace($keys, $this->val_cache, $val);
      }
    }
    return $array;
  }

  function _prepare_key(&$item) {
    $item = $this->start.$item.$this->end;
  }

  function print_template($template) {
    if (strpos($template, $this->start.'header'.$this->end) !== false) {
      $header = $this->analizar_plantilla("header");
      $template = str_replace($this->start.'header'.$this->end, $header, $template);
    }

    if (strpos($template, $this->start.'footer'.$this->end) !== false) {
      $footer = $this->analizar_plantilla("footer");
      $template = str_replace($this->start.'footer'.$this->end, $footer, $template);
    }

    print $this->clean_template($template);
  }

  function clean_template($template) {
    $search_array = array(
      '='.preg_quote($this->start).'([A-Z0-9_-]+)'.preg_quote($this->end).'=Usi',
      '='.preg_quote($this->start).'if(not?)?\s+([A-Z0-9_-]+)'.preg_quote($this->end).'=Usi',
      '='.preg_quote($this->start).'endif(not?)?\s+([A-Z0-9_-]+)'.preg_quote($this->end).'=Usi',
    );
    $replace_array = array(
      "",
      "",
      ""
    );
    $template = preg_replace($search_array, $replace_array, $template);

    return $template;
  }

  function error($errmsg, $halt = 0) {
    if (!$this->no_error) {
      echo  $error_page = <<< EOF
        <!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Ups ocurrió un error</title>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
       <link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>


        <style>
          body{
            background-color: #1c5e7e;
            padding:15% 0;
          }
          h1{
            text-transform: uppercase;
            font-size: 2.2em;
          }
          p{
            margin:0;
            padding: 0;
          }
          h1,p,a{
            color: #fff;
            text-align:center;
            font-family: 'Abel', sans-serif;
            text-decoration:none;
          }
        </style>    
    </head>
    <body>
        <h1>No se encuentra la plantilla</h1>
        <p>$errmsg</p> 
        <p>Por favor reportelo a: <a href="mailto:webmaster@esbrillante.mx">webmaster@esbrillante.mx</a></p>
    </body>
</html>
  </style>
EOF;
    
      if ($halt) {
        exit;
      }

    }
  }
} // end of class
?>