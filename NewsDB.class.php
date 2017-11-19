<?php
require_once 'INewsDB.class.php';

class NewsDB implements INewsDB {

	const DB_NAME = '../news.db'; // в корень сайта
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
			
				$sql = "CREATE TABLE msgs (
										id INTEGER PRIMARY KEY AUTOINCREMENT,
										title TEXT,
										category INTEGER,
										description TEXT,
										source TEXT,
										datetime INTEGER 
										)";
				$this->_db->exec($sql) or die($this->_db->lastErrorMsg());
				$sql = "CREATE TABLE category(
										id INTEGER,
										name TEXT
										)";
				$this->_db->exec($sql) or die($this->_db->lastErrorMsg());
				$sql = "INSERT INTO category(id, name)
									SELECT 1 as id, 'Политика' as name
									UNION SELECT 2 as id, 'Культура' as name
									UNION SELECT 3 as id, 'Спорт' as name ";
				$this->_db->exec($sql) or die($this->_db->lastErrorMsg());
		}	
	}

	function __destruct()
	{
		//удаляем БД
		unset($this->_db);
	}

	

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
		return $stmt->execute();
	}

	private function db2Arr($data)
	{
		$arr=[];
		while ($row = $data->fetchArray(SQLITE3_ASSOC)) {
				$arr[] = $row;
		}
		return $arr;
	}
	
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
	
	function deleteNews($id)
	{}

	public function clearStr($data)
	{
		$data = strip_tags($data);
		return $this->_db->escapeString($data);
	}

	public function clearInt($data)
	{
		
		return abs((int)$data);
	}
}
