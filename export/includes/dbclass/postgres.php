<?php
/*****************************************
  CdbClass : kelas untuk mengakses basis data
  Copyright (c) Invosa Systems, PT 

  dbClass.php
  Author:  Dedy Sukandar
  
  Ver   :  - 1.00, 2006-11-22
******************************************/

class CdbClass 
{
  var $host = "localhost";
  var $dbname = "";
  var $dbtype = "postgres";
  var $user = "";
  var $password = "";
  var $strConn = "";
  var $dbCon;
  var $dbError;
  var $lastQuery;
  var $divStyle = "border:1px solid black; margin:4px; padding:4px; background-color:#FFDEAD;";
  var $res;
  var $isConnected;
  var $isShowError = true;
  
  //fungsi untuk konek ke database postgre
  //parameternya adalah gabungan string dari nama host,port,dbname,user, dan password
  function connect()  
  {
    if (DEFINED('DB_SERVER')) $this->host = DB_SERVER; 
    if (DEFINED('DB_NAME')) $this->dbname = DB_NAME;
    if (DEFINED('DB_USER')) $this->user = DB_USER;
    if (DEFINED('DB_PWD')) $this->password = DB_PWD;
    $this->lastQuery = '';

    if (is_callable('pg_connect'))
    {
      $this->strConn = "host='".$this->host."' port=5432 dbname=".$this->dbname." user=".$this->user." password=".$this->password;
      $this->dbCon = @pg_connect($this->strConn);
      if (!$this->dbCon) 
      {
  			$this->dbError = '<div style="'.$this->divStyle.'"><strong>Error: </strong>Cannot connect to PostgreSQL server database</div>';
        echo $this->dbError;
      } 
      else 
      {
        $this->isConnected = true;
        return true;
      }
    }
    else
    {
			$this->dbError = '<div style="'.$this->divStyle.'"><strong>Fatal error:</strong> Call to undefined function pg_connect()<br />Please activated PostgreSQL php extension</div>';
      echo $this->dbError;
    }
    $this->isConnected = false;
    return false;
  }	

  function setErrorMessage($errMessage = "") 
  {
    if ($errMessage == "")
    {
  		$connection_status = @pg_connection_status($this->dbCon);
  		$last_error = @pg_last_error($this->dbCon);
  		$result_error = @pg_result_error($this->dbCon);
  		$last_notice = @pg_last_notice($this->dbCon);

  		$_errors = array();

      if ($connection_status != '') $_errors[] = $connection_status;
      if ($last_error != '') $_errors[] = $last_error;
      if ($result_error != '') $_errors[] = $result_error;
      if ($last_notice != '') $_errors[] = $last_notice;

  		if (count($_errors) > 0)
      {
        if ($this->lastQuery=='')
          $this->dbError = '<div style="'.$this->divStyle.'"><b>Error: </b>' . $last_error . "</div>";
        else
          $this->dbError = '<div style="'.$this->divStyle.'"><b>Query:</b> '.$this->lastQuery . '<br />' . implode('<br />', $_errors) . "</div>";
      }
      else
        $this->dbError = '';
    }
    else
    {
      $this->dbError = '<div style="'.$this->divStyle.'"><b>Error: </b>' . $errMessage . "</div>";
    }
	}
  ///fungsi untuk mengeksekusi perintah SQL
  ///parameter yang diperlukan adalah nilai string dari SQL tersebut
  function execute($stringSQL, $errMessage = "") 
  {
	  try
	  {
      $this->lastQuery = $stringSQL;
      //$this->res = pg_exec($this->dbCon, $stringSQL);
      $this->res = @pg_query($this->dbCon, $stringSQL);
	  }
	  catch (Exception $e)
	  {
      if ($this->isShowError || $errMessage != "")
      {
        $this->setErrorMessage($errMessage);
        echo $this->dbError;
      }
		  //echo "$stringSQL<br>";
		  return false;
	  }
    if ($this->res) 
      return $this->res;              
    else
    {
      if ($this->isShowError || $errMessage != "")
      {
  	    $this->setErrorMessage($errMessage);
        echo $this->dbError;
      }
      return false;
    }
  }
  
  //mengambil data dari tiap record
  //parameter yang diperlukan adalah hasil dari query
  function fetchrow($res = null, $result_type = "ASSOC")  
  {
    if ($res == null)
      $res = $this->res;
    
    if (!$res) return false;
    
    switch ($result_type)
    {
      case "ASSOC" :
        $result_type = PGSQL_ASSOC;
        break;
      case "NUM"   :
        $result_type = PGSQL_NUM;
        break;
      default      : 
        $result_type = PGSQL_BOTH;
    }
    $resFetch = @pg_fetch_array($res, NULL, $result_type);
    //$resFetch = mysql_fetch_array($res);
    if(!$resFetch)
    {
      $this->setErrorMessage();
      echo $this->dbError;
      return false;
    } 
    else 
      return $resFetch;
  }
  
  //mengetahui jumlah record dari hasil query
  //parameter yan diperlukan adalah hasil dari query
  //hasil dari fungsi ini INTEGER
  function numrows($res = null)
  {        
    if ($res == null)
      $res = $this->res;
    if (!$res) return false;    
    $intJmlBrs = pg_num_rows($res);
    //$intJmlBrs = mysql_numrows($res);
    return $intJmlBrs;
  }

  function affectedRows($res = null)
  {        
    if ($res == null)
      $res = $this->res;
    if (!$res) return false;    
    return pg_affected_rows($res);
  }
  
  function getLastID ($offset = 0, $seq_suffix = 'seq') {
		$regs = array();
		preg_match("/insert\\s*into\\s*\"?(\\w*)\"?/i", $this->last_query, $regs);

		if (count($regs) > 1) 
    {
			$table_name = $regs[1];
			$res = @pg_query($this->conn, "SELECT * FROM $table_name WHERE 1 != 1");
			$query_for_id = "SELECT CURRVAL('{$table_name}_".@pg_field_name($res, $offset)."_{$seq_suffix}'::regclass)";
			$result_for_id = @pg_query($this->conn, $query_for_id);

			$last_id = @pg_fetch_array($result_for_id, 0, PGSQL_NUM);
			return $last_id[0];
		}
		return null;
	}
  
  function getRecordSet($strSQL, $result_type = "BOTH")
  {
    if (!$this->isConnected)
      if (!$this->connect()) return null;

    $this->res = $this->execute($strSQL);
    $arrResult = array();
    while ($rowDb = $this->fetchrow($this->res, $result_type)) 
      $arrResult[] = $rowDb;

    return $arrResult;
  }

  function freeResult($res = null)
  {
    if ($res == null)
      $res = $this->res;
    if (!$res) return false;
    pg_free_result($res);    
  }

  // fungsi untuk memgambil sequence ID yang berikutnya, berdasar anma sequence-nya
  function getNextID($strSeq = "") {
    $intResult = 0;
    if ($strSeq != "") {
      $strSQL  = "SELECT nextval('\"$strSeq\"') AS id ";
      $res = $this->execute($strSQL);
      if ($row = $this->fetchrow($res))
      {
        $intResult = $row['id'];
      }
    }
    return $intResult;
  } //getNextID
  
  function DB_Maintenance()
  {
    $this->setErrorMessage("Silahkan tunggu, database administrator sedang menjalankan proses optimasi database!");
    echo $this->dbError;
    
    $Result = $this->execute('VACUUM ANALYZE');
    
    $Result = $this->execute("UPDATE config 
          SET confvalue='" . Date('Y-m-d') . "' 
          WHERE confname='DB_Maintenance_LastRun'");
  }

  function close()
  {        
    $tutupKoneksi = pg_close($this->dbCon);
    $this->isConnected = false;
    return 0;
  }
  
  function DB_Last_Insert_ID($table, $fieldname) 
  {
  	$tempres = $this->execute ("SELECT currval('".$table."_".$fieldname."_seq') FROM ".$table);
   
  	$Res = pg_fetch_result( $tempres, 0, 0 );
    $this->freeResult($tempres);
    
  	return $Res;
  }
  
  function DB_escape_string($String)
  {
    return pg_escape_string($String);
  }

  function INTERVAL( $val, $Inter ) 
  {
  	return "\n(CAST( (" . $val . ") as text ) || ' ". $Inter ."')::interval\n";
  }
  
  function DB_error_msg()
  {
    return pg_last_error($this->dbCon);
  }
  
  function DB_error_no ()
  {
    return DB_error_msg($this->dbCon) == ""? 0:-1;
  }
  
  function DB_data_seek (&$ResultIndex,$Record) 
  {
    pg_result_seek($ResultIndex,$Record);
  }

} // end of class db
?>
