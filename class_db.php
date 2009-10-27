<?

class connection
{
	private	$host = "localhost";
  private $username = "root";
  private $password = "password";
  private $database = "timeless";
  
  private $link = '';
  private $result = '';
  public $sql = '';

  function __construct($database=""){
  	if (!empty($database)){ $this->database = $database; }
    	$this->link = mysql_connect($this->host,$this->username,$this->password);
      mysql_select_db($this->database);
      return $this->link;  // returns false if connection could not be made.
  }
 
  function query($sql){
      if (!empty($sql)){
        $this->sql = $sql;
        $this->result = mysql_query($sql);
        return $this->result;
      }else{
    		return false;
    }
  }
 
  function fetchrow($result=""){
  	if (empty($result)){ $result = $this->result; }
    return mysql_fetch_row($result);
  }

  function fetchassoc($result=""){
  	if (empty($result)){ $result = $this->result; }
    return mysql_fetch_assoc($result);
  }

  function fetcharray($result=""){
  	if (empty($result)){ $result = $this->result; }
    return mysql_fetch_array($result);
  }
 
  function __destruct(){
  	mysql_close($this->link);
  }
}


?>