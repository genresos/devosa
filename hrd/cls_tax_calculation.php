<?php
  // ---- variabel untuk PPH21
  	include_once("cls_pph21_calculation.php");

  /* clsTaxCalculation : kelas untuk mengelola perhitungan Pph21, per karyawan
  */
  class clsTaxCalculation
  {
    var $data; //kelas database
    var $arrPTKP; // daftar PTKP sesuai status keluarga
    var $fltPKP      = 0; // nilai pendapatan kena pajak
    var $fltPosition = 0; // tunj. jabatan
    var $bolNPWP; //apakah punya NPWP atau tidak
    var $intPeriod; // periode kerja karyawan, dalam bulan, max 12 bulan
    var $strFamilyStatus; // kode status keluarga
    // atribut konstanta
    var $intMethod      = 1; // 0 : gross, 1 : gross up
    var $fltMaxPosition = 6000000; // maximum tunj. jabatan  - konstanta
    var $strIDEmployee  ="";
    var $arrEmpBaseTaxPaidTaxBefore;

    // konstruktor
    function clsTaxCalculation($db)
    {
      $this->data = $db;
      // inisialisasi
      $this->arrPTKP      = array();
      $this->fltPosition  = 0;
      $this->bolNPWP      = false;
      $this->intPeriod    = 12;
      $this->strFamilyStatus = "";
      $this->fltJamostekDeduction    = 0;
      $this->initTaxReduction();
	  $this->baseTaxBefore = 0;
	  $this->taxBefore = 0;
	  $this->baseIrrTaxBefore = 0;
	  $this->intTaxMonth = 1;
      $this->strIDEmployee = "";
      $this->arrEmpBaseTaxPaidTaxBefore = array();
    }

    /* initTaxReduction : fungsi untuk mengambil daftar PTKP (private)
        disimpan di atribut
    */
    function initTaxReduction()
    {
      $strSQL = "SELECT family_status_code, tax_reduction FROM hrd_family_status ";
      $res = $this->data->execute($strSQL);
      while ($row = $this->data->fetchrow($res))
      {
        $this->arrPTKP[$row['family_status_code']] = $row['tax_reduction'];
      }
    }

    /* getTaxReduction : mengambil nilai PTKP berdasar status keluarga
        input  : status keluarga
        output : nilai PTKP, ambil dari atribut
    */
    function getTaxReduction($strCode)
    {
      $fltResult = (isset($this->arrPTKP[$strCode])) ? $this->arrPTKP[$strCode] : 0;
      return $fltResult;
    }

    /* setData : fungsi untuk mengisi data atribut yang diperlukan dalam perhitungan pajak
        input  : PKP, status keluarga, ada NPWP atau tidak, jumlah periode bulan
    */
    function setData($fltPKP, $strFamilyStatus, $bolNPWP = false, $intPeriod = 12, $fltJamsostekDeduction)
    {
      $this->fltPKP     = (is_numeric($fltPKP)) ? $fltPKP : 0;
      $this->bolNPWP    = $bolNPWP;
      $this->intPeriod  = (is_numeric($intPeriod)) ? $intPeriod : 12;
      $this->strFamilyStatus = $strFamilyStatus;
      $this->fltPTKP    = $this->getTaxReduction($strFamilyStatus);
      $this->fltJamsostekDeduction    = $fltJamsostekDeduction;

    }

    function setDataIncludeIrregular($fltPKP, $fltPIKP, $strFamilyStatus, $bolNPWP = false , $fltJamsostekDeduction = 0, $fltBPJSDeduction = 0, $fltPensionDeduction = 0, $fltIuran = 0, $strIDEmployee, $tax_method, $arrEmpBaseTaxPaidTaxBefore = null, $calcMonth, $calcYear, $strJoinDate, $strResignDate, $bolExpat)
    {
      $this->fltPKP                	  	  = (is_numeric($fltPKP)) ? $fltPKP : 0;
      $this->fltPIKP   					  = (is_numeric($fltPIKP)) ? $fltPIKP : 0;
      $this->bolNPWP  					  = $bolNPWP;
      $this->strFamilyStatus 			  = $strFamilyStatus;
      $this->fltPTKP    				  = $this->getTaxReduction($strFamilyStatus);
      $this->fltJamsostekDeduction 		  = $fltJamsostekDeduction;
      $this->fltBPJSDeduction 		      = $fltBPJSDeduction;
      $this->fltPensionDeduction 		  = $fltPensionDeduction;
      $this->fltIuran            		  = $fltIuran;
      $this->fltTaxRegular             	  = 0;
	  $this->fltTaxIrregular              = 0;
      $this->strIDEmployee  		  	  = $strIDEmployee;
      $this->intMethod  			 	  = $tax_method;
      $this->arrEmpBaseTaxPaidTaxBefore   = $arrEmpBaseTaxPaidTaxBefore;
	  $this->calcMonth 				      = $calcMonth;
	  $this->calcYear 				      = $calcYear;
	  $this->strJoinDate 				  = $strJoinDate;
	  $this->strResignDate 				  = $strResignDate;
	  $this->intEndOfYearMonth 			  = 12;
	  $this->intEndOfMonthDay   		  = 30;
	  $this->intJoinDateDay				  = date('j', strtotime($strJoinDate));
	  $this->intJoinDateMonth			  = date('n', strtotime($strJoinDate));
	  $this->intJoinDateYear			  = date('Y', strtotime($strJoinDate));
	  $this->intResignDateDay    		  = ($this->strResignDate != '') ? date('j', strtotime($strResignDate)) : 0;
	  $this->intResignDateMonth			  = ($this->strResignDate != '') ? date('n', strtotime($strResignDate)) : 0;
	  $this->intResignDateYear			  = ($this->strResignDate != '') ? date('Y', strtotime($strResignDate)) : 0;
//      $this->intJoinDateLimit             = (isset(JOIN_DATE_LIMIT))?JOIN_DATE_LIMIT:100;

	  //== Inisialisasi untuk konstanta perhitungan==//

	  $this->taxableDayUpToEndOfYear	  = 0;
	  $this->taxableDayUpToCurrent 		  = 0;
	  $this->taxableMonth 				  = 0;
	  $this->currentTaxableMonth		  = 0;
	  $this->intJoinDateYear;
	  $this->calcYear;
	  $this->calcMonth;
	  if ($this->intJoinDateYear < $this->calcYear)// && $this->strResignDate == '')
	  {
		  $this->taxableDayUpToEndOfYear 	= $this->intEndOfYearMonth * $this->intEndOfMonthDay;
		  $this->taxableDayUpToCurrent 		= $this->calcMonth * $this->intEndOfMonthDay;
		  $this->taxableMonth 				= $this->intEndOfYearMonth;
		  $this->currentTaxableMonth		= $this->calcMonth;
	  }

	  elseif ($this->intJoinDateYear == $this->calcYear)// && $this->strResignDate == '')
	  {
		  $this->taxableDayUpToEndOfYear 	= ($this->intEndOfYearMonth - $this->intJoinDateMonth) * $this->intEndOfMonthDay + $this->intEndOfMonthDay + 1 - $this->intJoinDateDay;
		  $this->taxableDayUpToCurrent 		= ($this->calcMonth - $this->intJoinDateMonth) * $this->intEndOfMonthDay + $this->intEndOfMonthDay + 1 - $this->intJoinDateDay;
		  $this->taxableMonth 				= $this->intEndOfYearMonth - $this->intJoinDateMonth + 1;
		  $this->currentTaxableMonth		= $this->calcMonth - $this->intJoinDateMonth + 1;

          if ($this->intJoinDateDay >= JOIN_DATE_LIMIT )
          {
              $this->taxableMonth 				-=1;
		      $this->currentTaxableMonth		-=1;
          }
	  }

	  $this->intPeriod 	= $this->taxableMonth;

      if ($bolExpat)
      {
          $this->taxableDayUpToEndOfYear 	= 360;
          $this->taxableMonth 				= 12;
      }


      $this->currentTaxableMonth		=1;

      if ($this->intResignDateYear == $this->calcYear && $this->intResignDateMonth == $this->calcMonth)
      {
          $this->currentTaxableMonth = $this->taxableMonth;
      }

    }

    /* getTax : fungsi untuk mulai melakukan perhitungan pajak
    */
    function getTax($bolRegular)
    {
      if ($this->intMethod == 1) // gross up
        return $this->calculatePph21GrossUp($this->fltPKP, $this->fltPIKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth, $bolRegular);
      else // gross
        return $this->calculatePph21Gross($this->fltPKP, $this->fltPIKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth, $bolRegular);
    }

    function getTaxAnnual($bolRegular)
    {
      if ($this->intMethod == 1) // gross up
        return $this->calculatePph21Annual($this->fltPKP, $this->fltPIKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth, $bolRegular);
      else // gross
        return $this->calculatePph21Annual($this->fltPKP, $this->fltPIKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth, $bolRegular);
    }

     /* New Function calculatePph21Gross only calculate tax with using
        actual base income and irregular income */
	function calculatePph21Gross($fltNetIncome, $fltIrrIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth, $bolRegular)
	{
			$this->calculateBaseTaxBefore();
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;
			$fltNetIncome;
			$this->baseTaxBefore;
			$fltNetIncome 		= $fltNetIncome + $this->baseTaxBefore;																	//total upah kotor
			$fltIrrIncome 		= $fltIrrIncome + $this->baseIrrTaxBefore; 																//total irregular
			$netincomeannualize = ($taxableMonth / $currentTaxableMonth * $fltNetIncome) + $fltIrrIncome;					//total income kena pajak disetahunkan

			$functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);													//tunjangan jabatan
			$jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
//			$BPJSDeduction	    = ($this->BPJSDeductionBefore + $fltBPJSDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
//			$PensionDeduction	= ($this->PensionDeductionBefore + $fltPensionDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
//			$Iuran           	= ($this->IuranBefore + $fltIuran) * $taxableMonth / $currentTaxableMonth;
			$taxablenetincome   = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

			$annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun
			$taxIrregularUntilLastPeriod = $this->irrTaxBefore;																			//PPh Irregular terhutang sampai bulan kemarin

			$taxableIncomeNet = $this->calculatePph21GrossAnnualizedNet($this->fltPKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth);
            $annualizetaxincomeNet = $countpph21->calculateIncomeTaxAnnualized($taxableIncomeNet,$bolNPWP);
            $taxIrregular = $annualizetaxincome - $annualizetaxincomeNet;

        if ($bolRegular){
				$taxUntilCurrentPeriod = ($annualizetaxincome - $taxIrregular) * $currentTaxableMonth / $taxableMonth;		//PPh terhutang sampai bulan ini
				$taxUntilLastPeriod = $this->taxBefore;																						            //PPh terhutang sampai bulan kemarin
				$monthlytax = $countpph21->roundDown(($taxUntilCurrentPeriod - $taxUntilLastPeriod),0);			//die();					            //PPh terutang bulan ini
                $this->fltTaxRegular = $monthlytax;
				return $monthlytax;
			}
			else
			{
				$monthlytaxIrregular = $countpph21->roundDown(($taxIrregular - $taxIrregularUntilLastPeriod),0);			//die()
                $this->fltTaxIrregular = $monthlytaxIrregular;
				return $monthlytaxIrregular;
			}

	}

	function calculatePph21GrossAnnualizedNet($fltNetIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth)
	{
			$this->calculateBaseTaxBefore();
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;

			$fltNetIncome 		= $fltNetIncome + $this->baseTaxBefore;																	//total upah kotor
			$fltTotalIncome 	= $fltNetIncome;																						//total income kena pajak
			$netincomeannualize = $taxableMonth / $currentTaxableMonth * $fltTotalIncome;									//total income disetahunkan

			$functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);														//tunjangan jabatan
			$jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
//			$BPJSDeduction	    = ($this->BPJSDeductionBefore + $fltBPJSDeduction) * $taxableMonth / $currentTaxableMonth;
//			$PensionDeduction	    = ($this->PensionDeductionBefore + $fltPensionDeduction) * $taxableMonth / $currentTaxableMonth;
//			$Iuran	    = ($this->IuranBefore + $fltIuran) * $taxableMonth / $currentTaxableMonth;
            $taxablenetincome = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

			//$annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun
			return $taxablenetincome;
	}

	/* New Function to calculate base tax months before */
    function calculateBaseTaxBefore(){
			$this->baseTaxBefore = 0;
			$this->baseIrrTaxBefore = 0;
			$this->taxBefore = 0;
			$this->irrTaxBefore = 0;
			$this->baseJamsostekBefore = 0;
			$this->JamsostekDeductionBefore = 0;
			$this->BPJSDeductionBefore = 0;
			$this->PensionDeductionBefore = 0;
			$this->IuranBefore = 0;

//			if(isset($this->arrEmpBaseTaxPaidTaxBefore))
//			{
//				$this->baseTaxBefore = $this->arrEmpBaseTaxPaidTaxBefore['base_tax'];
//				$this->baseIrrTaxBefore = $this->arrEmpBaseTaxPaidTaxBefore['base_irregular_tax'];
//				$this->taxBefore = $this->arrEmpBaseTaxPaidTaxBefore['tax'];
//				$this->irrTaxBefore = $this->arrEmpBaseTaxPaidTaxBefore['irregular_tax'];
//				$this->baseJamsostekBefore = $this->arrEmpBaseTaxPaidTaxBefore['base_jamsostek'];
//				$this->JamsostekDeductionBefore = $this->arrEmpBaseTaxPaidTaxBefore['jamsostek_deduction'];
//				$this->BPJSDeductionBefore = $this->arrEmpBaseTaxPaidTaxBefore['bpjs_deduction'];
//				$this->PensionDeductionBefore = $this->arrEmpBaseTaxPaidTaxBefore['pension_deduction'];
//				$this->IuranBefore = $this->arrEmpBaseTaxPaidTaxBefore['iuran'];
//			}

    }

	/* New Function to calculate functional cost deduction */
    function calculateFunctionalCost($fltIncome){
		  $functionalCost = $fltIncome * 5 / 100;
		  if ($functionalCost>= $this->fltMaxPosition) $functionalCost = $this->fltMaxPosition;
		  return $functionalCost;
    }

         /* New Function calculatePph21Gross only calculate tax with using
        actual base income and irregular income */
	function calculatePph21GrossUp($fltNetIncome, $fltIrrIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth, $bolRegular)
	{
			$this->calculateBaseTaxBefore();
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;
            $fltTax = 0;
            $fltDelta = 0.01;
            $taxAllowance = 0;
            $taxIrregularAllowance = 0;
            $bolPuter = true;
//        echo $this->taxBefore;;
			$fltNetIncome0 		= $fltNetIncome + $this->baseTaxBefore;																	//total upah kotor
			$fltIrrIncome 		= $fltIrrIncome + $this->baseIrrTaxBefore; 																//total irregular
            $taxIrregularUntilLastPeriod = $this->irrTaxBefore;																			//PPh Irregular terhutang sampai bulan kemarin
            $annualizetaxincomeNet = $this->calculatePph21GrossUpAnnualizedNet($this->fltPKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth);

            while ($bolPuter)
            {
                $fltNetIncome = $fltNetIncome0 + $taxAllowance;

                if ($currentTaxableMonth > 0)
                    $netincomeannualize = (($taxableMonth / $currentTaxableMonth) * $fltNetIncome) + $fltIrrIncome + $taxIrregularAllowance;					//total income kena pajak disetahunkan
                else
                    $netincomeannualize = 0;

                $functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);														//tunjangan jabatan

                if ($currentTaxableMonth > 0)
                    $jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
                else
                    $jamsostekDeduction = 0;


                //                $BPJSDeduction	    = ($this->BPJSDeductionBefore + $fltBPJSDeduction) * $taxableMonth / $currentTaxableMonth;
//                $PensionDeduction	= ($this->PensionDeductionBefore + $fltPensionDeduction) * $taxableMonth / $currentTaxableMonth;
//                $Iuran	= ($this->IuranBefore + $fltIuran) * $taxableMonth / $currentTaxableMonth;
                $taxablenetincome = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

                $annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun
//                echo "<br><hr><br>";
                $taxIrregular = $annualizetaxincome - $annualizetaxincomeNet;
                $monthlytaxIrregular = $countpph21->roundDown(($taxIrregular - $taxIrregularUntilLastPeriod),0);

                $taxUntilCurrentPeriod = ($annualizetaxincome - $taxIrregular) * $currentTaxableMonth / $taxableMonth;		//PPh terhutang sampai bulan ini
                $taxUntilLastPeriod = $this->taxBefore;																						            //PPh terhutang sampai bulan kemarin
                $monthlytax = $countpph21->roundDown(($taxUntilCurrentPeriod - $taxUntilLastPeriod),0);

//                die();
                if ((abs($monthlytax - $taxAllowance) >= $fltDelta ) && (abs($monthlytaxIrregular - $taxIrregularAllowance) >= $fltDelta ) )
                {
                  $taxAllowance = ($taxAllowance + $monthlytax) / 2;
                  $taxIrregularAllowance = ( $taxIrregularAllowance + $monthlytaxIrregular )/2;
                }
                else
                {
                  $bolPuter = false;
                }
            }
            if ($bolRegular){
                $this->fltTaxRegular = $monthlytax;
				return $monthlytax;
			}
			else
			{			//die()
                $this->fltTaxIrregular = $monthlytaxIrregular;
				return $monthlytaxIrregular;
			}

	}

	function calculatePph21GrossUpAnnualizedNet($fltNetIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth)
	{
			$this->calculateBaseTaxBefore();
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;
            $fltTax = 0;
            $fltDelta = 0.01;
            $taxAllowance = 0;
            $bolPuter = true;
//        echo $this->baseTaxBefore;
			$fltNetIncome0 		= $fltNetIncome + $this->baseTaxBefore;																	//total upah kotor

            while ($bolPuter)
            {
                $fltNetIncome = $fltNetIncome0 + $taxAllowance;

                if ($currentTaxableMonth > 0)
                    $netincomeannualize = $taxableMonth / $currentTaxableMonth * $fltNetIncome;					//total income kena pajak disetahunkan
                else
                    $netincomeannualize = 0;

                $functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);														//tunjangan jabatan

                if ($currentTaxableMonth > 0)
                    $jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction) * $taxableMonth / $currentTaxableMonth;	//potongan jamsostek setahun
                else
                    $jamsostekDeduction = 0;

//                $BPJSDeduction	    = ($this->BPJSDeductionBefore + $fltBPJSDeduction) * $taxableMonth / $currentTaxableMonth;
//                $PensionDeduction	    = ($this->PensionDeductionBefore + $fltPensionDeduction) * $taxableMonth / $currentTaxableMonth;
//                $Iuran	    = ($this->IuranBefore + $fltIuran) * $taxableMonth / $currentTaxableMonth;
                $taxablenetincome = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

                $annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun

                $taxUntilCurrentPeriod = $annualizetaxincome * $currentTaxableMonth / $taxableMonth;		//PPh terhutang sampai bulan ini
                $taxUntilLastPeriod = $this->taxBefore;																						            //PPh terhutang sampai bulan kemarin
                $monthlytax = $countpph21->roundDown(($taxUntilCurrentPeriod - $taxUntilLastPeriod),0);

                if (abs($monthlytax - $taxAllowance) >= $fltDelta )
                {
                  $taxAllowance = ($taxAllowance + $monthlytax) / 2;
                }
                else
                {
                  $bolPuter = false;
                }

            }
			return $annualizetaxincome;
	}


          /* New Function calculatePph21Gross only calculate tax with using
        actual base income and irregular income */
	function calculatePph21Annual($fltNetIncome, $fltIrrIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth, $bolRegular)
	{
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;
            $fltTax = 0;
            $fltDelta = 0.01;
            $taxAllowance = 0;
            $bolPuter = true;

			$fltNetIncome0 		= $fltNetIncome;															//total upah kotor
			$fltIrrIncome 		= $fltIrrIncome;															//total irregular

            $annualizetaxincomeNet = $this->calculatePph21AnnualNet($this->fltPKP, $this->bolNPWP, $this->fltPTKP, $this->fltJamsostekDeduction, $this->fltBPJSDeduction, $this->fltPensionDeduction, $this->fltIuran, $this->taxableDayUpToEndOfYear, $this->taxableDayUpToCurrent, $this->taxableMonth, $this->currentTaxableMonth);

            $fltNetIncome = $fltNetIncome0;

            $netincomeannualize = ($fltNetIncome) + $fltIrrIncome;					//total income kena pajak disetahunkan

            $functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);														//tunjangan jabatan
            $jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction);	//potongan jamsostek setahun
            $taxablenetincome = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

            $annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun

            $taxIrregular = $annualizetaxincome - $annualizetaxincomeNet;

            $taxUntilCurrentPeriod = ($annualizetaxincome - $taxIrregular);		//PPh terhutang sampai bulan ini

            $yearlytax = $countpph21->roundDown(($taxUntilCurrentPeriod),0);


            if ($bolRegular){
                $this->fltTaxRegular = $yearlytax;
				return $yearlytax;
			}
			else
			{
				$yearlytaxIrregular = $countpph21->roundDown(($taxIrregular),0);			//die()
                $this->fltTaxIrregular = $yearlytaxIrregular;
				return $yearlytaxIrregular;
			}

	}

	function calculatePph21AnnualNet($fltNetIncome, $bolNPWP, $fltPTKP, $fltJamsostekDeduction, $fltBPJSDeduction, $fltPensionDeduction, $fltIuran, $taxableDayUpToEndOfYear, $taxableDayUpToCurrent, $taxableMonth, $currentTaxableMonth)
	{
			$countpph21 = new countPPH21(12, $this->arrPTKP);
			$netincomeannualize = 0;
            $fltTax = 0;
            $fltDelta = 0.01;
            $taxAllowance = 0;
            $bolPuter = true;

			$fltNetIncome0 		= $fltNetIncome;																	//total upah kotor

            $fltNetIncome = $fltNetIncome0;

            $netincomeannualize = $fltNetIncome;					//total income kena pajak disetahunkan

            $functionalCost 	= $this->calculateFunctionalCost($netincomeannualize);														//tunjangan jabatan
            $jamsostekDeduction	= ($this->JamsostekDeductionBefore + $fltJamsostekDeduction);	//potongan jamsostek setahun
//            $BPJSDeduction	    = ($this->BPJSDeductionBefore + $fltBPJSDeduction) * $taxableMonth / $currentTaxableMonth;
//            $PensionDeduction	    = ($this->PensionDeductionBefore + $fltPensionDeduction) * $taxableMonth / $currentTaxableMonth;
//            $Iuran	    = ($this->IuranBefore + $fltIuran) * $taxableMonth / $currentTaxableMonth;
            $taxablenetincome = $countpph21->roundDown(($netincomeannualize - $functionalCost - $jamsostekDeduction - $BPJSDeduction - $PensionDeduction - $Iuran - $fltPTKP),3);									//total pendapatan kena pajak bersih

            $annualizetaxincome = $countpph21->calculateIncomeTaxAnnualized($taxablenetincome,$bolNPWP);  								//Pph Terhutang setahun

            $taxUntilCurrentPeriod = $annualizetaxincome;		//PPh terhutang sampai bulan ini
																			            //PPh terhutang sampai bulan kemarin
            $yearlytax = $countpph21->roundDown(($taxUntilCurrentPeriod),0);


			return $annualizetaxincome;
	}

  }
?>
