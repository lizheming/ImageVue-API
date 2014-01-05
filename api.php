<?php
//error_reporting(E_ALL ^ E_NOTICE);
function format($data) {
	return isset($_GET['callback']) ? $_GET['callback'].'('.json_encode($data).')' : json_encode($data);
}

function _GET($var) {
	return isset($_GET[$var]) ? $_GET[$var] : '';
}

function get_folder($folder, $start, $max) {
	$gallery = array();
	$temp = array();
	$tmp = array();
	foreach($folder as $item) {
		if($item['page'] == 'gallery' && $item['hidden'] != true && $item['password'] == '') {
			$temp = get_object_vars($item);
			$gallery[$temp['@attributes']['name']] = $temp['@attributes'];
			if(isset($item->folder)) {
				$temp = get_object_vars($item->folder);
				$gallery[$temp['@attributes']['name']] = $temp['@attributes'];
			}
		}
	}
	if($start != '' && $max != '') {
		$album = array();
		foreach($gallery as $item) {
			$album[] = $item;
		}
		for($i=$start,$l=$start+$max;$i<$l;$i++) {
			$tmp[$album[$i]['name']] = $album[$i];
		}
		$gallery = $tmp;
	}
	return $gallery;
}

//获取存放图片文件夹
$xml = simplexml_load_file('./iv-includes/include/config.xml');
$picdir  = $xml-> imagevue-> settings-> contentfolder;
$xml = simplexml_load_file($picdir.'folders.xml');
$gallery = get_folder($xml->folder->folder, _GET('start-index'), _GET('max-results'));

//得到网站地址
define('URL', "http://".$_SERVER['HTTP_HOST'].str_replace("api.php","",$_SERVER["SCRIPT_NAME"]));

switch(_GET('method')) {
	
	/**
	 * 获取所有的相册
	 *
	 * @param string get.gallery.name API名称
	 * @param string start-index 相册起始号
	 * @param string max-results 返回结果最大数量
	 * @return 返回所有的相册的文件夹名称
	 */
	case 'get.gallery.name':
		$album = array();
		foreach($gallery as $item) 
			$album[] = $item['name'];
		echo format($album);
	break;

	/**
	 * 获取某一相册文件夹的具体信息
	 *
	 * @param string get.gallery.info API名称
	 * @param string name 相册文件夹名称
	 * @return 返回该相册文件夹的具体信息
	 */
	case 'get.gallery.info':
		$name = _GET('name');
		$res = isset($gallery[$name]) ? $gallery[$name] : 'false';
		echo format($res);
	break;

	/**
	 * 获取相册文件夹内的所有照片
	 *
	 * @param string get.photos API名称
	 * @param string name 相册文件夹名称
	 * @return 返回该相册文件夹内的所有照片
	 */
	case 'get.photos':
		$name = _GET('name');
		if(!isset($gallery[$name])) die(format('false'));
		$path = $picdir.$gallery[$name]['path'];
		$xml = simplexml_load_file($path.'folderdata.xml');
		$photos = array();
		$start_index = isset($_GET['start-index']) ? $_GET['start-index'] : 1;
		$max_result = isset($_GET['max-result']) ? $_GET['max-result'] : count($xml->file);
		for($i=$start_index-1, $end = $start_index+$max_result-1; $i<$end; $i++) {
			$temp = get_object_vars($xml->file[$i]);
			$photo = $temp['@attributes'];
			$photo['file'] = $photo['name'];
			$photo['url'] = URL.$path.$photo['file'];
			$photo['thumbnail'] = URL.$path.'tn_'.substr($photo['file'],0,-3).'jpg';
			$photos[] = $photo;
		}
		echo format($photos);
	break;

	/**
	 * 获取单张图片具体信息
	 *
	 * @param string get.photo API名称
	 * @param string album 图片所在相册文件夹名称
	 * @param string photo 图片文件名
	 * @return string 返回该图片的具体信息
	 */
	case 'get.photo':
		if(!isset($_GET['album']) || !isset($_GET['photo'])) die(format('false'));
		$album = $_GET['album'];
		$photo_name = $_GET['photo'];

		$path = $picdir.$gallery[$album]['path'];
		$xml = simplexml_load_file($picdir.$path.'folderdata.xml');
		$photo = 'false';
		foreach($xml->file as $item) {
			if($item['name'] != $photo_name) continue;
			$temp = get_object_vars($item);
			$photo = $temp['@attributes'];
			$photo['file'] = $photo['name'];
			$photo['url'] = URL.$picdir.$path.$photo['file'];
			$photo['thumbnail'] = URL.$picdir.$path.'tn_'.$photo['file'];
			break;
		}
		echo format($photo);
	break;

	/**
	 * 获取该相册文件夹内的图片总数
	 *
	 * @param string get.gallery.filecount API名称
	 * @param string name 相册文件夹名称
	 * @return 返回相册文件夹内的图片总数
	 */
	case 'get.gallery.filecount':
		if(!isset($_GET['name'])) die(format('false'));
		$name = $_GET['name'];
		echo format( isset($gallery[$name]) ? $gallery[$name]['totalFileCount'] : 'false' );
	break;

	/**
	 * 获取该相册文件夹的作用属性
	 *
	 * @param string get.gallery.page API名称
	 * @param string name 相册文件夹名称
	 * @param 返回相册文件夹的属性
	 */
	case 'get.gallery.page':
		if(!isset($_GET['name'])) die(format('false'));
		$name = $_GET['name'];
		$page = $gallery[$name]['page'];
		echo format($page);
	break;

	/**
	 * 获取相册文件夹的父文件夹名称
	 *
	 * @param string get.gallery.parent API名称
	 * @param string name 相册文件夹的名称
	 * @return 返回相册文件夹的父文件夹名称
	 */
	case 'get.gallery.parent':
		if(!isset($_GET['name'])) die(format('false'));
		$name = $_GET['name'];

		$folder = explode('/', $gallery[$name]['path']);
		$search = array(substr($picdir, 0, -1), $name, '');
		foreach($search as $item) {
			$k = array_search($item, $folder);
			unset($folder[$k]);
		}
		echo format( $folder == null ? 'false' : $folder[count($folder)] );
	break;

	default:
		echo '这是一个ImageVue的API接口文件，由<a href="http://imnerd.org">公子</a>全程制作。你看到这段话的原因是你没有定义接口名称或者你定义的接口API文件暂时不支持，如果你对这个接口有需求请联系<a href="mailto:i@imnerd.org">公子</a>';
	break;
}
?>
