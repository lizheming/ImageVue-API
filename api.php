<?php
  error_reporting(E_ALL ^ E_NOTICE);
	function format($data) {
	  if(isset($_GET['callback'])) {
	    return $_GET['callback'] . '(' . json_encode($data) . ')';          
	  } else {
	    return json_encode($data);
	  } 
	}
	function get_folder($folder, $start, $max) {
		$gallery = array();
		$temp = array();
		$tmp = array();
	  foreach($folder as $item) {
		  if($item['page'] == 'gallery' && $item['hidden'] != 'true' && $item['password'] == '') {
		    $temp = get_object_vars($item);
		    $gallery[$temp['@attributes']['name']] = $temp['@attributes'];
		    if(isset($item -> folder)) {
		      $temp = get_object_vars($item-> folder);
		      $gallery[$temp['@attributes']['name']] = $temp['@attributes'];
		    }
		  }
	  }		
	  if($start != '' && $max != '') {
	  	$album = array();
	  	foreach($gallery as $item) {
	  	  $album[] = $item;	
	  	}
	    for($i=$start;$i < $start+$max;$i++) {
	      $tmp[$album[$i]['name']] = $album[$i];	
	    }	
	    $gallery = $tmp;
	  }
	  return $gallery;
	}
  if(!isset($_GET['method'])) $_GET['method'] = '';
  //获取存放图片文件夹
  $xml = simplexml_load_file('./iv-includes/include/config.xml');
  $picdir  = $xml-> imagevue-> settings-> contentfolder;
  //得到网站地址
  $website = "http://".$_SERVER['HTTP_HOST'].str_replace("api2.php","",$_SERVER["SCRIPT_NAME"]);
  switch($_GET['method']) {
  	case 'get.gallery.name':
  	  $xml = simplexml_load_file($picdir . 'folders.xml');
  	  if(!isset($_GET['start-index'])) $_GET['start-index'] = '';
  	  if(!isset($_GET['max-results'])) $_GET['max-results'] = '';
  	  $gallery = get_folder($xml-> folder-> folder, $_GET['start-index'], $_GET['max-results']);
  	  $album = array();
  	  foreach($gallery as $item) {
  	    $album[] = $item['name'];	
  	  }
  	  echo format($album);
  	  break;
    
    /*获取某一相册的相册信息*/ 
  	case 'get.gallery.info':
      //判断是否存在该相册//若不存在则返回false
  	  if(!isset($_GET['name'])) {
  	    echo format('false');	
  	  } else {
	  	  $xml = simplexml_load_file($picdir . 'folders.xml');
	  	  $gallery = get_folder($xml-> folder-> folder, '', '');
	  	  if(!isset($gallery[$_GET['name']])){
	  	    echo format('false');	
	  	  } else {
	  	    echo format($gallery[$_GET['name']]);
	  	  }
  	  }
  	  break;
  	
    /*获取特定相册下的所有相片*/
    case 'get.photos':
      //判断是否存在该相册//若不存在则返回false
  	  if(!isset($_GET['name'])) {
  	    echo format('false');	
  	  } else {
	  	  $xml = simplexml_load_file($picdir . 'folders.xml');
	  	  $gallery = get_folder($xml-> folder-> folder, '', '');
	  	  if(!isset($gallery[$_GET['name']])){
	  	    echo format('false');	
	  	  } else {
	  	    $path = $gallery[$_GET['name']]['path'];
	  	    $xml = simplexml_load_file($path . 'folderdata.xml');
	  	    $photos = array();
          /*分别得到有设置起始结果和无起始结果的值*/
	  	    if(!isset($_GET['start-index']) OR !isset($_GET['max-results'])) {
		  	    foreach($xml->file as $item) {
		  	      $temp = get_object_vars($item);
		  	      $photo = $temp['@attributes'];
		  	      $photo['file'] = $photo['name'];
              //自定义增加原图绝对地址
		  	      $photo['url'] = $website . $path . $photo['file'];
              //自定义增加缩略图绝对地址
		  	      $photo['thumbnail'] = $website . $path . 'tn_' . substr($photo['file'], 0, -3) . 'jpg';
		  	      $photos[] = $photo;
		  	    }
	  	    } else {
	  	      $start = $_GET['start-index'];
	  	      $end = $start + $_GET['max-results'];
	  	      for($i=$start;$i< $end;$i++) {
	  	      	$item = $xml->file[$i-1];
	  	        $temp = get_object_vars($item);	
		  	      $photo = $temp['@attributes'];
		  	      $photo['file'] = $photo['name'];
              //自定义增加原图绝对地址
		  	      $photo['url'] = $website . $path . $photo['file'];
              //自定义增加缩略图绝对地址
		  	      $photo['thumbnail'] = $website . $path . 'tn_' . substr($photo['file'], 0, -3) . 'jpg';
		  	      $photos[] = $photo;
	  	      }	
	  	    }
	  	    echo format($photos);
	  	  }
	  	}
	  	break;
 
    /*获取图片信息*/
    case 'get.photo':
        if(!isset($_GET['album']) OR !isset($_GET['photo'])) {
          echo format('false');	
        } else {
		  	  $xml = simplexml_load_file($picdir . 'folders.xml');
		  	  $gallery = get_folder($xml-> folder-> folder, '', '');
	  	    $path = $gallery[$_GET['album']]['path'];
	  	    $xml = simplexml_load_file($path . 'folderdata.xml');
	  	    foreach($xml->file as $item) {
	  	      if($item['name'] == $_GET['photo']) {
		  	      $temp = get_object_vars($item);
		  	      $photo = $temp['@attributes'];
		  	      $photo['file'] = $photo['name'];
              //自定义增加原图绝对地址
		  	      $photo['url'] = $website . $path . $photo['file'];
              //自定义增加缩略图绝对地址
		  	      $photo['thumbnail'] = $website . $path . 'tn_' . substr($photo['file'], 0, -3) . 'jpg';
	  	      }	
	  	    }
	  	    echo format($photo);
        }
      break;
    /*获取某相册的文件总数//不包括文件夹*/
    case 'get.gallery.filecount':
      //判断是否存在该相册//若不存在则返回false
  	  if(!isset($_GET['name'])) {
  	    echo format('false');	
  	  } else {
	  	  $xml = simplexml_load_file($picdir . 'folders.xml');
	  	  $gallery = get_folder($xml-> folder-> folder, '', '');
	  	  if(!isset($gallery[$_GET['name']])){
	  	    echo format('false');	
	  	  } else {
	  	  	echo format($gallery[$_GET['name']]['totalFileCount']);
	  	  }
	  	}
	  	break;
	  	
    /*获取相册的作用属性*/         
    case 'get.gallery.page':
      //判断是否存在该相册//若不存在则返回false
  	  if(!isset($_GET['name'])) {
  	    echo format('false');	
  	  } else {
	  	  $xml = simplexml_load_file($picdir . 'folders.xml');
	  	  $gallery = get_folder($xml-> folder-> folder, '', '');
	  	  $page = $gallery[$_GET['name']]['page'];
	  	  echo format($page);
      }
    break;
    
    /*获取某相册的父相册//若为子相册则返回上一级父相册若本身是父相册则返回false*/
    case 'get.gallery.parent':
      //判断是否存在该相册//若不存在则返回false
  	  if(!isset($_GET['name'])) {
  	    echo format('false');	
  	  } else {
	  	  $xml = simplexml_load_file($picdir . 'folders.xml');
	  	  $gallery = get_folder($xml-> folder-> folder, '', '');
        $folder = explode('/', $gallery[$_GET['name']]['path']);
        $search = array(substr($picdir, 0, -1), $_GET['name'], '');
        foreach($search as $item) {
          $k = array_search($item,$folder);
          unset($folder[$k]);
        }
        if($folder != '' && $folder != NULL) {
          echo format($folder[count($folder)]);
        } else {
          echo format('false');	
        }
      }
    break;
  	default:
      if(!isset($_GET['method'])) {
        echo 'Welcome to ImageVue API Page, it made by <a href="http://imnerd.org">Austin</a>.';
      } else {
        echo 'oops!The method API is not supported now.Maybe you can email <a href="mailto:i@imnerd.org">author</a> to update it!';
      }
  	  break;
  }
?>