<?php
  /*
    Class untuk mengelola fungsi-fungsi terkait dengan hak akses tertentu
      data yang ada tidak perlu diquery ulang, cukup membaca dari informasi SESSION saja
    Author: Yudi (2009-06-01)
  */

  // kelas hak akses
  class clsUserPermission
  {
    var $data;
    var $strID;       // id user

    // konstruktor
    function clsUserPermission($strID = "")
    {
      // inisialisasi
      $this->strID = $strID;
    }

    /* getRole : fungsi untuk mengambil info user role
    */
    function getRole()
    {
      if (isset($_SESSION['sessionUserRole'])) return $_SESSION['sessionUserRole'];
      else return "";
    }

    /* getEmployeeLevel : fungsi untuk mengambil info level hak akses employee
                          diatur secara khusus di data user, karena sering berbeda dengan yang ada di data karyawan
                          misal: ka Unit/Section, bisa menjabat (approve) sebagai ka Dept
    */
    function getEmployeeLevel()
    {
      if (isset($_SESSION['sessionEmployeeLevel'])) return $_SESSION['sessionEmployeeLevel'];
      else return "";
    }
    
    /* getUserID : fungsi untuk mengambil info id user
    */
    function getUserID()
    {
      if (isset($_SESSION['sessionUserID'])) return $_SESSION['sessionUserID'];
      else return "";
    }

    /* getUserName : fungsi untuk mengambil info id NIK dari user
    */
    function getUserName()
    {
      if (isset($_SESSION['sessionUserName'])) return $_SESSION['sessionUserName'];
      else if (isset($_SESSION['sessionUser'])) return $_SESSION['sessionUser'];
      else return "";
    }
    
    /* getEmployeeID : fungsi untuk mengambil info id NIK dari user
    */
    function getEmployeeID()
    {
      if (isset($_SESSION['sessionEmployeeID'])) return $_SESSION['sessionEmployeeID'];
      else return "";
    }

    /* getIDEmployee : fungsi untuk mengambil info id (id_employee) dari user (jika ada NIK-nya)
    */
    function getIDEmployee()
    {
      if (isset($_SESSION['sessionEmployeeData']['id_employee'])) return $_SESSION['sessionEmployeeData']['id_employee'];
      else return "";
    }
    
    /* isHighSecurity : fungsi untuk menentukan apakah user ini boleh mengakses group user yang sekuritinya tinggi
    */
    function isHighSecurity()
    {
      if (isset($_SESSION['sessionGroupSecurity']))
      {
        return ($_SESSION['sessionGroupSecurity'] == 1);
      }
      else return false;
    }
    
    /* isHighSalaryAccess : fungsi untuk menentukan apakah berhak mengakses data gaji karyawan yang grade-nya 4 ke atas
    */
    function isHighSalaryAccess()
    {
      if (isset($_SESSION['sessionIsSpecifyBand']) && $_SESSION['sessionIsSpecifyBand'])
      {
        if (!isset($_SESSION['sessionBandList']))return false;
        
        $bolOK = false;
        foreach ($_SESSION['sessionBandList'] AS $i => $band)
        {
          if ($band > 3) // band 4 ke atas
          {
            $bolOK = true;
            break;
          }
        }
        return $bolOK;
      }
      else return true;
    }
 
    /* getUserBand : fungsi untuk mengecek apakah user bisa mengakses band/grade tertentu
    */
    function isBandAccess($strBand)
    {
      if (isset($_SESSION['sessionIsSpecifyBand']) && $_SESSION['sessionIsSpecifyBand'])
      {
        if (!isset($_SESSION['sessionBandList'])) return false;
        
        $bolOK = false;
        foreach ($_SESSION['sessionBandList'] AS $i => $band)
        {
          if ($band == $strBand) // band 4 ke atas
          {
            $bolOK = true;
            break;
          }
        }
        return $bolOK;
      }
      else return true; // bisa akses semua 
    }

    /* isManagerHR : fungsi untuk memeriksa apakah HRD Manager atau bukan
    */
    function isManagerHR()
    {
      $strRole = $this->getRole();
      return ($strRole == ROLE_SUPER || $strRole == ROLE_SUPERVISOR); // super admin juga dianggap manager HR
    }

    /* isAdminHR : fungsi untuk memeriksa apakah HRD Admin/Staff atau bukan
    */
    function isAdminHR()
    {
      $strRole = $this->getRole();
      return ($strRole == ROLE_ADMIN);
    }
    
    /* isRoleSupervisor : fungsi untuk memeriksa apakah memiliki role sebagai supervisor
    */
    function isRoleSupervisor()
    {
      $strRole = $this->getRole();
      return ($strRole == ROLE_SUPERVISOR);
    }
    
    /* isRoleSupervisorDivision : fungsi untuk memeriksa apakah memiliki role sebagai (KaDiv)
          sekedar di role user, belum tentu benar-benar kadir
    */
    function isRoleSupervisorDivision()
    {
      if ($this->isRoleSupervisor() && ($this->getEmployeeLevel() == 1))
        return true;
      else
        return false;
    }
    
    /* isEmpty : cek apakah data departemen untuk user ini kosong
        output : true jika tidak ada data departemen - asumsi merupakan kadiv
    */
    function isEmptyDepartment()
    {
      $bolResult = false;
      if (isset($_SESSION['sessionOrganization']))
      { 
        if (isset($_SESSION['sessionOrganization'][0]['department_code']))
        {
          $bolResult = $this->isEmpty($_SESSION['sessionOrganization'][0]['department_code']);
        }
      }
      
      return $bolResult;      
    }
    
    /* isEmpty : cek apakah data kosong atau tidak. */
    function isEmpty($strValue)
    {
      $strValue = trim($strValue);
      return ($strValue == "" || $strValue == "-");
    }

    /* isSupervisor : fungsi untuk memeriksa apakah user merupakan atasan
        (memegang peranan sebagai kepala departemen atau divisi tertentu), public
        input : kode division, kode department, kode section, kode subsection
        output : true jika merupakan atasan dalam struktur tersebut
    */
    function isSupervisor($strDivision = "", $strDepartment = "", $strSection = "", $strSubSection = "")
    {
      $strRole = $this->getRole();
      if ($strRole != ROLE_SUPERVISOR) return false; // harus masuk grup supervisor

      $strLevel = $this->getEmployeeLevel();
      if ($strLevel <> '' && $strLevel <> 0) return true; // levelnya ada
      
      if (!$this->isEmpty($strSubSection))
      {
        if ($this->isInStructure("sub_section_code", $strSubSection)) return true;
      }
      if (!$this->isEmpty($strSection))
      {
        if ($this->isSupervisorSection($strSection, $strDepartment, $strDivision)) return true;
      }
      if (!$this->isEmpty($strDepartment))
      {
        if ($this->isSupervisorDepartment($strDepartment, $strDivision)) return true;
      }
      if (!$this->isEmpty($strDivision))
      {
        if ($this->isSupervisorDivision($strDivision)) return true;
      }
      return false;
    }

    /* isSupervisorSection : apakah merupakan supervisor dari section tertentu, public
    */
    function isSupervisorSection($strSection, $strDepartment = "", $strDivision = "")
    {
      $strLevel = $this->getEmployeeLevel();
      
      if ($strDivision == "") return false;
      if ($this->getRole() != ROLE_SUPERVISOR) return false;
      if ($strLevel == 0 || $strLevel == '' || $strLevel > 3) return false; // section = level 3
      if (!isset($_SESSION['sessionOrganization'])) return false;
      
      foreach ($_SESSION['sessionOrganization'] AS $x => $arrOrg)
      {
        if (isset($arrOrg['division_code']))
        {
          if ($arrOrg['division_code'] == $strDivision && $arrOrg['department_code'] == $strDepartment
              && $arrOrg['section_code'] == $strSection && $this->isEmpty($arrOrg['sub_section_code'])) return true;
        }
      }
      return $bolOK;
    }

    /* isSupervisorDivision : apakah merupakan supervisor dari divisi tertentu, public
    */
    function isSupervisorDivision($strDivision)
    {
      $strLevel = $this->getEmployeeLevel();
      if ($strDivision == "") return false;
      if ($this->getRole() != ROLE_SUPERVISOR) return false;
      if ($strLevel == 0 || $strLevel == '' || $strLevel > 1) return false; // section = level 1
      if (!isset($_SESSION['sessionOrganization'])) return false;
      foreach ($_SESSION['sessionOrganization'] AS $x => $arrOrg)
      {
        if (isset($arrOrg['division_code']))
        {
          if ($arrOrg['division_code'] == $strDivision && $this->isEmpty($arrOrg['department_code'])
              && $this->isEmpty($arrOrg['section_code']) && $this->isEmpty($arrOrg['sub_section_code'])) return true;
        }
      }
      return $bolOK;
    }

    /* isSupervisorDepartment : apakah merupakan supervisor dari departemen tertentu, public
    */
    function isSupervisorDepartment($strDepartment, $strDivision = "")
    {
      $strLevel = $this->getEmployeeLevel();
      if ($strDepartment == "") return false;
      if ($this->getRole() != ROLE_SUPERVISOR) return false;
      if ($strLevel == 0 || $strLevel == '' || $strLevel > 2) return false; // section = level 2
      if (!isset($_SESSION['sessionOrganization'])) return false;
      foreach ($_SESSION['sessionOrganization'] AS $x => $arrOrg)
      {
        if (isset($arrOrg['department_code']))
        {
          if ($arrOrg['division_code'] == $strDivision && $arrOrg['department_code'] == $strDepartment 
            && $this->isEmpty($arrOrg['section_code']) && $this->isEmpty($arrOrg['sub_section_code']) ) return true;
        }
      }

      return false;
    }

    /* isDirector : fungsi untuk memeriksa apakah user merupakan direktur atau managing director
    */
    function isDirector()
    {
      $strRole = $this->getRole();
      return ($strRole == ROLE_DIRECTOR);
    }

    /* isUserEmployee : fungsi untuk memeriksa apakah user adalah karyawan biasa, bukan user HR
                        supervisor tetap dianggap karyawan
        output : true jika employee/supervisor
    */
    function isUserEmployee()
    {
      $strRole = $this->getRole();
      return ($strRole == ROLE_EMPLOYEE || $strRole == ROLE_SUPERVISOR);
    }
    
    /* isInStructure : fungsi untuk mencari apakah suatu user berada dalam suatu struktur tertentu,
          misal apakah berada di divisi tertentu, departemen tertentu, atau section tertentu
          cari infornya berdasarkan data di SESSION['sessionOrganization']
            input   : jenis struktur (division_code, department_code, section_code atau sub_section_code), nama yang terkait
            output  : true jika merupakan bagian dari struktur tersebut
    */
    function isInStructure($strCode, $strValue)
    {
      if ($strCode == "" || $strValue == "") return false;
      if (!isset($_SESSION['sessionOrganization'])) return false;
      foreach ($_SESSION['sessionOrganization'] AS $x => $arrOrg)
      {
        if (isset($arrOrg[$strCode]))
        {
          if ($arrOrg[$strCode] == $strValue) return true;
        }
      }
      return false;
    }
    
    /* genFilterCompany : untuk membuat filter (sintaks query) terhadap data company, 
        disesuaikan dengan data struktur organisasi karyawan (user) 
        Bisa dimanfaatkan saat menampilkan agar melakukan filter terhadap data karyawan, juga saat buat combobox
        input : intType - 0 : filter hrd_employee, 1 : filter hrd_company
    */
    function genFilterCompany($intType = 0)
    {
      $strResult = "";
      $strField = ($intType == 0) ? "id_company" : "id";
      if (isset($_SESSION['sessionCompanyData']['id']))
      {
        if ($_SESSION['sessionCompanyData']['id'] != "")
        {  
          
          $strResult .= " AND $strField = '".$_SESSION['sessionCompanyData']['id']."' ";
        }
          
      }
      else // dibatasi berdasar info karyawan
      {
        $intRole = $this->getRole();
        if ( ($intRole == ROLE_SUPERVISOR || $intRole == ROLE_EMPLOYEE) && isset($_SESSION['sessionOrganization']) )
        {
        /*
          $strList = "";
          foreach($_SESSION['sessionOrganization'] AS $x => $arrOrg)
          {
            if (!$this->isEmpty($arrOrg['id_company']))
            {
              if ($strList != "") $strList .= ", ";
              $strList .= "'".$arrOrg['id_company']."' ";
            }
          }
            
          $strResult .= ($strList == "") ? " AND 1=2 " : " AND $strField IN ($strList) ";
          */
        }
      }
      return $strResult;
    }
    
    /* genFilterDivision : untuk membuat filter (sintaks query) terhadap data divisi, 
        disesuaikan dengan data struktur organisasi karyawan (user) 
        Bisa dimanfaatkan saat menampilkan agar melakukan filter terhadap data karyawan, juga saat buat combobox
        input : intType - 0 : filter hrd_employee, 1 : filter hrd_company
    */
    function genFilterDivision($intType = 0)
    {
      $strResult = "";
      $strField = "division_code";
      $strLevel = $this->getEmployeeLevel();
    
      $intRole = $this->getRole();
      if ( ($intRole == ROLE_SUPERVISOR || $intRole == ROLE_EMPLOYEE) && isset($_SESSION['sessionOrganization']) )
      {
        $strList = "";
        foreach($_SESSION['sessionOrganization'] AS $x => $arrOrg)
        {
          if (!$this->isEmpty($arrOrg['division_code']))
          {
            if ($strList != "") $strList .= ", ";
            $strList .= "'".$arrOrg['division_code']."' ";
          }
        }
          
        $strResult .= ($strList == "") ? " AND 1=2 " : " AND $strField IN ($strList) ";
      }
      
      return $strResult;
    }
    
    /* genFilterDepartment : untuk membuat filter (sintaks query) terhadap data departemen, 
        disesuaikan dengan data struktur organisasi karyawan (user) 
        Bisa dimanfaatkan saat menampilkan agar melakukan filter terhadap data karyawan, juga saat buat combobox
        input : intType - 0 : filter hrd_employee, 1 : filter hrd_company
    */
    function genFilterDepartment($intType = 0)
    {
      $strResult = "";
      $strField = "department_code";
      $strLevel = $this->getEmployeeLevel();
    
      $intRole = $this->getRole();
      if ( ($intRole == ROLE_SUPERVISOR || $intRole == ROLE_EMPLOYEE) && isset($_SESSION['sessionOrganization']) )
      {
        if ($strLevel == 1) // divisi
          $strResult = $this->genFilterDivision();
        else
        {
          $strList = "";
          foreach($_SESSION['sessionOrganization'] AS $x => $arrOrg)
          {
            if (!$this->isEmpty($arrOrg['department_code']))
            {
              if ($strList != "") $strList .= ", ";
              $strList .= "'".$arrOrg['department_code']."' ";
            }
          }  
          if ($strList != "") $strResult .= " AND $strField IN ($strList) ";
        }
      }
      
      return $strResult;
    }
    
    /* genFilterSection : untuk membuat filter (sintaks query) terhadap data section, 
        disesuaikan dengan data struktur organisasi karyawan (user) 
        Bisa dimanfaatkan saat menampilkan agar melakukan filter terhadap data karyawan, juga saat buat combobox
        input : intType - 0 : filter hrd_employee, 1 : filter hrd_company
    */
    function genFilterSection($intType = 0)
    {
      $strResult = "";
      $strField = "section_code";
      $strLevel = $this->getEmployeeLevel();
    
      $intRole = $this->getRole();
      if ( ($intRole == ROLE_SUPERVISOR || $intRole == ROLE_EMPLOYEE) && isset($_SESSION['sessionOrganization']) )
      {
        if ($strLevel == 1) // divisi
          $strResult = $this->genFilterDivision();
        else if ($strLevel == 2) // departemen
          $strResult = $this->genFilterDepartment();
        else
        {
          $strList = "";
          foreach($_SESSION['sessionOrganization'] AS $x => $arrOrg)
          {
            if (!$this->isEmpty($arrOrg['section_code']))
            {
              if ($strList != "") $strList .= ", ";
              $strList .= "'".$arrOrg['section_code']."' ";
            }
          }  
          if ($strList != "") $strResult .= " AND $strField IN ($strList) ";
        }

      }
      
      return $strResult;
    }
    
    
    
    /* genFilterSubSection : untuk membuat filter (sintaks query) terhadap data subsection, 
        disesuaikan dengan data struktur organisasi karyawan (user) 
        Bisa dimanfaatkan saat menampilkan agar melakukan filter terhadap data karyawan, juga saat buat combobox
        input : intType - 0 : filter hrd_employee, 1 : filter hrd_company
    */
    function genFilterSubSection($intType = 0)
    {
      $strResult = "";
      $strField = "sub_section_code";
      $strLevel = $this->getEmployeeLevel();
    
      $intRole = $this->getRole();
      if ( ($intRole == ROLE_SUPERVISOR || $intRole == ROLE_EMPLOYEE) && isset($_SESSION['sessionOrganization']) )
      {
        if ($strLevel == 1) // divisi
          $strResult = $this->genFilterDivision();
        else if ($strLevel == 2) // departemen
          $strResult = $this->genFilterDepartment();
        else if ($strLevel == 3) // section
          $strResult = $this->genFilterSection();
        else
        {
          $strList = "";
          foreach($_SESSION['sessionOrganization'] AS $x => $arrOrg)
          {
            if (!$this->isEmpty($arrOrg['sub_section_code']))
            {
              if ($strList != "") $strList .= ", ";
              $strList .= "'".$arrOrg['sub_section_code']."' ";
            }
          }
          if ($strList != "") $strResult .= " AND $strField IN ($strList) ";
        }  
      }
      
      return $strResult;
    }
    
    /* genFilterEmployee : fungsi untuk membuat filter (sintaks query) secara umum sesuai data karyawan
          kombinasi dari subsection, section, department, division dan company
    */
    function genFilterEmployee()
    {
      $strResult = "";
      $strResult .= $this->genFilterCompany();
      $strResult .= $this->genFilterDivision();
      $strResult .= $this->genFilterDepartment();
      $strResult .= $this->genFilterSection();
      $strResult .= $this->genFilterSubSection();
      
      // jika dia karyawan, bukan atasan, maka hanya bisa akses datanya sendiri
      if ($this->isUserEmployee() && !$this->isRoleSupervisor())
      {
        $strResult .= " AND employee_id = '" .$this->getEmployeeID(). "' ";
      }
      
      return $strResult;
    }
    
    /*  generateApprovalButtons : fungsi untuk membuat tombol-tombol approval yang umum (verified, checked, approved)
                                  disesuaikan dengan user permissionnya
        input   : bolUnApprove : apakah termasuk tombol unapprove atau tidak
        output  : string, berisi daftar tombol/button approval
    */
    function generateApprovalButtons($bolUnApprove = false)
    {
      global $bolCanApprove;

      if (!$bolCanApprove) return "";
      
      $strAction = " if (typeof (confirmStatusChanges) == 'function') confirmStatusChanges(); ";
      
      $strResult = "";
      if ($this->isRoleSupervisor())
      {
        $strResult .= "&nbsp;<input type=submit name='btnVerified' id='btnVerified' value=\"" .getWords('verified'). "\" onClick=\"return confirmStatusChanges(false)\">";
      }
      if ($this->isAdminHR() || $this->isManagerHR())
      {
        $strResult .= "&nbsp;<input type=submit name='btnChecked' id='btnChecked' value=\"" .getWords('checked'). "\" onClick=\"return confirmStatusChanges(false)\">";
      }
      if ($this->isManagerHR() || ($this->isAdminHR() && $bolCanApprove))
      {
        $strResult .= "&nbsp;<input type=submit name='btnApproved' id='btnApproved' value=\"" .getWords('approved'). "\" onClick=\"return confirmStatusChanges(false)\">";
        if ($bolUnApprove)
          $strResult .= "&nbsp;<input type=submit name='btnUnApproved' id='btnUnApproved' value=\"" .getWords('unapprove'). "\" onClick=\"return confirmStatusChanges(false)\">";
      }
      if ($this->isAdminHR() || $this->isManagerHR() || $this->isRoleSupervisor())
      {
        $strResult .= "&nbsp;<input type=submit name='btnDenied' id='btnDenied' value=\"" .getWords('denied'). "\" onClick=\"return confirmStatusChanges(false)\">";
      }
      return $strResult;
    }
    
  } // end of class

  ?>