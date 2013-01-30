<?php

class PDO_Wrapper {
	
	public $query;
	public $active_query;

	function __construct($database, $pass = '', $user = 'root', $host = 'localhost', $driver = 'mysql'){
		$this->dsn = "$driver:host=$host;dbname=$database";
		$this->user = $user;
		$this->pass = $pass;
		$this->tag('PDO::QUERY');
	}

	function dsn($new = ''){
		
		if(!empty($new)){
			$this->dsn = $new;
		}

		return $this->dsn;
	}

	function connect($driver_opts = array()){
		try {
			$this->connection = new PDO($this->dsn, $this->user, $this->pass, $driver_opts);
		} catch (PDOException $e) {
			die("PDO Wrapper Error: " . $e->getMessage());
		}
	}

	function tag($name){

		$this->active = $name;

		$this->query[$this->active]['string'] = '';
		
		$this->params[$this->active] = array();

		return $this;
	}

	function get($cols = null){

		$cols = !empty($cols) ? func_get_args() : '*';

		if($cols != '*'){

			$columns = '';

			foreach($cols as $col){ 
				$columns .= $col . ','; 
			}

			$columns = rtrim($columns,',');

		} else {
			$columns = '*';
		}

		$this->query[$this->active]['string'] .= " SELECT $columns";

		return $this;
	}

	function table($table){;

	 $this->query[$this->active]['string'] .= " FROM $table";

		return $this;
	}

	function where($where){

		preg_match("#([ \w]+)([\=\!\<\>]{1,2})([ \w]+)#", $where, $match);

		$this->query[$this->active]['string'] .= " WHERE $match[1] $match[2] :where_value";

		$this->params[$this->active][':where_value'] = $match[3];

		return $this;
	}

	function result(){

		$this->active_query = $this->connection->prepare($this->query[$this->active]['string']);

		foreach($this->params[$this->active] as $param => $value){
			$this->active_query->bindParam($param, $value);
		}

		$this->active_query->execute();

		while($row = $this->active_query->fetch(PDO::FETCH_ASSOC)){
			$this->query[$this->active]['result'][] = $row;
		}

		$result =& $this->query[$this->active];

		$this->active = 'PDO::QUERY';

		return $result;
	}

	function __destruct(){

		$this->connection = null;
	}

}

?>