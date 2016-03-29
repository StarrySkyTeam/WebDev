<?php
class Mysql{
	private $link;
	private $db_config;

	private $handle;
	private $time;

	private $goneaway=5;

	//构造函数初始化
	public function __construct(){
		$this->time=microtime(True);
		$this->db_config=$GLOBALS['_config']['db'];
		$this->connect($this->db_config['hostname'],$this->db_config['username'],$this->db_config['password'],$this->db_config['database'],$this->db_config['pconnect']);
		if($this->db_config['log']){
			$this->handle=fopen($this->db_config['logpath'].'dblog.txt','a+');
		}
	}
	//连接数据库
	private function connect($dbhost,$dbuser,$dbpasswd,$dbname,$pconnect=0,$charset='utf8'){
		if($pconnect){
			if(!$this->link=mysql_pconnect($dbhost,$dbuser,$dbpasswd)){
				$this->halt("mysql pconnect failed");
			}
		}else{
			if(!$this->link=mysql_connect($dbhost,$dbuser,$dbpasswd)){
				$this->halt("mysql connect failed");
			}
		}
		if($this->version()>'4.1'){
			if($charset){
				mysql_query("SET character_set_connection=$charset,character_set_results=$charset,character_set_client=binary",$this->link);
			}
		}
		if($this->version()>'5.0.1'){
			if($charset){
				mysql_query("SET sql_mode=''",$this->link);
			}
		}
		if(!@mysql_select_db($dbname,$this->link)){
			$this->halt('database select failed');
		}
	}
	//查询
	private function query($sql){
		$this->write_log("sqlQuery:".$sql);
		$query=mysql_query($sql,$this->link);
		if(!$query){
			$this->halt("Query error:",$sql);
		}
		return $query;
	}

	//获取一条记录 MYSQL_ASSOC,MYSQL_NUM,MYSQL_BOTH
	public function get_one($list,$table,$condition,$result_type=MYSQL_ASSOC){
		$sql="SELECT $list FROM $table WHERE $condition LIMIT 0,1";
		$query=$this->query($sql);
		$result=mysql_fetch_array($query,$result_type);
		$this->write_log('get one record '.$sql);
		return $result;
	}

	//获取全部记录
	public function get_all($list,$table,$condition,$addition='',$key='',$result_type=MYSQL_ASSOC){
		$sql="SELECT $list FROM $table WHERE $condition $addition";
		$query=$this->query($sql);
		$result=array();
		for($i=0;$row=mysql_fetch_array($query,$result_type);$i++){
			$result[empty($key)?$i:$row[$key]]=$row;
		}
		$this->write_log('get all records '.$sql);
		return $result;
	}

	//插入
	public function insert($table,$dataArray){
		$field='';
		$value='';
		if(!is_array($dataArray)||count($dataArray)<=0){
			$this->halt('no data to insert');
			return false;
		}
		while(list($key,$val)=each($dataArray)){
			$field.="$key,";
			$value.="'$val',";
		}
		$field=substr($field,0,-1);
		$value=substr($value,0,-1);
		$sql="INSERT INTO $table($field) values($value)";
		$this->write_log('insert data '.$sql);
		if(!$this->query($sql)){
			return false;
		}
		return mysql_affected_rows(); 
	}

	//更新
	public function update($table,$dataArray,$condition=''){
		if(!is_array($dataArray)||count($dataArray)<=0){
			$this->halt('no data to update');
			return false;
		}
		$value='';
		while(list($key,$val)=each($dataArray)){
			$value.="$key = '$val',";
		}
		$value=substr($value,0,-1);
		$sql="UPDATE $table set $value WHERE 1=1 AND $condition";
		$this->write_log('update data '.$sql);
		if(!$this->query($sql)){
			return false;
		}
		return mysql_affected_rows();
	}

	//删除
	public function delete($table,$condition=''){
		if(empty($condition)){
			$this->halt('no delete condition set');
			return false;
		}
		$sql="DELETE FROM $table WHERE 1=1 and $condition";
		$this->write_log('delete date '.$sql);
		if(!$this->query($sql))
			return false;
		return true;
	}

	//返回结果集
	public function fetch_array($query,$result_type=MYSQL_ASSOC){
		$this->write_log('return fetch_array');
		return mysql_fetch_array($query,$result_type);
	}

	//获取记录条数
	public function rows_num($result){
		if(!is_bool($result)){
			$num=mysql_num_rows($result);
			$this->write_log('the number of record is '.$num);
			return $num;
		}else{
			return 0;
		}
	}

	//释放结果集
	public function free_result(){
		$void=func_get_args();
		foreach($void as $query){
			if(is_resource($query)&&get_resource_type($query)==='mysql result'){
				return mysql_free_result($query);
			}
			$this->write_log('free the result');
		}
	}

	//获取最后插入的id
	public function insert_id(){
		$id=mysql_insert_id($this->link);
		$this->write_log('the id of last record is '.$id);
		return $id;
	}

	//关闭数据库连接
	protected function close(){
		$this->write_log('database connection closed');
		return @mysql_close($this->link);
	}

	//获取版本信息
	public function version(){
		return mysql_get_server_info($this->link);
	}

	//获取表中记录的行数
	public function count_rows($table,$id='*'){
		$sql="SELECT COUNT($id) FROM $table";
		$query=$this->query($sql);
		$this->write_log('count rows '.$sql);
		return $query;
	}

	//错误提示
	protected function halt($message='',$sql=''){
		$error=mysql_error();
		$errno=mysql_errno();
		if($errno==2006 && $this->goneaway-- > 0){
			$this->connect($this->db_config['hostname'],$this->db_config['username'],$this->db_config['password'],$this->db_config['database'],$this->db_config['pconnect']);
			$this->query($sql);
		}else{
			$s='';
			if($message){
				$s='Info: message ';
			}
			if($sql){
				$s.='SQL: '.htmlspecialchars($sql);
			}
			$s.='Error: '.$error.' ';
			$s.='ErrNo: '.$errno.' ';
			$this->write_log($s);
			exit($s);
		}
	}

	//写入日志文件
	public function write_log($msg=''){
		if($this->db_config['log']){
			$text=date('Y-m-d H:i:s')." ".$msg."\r\n";
			fwrite($this->handle,$text);
		}
	}

	//返回上一个操作缠上的文本错误信息和错误码
	function error(){
		return (($this->link)?mysql_error($this->link):mysql_error());
	}
	function errno(){
		return intval(($this->link)?mysql_errno($this->link):mysql_errno());
	}

	//析构函数
	public function __destruct(){
		$this->free_result();
		$use_time=microtime(True)-$this->time;
		$this->write_log('mission complete, lasted '.$use_time.' seconds');
		if($this->db_config['log']){
			fclose($this->handle);
		}
	}

}
