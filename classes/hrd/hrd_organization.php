<?php  
class cHrdOrganization extends cModel
{  
  var $strTableName = "hrd_organization";
  var $strEntityName = "organization";  

  var $maxLevel = 0;
  var $minLevel = 0;

  function cHrdOrganization()  
  {
    parent::cModel();
  }
  
  function open()
  {    
    $strSQL = "
      SELECT * 
        FROM ".$this->strTableName." ORDER BY levelling";
    
    $res = $this->db->execute($strSQL);
    $data = array();
    $isFirst = true;
    while ($rowDb = $this->db->fetchrow($res))
    {
      if ($isFirst)
      {
        $this->minLevel = intval($rowDb['levelling']);
        $isFirst = false;
      }
      $data[$rowDb['id']] = $rowDb;
      $this->maxLevel = intval($rowDb['levelling']);
    }
    return $data; 
  }

  function getMinLevelling()
  {    
    $strSQL = "SELECT MIN(levelling) AS levelling FROM ".$this->strTableName;    
    $res = $this->db->execute($strSQL);
    if ($rowDb = $this->db->fetchrow($res))
    {
      return $rowDb['levelling'];
    }
    return false; 
  }
  
  function getMaxLevelling()
  {
    $strSQL = "SELECT MAX(levelling) AS levelling FROM ".$this->strTableName;    
    $res = $this->db->execute($strSQL);
    if ($rowDb = $this->db->fetchrow($res))
    {
      return $rowDb['levelling'];
    }
    return false; 
  }
  
  function findByLevelling($intLevel)
  {    
    $strSQL = "SELECT * FROM ".$this->strTableName." WHERE levelling=".$intLevel;    
    $res = $this->db->execute($strSQL);
    if ($rowDb = $this->db->fetchrow($res))
    {
      return $rowDb;
    }
    return false; 
  }

}

?>