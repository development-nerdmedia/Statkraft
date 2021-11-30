<?php 
/**
 * Mysql Database Driver Class
 */


class Mysql extends MyObject
{
	
	private $__conn = null;
	
	private $__rs = null;
	
	private $__connected = false;
	
	private $__params = array(
				'persistent' => false,
				'host' => 'localhost',
				'login' => 'root',
				'password' => '',
				'database' => 'default',
				'encoding' => 'utf8',
				'prefix' => ''
			);
	
	private $__queryaction = '';
	
	private $__queryfields = array();
	
	private $__queryjoins = array();
	
	private $__queryconditions = array();
	
	private $__queryoffset = 0;
	
	private $__querylimit = 0;
	
	private $__queryorder = array();
	
	private $__querylog = array();
	
	private $__startQuote = "`";

	private $__endQuote = "`";
	
	private $__index = array('PRI' => 'primary', 'MUL' => 'index', 'UNI' => 'unique');
	
	private $__sqlOps = array('like', 'ilike', 'or', 'not', 'in', 'between', 'regexp', 'similar to');

	private $__doCount = false;
	
	private $__countAlias = 'countfield';
	
	private $__columns = array(
			'primary_key' => array('name' => 'NOT NULL AUTO_INCREMENT'),
			'string' => array('name' => 'varchar', 'limit' => '255'),
			'text' => array('name' => 'text'),
			'integer' => array('name' => 'int', 'limit' => '11', 'formatter' => 'intval'),
			'float' => array('name' => 'float', 'formatter' => 'floatval'),
			'datetime' => array('name' => 'datetime', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
			'timestamp' => array('name' => 'timestamp', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'),
			'time' => array('name' => 'time', 'format' => 'H:i:s', 'formatter' => 'date'),
			'date' => array('name' => 'date', 'format' => 'Y-m-d', 'formatter' => 'date'),
			'binary' => array('name' => 'blob'),
			'boolean' => array('name' => 'tinyint', 'limit' => '1')
	);	
	
	public function __construct($params = null)
	{
		//We initialize de connection to the DB and store the resource
		if(!is_null($params))
		{
			if(isset($params['driver']))
			{
				unset($params['driver']);
			}
			$this->__params = array_merge($this->__params,$params);
		}
		if($this->__params['persistent'])
		{
			$this->__conn = mysql_pconnect($this->__params['host'],$this->__params['login'],$this->__params['password']);
		}else{
			$this->__conn = mysql_connect($this->__params['host'],$this->__params['login'],$this->__params['password']);
		}
		if(mysql_select_db($this->__params['database'],$this->__conn))
		{
			mysql_set_charset($this->__params['encoding'],$this->__conn);
			$this->__connected = true;
		}
	}
	
	public function __destruct()
	{
		@mysql_free_result($this->__rs);
		$this->__connected = !@mysql_close($this->__conn);
	}
	
	public function select(array $fields = array())
	{
		$this->__queryaction = 'select';
		$this->__queryfields = $fields;		
		$this->__queryjoins = array();
		$this->__queryconditions = array();
		$this->__queryoffset = 0;
		$this->__querylimit = 0;
		$this->__queryorder = array();
		return $this;
	}
	
	public function insert(array $fields = array())
	{
		$this->__queryaction = 'insert';
		$this->__queryfields = $fields;
		$this->__queryjoins = array();
		$this->__queryconditions = array();
		$this->__queryoffset = 0;
		$this->__querylimit = 0;
		$this->__queryorder = array();
		return $this;
	}
	
	public function update(array $fields = array())
	{
		$this->__queryaction = 'update';
		$this->__queryfields = $fields;
		$this->__queryjoins = array();
		$this->__queryconditions = array();
		$this->__queryoffset = 0;
		$this->__querylimit = 0;
		$this->__queryorder = array();
		return $this;
	}
	
	public function delete()
	{
		$this->__queryaction = 'delete';
		$this->__queryfields = array();
		$this->__queryjoins = array();
		$this->__queryconditions = array();
		$this->__queryoffset = 0;
		$this->__querylimit = 0;
		$this->__queryorder = array();
		return $this;
	}
	
	
	public function join($model, array $on, $alias = null)
	{
		$this->__queryjoins[] = array('model' => $model, 'on' => $on, 'alias' => !is_null($alias)?$alias:(string)get_class($model));
		return $this;
	}
	
	public function where(array $conditions = array())
	{
		$this->__queryconditions = $conditions;
		return $this;
	}
	
	public function limit($limit)
	{
		$this->__querylimit = $limit;
		return $this;
	}
	
	public function offest($offset)
	{
		$this->__queryoffset = $offset;
		return $this;
	}
	
	public function order(array $order)
	{
		$this->__queryorder = $order;
		return $this;
	}
	
	public function count($alias = '')
	{
		$this->__doCount = true;
		if(!empty($alias))
		{
			$this->__countAlias = $alias;
		}
		return $this;
	}
	
	public function run($query = null)
	{
		if(is_null($query))
		{
			$query = $this->buildQuery();			 			
		}
		$this->query($query);
		if($this->__rs)
		{	
			return $this->fetchData();
		}else{
			return null;
			e("There has been an error, check error log");
		}
	}
	
	public function rows()
	{
		if(in_array($this->__queryaction,array('delete','update','insert')))
		{
			return mysql_affected_rows($this->__rs);
		}else{
			return mysql_num_rows($this->__rs);
		}
	}
	
	public function describe($table)
	{
		$fields = false;
		$cols = $this->run('DESCRIBE ' . $table);
		foreach ($cols as $column) 
		{
			if (is_object($column)) 
			{
				$fields[$column->Field] = array(
						'type'		=> $this->column($column->Type),
						'null'		=> ($column->Null == 'YES' ? true : false),
						'default'	=> $column->Default,
						'length'	=> $this->length($column->Type),
				);
				if(!empty($column->Key) && isset($this->index[$column->Key])) 
				{
					$fields[$column->Field]['key']	= $this->index[$column->Key];
				}
			}
		}
		return $fields;		
	}
	
	
	private function query($query)
	{
		$this->__rs = mysql_query($query,$this->__conn);
		return $this->__rs;
	}
	
	private function fetchData()
	{
		$results = null;
		if($this->__rs)
		{			
			if(is_resource($this->__rs))
			{
				$results = array();
				while($row = mysql_fetch_object($this->__rs))
				{
					$results[] = $row;
				}
			}else{
				return $this->__rs;
			}
		}
		return $results;
	}
	
	private function buildQuery()
	{
		//At this point the query must have been build using the auxiliary functions
		//we take care of the where part
		$query = "select now()";		
		$tablealias = get_class($this->model);
		$where = '';
		$out = $this->conditionKeysToString($this->__queryconditions,true,$tablealias);
		if(!empty($out))
		{
			$where = 'where '.join(' AND ', $out);
		}		
		switch ($this->__queryaction)
		{
			case 'select':
				//First we need to process the form part and any join.
				$tablefields = $this->describe($this->__params['prefix'].$this->model->table);
				$form = "from ".$this->quote($this->__params['prefix'].$this->model->table)." ".$this->quote($tablealias);
				$joinfields = array();
				foreach ($this->__queryjoins as $join)
				{
					if(!isset($joinfields[$join['alias']]))
					{
						$joinfields[$join['alias']] = $this->describe($this->__params['prefix'].$join['model']->table);
					}
					$on = '';
					foreach($join['on']['field1'] as $key => $value)
					{
						$field1 = null;
						$field2 = null;
						if(array_key_exists($value, $tablefields))
						{
							$field1 = $value;
						}
						if(isset($join['on']['field2'][$key]) && !empty($join['on']['field2'][$key]) && array_key_exists($join['on']['field2'][$key], $joinfields[$join['alias']]))
						{
							$field2 = $join['on']['field2'][$key];
						}elseif(array_key_exists($join['on']['field1'][$key], $joinfields[$join['alias']])){
							$field2 = $field1;
						}
						if(!is_null($field1) && !is_null($field2))
						{
							if(empty($on))
							{
								$on .= " on ";
							}else{
								$on .= " and ";
							}
							$on .= $this->quote($tablealias).".".$this->quote($field1)." = ".$this->quote($join['alias']).".".$this->quote($field2);
						}
					}
					if(!empty($on))
					{
						$form .= " ".$join['on']['type']." join ".$this->quote($this->__params['prefix'].$join['model']->table)." ".$this->quote($join['alias']);
						$form .= $on;
					}
				}
				//now we take care of the fields
				$totalfields = count($this->__queryfields);
				$queryfields = $this->__queryfields;
				$this->__queryfields = array();
				for($i=0;$i<$totalfields;$i++)
				{
					if(strpos($queryfields[$i], ".") === false)
					{
						if(array_key_exists($queryfields[$i], $tablefields) || $queryfields[$i] == '*')
						{
							$this->__queryfields[$i] = $this->quote($tablealias).'.'.(($queryfields[$i]!='*')?($this->quote($queryfields[$i])):$queryfields[$i]);
						}
					}else{
						$parts = explode(".", $queryfields[$i]);
						if(count($parts) == 2)
						{
							if(array_key_exists($parts[0], $joinfields) && array_key_exists($parts[1], $joinfields[$parts[0]]))
							{
								$this->__queryfields[$i] = $this->quote($parts[0]).".".(($parts[1]!='*')?$this->quote($parts[1]):$parts[1]);
							}
						}
					}
					if($this->__doCount)
					{
						$this->__queryfields[$i] = 'count('.$this->__queryfields[$i].') as '.$this->__countAlias;
						break;
					}
				}				
				$query = 'select '.implode(", ",$this->__queryfields)." ".$form." ".$where." ".(!empty($this->__queryorder)?"order by ".implode(", ", $this->__queryorder):'').(!empty($this->__querylimit)?' limit '.$this->__querylimit:'').(!empty($this->__queryoffset)?', '.$this->__queryoffset:'');
				$this->__doCount = false;
				$this->__countAlias = 'countfield';
				break;
			case 'insert':
				//this case is simple we have an array of fields and values so
				$keys = array_keys($this->__queryfields);
				$values = array_values($this->__queryfields);
				$query = 'insert into '.$this->__params['prefix'].$this->model->table." (".$this->__startQuote.implode($this->__endQuote.", ".$this->__startQuote,$keys).$this->__endQuote.") values ('".implode("', '",$values)."')";
				break;
			case 'update':
				$sets = array();
				foreach ($this->__queryfields as $key => $value)
				{
					if(substr($value, 0, 11) == '[increment]')
					{
						$sets[] = $this->quote($key)." = ".$this->quote($key)." + ".substr($value, 11);
					}elseif(substr($value, 0, 11) == '[decrement]'){
						$sets[] = $this->quote($key)." = ".$this->quote($key)." - ".substr($value, 11);
					}else{
						$sets[] = $this->quote($key)." = '".$value."'";
					}
				}
				if(!empty($sets))
				{
					$query = "update ".$this->__params['prefix'].$this->model->table." ".$tablealias." set ".implode(", ",$sets)." ".$where;
				}
				break;
			case 'delete':
				$query = "delete from ".$tablealias." using ".$this->__params['prefix'].$this->model->table." as ".$tablealias." ".$where;
				break;
			default :
				
		}		
		return $query;
	}
	
	private function quote($text)
	{
		if(strpos($text, ".") === false)
		{
			return $this->__startQuote.$text.$this->__endQuote;
		}else{
			$parts = explode(".",$text);
			if(count($parts) == 2)
			{
				return $this->quote($parts[0]).".".$this->quote($parts[1]);
			}else{
				return $text;
			}
		}
	}	
	
	/********** CAKE IMPORTED FUNCTIONS ********************/
	/**
	 * I took this function from cake drivers and modified them very little too work as I need while I make time to make my own
	 * I don't intent to take credit for any of the followings.
	 * @todo Parts of the function are not goign to work due missing functions calls.
	 */
	
	/**
	 * Creates a WHERE clause by parsing given conditions array.
	 *
	 * @param array $conditions Array or string of conditions
	 * @param boolean $quoteValues If true, values should be quoted
	 * @param Model $model A reference to the Model instance making the query
	 * @return string SQL fragment
	 */
	private function conditionKeysToString($conditions, $quoteValues = true, $model = null) 
	{
		$c = 0;
		$out = array();
		$data = $columnType = null;
		$bool = array('and', 'or', 'not', 'and not', 'or not', 'xor', '||', '&&');
		foreach ($conditions as $key => $value) 
		{
			$join = ' AND ';
			$not = null;
			if (is_array($value)) 
			{
				$valueInsert = (
						!empty($value) &&
						(substr_count($key, '?') == count($value) || substr_count($key, ':') == count($value))
				);
			}
			if (is_numeric($key) && empty($value)) 
			{
				continue;
			} elseif (is_numeric($key) && is_string($value)) {
				$out[] = $not . $this->__quoteFields($value);
			} elseif ((is_numeric($key) && is_array($value)) || in_array(strtolower(trim($key)), $bool)) {
				if (in_array(strtolower(trim($key)), $bool)) 
				{
					$join = ' ' . strtoupper($key) . ' ';
				} else {
					$key = $join;
				}	
				$value = $this->conditionKeysToString($value, $quoteValues, $model);	
				if (strpos($join, 'NOT') !== false) 
				{
					if (strtoupper(trim($key)) == 'NOT') 
					{
						$key = 'AND ' . trim($key);
					}
					$not = 'NOT ';
				}
	
				if (empty($value[1])) 
				{
					if ($not) {
						$out[] = $not . '(' . $value[0] . ')';
					} else {
						$out[] = $value[0] ;
					}
				} else {
					$out[] = '(' . $not . '(' . join(') ' . strtoupper($key) . ' (', $value) . '))';
				}	
			} else {
				if (is_object($value) && isset($value->type)) 
				{
					if ($value->type == 'identifier') 
					{
						$data .= $this->name($key) . ' = ' . $this->name($value->value);
					}
				} elseif (is_array($value) && !empty($value) && !$valueInsert) {
					$keys = array_keys($value);
					if (array_keys($value) === array_values(array_keys($value))) 
					{
						$count = count($value);
						if ($count === 1) 
						{
							$data = $key . ' = (';
						} else
							$data = $key . ' IN (';
						if ($quoteValues || strpos($value[0], '-!') !== 0) 
						{
							$data .= "'".join('\', \'', $value)."'";
						} elseif (strpos($value[0], '-!') === 0) {
							trigger_error('Escape flag (-!) deprecated, use Model::query()', E_USER_WARNING);
							$data .= $this->value(str_replace('-!', '', $value[0]));
						}
						$data .= ')';
					} else {
						$ret = $this->conditionKeysToString($value, $quoteValues, $model);
						if (count($ret) > 1) 
						{
							$data = '(' . join(') AND (', $ret) . ')';
						} elseif (isset($ret[0])) {
							$data = $ret[0];
						}
					}
				} elseif (is_numeric($key) && !empty($value)) {
					$data = $this->__quoteFields($value);
				} else {
					$data = $this->__parseKey($model, trim($key), $value);
				}
	
				if ($data != null) 
				{
					if (preg_match('/^\(\(\((.+)\)\)\)$/', $data)) 
					{
						$data = substr($data, 1, strlen($data) - 2);
					}
					$out[] = $data;
					$data = null;
				}
			}
			$c++;
		}
		return $out;
	}	

	/**
	 * Extracts a Model.field identifier and an SQL condition operator from a string, formats and inserts values,
	 * and composes them into an SQL snippet.
	 *
	 * @param Model $model Model object initiating the query
	 * @param string $key An SQL key snippet containing a field and optional SQL operator
	 * @param mixed $value The value(s) to be inserted in the string
	 * @return string
	 * @access private
	 */
	private function __parseKey($model, $key, $value) 
	{
		if (!strpos($key, ' ')) 
		{
			$operator = '=';
		} else {
			list($key, $operator) = explode(' ', $key, 2);
		}
		$null = ($value === null || (is_array($value) && empty($value)));
	
		if (strtolower($operator) === 'not') 
		{
			$data = $this->conditionKeysToString(array($operator => array($key => $value)), true, $model);
			return $data[0];
		}
		if (!preg_match('/^((' . join(')|(', $this->__sqlOps) . '\\x20)|<[>=]?(?![^>]+>)\\x20?|[>=!]{1,3}(?!<)\\x20?)/is', trim($operator))) 
		{
			$operator .= ' =';
		}
	
		if (
				(is_array($value) && !empty($value) && is_string($value[0]) && strpos($value[0], '-!') === 0) ||
				(is_string($value) && strpos($value, '-!') === 0)
		) 
		{
			trigger_error('Escape flag (-!) deprecated, use Model::query()', E_USER_WARNING);
			$value = str_replace('-!', '', $value);
		} else {
			$value = "'".$value."'";
		}
		$operator = trim($operator);
		if(strpos($key, ".") === false)
		{
			$key = $model.".".$key;
		}
		$key = $this->quote($key);
		if (strpos($operator, '?') !== false || (is_array($value) && strpos($operator, ':') !== false))
		{
			return	"{$key} " . String::insert($operator, $value);
		} elseif (is_array($value)) {
			$value = join(', ', $value);	
			switch ($operator) 
			{
				case '=':
					$operator = 'IN';
					break;
				case '!=':
				case '<>':
					$operator = 'NOT IN';
					break;
			}
			$value = "({$value})";
		} elseif ($null) {
			switch ($operator) 
			{
				case '=':
					$operator = 'IS';
					break;
				case '!=':
				case '<>':
					$operator = 'IS NOT';
				break;
			}
		}
		
		return "{$key} {$operator} {$value}";
	}	
	
	/**
	 * Converts database-layer column types to basic types
	 *
	 * @param string $real Real database-layer column type (i.e. "varchar(255)")
	 * @return string Abstract column type (i.e. "string")
	 */
	private function column($real) 
	{
		if (is_array($real)) 
		{
			$col = $real['name'];
			if (isset($real['limit'])) 
			{
				$col .= '('.$real['limit'].')';
			}
			return $col;
		}
	
		$col = str_replace(')', '', $real);
		$limit = $this->length($real);
		if (strpos($col, '(') !== false) 
		{
			list($col, $vals) = explode('(', $col);
		}
		if (in_array($col, array('date', 'time', 'datetime', 'timestamp'))) 
		{
			return $col;
		}
		if ($col == 'tinyint' && $limit == 1) 
		{
			return 'boolean';
		}
		if (strpos($col, 'int') !== false) 
		{
			return 'integer';
		}
		if (strpos($col, 'char') !== false || $col == 'tinytext') 
		{
			return 'string';
		}
		if (strpos($col, 'text') !== false) 
		{
			return 'text';
		}
		if (strpos($col, 'blob') !== false || $col == 'binary') 
		{
			return 'binary';
		}
		if (in_array($col, array('float', 'double', 'decimal'))) 
		{
			return 'float';
		}
		if (strpos($col, 'enum') !== false) 
		{
			return "enum($vals)";
		}
		if ($col == 'boolean') 
		{
			return $col;
		}
		return 'text';
	}	
	
	/**
	 * Gets the length of a database-native column description, or null if no length
	 *
	 * @param string $real Real database-layer column type (i.e. "varchar(255)")
	 * @return mixed An integer or string representing the length of the column
	 */
	private function length($real) 
	{
		if (!preg_match_all('/([\w\s]+)(?:\((\d+)(?:,(\d+))?\))?(\sunsigned)?(\szerofill)?/', $real, $result)) 
		{
			trigger_error('FIXME: Can\'t parse field: ' . $real, E_USER_WARNING);
			$col = str_replace(array(')', 'unsigned'), '', $real);
			$limit = null;
			if (strpos($col, '(') !== false) 
			{
				list($col, $limit) = explode('(', $col);
			}
			if ($limit != null) 
			{
				return intval($limit);
			}
			return null;
		}
	
		$types = array(
				'int' => 1, 'tinyint' => 1, 'smallint' => 1, 'mediumint' => 1, 'integer' => 1, 'bigint' => 1
		);
	
		list($real, $type, $length, $offset, $sign, $zerofill) = $result;
		$typeArr = $type;
		$type = $type[0];
		$length = $length[0];
		$offset = $offset[0];	
		$isFloat = in_array($type, array('dec', 'decimal', 'float', 'numeric', 'double'));
		if ($isFloat && $offset) 
		{
			return $length.','.$offset;
		}
		if (($real[0] == $type) && (count($real) == 1)) 
		{
			return null;
		}
	
		if (isset($types[$type])) 
		{
			$length += $types[$type];
			if (!empty($sign)) 
			{
				$length--;
			}
		} elseif (in_array($type, array('enum', 'set'))) {
			$length = 0;
			foreach ($typeArr as $key => $enumValue) 
			{
				if ($key == 0) 
				{
					continue;
				}
				$tmpLength = strlen($enumValue);
				if ($tmpLength > $length) 
				{
					$length = $tmpLength;
				}
			}
		}
		return intval($length);
	}
}
?>