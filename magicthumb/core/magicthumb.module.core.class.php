<?php

if(!defined('MagicThumbModuleCoreClassLoaded')) {

    define('MagicThumbModuleCoreClassLoaded', true);

    require_once(dirname(__FILE__) . '/magictoolbox.params.class.php');

    class MagicThumbModuleCoreClass {
        var $params;
        var $general;//initial parameters
        var $id;
        var $type = 'standard';

        function MagicThumbModuleCoreClass() {
            $this->params = new MagicToolboxParamsClass();
            $this->general = new MagicToolboxParamsClass();
            $this->_paramDefaults();
        }

        function headers($jsPath = '', $cssPath = null, $notCheck = false) {

            //to prevent multiple displaying of headers
            if(!defined('MagicThumbModuleHeaders')) {
                define('MagicThumbModuleHeaders', true);
            } else {
                return '';
            }
            if($cssPath == null) {
                $cssPath = $jsPath;
            }
            $headers = array();
            $headers[] = '<!-- Magic Thumb WordPress module version v5.11.12 [v1.4.6:v2.0.64] -->';
            $headers[] = '<link type="text/css" href="' . $cssPath . '/magicthumb.css" rel="stylesheet" media="screen" />';
            $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magicthumb.js"></script>';
            $headers[] = "<script type=\"text/javascript\">\n\tMagicThumb.options = {\n\t\t".implode(",\n\t\t", $this->options($notCheck))."\n\t}\n</script>\n";
            return implode("\r\n", $headers);

        }

        function options($notCheck = false) {

            if($this->params->checkValue('restore-speed', '-1')) {
                $this->params->set('restore-speed', $this->params->getValue('expand-speed'));
            }

            $conf = Array(
                "'expand-speed': " . $this->params->getValue("expand-speed"),
                "'restore-speed': " . $this->params->getValue("restore-speed"),
                "'expand-effect': '" . $this->params->getValue("expand-effect") . "'",
                "'restore-effect': '" . $this->params->getValue("restore-effect") . "'",
                "'expand-trigger': '" . $this->params->getValue("expand-trigger") . "'",
                "'restore-trigger': '" . $this->params->getValue("restore-trigger") . "'",
                "'expand-trigger-delay': " . $this->params->getValue("expand-trigger-delay"),
                "'expand-align': '" . $this->params->getValue("expand-align") . "'",
                "'expand-position': '" . $this->params->getValue("expand-position") . "'",
                "'image-size': '" . $this->params->getValue("image-size") . "'",
                //"'keep-thumbnail': " . $this->params->getValue("keep-thumbnail"),
                //"'click-to-initialize': " . $this->params->getValue("click-to-initialize"),
                "'background-color': '" . $this->params->getValue("background-color") . "'",
                "'background-opacity': " . $this->params->getValue("background-opacity"),
                "'background-speed': " . $this->params->getValue("background-speed"),
                "'caption-speed': " . $this->params->getValue("caption-speed"),
                "'caption-position': '" . $this->params->getValue("caption-position") . "'",
                "'caption-height': " . $this->params->getValue("caption-height"),
                "'caption-width': " . $this->params->getValue("caption-width"),
                "'buttons': '" . $this->params->getValue("buttons") . "'",
                "'buttons-position': '" . $this->params->getValue("buttons-position") . "'",
                "'buttons-display': '" . $this->params->getValue("buttons-display") . "'",
                //"'show-loading': " . $this->params->getValue("show-loading"),
                "'loading-msg': '" . $this->params->getValue("loading-msg") . "'",
                "'loading-opacity': " . $this->params->getValue("loading-opacity"),
                "'swap-image': '" . $this->params->getValue("swap-image") . "'",
                "'swap-image-delay': " . $this->params->getValue("swap-image-delay"),
                "'slideshow-effect': '" . $this->params->getValue("slideshow-effect") . "'",
                "'slideshow-speed': " . $this->params->getValue("slideshow-speed"),
                //"'slideshow-loop': " . $this->params->getValue("slideshow-loop"),
                //"'link': '" . $this->params->getValue("link") . "'",
                //"'link-target': '" . $this->params->getValue("link-target") . "'",
                //"'thumb-id': '" . $this->params->getValue("thumb-id") . "'",
                //"'group': '" . $this->params->getValue("group") . "'",
                //"'keyboard': " . $this->params->getValue("keyboard"),
                //"'keyboard-ctrl': " . $this->params->getValue("keyboard-ctrl"),
                "'z-index': " . $this->params->getValue("z-index"),
            );

            if($notCheck) {
                $conf = array_merge($conf, array(
                    "'keep-thumbnail': " . $this->params->getValue("keep-thumbnail"),
                    "'click-to-initialize': " . $this->params->getValue("click-to-initialize"),
                    "'show-loading': " . $this->params->getValue("show-loading"),
                    "'slideshow-loop': " . $this->params->getValue("slideshow-loop"),
                    "'keyboard': " . $this->params->getValue("keyboard"),
                    "'keyboard-ctrl': " . $this->params->getValue("keyboard-ctrl"),
                ));
            } else {
                $conf = array_merge($conf, array(
                    "'keep-thumbnail': " . ($this->params->checkValue('keep-thumbnail', 'Yes')?'true':'false'),
                    "'click-to-initialize': " . ($this->params->checkValue('click-to-initialize', 'Yes')?'true':'false'),
                    "'show-loading': " . ($this->params->checkValue('show-loading', 'Yes')?'true':'false'),
                    "'slideshow-loop': " . ($this->params->checkValue('slideshow-loop', 'Yes')?'true':'false'),
                    "'keyboard': " . ($this->params->checkValue('keyboard', 'Yes')?'true':'false'),
                    "'keyboard-ctrl': " . ($this->params->checkValue('keyboard-ctrl', 'Yes')?'true':'false'),
                ));
            }

            $cSource = $this->params->get("caption-source");
            if(is_array($cSource) && isset($cSource['core']) && $cSource['core']) {
                $conf = array_merge($conf, array(
                    "'caption-source': '" . $this->params->getValue("caption-source") . "'"
                ));
            } else {
                $conf = array_merge($conf, array(
                    "'caption-source': 'span'"
                ));
            }

            return $conf;

        }

        function template($params) {
            extract($params);

            if(!isset($img) || empty($img)) return false;
            if(!isset($thumb) || empty($thumb)) $thumb = $img;
            if(!isset($id) || empty($id)) $id = md5($img);

            $this->id = $id;

            if(!isset($alt) || empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
            if(!isset($title)) $title = '';
            if(empty($alt) && !empty($title)) $alt = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
            if(!isset($description)) $description = '';

            if($this->params->checkValue('show-caption', 'Yes')) {
                $captionSource = $this->params->getValue('caption-source');
                $captionSource = trim($captionSource);
                if(strtolower($captionSource) == 'all' || strtolower($captionSource) == 'both') {
                    $captionSource = $this->params->getValues('caption-source');
                } else {
                    $captionSource = explode(',',$captionSource);
                }
                $fullTitle = array();
                foreach($captionSource as $caption) {
                    $caption = trim($caption);
                    $caption = strtolower($caption);
                    $caption = lcfirst(implode(explode(' ', ucwords($caption))));
                    if($caption == 'all' || $caption == 'both') continue;
                    if(!isset($$caption)) continue;
                    if($$caption == '') continue;
                    if($caption == 'title') {
                        $fullTitle[] = '<b>' . $$caption . '</b>';
                    } else {
                        $fullTitle[] = $$caption;
                    }
                }
                $title = implode('<br/>',$fullTitle);
            } else $title = '';
            $title = trim(preg_replace("/\s+/is", " ", $title));
            if(!empty($title)) {
                $title = preg_replace("/<(\/?)a([^>]*)>/is", "[$1a$2]", $title);
                $title = "<span>{$title}</span>";
            }

            if(!isset($width) || empty($width)) $width = "";
            else $width = " width=\"{$width}\"";
            if(!isset($height) || empty($height)) $height = "";
            else $height = " height=\"{$height}\"";

            if($this->params->checkValue('show-message', 'Yes')) {
                $message = '<div class="MagicToolboxMessage">' . $this->params->getValue('message') . '</div>';
            } else $message = '';

            $rel = $this->getRel();
            if(isset($link) && !empty($link)) {
                $rel .= 'link: ' . ($link) . ';';
            }
            if(isset($group) && !empty($group)) {
                $rel .= 'group: ' . ($group) . ';';
            }
            if(!empty($rel)) $rel = 'rel="'.$rel.'"';

            return "<a class=\"MagicThumb\" id=\"MagicThumbImage{$id}\" href=\"{$img}\" {$rel}><img itemprop=\"image\"{$width}{$height} src=\"{$thumb}\" alt=\"{$alt}\" />{$title}</a><br />{$message}";
        }

        function subTemplate($params) {

            if($this->params->checkValue('use-selectors', 'No')) {
                unset($params['id']);
                $this->params->set('show-message', 'No');
                return $this->template($params);
            } else {
                extract($params);

                if(!isset($img) || empty($img)) return false;
                if(!isset($medium) || empty($medium)) $medium = $img;
                if(!isset($thumb) || empty($thumb)) $thumb = $img;
                if(!isset($id) || empty($id)) $id = $this->id;

                if(!isset($alt) || empty($alt)) {
                    $alt = '';
                } else {
                    $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
                }
                if(!isset($title)) $title = '';
                if(empty($alt) && !empty($title)) $alt = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                if(!isset($description)) $description = '';

                if($this->params->checkValue('show-caption', 'Yes')) {
                    $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                    if(empty($alt)) $alt = $title;
                } else $title = '';
                $title = trim(preg_replace("/\s+/is", " ", $title));
                if(!empty($title)) {
                    $title = preg_replace("/<(\/?)a([^>]*)>/is", "[$1a$2]", $title);
                    $title = " title=\"{$title}\"";
                }

                if(!isset($width) || empty($width)) $width = "";
                else $width = " width=\"{$width}\"";
                if(!isset($height) || empty($height)) $height = "";
                else $height = " height=\"{$height}\"";

                return "<a{$title} href=\"{$img}\" rel=\"thumb-id: MagicThumbImage{$id};caption-source: a:title;\" rev=\"$medium\"><img{$width}{$height} src=\"{$thumb}\" alt=\"{$alt}\" /></a>";
            }
        }

        function getRel() {
            if(defined('MagicToolboxOptionsLoaded')) {
                return $this->params->serialize() . ';';
            }
            $rel = array();
            if(count($this->general->params)) {
                foreach($this->general->params as $name => $param) {
                    if($this->params->checkValue($name, $param['value'])) continue;
                    switch($name) {
                        case 'expand-speed':
                            $rel[] = 'expand-speed: ' . $this->params->getValue('expand-speed');
                            break;
                        case 'restore-speed':
                            if($this->params->checkValue('restore-speed', '-1')) {
                                $rel[] = 'restore-speed: ' . $this->params->getValue('expand-speed');
                            } else {
                                $rel[] = 'restore-speed: ' . $this->params->getValue('restore-speed');
                            }
                            break;
                        case 'expand-effect':
                            $rel[] = 'expand-effect: ' . $this->params->getValue('expand-effect');
                            break;
                        case 'restore-effect':
                            $rel[] = 'restore-effect: ' . $this->params->getValue('restore-effect');
                            break;
                        case 'expand-trigger':
                            $rel[] = 'expand-trigger: ' . $this->params->getValue('expand-trigger');
                            break;
                        case 'restore-trigger':
                            $rel[] = 'restore-trigger: ' . $this->params->getValue('restore-trigger');
                            break;
                        case 'expand-trigger-delay':
                            $rel[] = 'expand-trigger-delay: ' . $this->params->getValue('expand-trigger-delay');
                            break;
                        case 'expand-align':
                            $rel[] = 'expand-align: ' . $this->params->getValue('expand-align');
                            break;
                        case 'expand-position':
                            $rel[] = 'expand-position: ' . $this->params->getValue('expand-position');
                            break;
                        case 'image-size':
                            $rel[] = 'image-size: ' . $this->params->getValue('image-size');
                            break;
                        case 'background-color':
                            $rel[] = 'background-color: ' . $this->params->getValue('background-color');
                            break;
                        case 'background-opacity':
                            $rel[] = 'background-opacity: ' . $this->params->getValue('background-opacity');
                            break;
                        case 'background-speed':
                            $rel[] = 'background-speed: ' . $this->params->getValue('background-speed');
                            break;
                        case 'caption-speed':
                            $rel[] = 'caption-speed: ' . $this->params->getValue('caption-speed');
                            break;
                        case 'caption-position':
                            $rel[] = 'caption-position: ' . $this->params->getValue('caption-position');
                            break;
                        case 'caption-height':
                            $rel[] = 'caption-height: ' . $this->params->getValue('caption-height');
                            break;
                        case 'caption-width':
                            $rel[] = 'caption-width: ' . $this->params->getValue('caption-width');
                            break;
                        case 'buttons':
                            $rel[] = 'buttons: ' . $this->params->getValue('buttons');
                            break;
                        case 'buttons-position':
                            $rel[] = 'buttons-position: ' . $this->params->getValue('buttons-position');
                            break;
                        case 'buttons-display':
                            $rel[] = 'buttons-display: ' . $this->params->getValue('buttons-display');
                            break;
                        case 'loading-msg':
                            $rel[] = 'loading-msg: ' . $this->params->getValue('loading-msg');
                            break;
                        case 'loading-opacity':
                            $rel[] = 'loading-opacity: ' . $this->params->getValue('loading-opacity');
                            break;
                        case 'swap-image':
                            $rel[] = 'swap-image: ' . $this->params->getValue('swap-image');
                            break;
                        case 'swap-image-delay':
                            $rel[] = 'swap-image-delay: ' . $this->params->getValue('swap-image-delay');
                            break;
                        case 'slideshow-effect':
                            $rel[] = 'slideshow-effect: ' . $this->params->getValue('slideshow-effect');
                            break;
                        case 'slideshow-speed':
                            $rel[] = 'slideshow-speed: ' . $this->params->getValue('slideshow-speed');
                            break;
                        case 'z-index':
                            $rel[] = 'z-index: ' . $this->params->getValue('z-index');
                            break;
                        case 'keep-thumbnail':
                            $rel[] = 'keep-thumbnail: ' . ($this->params->checkValue('keep-thumbnail', 'Yes')?'true':'false');
                            break;
                        case 'click-to-initialize':
                            $rel[] = 'click-to-initialize: ' . ($this->params->checkValue('click-to-initialize', 'Yes')?'true':'false');
                            break;
                        case 'show-loading':
                            $rel[] = 'show-loading: ' . ($this->params->checkValue('show-loading', 'Yes')?'true':'false');
                            break;
                        case 'slideshow-loop':
                            $rel[] = 'slideshow-loop: ' . ($this->params->checkValue('slideshow-loop', 'Yes')?'true':'false');
                            break;
                        case 'keyboard':
                            $rel[] = 'keyboard: ' . ($this->params->checkValue('keyboard', 'Yes')?'true':'false');
                            break;
                        case 'keyboard-ctrl':
                            $rel[] = 'keyboard-ctrl: ' . ($this->params->checkValue('keyboard-ctrl', 'Yes')?'true':'false');
                            break;
                    }
                }
            }
            if(count($rel)) {
                $rel = implode(';',$rel) . ';';
            } else {
                $rel = '';
            }
            return $rel;
        }

//         function getRel($notCheck = false) {
//             return '';
//         }

        function addonsTemplate() {
            return '';
        }

        function _paramDefaults() {
            $params = array("image-size"=>array("id"=>"image-size","group"=>"Positioning and Geometry","order"=>"210","default"=>"fit-screen","label"=>"Size of the enlarged image","type"=>"array","subType"=>"select","values"=>array("original","fit-screen"),"scope"=>"tool"),"expand-position"=>array("id"=>"expand-position","group"=>"Positioning and Geometry","order"=>"220","default"=>"center","label"=>"Precise position of enlarged image (px)","type"=>"text","description"=>"The value can be 'center' or coordinates. E.g. 'top:0, left:0' or 'bottom:100, left:100'","scope"=>"tool"),"expand-align"=>array("id"=>"expand-align","group"=>"Positioning and Geometry","order"=>"230","default"=>"screen","label"=>"Align expanded image relative to screen or thumbnail","type"=>"array","subType"=>"select","values"=>array("screen","image"),"scope"=>"tool"),"expand-effect"=>array("id"=>"expand-effect","group"=>"Effects","order"=>"10","default"=>"linear","label"=>"Effect while expanding image","type"=>"array","subType"=>"select","values"=>array("linear","cubic","back","elastic","bounce"),"scope"=>"tool"),"restore-effect"=>array("id"=>"restore-effect","group"=>"Effects","order"=>"20","default"=>"linear","label"=>"Effect while restoring image","type"=>"array","subType"=>"select","values"=>array("linear","cubic","back","elastic","bounce"),"scope"=>"tool"),"expand-speed"=>array("id"=>"expand-speed","group"=>"Effects","order"=>"30","default"=>"500","label"=>"Expand duration (milliseconds: 0-10000)","type"=>"num","scope"=>"tool"),"restore-speed"=>array("id"=>"restore-speed","group"=>"Effects","order"=>"40","default"=>"-1","label"=>"Restore duration (milliseconds: 0-10000, -1: use expand duration value)","type"=>"num","scope"=>"tool"),"expand-trigger"=>array("id"=>"expand-trigger","group"=>"Effects","order"=>"50","default"=>"click","label"=>"Trigger for the enlarge effect","type"=>"array","subType"=>"select","values"=>array("click","mouseover"),"scope"=>"tool"),"expand-trigger-delay"=>array("id"=>"expand-trigger-delay","group"=>"Effects","order"=>"60","default"=>"500","label"=>"Delay before mouseover triggers expand effect (milliseconds: 0 or larger)","type"=>"num","scope"=>"tool"),"restore-trigger"=>array("id"=>"restore-trigger","group"=>"Effects","order"=>"70","default"=>"auto","label"=>"Trigger to restore image to its small state","type"=>"array","subType"=>"select","values"=>array("auto","click","mouseout"),"scope"=>"tool"),"keep-thumbnail"=>array("id"=>"keep-thumbnail","group"=>"Effects","order"=>"80","default"=>"Yes","label"=>"Show/hide thumbnail when image enlarged","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"swap-image"=>array("id"=>"swap-image","group"=>"Multiple images","order"=>"210","default"=>"click","label"=>"Method to switch between multiple images","type"=>"array","subType"=>"radio","values"=>array("click","mouseover"),"scope"=>"tool"),"swap-image-delay"=>array("id"=>"swap-image-delay","group"=>"Multiple images","order"=>"220","default"=>"100","label"=>"Delay before switching thumbnails (milliseconds: 0 or larger)","type"=>"num","scope"=>"tool"),"click-to-initialize"=>array("id"=>"click-to-initialize","group"=>"Initialization","order"=>"10","default"=>"No","label"=>"Click to download large image","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"show-loading"=>array("id"=>"show-loading","group"=>"Initialization","order"=>"20","default"=>"Yes","label"=>"Show or not loading box","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"loading-msg"=>array("id"=>"loading-msg","group"=>"Initialization","order"=>"30","default"=>"Loading","label"=>"Text of the loading message","type"=>"text","scope"=>"tool"),"loading-opacity"=>array("id"=>"loading-opacity","group"=>"Initialization","order"=>"40","default"=>"75","label"=>"Opacity of the loading box (0 to 100)","type"=>"num","scope"=>"tool"),"show-caption"=>array("id"=>"show-caption","group"=>"Title and Caption","order"=>"20","default"=>"Yes","label"=>"Show caption","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"caption-source"=>array("id"=>"caption-source","group"=>"Title and Caption","order"=>"30","default"=>"Title","label"=>"Caption source","type"=>"array","subType"=>"select","values"=>array("Title","Description","Both")),"caption-width"=>array("id"=>"caption-width","group"=>"Title and Caption","order"=>"40","default"=>"300","label"=>"Max width of bottom caption (pixels: 0 or larger)","type"=>"num","scope"=>"tool"),"caption-height"=>array("id"=>"caption-height","group"=>"Title and Caption","order"=>"50","default"=>"300","label"=>"Max height of bottom caption (pixels: 0 or larger)","type"=>"num","scope"=>"tool"),"caption-position"=>array("id"=>"caption-position","group"=>"Title and Caption","order"=>"60","default"=>"bottom","label"=>"Where to position the caption","type"=>"array","subType"=>"select","values"=>array("bottom","right","left"),"scope"=>"tool"),"caption-speed"=>array("id"=>"caption-speed","group"=>"Title and Caption","order"=>"70","default"=>"250","label"=>"Speed of the caption slide effect (milliseconds: 0 or larger)","type"=>"num","scope"=>"tool"),"class"=>array("id"=>"class","group"=>"Miscellaneous","order"=>"20","default"=>"MagicThumb","label"=>"Class Name","type"=>"array","subType"=>"select","values"=>array("all","MagicThumb")),"nextgen-gallery"=>array("id"=>"nextgen-gallery","group"=>"Miscellaneous","order"=>"24","default"=>"No","label"=>"Apply effect to NextGen gallery images","type"=>"array","subType"=>"select","values"=>array("Yes","No")),"show-message"=>array("id"=>"show-message","group"=>"Miscellaneous","order"=>"500","default"=>"Yes","label"=>"Show message under image?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"message"=>array("id"=>"message","group"=>"Miscellaneous","order"=>"510","default"=>"Click to enlarge","label"=>"Message under images","type"=>"text"),"background-opacity"=>array("id"=>"background-opacity","group"=>"Background","order"=>"10","default"=>"0","label"=>"Opacity of the background effect (0-100)","type"=>"num","scope"=>"tool"),"background-color"=>array("id"=>"background-color","group"=>"Background","order"=>"20","default"=>"#000000","label"=>"Fade background color (RGB)","type"=>"text","scope"=>"tool"),"background-speed"=>array("id"=>"background-speed","group"=>"Background","order"=>"30","default"=>"200","label"=>"Speed of the fade effect (milliseconds: 0 or larger)","type"=>"num","scope"=>"tool"),"buttons"=>array("id"=>"buttons","group"=>"Buttons","order"=>"10","default"=>"show","label"=>"Whether to show navigation buttons","type"=>"array","subType"=>"select","values"=>array("show","hide","autohide"),"scope"=>"tool"),"buttons-display"=>array("id"=>"buttons-display","group"=>"Buttons","order"=>"20","default"=>"previous, next, close","label"=>"Display button","type"=>"text","description"=>"Show all three buttons or just one or two. E.g. 'previous, next' or 'close, next'","scope"=>"tool"),"buttons-position"=>array("id"=>"buttons-position","group"=>"Buttons","order"=>"30","default"=>"auto","label"=>"Location of navigation buttons","type"=>"array","subType"=>"select","values"=>array("auto","top left","top right","bottom left","bottom right"),"scope"=>"tool"),"slideshow-effect"=>array("id"=>"slideshow-effect","group"=>"Expand mode","order"=>"10","default"=>"dissolve","label"=>"Visual effect for switching images","type"=>"array","subType"=>"select","values"=>array("dissolve","fade","expand"),"scope"=>"tool"),"slideshow-loop"=>array("id"=>"slideshow-loop","group"=>"Expand mode","order"=>"20","default"=>"Yes","label"=>"Restart slideshow after last image","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"slideshow-speed"=>array("id"=>"slideshow-speed","group"=>"Expand mode","order"=>"30","default"=>"800","label"=>"Speed of slideshow effect (milliseconds: 0 or larger)","type"=>"num","scope"=>"tool"),"z-index"=>array("id"=>"z-index","group"=>"Expand mode","order"=>"40","default"=>"10001","label"=>"The z-index for the enlarged image","type"=>"num","scope"=>"tool"),"keyboard"=>array("id"=>"keyboard","group"=>"Expand mode","order"=>"50","default"=>"Yes","label"=>"Ability to use keyboard shortcuts","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"keyboard-ctrl"=>array("id"=>"keyboard-ctrl","group"=>"Expand mode","order"=>"60","default"=>"No","label"=>"Require Ctrl key to permit shortcuts","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"));
            $this->params->appendArray($params);
        }
    }

}
?>
