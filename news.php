<?php
  spl_autoload_register(function($class) {
    include_once "$class" . ".class.php";
  });

  $news = new NewsDB();
  $errMsg = "";
  $title = "";
  $category = "";
  $description = "";
  $source = "";
  $SingleNews = false;
  $strCount = "";

  if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'save_news.inc.php';
  }
  require_once 'delete_news.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Новостная лента</title>
	<meta charset="utf-8" />
</head>
<body>
  <p style="font-size:2em; font-weight:bold">Последние новости</p>

  <?
  require 'get_news.inc.php';
?>
  <?foreach ($posts as $post): ?>
  <?if($SingleNews == true):?>
  <p><a href="news.php">На главную</a> / <a href="news.php?id_del=<?=$post['id']?>">Удалить</a></p>
  <?endif?>

    <h2><a href="news.php?id=<?=$post['id']?>"><?=$post['title']?></a></h2>
    <b><?=$post['category']?></b><br>
    <small style="text-align:right; display:block"><?=date('d-m-Y' ,$post['datetime'])?></small>
    <p><?=$post['description']?>...</p>
    <small>Источник: <?=$post['source']?></small>

  <?endforeach?>
  <p style="font-size:2em; font-weight:bold">Добавить новость</p>
  <?php
  if (!empty($errMsg)) {
    echo "<p style='color:red'>$errMsg</p>";
  }
  ?>
  
  <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
    Заголовок новости:<br />
    <input type="text" name="title" value="<?=$title?>" /><br />
    Выберите категорию:<br />
    <select name="category">
      <option value="1">Политика</option>
      <option value="2">Культура</option>
      <option value="3">Спорт</option>
    </select>
    <br />
    Текст новости:<br />
    <textarea name="description"  cols="50" rows="5"><?=$description?></textarea><br />
    Источник:<br />
    <input type="text" name="source" value="<?=$source?>" /><br />
    <br />
    <input type="submit" value="Добавить!" />
</form>

</body>
</html>