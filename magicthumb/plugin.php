<?php
/*

Copyright 2012 MagicToolbox (email : support@magictoolbox.com)

*/

$error_message = false;

function WordPress_MagicThumb_activate () {

    if(!function_exists('file_put_contents')) {
        function file_put_contents($filename, $data) {
            $fp = fopen($filename, 'w+');
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }
        }
    }


    //fix url's in css files
    $fileContents = file_get_contents(dirname(__FILE__) . '/core/magicthumb.css');
    $cssPath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));

    $cssPath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__))).'/core';

    $pattern = '/url\(\s*(?:\'|")?(?!'.preg_quote($cssPath, '/').')\/?([^\)\s]+?)(?:\'|")?\s*\)/is';
    $replace = 'url(' . $cssPath . '/$1)';
    $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
    if($fixedFileContents != $fileContents) {
        file_put_contents(dirname(__FILE__) . '/core/magicthumb.css', $fixedFileContents);
    }
    magictoolbox_WordPress_MagicThumb_init() ;

    WordPress_MagicThumb_send_stat('install');

}

function WordPress_MagicThumb_deactivate () {

    //delete_option("WordPressMagicThumbCoreSettings");
    WordPress_MagicThumb_send_stat('uninstall');
}

function WordPress_MagicThumb_send_stat($action = '') {

    //NOTE: don't send from working copy
    if('working' == 'v5.11.12' || 'working' == 'v2.0.64') {
        return;
    }

    $hostname = 'www.magictoolbox.com';

    $url = preg_replace('/^https?:\/\//is', '', get_option("siteurl"));
    $url = urlencode(urldecode($url));

    global $wp_version;
    $platformVersion = isset($wp_version) ? $wp_version : '';

    $path = "api/stat/?action={$action}&tool_name=magicthumb&license=trial&tool_version=v2.0.64&module_version=v5.11.12&platform_name=wordpress&platform_version={$platformVersion}&url={$url}";
    $handle = @fsockopen($hostname, 80, $errno, $errstr, 30);
    if($handle) {
        $headers  = "GET /{$path} HTTP/1.1\r\n";
        $headers .= "Host: {$hostname}\r\n";
        $headers .= "Connection: Close\r\n\r\n";
        fwrite($handle, $headers);
        fclose($handle);
    }

}

function showMessage_WordPress_MagicThumb($message, $errormsg = false) {
    if ($errormsg) {
        echo '<div id="message" class="error">';
    } else {
        echo '<div id="message" class="updated fade">';
    }
    echo "<p><strong>$message</strong></p></div>";
}    


function showAdminMessages_WordPress_MagicThumb(){
    global $error_message;
    if (current_user_can('manage_options')) {
       showMessage_WordPress_MagicThumb($error_message,true);
    }
}


function plugin_get_version_WordPress_MagicThumb() {
    $plugin_data = get_plugin_data(str_replace('/plugin.php','.php',__FILE__));
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

function update_plugin_message_WordPress_MagicThumb() {
    $ver = json_decode(@file_get_contents('http://www.magictoolbox.com/api/platform/wordpress/version/'));
    if (empty($ver)) return false;
    $ver = str_replace('v','',$ver->version);
    $oldVer = plugin_get_version_WordPress_MagicThumb();
    if (version_compare($oldVer, $ver, '<')) {
        echo '<div id="message" class="updated fade">
                  <p>New version available! We recommend that you download the latest version of the plugin <a href="http://magictoolbox.com/magicthumb/modules/wordpress/">here</a>. </p>
              </div>';
    }
}

function MagicThumb_remove_update_nag($value) {
    if (isset($value->response)) {
        unset($value->response[ str_replace('/plugin','',plugin_basename(__FILE__)) ]);
    }
    return $value;
}

function  magictoolbox_WordPress_MagicThumb_init() {

    global $error_message;

    /* add filters and actions into WordPress */
    add_action("admin_menu", "magictoolbox_WordPress_MagicThumb_config_page_menu");

    //add_action("template_redirect", "magictoolbox_WordPress_MagicThumb_styles"); //load scripts and styles only for frontend
	add_action("wp_head", "magictoolbox_WordPress_MagicThumb_styles"); //load scripts and styles

    add_filter("the_content", "magictoolbox_WordPress_MagicThumb_create", 13); //filter content


    add_filter('site_transient_update_plugins', 'MagicThumb_remove_update_nag');
    add_filter( 'plugin_action_links', 'magictoolbox_WordPress_MagicThumb_links', 10, 2 );

    if (!file_exists(dirname(__FILE__) . '/core/magicthumb.js')) {
        $jsContents = file_get_contents('http://www.magictoolbox.com/static/magicthumb/trial/magicthumb.js');
        if (!empty($jsContents) && preg_match('/\/\*.*?\\\*/is',$jsContents)){
            if ( !is_writable(dirname(__FILE__) . '/core/')) {
                $error_message = 'The '.substr(dirname(__FILE__),strpos(dirname(__FILE__),'wp-content')).'/core/magicthumb.js file is missing. Please re-uplaod it.';
            }
            file_put_contents(dirname(__FILE__) . '/core/magicthumb.js', $jsContents);
            chmod(dirname(__FILE__) . '/core/magicthumb.js', 0777);
        } else {
            $error_message = 'The '.substr(dirname(__FILE__),strpos(dirname(__FILE__),'wp-content')).'/core/magicthumb.js file is missing. Please re-uplaod it.';
        }
    }
    if ($error_message) add_action('admin_notices', 'showAdminMessages_WordPress_MagicThumb');

    //add_filter("shopp_catalog", "magictoolbox_create", 1); //filter content for SHOPP plugin

    if(!isset($GLOBALS['magictoolbox']['WordPressMagicThumb'])) {
        require_once(dirname(__FILE__) . '/core/magicthumb.module.core.class.php');
        $coreClassName = "MagicThumbModuleCoreClass";
        $GLOBALS['magictoolbox']['WordPressMagicThumb'] = new $coreClassName;
        $coreClass = &$GLOBALS['magictoolbox']['WordPressMagicThumb'];
    }
    $coreClass = &$GLOBALS['magictoolbox']['WordPressMagicThumb'];
    /* get current settings */
    $settings = get_option("WordPressMagicThumbCoreSettings");
    if($settings !== false && is_array($settings) && !isset($_GET['reset_settings'])) {
        $coreClass->params->appendArray($settings);
    } else {
        update_option("WordPressMagicThumbCoreSettings", $coreClass->params->getArray());
    }
}


function WordPressMagicThumb_config_page() {
     magictoolbox_WordPress_MagicThumb_config_page('WordPressMagicThumb');
}

function magictoolbox_WordPress_MagicThumb_links( $links, $file ) {
    if ( $file == plugin_basename( dirname(__FILE__).'.php' ) ) {
        $links[] = '<a href="plugins.php?page=WordPressMagicThumb-config-page">'.__('Settings').'</a>';
    }
    return $links;
}

function magictoolbox_WordPress_MagicThumb_config_page_menu() {
    if(function_exists("add_submenu_page")) {
        $page = add_submenu_page("plugins.php", __("Magic Thumb Plugin Configuration"), __("Magic Thumb Configuration"), "manage_options", "WordPressMagicThumb-config-page", "WordPressMagicThumb_config_page");
    }
}

function  magictoolbox_WordPress_MagicThumb_config_page($id) {
    update_plugin_message_WordPress_MagicThumb();
    $settings = $GLOBALS['magictoolbox'][$id]->params->getArray();
    if(isset($_POST["submit"])) {
        /* save settings */
        foreach($settings as $name => $s) {
            if(isset($_POST["magicthumbsettings".ucwords(strtolower($name))])) {
                $v = $_POST["magicthumbsettings".ucwords(strtolower($name))];
                switch($s["type"]) {
                    case "num": $v = intval($v); break;
                    case "array": 
                        $v = trim($v);
                        if(!in_array($v,$s["values"])) $v = $s["default"];
                        break;
                    case "text":
                    default: $v = trim($v);
                }
                $s["value"] = $v;
                $settings[$name] = $s;                
            }
        }
        update_option($id . "CoreSettings", $settings);
        $GLOBALS['magictoolbox'][$id]->params->appendArray($settings);
    }
    
    $toolAbr = '';
    $abr = explode(" ", strtolower("Magic Thumb"));
    foreach ($abr as $word) $toolAbr .= $word{0};
    
     $corePath = preg_replace('/https?:\/\/[^\/]*/is', '', get_option("siteurl"));
     $corePath .= '/wp-content/'.preg_replace('/^.*?\/(plugins\/.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__))).'/core';
    ?>
	<style>
        .<?php echo $toolAbr; ?>params { margin:20px 0; width:90%; border:1px solid #dfdfdf; }
        .<?php echo $toolAbr; ?>params .params { margin:0; width:100%;}
        .<?php echo $toolAbr; ?>params .params th { <? /*white-space:nowrap; */ ?> vertical-align:middle; border-bottom:1px solid #dfdfdf; padding:15px 5px; font-weight:bold; background:#fff; text-align:left; padding:0 20px; }
        .<?php echo $toolAbr; ?>params .params td { vertical-align:middle; border-bottom:1px solid #dfdfdf; padding:10px 5px; background:#fff; width:100%; }
        .<?php echo $toolAbr; ?>params .params tr.back th, .<?php echo $toolAbr; ?>params .params tr.back td { background:#f9f9f9; }
        .<?php echo $toolAbr; ?>params .params tr.last th, .<?php echo $toolAbr; ?>params .params tr.last td { border:none; }
        .afterText {font-size:10px;font-style:normal;font-weight:normal;}
        .settingsTitle {font-size: 1.5em;font-weight: normal;margin: 1.7em 0 1em 0;}
        input[type="checkbox"],input[type="radio"] {margin:5px;vertical-align:middle !important;}
        td img {vertical-align:middle !important; margin-right:10px;}
        td span {vertical-align:middle !important; margin-right:10px;}
		#footer , #wpfooter {position:relative;}
    </style>
    
    <div class="icon32" id="icon-options-general"><br></div>
    <h2>Magic Thumb Settings</h2><br/>
    <p>Learn about all the <a href="http://www.magictoolbox.com/magicthumb/integration/" target="_blank">Magic Thumb&trade; settings and examples too!</a></p>
    <form action="" method="post" id="magicthumb-config-form">
            <?php
                $groups = array();
                $imgArray = array('zoom & expand','zoom&expand','yes','zoom','expand','swap images only','original','expanded','no','left','top left','top','top right', 'right', 'bottom right', 'bottom', 'bottom left'); //array for the images ordering

                foreach($settings as $name => $s) { 

                    if (strtolower($s['id']) == 'disable-expand' || strtolower($s['id']) == 'disable-zoom') continue;
                    if (strtolower($s['id']) == 'direction') continue;
                    if (!isset($groups[$s['group']])) {
                        $groups[$s['group']] = array();
                    }

                    $s['value'] = $GLOBALS['magictoolbox'][$id]->params->getValue($name);

                    if (strpos($s["label"],'(')) {
                        $before = substr($s["label"],0,strpos($s["label"],'('));
                        $after = ' '.str_replace(')','',substr($s["label"],strpos($s["label"],'(')+1));
                    } else {
                        $before = $s["label"];
                        $after = '';
                    }
                    if (strpos($after,'%')) $after = ' %';
                    if (strpos($after,'in pixels')) $after = ' pixels';
                    if (strpos($after,'milliseconds')) $after = ' milliseconds';

                    $html  .= '<tr>';
                    $html  .= '<th width="50%">';
                    $html  .= '<label for="magicthumbsettings'. ucwords(strtolower($name)).'">'.$before.'</label>';

                    if(($s['type'] != 'array') && isset($s['values'])) $html .= '<br/> <span class="afterText">' . implode(', ',$s['values']).'</span>';

                    $html .= '</th>';
                    $html .= '<td width="50%">';

                    switch($s["type"]) {
                        case "array": 
                                $rButtons = array();
                                foreach($s["values"] as $p) {
                                    $rButtons[strtolower($p)] = '<label><input type="radio" value="'.$p.'"'. ($s["value"]==$p?"checked=\"checked\"":"").' name="magicthumbsettings'.ucwords(strtolower($name)).'" id="magicthumbsettings'. ucwords(strtolower($name)).$p.'">';
                                    $pName = ucwords($p);
                                    if(strtolower($p) == "yes")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/yes.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "no")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/no.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "left")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "right")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "top")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "bottom")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "bottom left")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom-left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "bottom right")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/bottom-right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "top left")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top-left.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    elseif(strtolower($p) == "top right")
                                        $rButtons[strtolower($p)] .= '<img src="'.$corePath.'/admin_graphics/top-right.gif" alt="'.$pName.'" title="'.$pName.'" /></label>';
                                    else {
                                        if (strtolower($p) == 'load,hover') $p = 'Load & hover';
                                        if (strtolower($p) == 'load,click') $p = 'Load & click';
                                        $rButtons[strtolower($p)] .= '<span>'.ucwords($p).'</span></label>';
                                    }
                                }
                                foreach ($imgArray as $img){
                                    if (isset($rButtons[$img])) {
                                        $html .= $rButtons[$img];
                                        unset($rButtons[$img]);
                                    }
                                }
                                $html .= implode('',$rButtons);
                            break;
                        case "num": 
                        case "text": 
                        default:
                            if (strtolower($name) == 'message') { $width = 'style="width:95%;"';} else {$width = '';}
                            $html .= '<input '.$width.' type="text" name="magicthumbsettings'.ucwords(strtolower($name)).'" id="magicthumbsettings'. ucwords(strtolower($name)).'" value="'.$s["value"].'" />';
                            break;
                    }
                    $html .= '<span class="afterText">'.$after.'</span>';
                    $html .= '</td>';
                    $html .= '</tr>';
                    $groups[$s['group']][] = $html;
                    $html = '';
                }
            foreach ($groups as $name => $group) {
                $i = 0;
                $group[count($group)-1] = str_replace('<tr','<tr class="last"',$group[count($group)-1]); //set "last" class
                echo '<h3 class="settingsTitle">'.$name.'</h3>
                            <div class="'.$toolAbr.'params">
                            <table class="params" cellspacing="0">';
                foreach ($group as $g) {
                    if (++$i%2==0) { //set stripes
                        if (strpos($g,'class="last"')) {
                            $g = str_replace('class="last"','class="back last"',$g);
                        } else {
                            $g = str_replace('<tr','<tr class="back"',$g);
                        }
                    }
                    echo $g;
                }
                echo '</table> </div>';
            }
            ?>
            
            <p><input type="submit" name="submit" class="button-primary" value="Save settings" />&nbsp;<a href="plugins.php?page=WordPressMagicThumb-config-page&reset_settings=true">Reset to defaults</a></p>
        </form>

   
    </div>
    <div style="font-size:12px;margin:5px auto;text-align:center;">Learn more about the <a href="http://www.magictoolbox.com/magicthumb_integration/" target="_blank">customisation options</a></div>
    <?php
}



function  magictoolbox_WordPress_MagicThumb_styles() {
    if(!defined('MAGICTOOLBOX_MAGICTHUMB_HEADERS_LOADED')) {
        $plugin = $GLOBALS['magictoolbox']['WordPressMagicThumb'];
		if (function_exists('plugins_url')) {
			$core_url = plugins_url();
		} else {
			$core_url = get_option("siteurl").'/wp-content/plugins';
		}


        $path = preg_replace('/^.*?\/plugins\/(.*?)$/is', '$1', str_replace("\\","/",dirname(__FILE__)));
        $headers = $plugin->headers($core_url."/{$path}/core");
        echo $headers;
        define('MAGICTOOLBOX_MAGICTHUMB_HEADERS_LOADED', true);
    }
}



function  magictoolbox_WordPress_MagicThumb_create($content) {
    $plugin = $GLOBALS['magictoolbox']['WordPressMagicThumb'];
    /*$pattern = "<img([^>]*)(?:>)(?:[^<]*<\/img>)?";
    $pattern = "(?:<a([^>]*)>.*?){$pattern}(.*?)(?:<\/a>)";*/
    $pattern = "(?:<a([^>]*)>)[^<]*<img([^>]*)(?:>)(?:[^<]*<\/img>)?(.*?)[^<]*?<\/a>";


    $oldContent = $content;
        $content = preg_replace_callback("/{$pattern}/is","magictoolbox_WordPress_MagicThumb_callback",$content);
        if ($content == $oldContent) return $content;

      

    /*$content = str_replace('{MAGICTOOLBOX_'.strtoupper('magicthumb').'_MAIN_IMAGE_SELECTOR}',$GLOBALS['MAGICTOOLBOX_'.strtoupper('magicthumb').'_MAIN_IMAGE_SELECTOR'],$content);  //add main image selector to other
    $content = str_replace('{MAGICTOOLBOX_'.strtoupper('magicthumb').'_SELECTORS}','',$content); //if no selectors - remove constant
     onlyForModend  */


    if (!$plugin->params->checkValue('template','original') && $plugin->type == 'standard' && isset($GLOBALS['magictoolbox']['MagicThumb']['main'])) {
        // template helper class
        require_once(dirname(__FILE__) . '/core/magictoolbox.templatehelper.class.php');
        MagicToolboxTemplateHelperClass::setPath(dirname(__FILE__).'/core/templates');
        MagicToolboxTemplateHelperClass::setOptions($plugin->params);
        if (!WordPress_MagicThumb_page_check('WordPress')) { //do not render thumbs on category pages
            $thumbs = WordPress_MagicThumb_get_prepared_selectors();
        } else {
            $thumbs = array();
        }
        $html = MagicToolboxTemplateHelperClass::render(array(
            'main' => $GLOBALS['magictoolbox']['MagicThumb']['main'],
            'thumbs' => (count($thumbs) >= 1) ? $thumbs : array(),
            'pid' => $GLOBALS['magictoolbox']['prods_info']['product_id'],
        ));

        $content = str_replace('MAGICTOOLBOX_PLACEHOLDER', $html, $content);
    } else if ($plugin->params->checkValue('template','original') || $plugin->type != 'standard') {
        $html = $GLOBALS['magictoolbox']['MagicThumb']['main'];
        $content = str_replace('MAGICTOOLBOX_PLACEHOLDER', $html, $content);
    }


    return $content;
}
function  magictoolbox_WordPress_MagicThumb_callback($matches) {
    $plugin = $GLOBALS['magictoolbox']['WordPressMagicThumb'];


    if(!$plugin->params->checkValue('class', 'all')) {
        if(!preg_match("/class\s*=\s*[\'\"]\s*(?:[^\"\'\s]*\s)*" . preg_quote(strtolower($plugin->params->getValue('class')), '/') . "(?:\s[^\"\'\s]*)*\s*[\'\"]/iUs",$matches[0])) {
            if(!$plugin->params->checkValue('nextgen-gallery', 'no')) {
                if (!preg_match("/class\s*=\s*[\'\"]\s*(?:[^\"\'\s]*\s)*shutterset_.*?(?:\s[^\"\'\s]*)*\s*[\'\"]/iUs",$matches[0])) {
                    return $matches[0];
                }
            } else {
                return $matches[0];
            }
        }
    } else {
        if (preg_match("/class\s*=\s*[\'\"]\s*(?:[^\"\'\s]*\s)*Magic[A-Za-z]+?(?:\s[^\"\'\s]*)*\s*[\'\"]/iUs",$matches[0])) { //if already modified
            return $matches[0];
        }
    }


    $alignclass = preg_replace('/^.*?align(left|right|center|none).*$/is', '$1', $matches[2]);
    if($alignclass != $matches[2]) {
        $alignclass = ' align'.$alignclass;
    } else {
        $alignclass='';
        $float = preg_replace('/^.*?float:\s*(left|right|none).*$/is', '$1', $matches[2]);
        if($float == $matches[2]) {
            $float = '';
        } else {
            $float = ' float: ' . $float . ';';
        }
    }

    // get needed attributes 
    global $wp_query;
    $alt = preg_replace("/^.*?alt\s*=\s*[\"\'](.*?)[\"\'].*$/is","$1",$matches[2]);
    $img = preg_replace("/^.*?href\s*=\s*[\"\'](.*?)[\"\'].*$/is","$1",$matches[1]);
    $thumb = preg_replace("/^.*?src\s*=\s*[\"\'](.*?)[\"\'].*$/is","$1",$matches[2]);
    $title = preg_replace("/^.*?title\s*=\s*[\"\'](.*?)[\"\'].*$/is","$1",$matches[0]);
    if($title == $matches[0]) unset($title);
    $id = preg_replace("/^.*?id\s*=\s*[\"\'](.*?)[\"\'].*$/is","$1",$matches[1]);
    if($id == $matches[1]) unset($id);

    

    $aStyles = $matches[1];
    $imgStyles = $matches[2];
    // remove id,rel,class,href,title,rev attributes from link 
    $description = $alt;
    $result = $plugin->template(compact('img','thumb','id','title','description'));
      //restore after the rel was generated
    $plugin->params->params['disable-expand'] = $plugin->general->params['disable-expand'];
    $plugin->params->params['disable-zoom'] = $plugin->general->params['disable-zoom'];

	$result = preg_replace('/id=\"MagicThumb[^\"]*?'. $id.'\"/is', 'id="'.$id.'"', $result);



    
    //$result = "<div style=\"width:{$divWidth}px;{$float}\" class=\"MagicToolboxContainer {$alignclass}\">{$result}</div>";
      //$result = "<span>{$result}</span>";
      $result = preg_replace('/\<div\s+class\=\"MagicToolboxMessage\"\>(.*?)\<\/div\>/is','<span class="MagicToolboxMessage">$1</span>',$result); // change message div to span
      $result = "<span class=\"MagicToolboxContainer {$alignclass}\">{$result}</span>";


    return $result;
    //return $matches[0];
}


function WordPress_MagicThumb_get_post_attachments()  {
    $args = array(
            'post_type' => 'attachment',
            'numberposts' => '-1',
            'post_status' => null,
            'post_parent' => $post_id
        );

    $attachments = get_posts($args);
    return $attachments;
}



?>
