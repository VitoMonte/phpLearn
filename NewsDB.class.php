<?php
require_once 'INewsDB.class.php';

class NewsDB implements INewsDB {

	const DB_NAME = '../news.db'; // в корень сайта
	const RSS_NAME = 'rss.xml'; // в корень сайта
	const RSS_TITLE = 'Последние новости'; // в корень сайта
	const RSS_LINK = 'http://learn.loc/news/news.php'; // в корень сайта
	private $_db = null;

	function __get($name)
	{
		if($name == 'db'){
			return $this->$_db;
		} else {
			throw new Exception("Unknown property");			
		}		
	}

	function __construct()
	{
		// Создаём или открываем базу данных DB_NAME
		$this->_db = new SQLite3(self::DB_NAME);

		if(is_file(self::DB_NAME) and (filesize(self::DB_NAME) == 0)) {
			try {
				$sql = "CREATE TABLE msgs (
							id INTEGER PRIMARY KEY AUTOINCREMENT,
							title TEXT,
							category INTEGER,
							description TEXT,
							source TEXT,
							datetime INTEGER 
							)";
				if(!$this->_db->exec($sql))
					throw new Exception($this->_db->lastErrorMsg());
				$sql = "CREATE TABLE category(
										id INTEGER,
										name TEXT
										)";
				if(!$this->_db->exec($sql))
					throw new Exception($this->_db->lastErrorMsg());
				$sql = "INSERT INTO category(id, name)
									SELECT 1 as id, 'Политика' as name
									UNION SELECT 2 as id, 'Культура' as name
									UNION SELECT 3 as id, 'Спорт' as name ";
				if(!$this->_db->exec($sql))
					throw new Exception($this->_db->lastErrorMsg());
				
			} catch (Exception $e) {
					//$e->getMessage;
					echo 'Ошибка создания Data Base';
			}
			

		}	
	}

	function __destruct()
	{
		//удаляем БД
		unset($this->_db);
	}

	
	//сохраняем новость
	function saveNews($title, $category, $description, $source) 
	{
		$datetime = time();
		$sql = "INSERT INTO msgs (title, category, description, source, datetime)
									VALUES (:title, :category, :description, :source, :datetime)";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindParam(':title', $title);
		$stmt->bindParam(':category', $category);
		$stmt->bindParam(':description', $description);
		$stmt->bindParam(':source', $source);
		$stmt->bindParam(':datetime', $datetime);
		$result = $stmt->execute();
		
		if(!$result)
			return false;
		$this->createRss();
		return $result;
	}

	//перегоняем значение полученное из БД в массив
	private function db2Arr($data)
	{
		$arr=[];
		while ($row = $data->fetchArray(SQLITE3_ASSOC)) {
				$arr[] = $row;
		}
		return $arr;
	}
	
	//берем все новости
	function getNews()
	{
		$sql = "SELECT msgs.id as id, title, category.name as category,
									description, source, datetime
									FROM msgs, category
									WHERE category.id = msgs.category
									ORDER BY msgs.id DESC";
		$result = $this->_db->query($sql);

		if (!$result) {
			return false;
		}

		return $this->db2Arr($result);
	}
	
	//берем отдельную новость
	function getSingle($id)
	{
		$sql = "SELECT msgs.id as id, title, category.name as category,
									description, source, datetime
									FROM msgs, category
									WHERE msgs.id = $id AND category.id = msgs.category
									ORDER BY msgs.id DESC";
		$result = $this->_db->query($sql);

		if (!$result) {
			return false;
		}

		return $this->db2Arr($result);
	}

	//удаляем новость
	function deleteNews($id)
	{
		$sql = "DELETE FROM msgs 
								WHERE id = $id";
	  if(!$result = $this->_db->exec($sql))
	  	return false;
	  return $this->_db->changes();
	}

	//очищаем принимаемое строковое значение
	public function clearStr($data)
	{
		$data = strip_tags($data);
		return $this->_db->escapeString($data);
	}

	//очищаем принимаемое численное значение
	public function clearInt($data)
	{
		
		return abs((int)$data);
	}

	// формирует RSS-документ
	public function createRss()
	{
		$dom = new DOMDocument("1.0", "utf-8");
		$dom->formatOutput = true; //Форматирует вывод, добавляя отступы и дополнительные пробелы
		$dom->preserveWhiteSpace = false;//Указание не убирать лишние пробелы и отступы
		
		$rss = $dom->createElement('rss');//создаем корневой документ
		$dom->appendChild($rss);//привязали к документу

		$version = $dom->createAttribute("version");
		$version->value = '2.0';
		$rss->appendChild($version);

		$channel = $dom->createElement('channel');
		$rss->appendChild($channel);

		$title = $dom->createElement('title', self::RSS_TITLE);
		$link = $dom->createElement('link', self::RSS_LINK);		
		$channel->appendChild($title);
		$channel->appendChild($link);
		

		$posts = $this->getNews();
		foreach ($posts as $post) {

			$item = $dom->createElement('item');
			$title = $dom->createElement('title', $post['title']);
			$link = $dom->createElement('link', self::RSS_LINK . "?id=" . $post['id']);
			$description = $dom->createElement('description');
			$cdata = $dom->createCDATASection($post['description']);
			$description->appendChild($cdata);
			$pubDate = $dom->createElement('pubDate', date('r' ,$post['datetime']));
			$category = $dom->createElement('category', $post['category']);

			$item->appendChild($title);
			$item->appendChild($link);
			$item->appendChild($description);
			$item->appendChild($pubDate);
			$item->appendChild($category);

			$channel->appendChild($item);
		}
		$dom->save(self::RSS_NAME);
	}
}
