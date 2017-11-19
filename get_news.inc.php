<?php

if(!$posts = $news->getNews()) {
	$errMsg = "Произошла ошибка при выводе новостной ленты";
} else {
	var_dump($posts);
}

