<?php

	$title = $news->clearStr($_POST['title']);
	$category = $news->clearStr($_POST['category']);
	$description = $news->clearStr($_POST['description']);
	$source = $news->clearStr($_POST['source']);

/*if(!empty(trim($title)) && !empty(trim($description)))
{
	if($news->saveNews($title, $category, $description, $source)) {
		header('Location: '.$_SERVER['REQUEST_URI']);
		die;
	} else {
		$errMsg = "Произошла ошибка при добавлении новости";
	}
} else {
	$errMsg = "Заполните все поля формы!";
}*/

if(empty($title) or empty($description)) {
	$errMsg = "Произошла ошибка при добавлении новости";
} else {
	if(!$news->saveNews($title, $category, $description, $source)) {
		$errMsg = "Произошла ошибка при добавлении новости";		
	} else {
		header('Location: news.php');
		exit;
	}
}
