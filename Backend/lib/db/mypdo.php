<?php 
/**
 * Pdo Database Driver Class
 */


class MyPdo extends MyObject
{
	
	private $__conn = null;
	
	private $__rs = null;
	
	private $__connected = false;
	
	private $__params = array(
	            'driver' => 'mysql',
				'persistent' => false,
				'host' => 'localhost',
				'login' => 'root',
				'password' => '',
				'database' => 'default',
				'encoding' => 'utf8',
				'prefix' => '',
	            'port' => 3306
			);
	
	private $__queryaction = '';
	
	private $__queryfields = [];
	
	private $__queryrawfields = [];
	
	private $__queryjoins = [];
	
	private $__queryconditions = [];
	
	private $__querygroupfields = [];
	
	private $__queryoffset = 0;
	
	private $__querylimit = -1;
	
	private $__queryorder = [];
	
	private $__querylog = [];
	
	private $__startQuote = "`";

	private $__endQuote = "`";
	
	private $__index = ['PRI' => 'primary', 'MUL' => 'index', 'UNI' => 'unique'];
	
	private $__sqlOps = ['like', 'ilike', 'or', 'not', 'in', 'between', 'regexp', 'similar to'];

	private $__doCount = false;
	
	private $__countAlias = 'countfield';
	
	private $__columns = [
			'primary_key' => ['name' => 'NOT NULL AUTO_INCREMENT'],
			'string' => ['name' => 'varchar', 'limit' => '255'],
			'text' => ['name' => 'text'],
			'integer' => ['name' => 'int', 'limit' => '11', 'formatter' => 'intval'],
			'float' => ['name' => 'float', 'formatter' => 'floatval'],
			'datetime' => ['name' => 'datetime', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'],
			'timestamp' => ['name' => 'timestamp', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'],
			'time' => ['name' => 'time', 'format' => 'H:i:s', 'formatter' => 'date'],
			'date' => ['name' => 'date', 'format' => 'Y-m-d', 'formatter' => 'date'],
			'binary' => ['name' => 'blob'],
			'boolean' => ['name' => 'tinyint', 'limit' => '1']
	];	
	
	private $__bindValues = [];
	
	private $__innercall = false;
	
	private $__lastInsertedId = null;
	
	public function __construct($params = null)
	{
		//We initialize de connection to the DB and store the resource
		if(!is_null($params))
		{
			$this->__params = array_merge($this->__params,$params);
		}
		$dsn = $this->__params['driver'].":host=".$this->__params['host'].";port=".$this->__params['port'].";dbname=".$this->__params['database'].";charset=".strtoupper($this->__params['encoding']);
		$options = [
		    PDO::ATTR_PERSISTENT => $this->__params['persistent'],
		    PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
		    PDO::ATTR_EMULATE_PREPARES => false
		];
		try{
		    $this->__conn = new PDO($dsn, $this->__params['login'], $this->__params['password'], $options);
		    $this->__connected = true;
		}catch(Exception $e){
		    $this->__connected = false;
        }
	}
	
	public function __destruct()
	{
		$this->__conn = null;
		$this->__connected = false;
	}
	
	public function select(array $fields = ['*'], $rawfields = [])
	{
		$this->__queryaction = 'select';
		$this->__queryfields = $fields;
		$this->__queryrawfields = $rawfields;
		$this->__queryjoins = [];
		$this->__queryconditions = [];
		$this->__queryoffset = 0;
		$this->__querylimit = -1;
		$this->__queryorder = [];
		$this->__doCount = false;
		$this->__countAlias = 'countfield';
		$this->__bindValues = [];
		return $this;
	}
	
	public function insert(array $fields = [])
	{
		$this->__queryaction = 'insert';
		$this->__queryfields = $fields;
		$this->__queryrawfields = [];
		$this->__queryjoins = [];
		$this->__queryconditions = [];
		$this->__queryoffset = 0;
		$this->__querylimit = -1;
		$this->__queryorder = [];
		$this->__doCount = false;
		$this->__countAlias = 'countfield';
		$this->__bindValues = [];
		return $this;
	}
	
	public function update(array $fields = [])
	{
		$this->__queryaction = 'update';
		$this->__queryfields = $fields;
		$this->__queryrawfields = [];
		$this->__queryjoins = [];
		$this->__queryconditions = [];
		$this->__queryoffset = 0;
		$this->__querylimit = -1;
		$this->__queryorder = [];
		$this->__doCount = false;
		$this->__countAlias = 'countfield';
		$this->__bindValues = [];
		return $this;
	}
	
	public function delete()
	{
		$this->__queryaction = 'delete';
		$this->__queryfields = [];
		$this->__queryrawfields = [];
		$this->__queryjoins = [];
		$this->__queryconditions = [];
		$this->__queryoffset = 0;
		$this->__querylimit = -1;
		$this->__queryorder = [];
		$this->__doCount = false;
		$this->__countAlias = 'countfield';
		$this->__bindValues = [];
		return $this;
	}
	
	public function sp($name, $params)
	{
	    if(!is_array($params))
	    {
	        $sp_params[] = $params; 
	    }else{
	        $sp_params = $params;
	    }
	    $this->__bindValues = $sp_params;
	    return $this->run('CALL '.$name."(".implode(",", explode(",", str_repeat("?", count($sp_params)))).")");
	}
	
	public function join($model, array $on, $alias = null, $targetModel = null, $targetAlias = null)
	{
	    $this->__queryjoins[] = ['model' => $model, 'on' => $on, 'alias' => !is_null($alias)?$alias:(string)get_class($model), 'targetModel' => $targetModel, 'targetAlias' => !is_null($targetAlias)?$targetAlias:(!is_null($targetModel)?(string)get_class($targetModel):null)];
		return $this;
	}
	
	public function where(array $conditions = [])
	{
		$this->__queryconditions[] = $conditions;
		return $this;
	}
	
	public function group(array $fields = [])
	{
	    $this->__querygroupfields = $fields;
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
	
	public function page($pageNumber)
	{
	    $pageNumber = intval($pageNumber);
	    if($pageNumber < 0)
	    {
	        $pageNumber = 1;
	    }
		if($this->__querylimit < 1)
		{
			$this->__querylimit = Configure::read("pagination.perpage", 10);
		}
	    if($pageNumber > 1)
	    {
	        $this->__queryoffset = ($pageNumber - 1)*$this->__querylimit;
	    }
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
	
	public function getLastId()
	{
	    return $this->__lastInsertedId;
	}
	
	public function run($query = null)
	{
	    if(is_null($query))
		{
		    $query = $this->buildQuery();
		}else{
		    $this->__innercall = true;
		}
		$this->query($query);		
		if($this->__rs instanceof PDOStatement)		
		{	
		    if($this->__queryaction == 'select')
		    {
		        if($this->__doCount)
		        {
		            $this->__innercall = true;
		        }
		        $data = $this->fetchData();
		        if($this->__doCount && isset($data[0]->{ $this->__countAlias }))
		        {
		            $data = $data[0]->{ $this->__countAlias };
		        }
		    }else{
		        if($this->__queryaction == 'insert')
		        {
		            $this->__lastInsertedId = $this->__conn->lastInsertId();
		        }
		        $data = $this->rows();
		    }
		    $this->__rs->closeCursor();
		    $this->__rs = null;
		}else{
		    de("There has been an error, check error log");
		    pr($this->__conn->errorInfo());
		    $data = null;
		}
		$this->__innercall = false;
		return $data;
	}
	
	public function first($query = null)
	{
	    if(is_null($query))
	    {
	        $query = $this->buildQuery();
	    }else{
	        $this->__innercall = true;
	    }
	    $this->query($query);
	    if($this->__rs instanceof PDOStatement)
	    {
	        if($this->__doCount)
	        {
	            $this->__innercall = true;
	        }
			$data = $this->fetchData();
			if(is_array($data) and count($data) > 0)
			{
	        	$data = $data[0];
			}
	        $this->__innercall = false;
	        $this->__rs->closeCursor();
	        $this->__rs = null;
	        return $data;
	    }else{
	        de("There has been an error, check error log");
	        return null;
	    }
	}
	
	public function rows()
	{
	    if($this->__rs instanceof PDOStatement)
	    {
            return $this->__rs->rowCount();
	    }else{
	        return 0;
	    }
	}
	
	public function describe($table)
	{
		$fields = false;		
		$this->__innercall = true;
		$bindVariables = $this->__bindValues;
		$this->__bindValues = [];
		$cols = $this->run('DESCRIBE ' . $table);
		$this->__innercall = false;;		
		$this->__bindValues = $bindVariables;
		unset($bindVariables);
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
	    try{
    	    $this->__rs = $this->__conn->prepare($query);
    	    if($this->__rs instanceof PDOStatement)
    	    {
        	    if(!empty($this->__bindValues))
        	    {
        	        foreach ($this->__bindValues as $key => &$value)
        	        {
        	            switch (gettype($value))
        	            {
        	                case "boolean":
        	                    $type = PDO::PARAM_BOOL;
        	                    break;
        	                case "double":
        	                case "integer":
        	                    $type = PDO::PARAM_INT;
        	                    break;
        	                case  "NULL":
        	                    $type = PDO::PARAM_NULL;
        	                    break;
        	                case "string":
        	                case "array":	                    
        	                case "object":
        	                case "resource":
                            case "unknown type":
                            default:
                                $type = PDO::PARAM_STR;
                                break;
        	            }
						$this->__rs->bindParam(is_int($key)?($key + 1):$key, $value, $type);
        	        }
        	    }
        	    if($this->__rs->execute())
        	    {
            		return $this->__rs;
        	    }else{
        	        pr($this->__rs->errorInfo());
        	        $this->__rs->closeCursor();
        	        $this->__rs = null;
        	        return null;
        	    }
    	    }else{
    	        echo $query;
    	        pr($this->__conn->errorInfo());    	        
    	    }
	    }catch (Exception $e) {
	        prd($e);
	        if($this->__rs instanceof PDOStatement)
	        {
    	        pr($this->__rs->errorInfo());
    	        $this->__rs->closeCursor();
    	        $this->__rs = null;
	        }
	        return null;	        
	    }
	}
	
	private function fetchData()
	{
		$results = null;
		if($this->__rs)
		{			
			if($this->__rs instanceof PDOStatement)
			{
			    if($this->__queryaction == 'select' && !$this->__innercall)
			    {
				    $results = $this->__rs->fetchAll(PDO::FETCH_CLASS, get_class($this->model));
			    }else{
				    $results = $this->__rs->fetchAll(PDO::FETCH_OBJ);
			    }
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
		$group = '';
		$out = $this->conditionKeysToString($this->__queryconditions,true,$tablealias);
		if(!empty($out))
		{
			$where = 'WHERE '.join(' AND ', $out);
		}
		switch ($this->__queryaction)
		{
			case 'select':
				//First we need to process the form part and any join.
			    $tablefields = $this->describe($this->__params['prefix'].$this->model->getTable());
				$form = "from ".$this->quote($this->__params['prefix'].$this->model->getTable())." ".$tablealias;
				$joinfields = [];
				foreach ($this->__queryjoins as $join)
				{
				    $on = '';
					if(!isset($joinfields[$join['alias']]))
					{
						$joinfields[$join['alias']] = $this->describe($this->__params['prefix'].$join['model']->getTable());
					}
					if($join['targetAlias'] !=null && !isset($joinfields[$join['targetAlias']]))
					{
					    $joinfields[$join['targetAlias']] = $this->describe($this->__params['prefix'].$join['targetModel']->getTable());
					}
					foreach($join['on']['field1'] as $key => $value)
					{
					    $field1 = null;
					    $field2 = null;
					    if(is_null($join['targetModel']))
					    {
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
    						        $on = " on ";
    						    }else{
    						        $on .= " and ";
    						    }
    						    $on .= $this->quote($tablealias).".".$this->quote($field1)." = ".$this->quote($join['alias']).".".$this->quote($field2);
    						}
					    }else{
					        if(array_key_exists($value, $joinfields[$join['alias']]))
					        {
					            $field1 = $value;
					        }
					        if(isset($join['on']['field2'][$key]) && !empty($join['on']['field2'][$key]) && array_key_exists($join['on']['field2'][$key], $joinfields[$join['targetAlias']]))
					        {
					            $field2 = $join['on']['field2'][$key];
					        }elseif(array_key_exists($join['on']['field1'][$key], $joinfields[$join['targetAlias']])){
					            $field2 = $field1;
					        }
					        if(!is_null($field1) && !is_null($field2))
					        {
					            if(empty($on))
					            {
					                $on = " on ";
					            }else{
					                $on .= " and ";
					            }
					            $on .= $this->quote($join['alias']).".".$this->quote($field1)." = ".$this->quote($join['targetAlias']).".".$this->quote($field2);
					        }
					    }
					}
					if(!empty($on))
					{
					    if(is_null($join['targetModel']))
					    {
    						$form .= " ".$join['on']['type']." join ".$this->quote($this->__params['prefix'].$join['model']->getTable())." ".$join['alias'];
					    }else{
					        $form .= " ".$join['on']['type']." join ".$this->quote($this->__params['prefix'].$join['targetModel']->getTable())." ".$join['targetAlias'];					        
					    }
					    $form .= $on;
					}
				}
				//now we take care of the fields
				$totalfields = count($this->__queryfields);
				$queryfields = $this->__queryfields;
				$this->__queryfields = [];
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
				
				if(!empty($this->__querygroupfields))
				{
				    $group = ' GROUP BY '.implode(",", $this->__querygroupfields);
				}
				$query = 'SELECT '.implode(", ", array_merge($this->__queryfields, $this->__queryrawfields))." ".$form." ".$where.$group." ".(!empty($this->__queryorder)?"ORDER BY ".implode(", ", $this->__queryorder):'').(($this->__querylimit > 0)?' LIMIT '.$this->__querylimit:'').(!empty($this->__queryoffset)?' OFFSET '.$this->__queryoffset:'');
				break;
			case 'insert':
				//this case is simple we have an array of fields and values so
				$keys = array_keys($this->__queryfields);
				$this->__bindValues = array_values($this->__queryfields);
				$valuesTotal = count($keys);
				$query = 'INSERT INTO '.$this->__params['prefix'].$this->model->getTable()." (".$this->__startQuote.implode($this->__endQuote.", ".$this->__startQuote,$keys).$this->__endQuote.") VALUES (".substr(str_repeat("?,", $valuesTotal),0 , -1).")";
				break;
			case 'update':
				$sets = [];
				foreach ($this->__queryfields as $key => $value)
				{
					if(substr($value, 0, 11) == '[increment]')
					{
						$sets[] = $this->quote($key)." = ".$this->quote($key)." + ".substr($value, 11);
					}elseif(substr($value, 0, 11) == '[decrement]'){
						$sets[] = $this->quote($key)." = ".$this->quote($key)." - ".substr($value, 11);
					}else{
					    $i = 0;
					    $bindKey = ":".$key.$i;
					    while(array_key_exists($bindKey, $this->__bindValues))
					    {
					        $bindKey = ":".$key.$i;
					        $i++;
					    }
						$sets[] = $this->quote($key)." = ".$bindKey;
						$this->__bindValues[$bindKey] = $value;
					}
				}
				if(!empty($sets))
				{
					$query = "UPDATE ".$this->__params['prefix'].$this->model->getTable()." ".$tablealias." SET ".implode(", ",$sets)." ".$where;
				}
				break;
			case 'delete':
				$query = "DELETE FROM ".$tablealias." USING ".$this->__params['prefix'].$this->model->getTable()." AS ".$tablealias." ".$where;
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
		$out = [];
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
				if(strrpos($value, $model.".") === false)
				{
					$out[] = $not . $this->__quoteFields($value);
				}else{
					$out[] = $value;
				}
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
					    if (strpos($key, ' '))
					    {
					        list($key, $operator) = explode(' ', $key, 2);
					        $dataA = [];
					        if(is_array($value))
					        {
    					        foreach ($value as $mvalue)
    					        {
    	   				            $dataA[] = $this->__parseKey($model, $key, $mvalue);
    					        }
					        }
					        if(!empty($dataA)){
					            $data = "(".join(" ".$operator." ", $dataA).")";
					        }
					    } else {
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
					    }
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
	    $i = 0;
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
		}
		$operator = trim($operator);
		$originalkey = $key;
		if(strpos($originalkey, ".") !== false)
		{
		    $originalkey = str_replace($model.".", "", $originalkey);
		}
		if(strpos($key, ".") === false)
		{
			$key = $model.".".$key;
		}
		$key = $this->quote($key);
		if (strpos($operator, '?') !== false || (is_array($value) && strpos($operator, ':') !== false))
		{
			return	"{$key} " . String::insert($operator, $value);
		} elseif (is_array($value)) {
		    $joinkeys = [];
		    $bindkey = ":".$originalkey.$i;
		    foreach ($value as $v)
		    {
		        while(array_key_exists($bindkey, $this->__bindValues))
		        {
		            $bindkey = ":".$originalkey.$i;		            
		            $i++;
		        }
		        $joinkeys[] = $bindkey;
		        $this->__bindValues[$bindkey] = $value;
		    }
			$value = join(', ', $joinkeys);	
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
		} else {
		    if(strtolower(substr($value, 0, 6)) == 'select')
		    {
		        $value = "(".$value.")";
		    }else{
    		    $bindkey = ":".$originalkey.$i;
    		    while(array_key_exists($bindkey, $this->__bindValues))
    		    {
    		        $bindkey = ":".$originalkey.$i;
    		        $i++;
    		    }
    		    $this->__bindValues[$bindkey] = $value;
    		    $value = $bindkey;
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

if (function_exists('mb_ereg_replace'))
{
    function mb_escape(string $string)
    {
        return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]', '\\\0', $string);
    }
} else {
    function mb_escape(string $string)
    {
        return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', '\\\$0', $string);
    }
}
?>