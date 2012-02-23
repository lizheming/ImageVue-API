ul {
    margin:0 auto;
  } 
  li {
    display:inline;
  }
</style>
<?php
  $api = 'http://album.imnerd.org/api.php?method=';  //注意大家要将这里的网址改成你自己的相册网址哦~  
  echo '<ul>';
  switch($_GET['page']) {
    case 'photo':
      $photos = file_get_contents($api . 'get.photos&name=' . $_GET['album']);
      $photos = json_decode($photos, true);
      foreach($photos as $item) {
        echo '<li>';
        echo '<a href="' . $item['url'] . '" class="highslide" onclick="return hs.expand(this,{slideshowGroup:\'images\'})"><img src="' . $item['thumbnail'] . '" alt="' . $item['title'] . '" />';
        echo '</li>';
      }
    break;
    default:
          $gallery_name = file_get_contents($api . 'get.gallery.name');
          $gallery_name = json_decode($gallery_name, true);
          foreach($gallery_name as $item) {
            $gallery_info = file_get_contents($api . 'get.gallery.info&name=' . $item);
            $gallery_info = json_decode($gallery_info, true);
            $preview = file_get_contents($api . 'get.photo&album=' . $item . '&photo=' . $gallery_info['previewimage']);
            $preview = json_decode($preview, true); 
        if($preview['thumbnail'] != "") {
                echo '<li>';
                echo '<ol style="display:inline;">';
                echo '<li><a href="?page=photo&album=' . $item . '" alt="' . $gallery_info['title'] . '"><img src="' . $preview['thumbnail'] . '" alt="' . $preview['title'] . '" /></a></li>';
          echo '<li><div id="album"><span class="tilte">' . $gallery_info['title'] . '</span><span class="count">(' . $gallery_info['fileCount'] . ')</span><span class="discription">' . $gallery_info['description'] . '</span></div></li>';
                echo '</ol>';
                echo '</li>';
              }
      }
    break;
    }
  echo '</ul>';