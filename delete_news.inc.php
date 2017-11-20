<?php

if(!empty($_GET['id_del'])) {
	$id = $news->clearInt($_GET['id_del']);
	$strCount = $news->deleteNews($id);

	if($strCount == 0)
		$errMsg = "Произошла ошибка при удалении новости"; 
	
}
	

