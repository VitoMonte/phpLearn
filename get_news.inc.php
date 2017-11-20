<?php
if (empty($_GET['id'])) {

	$posts = $news->getNews();
	foreach ($posts as $key => $value) {		
		$posts[$key]['description'] = mb_substr(($posts[$key]['description']),0,200, "utf-8");
	}

	if(!$posts) {
		$errMsg = "Произошла ошибка при выводе новостной ленты";
	}
} else {
	$id = $news->clearInt($_GET['id']);
	$posts = $news->getSingle($id);
	$SingleNews = true;

	if(!$posts) {
		header("Location: news.php");
		exit;

	}
}



