<?
/*****************************************
  CdbClass : kelas untuk mengakses basis data
  Copyright (c) Invosa Systems, PT 

  db_class.php
  Author:  Dillah, Yudi.
  
  Ver   :  - 1.00, 2004-08-30
******************************************/
class CdbClass {

  var $strConn;  // connection string
  var $strHost;
  var $strUser;
  var $strPass;
  var $dbCon;
  var $dbError;
  
  // fungsi untuk mengambil connection string
  // data disimpan dalam strConn
  function getConnString() {
    //$this->strHost = "localhost";
    //$this->strUser = "root";
    //$this->strPass = "invosa123";
    $this->strConn = "host=localhost port=5432 dbname=artajasa user=mahawan password=mahawan";
  }  
  
  //fungsi untuk konek ke database postgre
  //parameternya adalah gabungan string dari nama host,port,dbname,user, dan password
  function connect()  {
    
    $this->getConnString();
    $this->dbCon = pg_connect($this->strConn);
    //$this->dbCon = mysql_connect($this->strHost,$this->strUser,$this->strPass);
    //mysql_select_db("erp",$this->dbCon);
    if (!$this->dbCon) {
      $this->dbError = "Cannot connect to database!";      
      
      return false;
    } else {
      return true;
    }
  }	
  
  ///fungsi untuk mengeksekusi perintah SQL
  ///parameter yang diperlukan adalah nilai string dari SQL tersebut
  function execute($stringSQL) {		
    $result = pg_exec($this->dbCon, $stringSQL);
    //$result = mysql_query($stringSQL,$this->dbCon);
    if ($result) {
      return $result;              
    }else{
      $this->dbError = "Error executing query!";
      echo "$stringSQL<br>";
      return false;
    }
  }
  
  //mengambil data dari tiap record
  //parameter yang diperlukan adalah hasil dari query
  function fetchrow($res)  {
    $resFetch = pg_fetch_array($res);
    //$resFetch = mysql_fetch_array($res);
    if(!$resFetch){
      $this->dbError = "Error executing query!";
      return false;
    } else {
      return $resFetch;
    }				
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

  //mengetahui jumlah record dari hasil query
  //parameter yan diperlukan adalah hasil dari query
  //hasil dari fungsi ini INTEGER
  function numrows($res)
  {        
    $intJmlBrs = pg_numrows($res);
    //$intJmlBrs = mysql_numrows($res);
    return $intJmlBrs;
  }
  
  function close()
  {        
    $tutupKoneksi = pg_close($dbCon);
    //$tutupKoneksi =mysql_close($dbCon);
    return 0;
  }
  
  //fungsi untuk mengirimkan dump data
  // dengan eksekusi langsung
  // masih error
  function dump() {
    // put environment dulu
    
   putenv('PGPASSWORD=mahawan');
   putenv('PGUSER=mahawan');
    
    $strCmd  = "/usr/bin/pg_dump -i -p '5432' 'aska' "; 
    passthru($strCmd);
    //echo $strCmd;
    return 0;
  }
} // end of class db


?>
