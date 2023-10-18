<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  //---- INISIALISASI ----------------------------------------------------
  $strModule = "payroll";

  $strCurrentActiveTemplate = "";

  $strMessages = "";
  $strWordsSave = getWords("save");
  $strWordsDelete = getWords("delete");
  $strWordsSaveTemplate = getWords("save as template");
  $strWordsDeleteTemplate = getWords("delete template");
  $strWordsLoadTemplate = getWords("load template");
  $strMsgClass = "";
  $strWordsGeneralSetting    = getWords("general setting");
  $strWordsSalarySet          = getWords("salary set");
  $strWordsEmployeeAllowance = getWords("employee allowance");
  $strWordsManagerialAllowance = getWords("managerial allowance");
  $strWordsEmployeeDeduction = getWords("employee deduction");
  $strWordsManagerialDeduction = getWords("managerial deduction");
  $strWordsCommonSetting = getWords("common setting");
  $strWordsHourPerMonth = getWords("hour per month");
  $strWordsDaysPerMonth = getWords("days per month");
  $strWordsDaysPerMonthAllDay = getWords("days per month all day");
  $strWordsDaysPerMonthSatIn = getWords("days per month Saturday IN");
  $strWordsRoundSalaryTo = getWords("round salary to (rp)");
  $strWordsMaxStandardOT = getWords("max standard OT");
  $strWordsOTRate = getWords("OT rate after max standard (rp/hour)");
  $strWordsOTBreakfast = getWords("OT breakfast allowance (rp)");
  $strWordsOTBase = getWords("OT base percentage");
  $strWordsNishab = getWords("nishab");
  $strWordsTaxMethod = getWords("tax method");
  $strWordsBasicAllowanceList = getWords("basic and allowance list");
  $strWordsSeq = getWords("seq");
  $strWordCode = getWords("code");
  $strWordsDispName = getWords("display name");
  $strWordsAmount = getWords("amount");
  $strWordsActive = getWords("active");
  $strWordsSlip = getWords("slip");
  $strWordsProrate = getWords("prorate");
  $strWordsOT = getWords("OT");
  $strWordsTax = getWords("tax");
  $strWordsJamsostek = getWords("jamsostek");
  $strWordsHideIfZero = getWords("hide if zero");
  $strWordsDaily = getWords("daily");
  $strWordsBenefit = getWords("benefit");
  $strWordsIrregular = getWords("irregular");
  $strWordsMaxLink = getWords("maxlink");
  $strWordsDeductionList = getWords("deduction list");
  $strWordsMoreDeduction = getWords("more deduction");
  $strWordsMoreAllowance = getWords("more allowance");
  $strWordsBasicSalaryCode = getWords("basic salary code");
  $strWordsBPJSMax = getWords("maximum allowance for BPJS Kesehatan");
  $strWordsPensionMax = getWords("maximum allowance for BPJS TK Pensiun");
  $strWordsUseUMKOT = getWords("use u m k for o t");
  $strWordsUseUMKBPJSTK = getWords("use u m k for b p j s - t k");
  $strWordsUseUMKBPJSKS = getWords("use u m k for b p j s - k s");
  $strWordsLateCoefficient = getWords("late t i multiplier");

  $arrSetting = array(
    "strHour" => array("code" => "hour_per_month", "value" => "0", "note" => "Total Hour Per Month", "default" => "0",),
    "strDays" => array("code" => "days_per_month", "value" => "0", "note" => "Total Days Per Month", "default" => "0",),
    "strDaysAllDay" => array("code" => "days_per_month_all_day", "value" => "0", "note" => "Total Days Per Month All Day", "default" => "0",),
    "strDaysSatIn" => array("code" => "days_per_month_sat_in", "value" => "0", "note" => "Total Days Per Month Saturday In", "default" => "0",),
    "strRound" => array("code" => "salary_round", "value" => "100", "note" => "Salary Rounding Factor", "default" => "100",),
    //"strBasicName" => array("code" => "basicsalary_name", "value" => "Basic Salary", "note" => "Display Name for Basic Salary", "default" => "Basic Salary",),
     "strSalaryDateFrom" => array("code" => "salary_date_from", "value" => "16", "note" => "default date from for salary calc", "default" => "16", "oldparameter" => "oldSalaryDateFrom"),
    "strSalaryDateThru" => array("code" => "salary_date_thru", "value" => "15", "note" => "default date thru for salary calc", "default" => "15", "oldparameter" => "oldSalaryDateThru"),

    "strSalaryDate" => array("code" => "salary_date", "value" => "25", "note" => "default date for salary calculation", "default" => "25", "oldparameter" => "oldSalaryDate"),
    "strHalfOTMax" => array("code" => "half_ot_max", "value" => "65", "note" => "Max Standard OT", "default" => "65",),
    "strHalfOTRate" => array("code" => "half_ot_rate", "value" => "10000", "note" => "Amount OT Rate after Max Standard hours", "default" => "10000",),
    "strOTBreakfastAllowance" => array("code" => "ot_breakfast_allowance", "value" => "2000", "note" => "Amount of OT breakfast allowance ", "default" => "2000",),
    "intOTPercent" => array("code" => "ot_percent", "value" => "100", "note" => "Percentage of OT per hour", "default" => "100",),
    "strFullAttendanceAllowance" => array("code" => "full_attendance_allowance", "value" => "0", "note" => "Amount for Full Attendance Allow %", "default" => "30000",),
    "strHalfAttendanceAllowance" => array("code" => "half_attendance_allowance", "value" => "0", "note" => "Amount for 1 off Attendance Allow %", "default" => "15000",),
    "strNishab" => array("code" => "nishab", "value" => "0", "note" => "Amount of Nishab", "default" => "0",),
    "strTaxMethod" => array("code" => "tax_method", "value" => "t", "note" => "0:gross, 1:gross up", "default" => "t",),
    "strBasicSalaryCode" => array("code" => "basic_salary_code", "value" => "basic_salary", "note" => "Basic Salary Code", "default" => "basic_salary",),
    "strBPJSMax" => array("code" => "bpjs_max", "value" => "bpjs_max", "note" => "Maximum allowance for BPJS", "default" => "bpjs_max",),
    "strPensionMax" => array("code" => "pension_max", "value" => "pension_max", "note" => "Maximum allowance for Pension", "default" => "pension_max",),
    "strUseUMKForOT" => array("code" => "use_umk_ot", "value" => "t", "note" => "0:no, 1:yes", "default" => "t",),
    "strUseUMKForBPJSKS" => array("code" => "use_umk_bpjs_ks", "value" => "t", "note" => "0:no, 1:yes", "default" => "t",),
    "strUseUMKForBPJSTK" => array("code" => "use_umk_bpjs_tk", "value" => "t", "note" => "0:no, 1:yes", "default" => "t",),
    "strValueForOT" => array("code" => "value_ot", "value" => "0", "note" => "", "default" => "0",),
    "strValueForBPJSKS" => array("code" => "value_bpjs_ks", "value" => "0", "note" => "", "default" => "0",),
    "strValueForBPJSTK" => array("code" => "value_bpjs_tk", "value" => "0", "note" => "", "default" => "0",),
    "strLateCoefficient" => array("code" => "late_coefficient", "value" => "5000", "note" => "Late TI Multiplier", "default" => "5000",),
    //Basic Salary
    /*"strBasicSalaryName" => array("code" => "basic_salary_name", "value" => "Basic Salary Allowance", "note" => "Display Name for Basic Salary", "default" => "Basic Salary",),
    "strBasicSalaryActive" => array("code" => "basic_salary_active", "value" => "t", "note" => "Is Basic Salary Active?", "default" => "t",),
    "strBasicSalaryIr" => array("code" => "basic_salary_irregular", "value" => "t", "note" => "Is Irregular", "default" => "f",),
    "strBasicSalaryBen" => array("code" => "basic_salary_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strBasicSalaryShow" => array("code" => "basic_salary_show", "value" => "t", "note" => "Show Basic Salary", "default" => "t",),
    "strBasicSalaryProrate" => array("code" => "basic_salary_prorate", "value" => "t", "note" => "Prorate Overtime Allow", "default" => "t",),
    "strBasicSalaryOT" => array("code" => "basic_salary_ot", "value" => "f", "note" => "Basic Salary include OT", "default" => "f",),
    "strBasicSalaryTax" => array("code" => "basic_salary_tax", "value" => "t", "note" => "Basic Salary include in tax", "default" => "t",),
    "strBasicSalaryJams" => array("code" => "basic_salary_jams", "value" => "t", "note" => "Basic Salary include in jamsostek", "default" => "t",),
    "strBasicSalaryDaily" => array("code" => "basic_salary_daily", "value" => "t", "note" => "Basic Salary is daily allowance", "default" => "f",),
    "strBasicSalaryHidezero" => array("code" => "basic_salary_hidezero", "value" => "t", "note" => "Basic Salary is hidden if the value is zero", "default" => "f",),*/

    //Shift Allowance
    "strShiftName" => array("code" => "shift_allowance_name", "value" => "Shift Allowance", "note" => "Display Name for Shift Allowance", "default" => "Shift Allowance",),
    "strShiftActive" => array("code" => "shift_allowance_active", "value" => "t", "note" => "Is Poition Allow Active?", "default" => "t",),
    "strShiftIr" => array("code" => "shift_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "f",),
    "strShiftBen" => array("code" => "shift_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strShiftShow" => array("code" => "shift_allowance_show", "value" => "t", "note" => "Show Shift Allow", "default" => "t",),
    "strShiftProrate" => array("code" => "shift_allowance_prorate", "value" => "t", "note" => "Prorate Shift Allow", "default" => "t",),
    "strShiftOT" => array("code" => "shift_allowance_ot", "value" => "f", "note" => "Shift Allow include OT", "default" => "f",),
    "strShiftTax" => array("code" => "shift_allowance_tax", "value" => "t", "note" => "Shift Allow include in tax", "default" => "t",),
    "strShiftJams" => array("code" => "shift_allowance_jams", "value" => "t", "note" => "Shift Allow include in jamsostek", "default" => "t",),
    "strShiftDaily" => array("code" => "shift_allowance_daily", "value" => "t", "note" => "Shift Allow is daily allowance", "default" => "f",),
    "strShiftHidezero" => array("code" => "shift_allowance_hidezero", "value" => "t", "note" => "Shift Allow is hidden if the value is zero", "default" => "f",),
    //"strShiftMultival" => array("code" => "overtime_allowance_multival", "value" => "t", "note" => "Indicator of Out Fix Allow ", "default" => "f",),

    //Overtime Allowance
    "strOvertimeName" => array("code" => "overtime_allowance_name", "value" => "Overtime Allowance", "note" => "Display Name for Overtime Allowance", "default" => "Overtime Allowance",),
    "strOvertimeActive" => array("code" => "overtime_allowance_active", "value" => "t", "note" => "Is Poition Allow Active?", "default" => "t",),
    "strOvertimeIr" => array("code" => "overtime_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "f",),
    "strOvertimeBen" => array("code" => "overtime_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strOvertimeShow" => array("code" => "overtime_allowance_show", "value" => "t", "note" => "Show Overtime Allow", "default" => "t",),
    "strOvertimeProrate" => array("code" => "overtime_allowance_prorate", "value" => "t", "note" => "Prorate Overtime Allow", "default" => "t",),
    "strOvertimeOT" => array("code" => "overtime_allowance_ot", "value" => "f", "note" => "Overtime Allow include OT", "default" => "f",),
    "strOvertimeTax" => array("code" => "overtime_allowance_tax", "value" => "t", "note" => "Overtime Allow include in tax", "default" => "t",),
    "strOvertimeJams" => array("code" => "overtime_allowance_jams", "value" => "t", "note" => "Overtime Allow include in jamsostek", "default" => "t",),
    "strOvertimeDaily" => array("code" => "overtime_allowance_daily", "value" => "t", "note" => "Overtime Allow is daily allowance", "default" => "f",),
    "strOvertimeHidezero" => array("code" => "overtime_allowance_hidezero", "value" => "t", "note" => "Overtime Allow is hidden if the value is zero", "default" => "f",),
    //"strOvertimeMultival" => array("code" => "overtime_allowance_multival", "value" => "t", "note" => "Indicator of Out Fix Allow ", "default" => "f",),


    //Overtime Allowance Auto Paid
    "strOvertimeAutoPaidName" => array("code" => "overtime_allowance_auto_paid_name", "value" => "Overtime Allowance Auto Paid", "note" => "Display Name for Overtime Allowance", "default" => "Overtime Allowance Auto Paid",),
    "strOvertimeAutoPaidAmount" => array("code" => "overtime_allowance_auto_paid_amount", "value" => "0", "note" => "Display Name for Overtime Allowance", "default" => "0",),
    "strOvertimeAutoPaidActive" => array("code" => "overtime_allowance_auto_paid_active", "value" => "t", "note" => "Is Poition Allow Active?", "default" => "t",),
    "strOvertimeAutoPaidIr" => array("code" => "overtime_allowance_auto_paid_irregular", "value" => "t", "note" => "Is Irregular", "default" => "f",),
    "strOvertimeAutoPaidBen" => array("code" => "overtime_allowance_auto_paid_benefit", "value" => "f", "note" => "Is Benefit", "default" => "t",),
    "strOvertimeAutoPaidShow" => array("code" => "overtime_allowance_auto_paid_show", "value" => "t", "note" => "Show Overtime Allow", "default" => "t",),
    "strOvertimeAutoPaidProrate" => array("code" => "overtime_allowance_auto_paid_prorate", "value" => "t", "note" => "Prorate Overtime Allow", "default" => "t",),
    "strOvertimeAutoPaidOT" => array("code" => "overtime_allowance_auto_paid_ot", "value" => "f", "note" => "Overtime Allow include OT", "default" => "f",),
    "strOvertimeAutoPaidTax" => array("code" => "overtime_allowance_auto_paid_tax", "value" => "t", "note" => "Overtime Allow include in tax", "default" => "t",),
    "strOvertimeAutoPaidJams" => array("code" => "overtime_allowance_auto_paid_jams", "value" => "t", "note" => "Overtime Allow include in jamsostek", "default" => "t",),
    "strOvertimeAutoPaidDaily" => array("code" => "overtime_allowance_auto_paid_daily", "value" => "t", "note" => "Overtime Allow is daily allowance", "default" => "f",),
    "strOvertimeAutoPaidHidezero" => array("code" => "overtime_allowance_auto_paid_hidezero", "value" => "t", "note" => "Overtime Allow is hidden if the value is zero", "default" => "f",),
    //"strOvertimeMultival" => array("code" => "overtime_allowance_multival", "value" => "t", "note" => "Indicator of Out Fix Allow ", "default" => "f",),

    //THR
    "strTHRName" => array("code" => "thr_allowance_name", "value" => "THR Allowance", "note" => "Display Name for THR allowance", "default" => "THR Allowance",),
    "strTHRAmount" => array("code" => "thr_allowance", "value" => "0", "note" => "Amount for THR Allow ", "default" => "0",),
    "strTHRActive" => array("code" => "thr_allowance_active", "value" => "t", "note" => "Is THR Allow Active?", "default" => "t",),
    "strTHRIr" => array("code" => "thr_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strTHRBen" => array("code" => "thr_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strTHRShow" => array("code" => "thr_allowance_show", "value" => "t", "note" => "Show THR Allow", "default" => "t",),
    "strTHRProrate" => array("code" => "thr_allowance_prorate", "value" => "t", "note" => "Prorate THR Allow", "default" => "t",),
    "strTHROT" => array("code" => "thr_allowance_ot", "value" => "t", "note" => "THR Allow include OT", "default" => "t",),
    "strTHRTax" => array("code" => "thr_allowance_tax", "value" => "t", "note" => "THR Allow include in tax", "default" => "t",),
    "strTHRJams" => array("code" => "thr_allowance_jams", "value" => "t", "note" => "THR Allow include in jamsostek", "default" => "t",),
    "strTHRDaily" => array("code" => "thr_allowance_daily", "value" => "t", "note" => "THR Allow include is daily allowance", "default" => "f",),
    "strTHRHidezero" => array("code" => "thr_allowance_hidezero", "value" => "t", "note" => "THR Allow  is hidden if the value is zero", "default" => "f",),
    //"strTHRMultival" => array("code" => "thr_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

        //pension Allowance
    "strPensionAllowName" => array("code" => "pension_allowance_name", "value" => "pension Allowance", "note" => "Display Name for pension Allow", "default" => "Pension Allowance",),
    "strPensionAllowAmount" => array("code" => "pension_allowance", "value" => "0", "note" => "Amount for pension Allow %", "default" => "0",),
    "strPensionAllowActive" => array("code" => "pension_allowance_active", "value" => "t", "note" => "Is pension Allow Active?", "default" => "t",),
    "strPensionAllowIr" => array("code" => "pension_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strPensionAllowBen" => array("code" => "pension_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strPensionAllowShow" => array("code" => "pension_allowance_show", "value" => "t", "note" => "Show pension Allow", "default" => "t",),
    "strPensionAllowProrate" => array("code" => "pension_allowance_prorate", "value" => "t", "note" => "Prorate pension Allow", "default" => "t",),
    "strPensionAllowOT" => array("code" => "pension_allowance_ot", "value" => "t", "note" => "pension Allow include OT", "default" => "t",),
    "strPensionAllowTax" => array("code" => "pension_allowance_tax", "value" => "t", "note" => "pension Allow include in tax", "default" => "t",),
    "strPensionAllowJams" => array("code" => "pension_allowance_jams", "value" => "f", "note" => "pension Allow include in jamsostek", "default" => "f",),
    "strPensionAllowDaily" => array("code" => "pension_allowance_daily", "value" => "t", "note" => "pension Allow include is daily allowance", "default" => "f",),
    "strPensionAllowHidezero" => array("code" => "pension_allowance_hidezero", "value" => "t", "note" => "pension Allow  is hidden if the value is zero", "default" => "f",),



    //Leave Allowance
    "strLeaveName" => array("code" => "leave_allowance_name", "value" => "Leave Allowance", "note" => "Display Name for Leave Allow", "default" => "Leave Allowance",),
    "strLeaveAmount" => array("code" => "leave_allowance", "value" => "0", "note" => "Amount for Leave Allow ", "default" => "0",),
    "strLeaveActive" => array("code" => "leave_allowance_active", "value" => "t", "note" => "Is Leave Allow Active?", "default" => "t",),
    "strLeaveIr" => array("code" => "leave_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strLeaveBen" => array("code" => "leave_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strLeaveShow" => array("code" => "leave_allowance_show", "value" => "t", "note" => "Show Leave Allow", "default" => "t",),
    "strLeaveProrate" => array("code" => "leave_allowance_prorate", "value" => "t", "note" => "Prorate Leave Allow", "default" => "t",),
    "strLeaveOT" => array("code" => "leave_allowance_ot", "value" => "t", "note" => "Leave Allow include OT", "default" => "t",),
    "strLeaveTax" => array("code" => "leave_allowance_tax", "value" => "t", "note" => "Leave Allow include in tax", "default" => "t",),
    "strLeaveJams" => array("code" => "leave_allowance_jams", "value" => "t", "note" => "Leave Allow include in jamsostek", "default" => "t",),
    "strLeaveDaily" => array("code" => "leave_allowance_daily", "value" => "t", "note" => "Leave Allow include is daily allowance", "default" => "f",),
    "strLeaveHidezero" => array("code" => "leave_allowance_hidezero", "value" => "t", "note" => "Leave Allow  is hidden if the value is zero", "default" => "f",),
    //"strLeaveMultival" => array("code" => "leave_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

    //Jamsostek Allowance
    "strJamsAllowName" => array("code" => "jamsostek_allowance_name", "value" => "Jamsostek Allowance", "note" => "Display Name for Jams Allow", "default" => "Jamsosteck Allowance",),
    "strJamsAllowAmount" => array("code" => "jamsostek_allowance", "value" => "0", "note" => "Amount for Jamsostek Allow %", "default" => "0",),
    "strJamsAllowActive" => array("code" => "jamsostek_allowance_active", "value" => "t", "note" => "Is Jams Allow Active?", "default" => "t",),
    "strJamsAllowIr" => array("code" => "jamsostek_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strJamsAllowBen" => array("code" => "jamsostek_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strJamsAllowShow" => array("code" => "jamsostek_allowance_show", "value" => "t", "note" => "Show Jams Allow", "default" => "t",),
    "strJamsAllowProrate" => array("code" => "jamsostek_allowance_prorate", "value" => "t", "note" => "Prorate Jams Allow", "default" => "t",),
    "strJamsAllowOT" => array("code" => "jamsostek_allowance_ot", "value" => "t", "note" => "Jams Allow include OT", "default" => "t",),
    "strJamsAllowTax" => array("code" => "jamsostek_allowance_tax", "value" => "t", "note" => "Jams Allow include in tax", "default" => "t",),
    "strJamsAllowJams" => array("code" => "jamsostek_allowance_jams", "value" => "f", "note" => "Jams Allow include in jamsostek", "default" => "f",),
    "strJamsAllowDaily" => array("code" => "jamsostek_allowance_daily", "value" => "t", "note" => "Jams Allow is daily allowance", "default" => "f",),
    "strJamsAllowHidezero" => array("code" => "jamsostek_allowance_hidezero", "value" => "t", "note" => "Jams Allow  is hidden if the value is zero", "default" => "f",),
    //"strJamsAllowMultival" => array("code" => "jamsostek_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

    //Jkk Allowance
    "strJkkAllowName" => array("code" => "jkk_allowance_name", "value" => "Jkk Allowance", "note" => "Display Name for Jkk Allow", "default" => "Jkk Allowance",),
    "strJkkAllowAmount" => array("code" => "jkk_allowance", "value" => "0", "note" => "Amount for Jkk Allow %", "default" => "0",),
    "strJkkAllowActive" => array("code" => "jkk_allowance_active", "value" => "t", "note" => "Is Jkk Allow Active?", "default" => "t",),
    "strJkkAllowIr" => array("code" => "jkk_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strJkkAllowBen" => array("code" => "jkk_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strJkkAllowShow" => array("code" => "jkk_allowance_show", "value" => "t", "note" => "Show Jkk Allow", "default" => "t",),
    "strJkkAllowProrate" => array("code" => "jkk_allowance_prorate", "value" => "t", "note" => "Prorate Jkk Allow", "default" => "t",),
    "strJkkAllowOT" => array("code" => "jkk_allowance_ot", "value" => "t", "note" => "Jkk Allow include OT", "default" => "t",),
    "strJkkAllowTax" => array("code" => "jkk_allowance_tax", "value" => "t", "note" => "Jkk Allow include in tax", "default" => "t",),
    "strJkkAllowJams" => array("code" => "jkk_allowance_jams", "value" => "f", "note" => "Jkk Allow include in jamsostek", "default" => "f",),
    "strJkkAllowDaily" => array("code" => "jkk_allowance_daily", "value" => "t", "note" => "Jkk Allow include is daily allowance", "default" => "f",),
    "strJkkAllowHidezero" => array("code" => "jkk_allowance_hidezero", "value" => "t", "note" => "Jkk Allow  is hidden if the value is zero", "default" => "f",),
    //"strJkkAllowMultival" => array("code" => "jkk_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

     //Jkm Allowance
    "strJkmAllowName" => array("code" => "jkm_allowance_name", "value" => "Jkm Allowance", "note" => "Display Name for Jkm Allow", "default" => "Jkm Allowance",),
    "strJkmAllowAmount" => array("code" => "jkm_allowance", "value" => "0", "note" => "Amount for Jkm Allow %", "default" => "0",),
    "strJkmAllowActive" => array("code" => "jkm_allowance_active", "value" => "t", "note" => "Is Jkm Allow Active?", "default" => "t",),
    "strJkmAllowIr" => array("code" => "jkm_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strJkmAllowBen" => array("code" => "jkm_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strJkmAllowShow" => array("code" => "jkm_allowance_show", "value" => "t", "note" => "Show Jkm Allow", "default" => "t",),
    "strJkmAllowProrate" => array("code" => "jkm_allowance_prorate", "value" => "t", "note" => "Prorate Jkm Allow", "default" => "t",),
    "strJkmAllowOT" => array("code" => "jkm_allowance_ot", "value" => "t", "note" => "Jkm Allow include OT", "default" => "t",),
    "strJkmAllowTax" => array("code" => "jkm_allowance_tax", "value" => "t", "note" => "Jkm Allow include in tax", "default" => "t",),
    "strJkmAllowJams" => array("code" => "jkm_allowance_jams", "value" => "f", "note" => "Jkm Allow include in jamsostek", "default" => "f",),
    "strJkmAllowDaily" => array("code" => "jkm_allowance_daily", "value" => "t", "note" => "Jkm Allow include is daily allowance", "default" => "f",),
    "strJkmAllowHidezero" => array("code" => "jkm_allowance_hidezero", "value" => "t", "note" => "Jkm Allow  is hidden if the value is zero", "default" => "f",),
    //"strJkmAllowMultival" => array("code" => "jkm_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),
     //Jkm Allowance

    "strBPJSAllowanceName" => array("code" => "bpjs_allowance_name", "value" => "BPJS allowance", "note" => "Display Name for BPJS", "default" => "BPJS allowance",),
    "strBPJSAllowanceAmount" => array("code" => "bpjs_allowance", "value" => "0", "note" => "Amount for BPJS allowance %", "default" => "0",),
    "strBPJSAllowanceActive" => array("code" => "bpjs_allowance_active", "value" => "t", "note" => "Is BPJS allowance Active?", "default" => "t",),
    "strBPJSAllowanceIr" => array("code" => "bpjs_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strBPJSAllowanceBen" => array("code" => "bpjs_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strBPJSAllowanceShow" => array("code" => "bpjs_allowance_show", "value" => "t", "note" => "Show BPJS allowance ", "default" => "t",),
    "strBPJSAllowanceProrate" => array("code" => "bpjs_allowance_prorate", "value" => "t", "note" => "Prorate BPJS allowance ", "default" => "t",),
    "strBPJSAllowanceOT" => array("code" => "bpjs_allowance_ot", "value" => "t", "note" => "BPJS allowance include OT", "default" => "t",),
    "strBPJSAllowanceTax" => array("code" => "bpjs_allowance_tax", "value" => "t", "note" => "BPJS allowance include in tax", "default" => "t",),
    "strBPJSAllowanceJams" => array("code" => "bpjs_allowance_jams", "value" => "f", "note" => "BPJS allowance include in jamsostek", "default" => "f",),
    "strBPJSAllowanceDaily" => array("code" => "bpjs_allowance_daily", "value" => "t", "note" => "BPJS allowance include is daily allowance", "default" => "f",),
    "strBPJSAllowanceHidezero" => array("code" => "bpjs_allowance_hidezero", "value" => "t", "note" => "BPJS allowance  is hidden if the value is zero", "default" => "f",),

    "strMedicalAllowName" => array("code" => "medical_allowance_name", "value" => "Medical allowance", "note" => "Display Name for Medical", "default" => "Medical allowance",),
    "strMedicalAllowAmount" => array("code" => "medical_allowance", "value" => "0", "note" => "Amount for Medical allowance %", "default" => "0",),
    "strMedicalAllowActive" => array("code" => "medical_allowance_active", "value" => "t", "note" => "Is Medical allowance Active?", "default" => "t",),
    "strMedicalAllowShow" => array("code" => "medical_allowance_show", "value" => "t", "note" => "Show Medical allowance ", "default" => "t",),
  "strMedicalAllowIr" => array("code" => "medical_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
   "strMedicalAllowBen" => array("code" => "medical_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strMedicalAllowProrate" => array("code" => "medical_allowance_prorate", "value" => "t", "note" => "Prorate Medical allowance ", "default" => "t",),
    "strMedicalAllowOT" => array("code" => "medical_allowance_ot", "value" => "t", "note" => "Medical allowance include OT", "default" => "t",),
    "strMedicalAllowTax" => array("code" => "medical_allowance_tax", "value" => "t", "note" => "Medical allowance include in tax", "default" => "t",),
    "strMedicalAllowJams" => array("code" => "medical_allowance_jams", "value" => "f", "note" => "Medical allowance include in jamsostek", "default" => "f",),
    "strMedicalAllowDaily" => array("code" => "medical_allowance_daily", "value" => "t", "note" => "Medical allowance include is daily allowance", "default" => "f",),
    "strMedicalAllowHidezero" => array("code" => "medical_allowance_hidezero", "value" => "t", "note" => "Medical allowance  is hidden if the value is zero", "default" => "f",),

    "strSeniorityAllowName" => array("code" => "seniority_allowance_name", "value" => "Seniority Allowance", "note" => "Display Name for Seniority Allow", "default" => "Seniority Allowance",),
    "strSeniorityAllowAmount" => array("code" => "seniority_allowance", "value" => "0", "note" => "Amount for Seniority Allow %", "default" => "0",),
    "strSeniorityAllowActive" => array("code" => "seniority_allowance_active", "value" => "t", "note" => "Is Seniority Allow Active?", "default" => "t",),
    "strSeniorityAllowIr" => array("code" => "seniority_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strSeniorityAllowBen" => array("code" => "seniority_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strSeniorityAllowShow" => array("code" => "seniority_allowance_show", "value" => "t", "note" => "Show Seniority Allow", "default" => "t",),
    "strSeniorityAllowProrate" => array("code" => "seniority_allowance_prorate", "value" => "t", "note" => "Prorate Seniority Allow", "default" => "t",),
    "strSeniorityAllowOT" => array("code" => "seniority_allowance_ot", "value" => "t", "note" => "Seniority Allow include OT", "default" => "t",),
    "strSeniorityAllowTax" => array("code" => "seniority_allowance_tax", "value" => "t", "note" => "Seniority Allow include in tax", "default" => "t",),
    "strSeniorityAllowJams" => array("code" => "seniority_allowance_jams", "value" => "f", "note" => "Seniority Allow include in jamsostek", "default" => "f",),
    "strSeniorityAllowDaily" => array("code" => "seniority_allowance_daily", "value" => "t", "note" => "Seniority Allow include is daily allowance", "default" => "f",),
    "strSeniorityAllowHidezero" => array("code" => "seniority_allowance_hidezero", "value" => "t", "note" => "Seniority Allow  is hidden if the value is zero", "default" => "f",),
    //"strJkmAllowMultival" => array("code" => "jkm_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

    "strKerajinanAllowName" => array("code" => "kerajinan_allowance_name", "value" => "Kerajinan Allowance", "note" => "Display Name for Kerajinan Allow", "default" => "Kerajinan Allowance",),
    "strKerajinanAllowAmount" => array("code" => "kerajinan_allowance", "value" => "0", "note" => "Amount for Kerajinan Allow %", "default" => "0",),
    "strKerajinanAllowActive" => array("code" => "kerajinan_allowance_active", "value" => "t", "note" => "Is Kerajinan Allow Active?", "default" => "t",),
    "strKerajinanAllowIr" => array("code" => "kerajinan_allowance_irregular", "value" => "t", "note" => "Is Irregular", "default" => "t",),
    "strKerajinanAllowBen" => array("code" => "kerajinan_allowance_benefit", "value" => "t", "note" => "Is Benefit", "default" => "t",),
    "strKerajinanAllowShow" => array("code" => "kerajinan_allowance_show", "value" => "t", "note" => "Show Kerajinan Allow", "default" => "t",),
    "strKerajinanAllowProrate" => array("code" => "kerajinan_allowance_prorate", "value" => "t", "note" => "Prorate Kerajinan Allow", "default" => "t",),
    "strKerajinanAllowOT" => array("code" => "kerajinan_allowance_ot", "value" => "t", "note" => "Kerajinan Allow include OT", "default" => "t",),
    "strKerajinanAllowTax" => array("code" => "kerajinan_allowance_tax", "value" => "t", "note" => "Kerajinan Allow include in tax", "default" => "t",),
    "strKerajinanAllowJams" => array("code" => "kerajinan_allowance_jams", "value" => "f", "note" => "Kerajinan Allow include in jamsostek", "default" => "f",),
    "strKerajinanAllowDaily" => array("code" => "kerajinan_allowance_daily", "value" => "t", "note" => "Kerajinan Allow include is daily allowance", "default" => "f",),
    "strKerajinanAllowHidezero" => array("code" => "kerajinan_allowance_hidezero", "value" => "t", "note" => "Kerajinan Allow  is hidden if the value is zero", "default" => "f",),
    //"strJkmAllowMultival" => array("code" => "jkm_allowance_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),


    "strJamsDeducName" => array("code" => "jamsostek_deduction_name", "value" => "Jamsostec Deduction", "note" => "Display Name for Jams Deduc", "default" => "Jamsostec Deduction",),
    "strJamsDeducAmount" => array("code" => "jamsostek_deduction", "value" => "0", "note" => "Amount for Jamsostek Deduc %", "default" => "0",),
    "strJamsDeducActive" => array("code" => "jamsostek_deduction_active", "value" => "t", "note" => "Is Jams Deduc Active?", "default" => "t",),
    "strJamsDeducShow" => array("code" => "jamsostek_deduction_show", "value" => "t", "note" => "Show Jams Deduc ", "default" => "t",),
    "strJamsDeducProrate" => array("code" => "jamsostek_deduction_prorate", "value" => "t", "note" => "Prorate Jams Deduc ", "default" => "t",),
    "strJamsDeducOT" => array("code" => "jamsostek_deduction_ot", "value" => "t", "note" => "Jams Deduc include OT", "default" => "t",),
    "strJamsDeducTax" => array("code" => "jamsostek_deduction_tax", "value" => "t", "note" => "Jams Deduc include in tax", "default" => "t",),
    "strJamsDeducJams" => array("code" => "jamsostek_deduction_jams", "value" => "f", "note" => "Jams Deduc include in jamsostek", "default" => "f",),
    "strJamsDeducDaily" => array("code" => "jamsostek_deduction_daily", "value" => "t", "note" => "Jams Deduc include is daily deduction", "default" => "f",),
    "strJamsDeducHidezero" => array("code" => "jamsostek_deduction_hidezero", "value" => "t", "note" => "Jams Deduc  is hidden if the value is zero", "default" => "f",),
    //"strJamsDeducMultival" => array("code" => "jamsostek_deduction_multival", "value" => "f", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

    "strLoanName" => array("code" => "loan_deduction_name", "value" => "Loan Deduction", "note" => "Display Name for Loan", "default" => "Loan Deduction",),
    "strLoanAmount" => array("code" => "loan_deduction", "value" => "0", "note" => "Amount for Loan", "default" => "0",),
    "strLoanActive" => array("code" => "loan_deduction_active", "value" => "t", "note" => "Is Loan Active?", "default" => "t",),
    "strLoanShow" => array("code" => "loan_deduction_show", "value" => "t", "note" => "Show Loan ", "default" => "t",),
    "strLoanProrate" => array("code" => "loan_deduction_prorate", "value" => "t", "note" => "Prorate Loan ", "default" => "t",),
    "strLoanOT" => array("code" => "loan_deduction_ot", "value" => "t", "note" => "Loan include OT", "default" => "t",),
    "strLoanTax" => array("code" => "loan_deduction_tax", "value" => "t", "note" => "Loan include in tax", "default" => "t",),
    "strLoanJams" => array("code" => "loan_deduction_jams", "value" => "f", "note" => "Loan include in jamsostek", "default" => "f",),
    "strLoanDaily" => array("code" => "loan_deduction_daily", "value" => "t", "note" => "Loan Deduc include is daily deduction", "default" => "f",),
    "strLoanHidezero" => array("code" => "loan_deduction_hidezero", "value" => "t", "note" => "Loan Deduc is hidden if the value is zero", "default" => "f",),

    "strAbsenceDeductionName" => array("code" => "absence_deduction_name", "value" => "Absence Deduction", "note" => "Display Name for absence", "default" => "absence Deduction",),
    "strAbsenceDeductionAmount" => array("code" => "absence_deduction", "value" => "0", "note" => "Amount for absence", "default" => "0",),
    "strAbsenceDeductionActive" => array("code" => "absence_deduction_active", "value" => "t", "note" => "Is absence Active?", "default" => "t",),
    "strAbsenceDeductionShow" => array("code" => "absence_deduction_show", "value" => "t", "note" => "Show absence ", "default" => "t",),
    "strAbsenceDeductionProrate" => array("code" => "absence_deduction_prorate", "value" => "t", "note" => "Prorate absence ", "default" => "t",),
    "strAbsenceDeductionOT" => array("code" => "absence_deduction_ot", "value" => "t", "note" => "absence include OT", "default" => "t",),
    "strAbsenceDeductionTax" => array("code" => "absence_deduction_tax", "value" => "t", "note" => "absence include in tax", "default" => "t",),
    "strAbsenceDeductionJams" => array("code" => "absence_deduction_jams", "value" => "f", "note" => "absence include in jamsostek", "default" => "f",),
    "strAbsenceDeductionDaily" => array("code" => "absence_deduction_daily", "value" => "t", "note" => "absence Deduc include is daily deduction", "default" => "f",),
    "strAbsenceDeductionHidezero" => array("code" => "absence_deduction_hidezero", "value" => "t", "note" => "absence Deduc is hidden if the value is zero", "default" => "f",),

    "strLateDeductionName" => array("code" => "late_deduction_name", "value" => "Late Deduction", "note" => "Display Name for late", "default" => "Late Deduction",),
    "strLateDeductionAmount" => array("code" => "late_deduction", "value" => "0", "note" => "Amount for late", "default" => "0",),
    "strLateDeductionActive" => array("code" => "late_deduction_active", "value" => "t", "note" => "Is late Active?", "default" => "t",),
    "strLateDeductionShow" => array("code" => "late_deduction_show", "value" => "t", "note" => "Show late ", "default" => "t",),
    "strLateDeductionProrate" => array("code" => "late_deduction_prorate", "value" => "t", "note" => "Prorate late ", "default" => "t",),
    "strLateDeductionOT" => array("code" => "late_deduction_ot", "value" => "t", "note" => "late include OT", "default" => "t",),
    "strLateDeductionTax" => array("code" => "late_deduction_tax", "value" => "t", "note" => "late include in tax", "default" => "t",),
    "strLateDeductionJams" => array("code" => "late_deduction_jams", "value" => "f", "note" => "late include in jamsostek", "default" => "f",),
    "strLateDeductionDaily" => array("code" => "late_deduction_daily", "value" => "t", "note" => "late Deduc include is daily deduction", "default" => "f",),
    "strLateDeductionHidezero" => array("code" => "late_deduction_hidezero", "value" => "t", "note" => "late Deduc is hidden if the value is zero", "default" => "f",),

    "strLateTIDeductionName" => array("code" => "late_ti_deduction_name", "value" => "Late TI Deduction", "note" => "Display Name for late", "default" => "Late TI Deduction",),
    "strLateTIDeductionAmount" => array("code" => "late_ti_deduction", "value" => "0", "note" => "Amount for late", "default" => "0",),
    "strLateTIDeductionActive" => array("code" => "late_ti_deduction_active", "value" => "t", "note" => "Is late Active?", "default" => "t",),
    "strLateTIDeductionShow" => array("code" => "late_ti_deduction_show", "value" => "t", "note" => "Show late ", "default" => "t",),
    "strLateTIDeductionProrate" => array("code" => "late_ti_deduction_prorate", "value" => "t", "note" => "Prorate late ", "default" => "t",),
    "strLateTIDeductionOT" => array("code" => "late_ti_deduction_ot", "value" => "t", "note" => "late include OT", "default" => "t",),
    "strLateTIDeductionTax" => array("code" => "late_ti_deduction_tax", "value" => "t", "note" => "late include in tax", "default" => "t",),
    "strLateTIDeductionJams" => array("code" => "late_ti_deduction_jams", "value" => "f", "note" => "late include in jamsostek", "default" => "f",),
    "strLateTIDeductionDaily" => array("code" => "late_ti_deduction_daily", "value" => "t", "note" => "late Deduc include is daily deduction", "default" => "f",),
    "strLateTIDeductionHidezero" => array("code" => "late_ti_deduction_hidezero", "value" => "t", "note" => "late Deduc is hidden if the value is zero", "default" => "f",),


    "strZakatName" => array("code" => "zakat_deduction_name", "value" => "Zakat Deduction", "note" => "Display Name for Zakat", "default" => "Zakat Deduction",),
    "strZakatAmount" => array("code" => "zakat_deduction", "value" => "0", "note" => "Amount for Zakat", "default" => "0",),
    "strZakatActive" => array("code" => "zakat_deduction_active", "value" => "t", "note" => "Is Zakat Active?", "default" => "t",),
    "strZakatShow" => array("code" => "zakat_deduction_show", "value" => "t", "note" => "Show Zakat ", "default" => "t",),
    "strZakatProrate" => array("code" => "zakat_deduction_prorate", "value" => "t", "note" => "Prorate Zakat ", "default" => "t",),
    "strZakatOT" => array("code" => "zakat_deduction_ot", "value" => "t", "note" => "Zakat include OT", "default" => "t",),
    "strZakatTax" => array("code" => "zakat_deduction_tax", "value" => "t", "note" => "Zakat include in tax", "default" => "t",),
    "strZakatJams" => array("code" => "zakat_deduction_jams", "value" => "f", "note" => "Zakat include in jamsostek", "default" => "f",),
    "strZakatDaily" => array("code" => "zakat_deduction_daily", "value" => "t", "note" => "Zakat Deduc include is daily deduction", "default" => "f",),
    "strZakatHidezero" => array("code" => "zakat_deduction_hidezero", "value" => "t", "note" => "Zakat Deduc is hidden if the value is zero", "default" => "f",),
    //"strZakatMultival" => array("code" => "zakat_deduction_multival", "value" => "t", "note" => "Indicator of Out Fix Allow ", "default" => "t",),

    "strBPJSDeducName" => array("code" => "bpjs_deduction_name", "value" => "BPJS Deduction", "note" => "Display Name for BPJS Deduc", "default" => "Jamsostec Deduction",),
    "strBPJSDeducAmount" => array("code" => "bpjs_deduction", "value" => "0", "note" => "Amount for BPJS Deduc %", "default" => "0",),
    "strBPJSDeducActive" => array("code" => "bpjs_deduction_active", "value" => "t", "note" => "Is BPJS Deduc Active?", "default" => "t",),
    "strBPJSDeducShow" => array("code" => "bpjs_deduction_show", "value" => "t", "note" => "Show BPJS Deduc ", "default" => "t",),
    "strBPJSDeducProrate" => array("code" => "bpjs_deduction_prorate", "value" => "t", "note" => "Prorate BPJS Deduc ", "default" => "t",),
    "strBPJSDeducOT" => array("code" => "bpjs_deduction_ot", "value" => "t", "note" => "BPJS Deduc include OT", "default" => "t",),
    "strBPJSDeducTax" => array("code" => "bpjs_deduction_tax", "value" => "t", "note" => "BPJS Deduc include in tax", "default" => "t",),
    "strBPJSDeducJams" => array("code" => "bpjs_deduction_jams", "value" => "f", "note" => "BPJS Deduc include in jamsostek", "default" => "f",),
    "strBPJSDeducDaily" => array("code" => "bpjs_deduction_daily", "value" => "t", "note" => "BPJS Deduc include is daily deduction", "default" => "f",),
    "strBPJSDeducHidezero" => array("code" => "bpjs_deduction_hidezero", "value" => "t", "note" => "BPJS Deduc  is hidden if the value is zero", "default" => "f",),

        //Pension Deduction
	"strPensionDeducName" => array("code" => "pension_deduction_name", "value" => "pension Deduction", "note" => "Display Name for pension Deduc", "default" => "pension Deduction",),
    "strPensionDeducAmount" => array("code" => "pension_deduction", "value" => "0", "note" => "Amount for pension Deduc %", "default" => "0",),
    "strPensionDeducActive" => array("code" => "pension_deduction_active", "value" => "t", "note" => "Is pension Deduc Active?", "default" => "t",),
    "strPensionDeducShow" => array("code" => "pension_deduction_show", "value" => "t", "note" => "Show pension Deduc ", "default" => "t",),
    "strPensionDeducProrate" => array("code" => "pension_deduction_prorate", "value" => "t", "note" => "Prorate pension Deduc ", "default" => "t",),
    "strPensionDeducOT" => array("code" => "pension_deduction_ot", "value" => "t", "note" => "pension Deduc include OT", "default" => "t",),
    "strPensionDeducTax" => array("code" => "pension_deduction_tax", "value" => "t", "note" => "pension Deduc include in tax", "default" => "t",),
    "strPensionDeducJams" => array("code" => "pension_deduction_jams", "value" => "f", "note" => "pension Deduc include in jamsostek", "default" => "f",),
    "strPensionDeducDaily" => array("code" => "pension_deduction_daily", "value" => "t", "note" => "pension Deduc include is daily deduction", "default" => "f",),
    "strPensionDeducHidezero" => array("code" => "pension_deduction_hidezero", "value" => "t", "note" => "pension Deduc  is hidden if the value is zero", "default" => "f",),


  );

  $arrDeductionAttributes = array(// daftar attribut allowance
    array("code" => "active", "name" => "Active"),
    array("code" => "show", "name" => "Show"),
    array("code" => "prorate", "name" => "Prorate"),
    array("code" => "ot", "name" => "OT"),
    array("code" => "tax", "name" => "Tax"),
    array("code" => "jams", "name" => "Jams"),
    array("code" => "hidezero", "name" => "Hidezero"),
    array("code" => "daily", "name" => "Daily"),
  );

  $arrAllowanceAttributes = $arrDeductionAttributes;
  $arrAllowanceAttributes[] = array("code" => "benefit", "name" => "Ben");
  $arrAllowanceAttributes[] = array("code" => "irregular", "name" => "Ir");


  // daftar tunjangan yang "FIX" -> settingnya hardcode, karena nilainya % atau ada perlakukan khusus
  $arrFixAllowance = array(
    "JamsAllow" => array("jht allowance","%"),
    "JkkAllow" => array("jkk allowance","default %"),
    "JkmAllow" => array("jkm allowance","%"),
    "BPJSAllowance" => array("bpjs allowance","%"),
    "PensionAllow" => array("pension allowance","%"),
    "SeniorityAllow" => array("seniority allowance","per year"),
  );

  // daftar tunjangan yang "FIX" -> settingnya dari data master
  $arrFixAllowance2["Overtime"] = array("overtime allowance","");
  $arrFixAllowance2["Shift"] = array("shift allowance","data_shift_type.php");
  $arrFixAllowance2["THR"] = array("THR allowance","");
//  $arrFixAllowance2["THR"] = array("THR allowance","");


  foreach ($ARRAY_ALLOWANCE_SET AS $strKey => $arrVal)
  {
    for ($i = 1; $i <= MAX_ALLOWANCE_SET; $i++)
    {
      $arrFixAllowance2[$strKey.$i] = array($strKey.$i."_allowance", $arrVal['page_name']);
      $arrSetting["str".$strKey.$i."Name"] = array("code" => $strKey.$i."_allowance_name", "value" => $arrVal['field_name'], "note" => "", "default" => $arrVal['field_name']);
      $arrSetting["str".$strKey.$i."Amount"] = array("code" => $strKey.$i."_allowance", "value" => "", "note" => "", "default" => "");
      $arrSetting["str".$strKey.$i."Multival"] = array("code" => $strKey.$i."_allowance_multival", "value" => "t", "note" => "", "default" => "t");
      foreach ($arrAllowanceAttributes as $strAtt => $arrAtt)
      {
        $arrSetting["str".$strKey.$i.$arrAtt["name"]] = array("code" => $strKey.$i."_allowance_".$arrAtt["code"], "value" => "", "note" => "", "default" => "f");
      }
    }
  }



  $arrFixDeduction = array(// daftar potongan yang "FIX" -> settingnya hardcode, karena nilainya % atau ada perlakukan khusus
    "JamsDeduc" => array("jht deduction","%"),
    "BPJSDeduc" => array("bpjs kesehatan deduction","%"),
    "PensionDeduc" => array("pension deduction","%"),
    "Zakat" => array("zakat deduction", "%"),

  );
  $arrFixDeduction2 = array(// daftar potongan yang multival (beda untuk masing karyawan)
    "Loan" => array("loan deduction","loan_list.php"),
    "AbsenceDeduction" => array("Absence deduction",""),
    "LateDeduction" => array("late deduction",""),
    "LateTIDeduction" => array("late TI deduction",""),
  );

  // untuk breakTime, dipisiahkan, karena tabelnya beda
  $strDetailAllowance = "";
  $strDetailDeduction = "";
  $strHiddenAllowance = "";
  $strHiddenDeduction = "";
  $arrFirstData = array();

  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database
  // return berubah $arrOvertime (parameter)
  function getData($db)
  {
    global $words;
    global $_SESSION;
    global $strModule;
    global $arrSetting;

    $intIDmodified_by = $_SESSION['sessionUserID'];

    $tblSetting = new cModel("all_setting");
    foreach ($arrSetting AS $kode => $arrData)
    {
      if ($arrData['code'] != "")
      {
        if ($arrHasil = $tblSetting->find(" code='".$arrData['code']."' AND template_name IS NULL "))
        {
          $arrSetting[$kode]["value"] = $arrHasil['value'];
        }
        else
        {
          $data = array("code"   => $arrData['code'],
                        "value"  => $arrData['default'],
                        "note"   => $arrData['note'],
                        "module" => $strModule,
                        );
          $tblSetting->insert($data);
        }
      }

    }
    $strtempSQL  = "SELECT value FROM all_setting where code='template_name'";
    $resDb = $db->execute($strtempSQL);
    $rowDb = $db->fetchrow($resDb);
    $strTemplateName = $rowDb['value'];
    $_SESSION['currentActiveTemplate'] = $strTemplateName;
    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    return true;
  } // showData

  // fungsi untuk mengambil data tunjangan
  function getDataAllowance($db) {
    global $strHiddenAllowance;
    global $arrSetting;
    global $arrFixAllowance;
    global $arrFixAllowance2;
    global $arrAllowanceAttributes;
    global $words;
    $arrAllowanceAttributes;
    // inisialisasi
    $intMaxDetail = 100;
    $strResult = "";

    //ambil data basic salary dulu
    /*
    $strResult .= " <tr valign=top>\n";
    $strResult .= "  <td>&nbsp;</td>\n";
    $strResult .= "  <td nowrap>&nbsp;" .getWords('basic salary')."</td>\n";
    $strResult .= "  <td align=center><input type=text name=\"strBasicName\" size=30 maxlength=40 value=\"" .$arrSetting['strBasicName']['value']."\"></td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= " </tr>\n";*/

    // ambil info tunjangan yang punya perlakukan khusus
    foreach ($arrFixAllowance AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrAllowanceAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "  <td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "  <td nowrap>&nbsp;" .$arrTmp[0]." (".$arrTmp[1].")</td>\n";
       $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string'></td>\n";
       $strResult .= "  <td><input type=text name=\"str".$strKode."Amount\" value=\"" .(float)$arrSetting['str'.$strKode.'Amount']['value']."\" size=20 maxlength=20 class=numeric class='numeric'></td>\n";

       $strResult .= $strAttribute;
       $strResult .= " </tr>\n";
    }

    //Info tunjangan yang di link ke page data master
    foreach ($arrFixAllowance2 AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrAllowanceAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "<td nowrap>&nbsp;" .$arrTmp[0]."</td>\n";
       $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string'></td>\n";
       $strLink = ($arrTmp[1] != "") ? "<a href=\"".$arrTmp[1]."\">".$arrSetting['str'.$strKode.'Name']['value']."</a>" : "";
       $strResult .= "<td align=left>&nbsp;$strLink</td>\n";
       $strResult .= $strAttribute;
       $strResult .= " </tr>\n";
    }


    //data Tunjangan Tambahan
    $strSQL  = "SELECT * FROM hrd_allowance_type ORDER BY seq ";
    $resDb = $db->execute($strSQL);
    $intRows = 0;
    while ($rowDb = $db->fetchrow($resDb))
    {
       $intRows++;
       $strAttribute = "";
       foreach($arrAllowanceAttributes AS $arrAttribute)
       {
          $strChecked = ($rowDb[$arrAttribute['code']] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Allowance_$intRows\" value='t' $strChecked></td>\n";
       }
       $strResult .= "<tr valign=top id='detailDataAllowance"."_$intRows'>\n";
       $strResult .= "  <td align=center><input type=checkbox name=\"cbxAllow"."_$intRows\" value='".$rowDb['id']."' ></td>\n";
       $strResult .= "  <td><input type=text name=dataSeqAllowance"."_$intRows size=5 value=\"" .$rowDb['seq']."\">";
       $strResult .= "  <td><input type=hidden name=dataIDAllowance"."_$intRows value=\"" .$rowDb['id']."\">";
       $strResult .= "<input type=text id=dataCodeAllowance"."_$intRows name=dataCodeAllowance"."_$intRows value=\"" .$rowDb['code']."\" size=20 maxlength=40 onChange=\"changeCode('Allowance',$intRows)\" class='string-empty' onkeypress=\"validCode('Allowance',$intRows)\" onkeyup=\"validCode('Allowance',$intRows)\"></td>\n";
        $strResult .= "  <td><input type=text name=dataNameAllowance"."_$intRows value=\"" .$rowDb['name']."\" size=30 maxlength=40 class='string-empty'></td>\n";
        $strResult .= "  <td><input type=text name=dataAmountAllowance"."_$intRows value=\"" .$rowDb['amount']."\" size=20 ></td>\n";
        $strResult .= $strAttribute;
        $strResult .= "</tr>\n";
    }

    $intNumShow = $intRows;
    // tambahkan detail tambahan
    while ($intRows <= $intMaxDetail) {
      $intRows++;
      $strAttribute = "";
      foreach($arrAllowanceAttributes AS $arrAttribute)
      {
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Allowance_$intRows\" value='t' checked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataAllowance"."_$intRows' style=\"display:none\">\n";
      $strResult .= "  <td align=center><input type=checkbox name=\"cbxAllow"."_$intRows\" value='' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqAllowance"."_$intRows size=5 maxlength=40 disabled   class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text id=dataCodeAllowance"."_$intRows name=dataCodeAllowance"."_$intRows size=20 maxlength=40 disabled  onChange=\"changeCode('Allowance',$intRows)\" class='string-empty' onkeypress=\"validCode('Allowance',$intRows)\" onkeyup=\"validCode('Allowance',$intRows)\"></td>\n";
      $strResult .= "  <td><input type=text name=dataNameAllowance"."_$intRows size=30 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountAllowance"."_$intRows  size=20  disabled value=0></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "<td align=center>&nbsp;</td>\n";

      $strResult .= "</tr>\n";

    }
    // tambahkan hidden value
    $strHiddenAllowance .= "<input type=hidden name='numShowAllowance' value=$intNumShow>";
    $strHiddenAllowance .= "<input type=hidden name='maxDetailAllowance' value=$intMaxDetail>";


    return $strResult;
  }//getDataAllowance

  // fungsi untuk mengambil data potongan
  function getDataDeduction($db) {
    global $strHiddenDeduction;
    global $arrSetting;
    global $arrFixDeduction;
    global $arrFixDeduction2;
    global $arrDeductionAttributes;
    global $words;
    // inisialisasi
    $intMaxDetail = 100;
    $strResult = "";

    // ambil info potongan yang punya perlakukan khusus
    foreach ($arrFixDeduction AS $strKode => $arrTmp) {

      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
         $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
      }

      $strResult .= " <tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;</td><td nowrap>&nbsp;</td>";
      $strResult .= "  <td nowrap>&nbsp;" .getWords($arrTmp[0])." (".$arrTmp[1].")</td>\n";

      $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string' ></td>\n";
      $strResult .= "  <td><input type=text name=\"str".$strKode."Amount\" value=\"" .(float)$arrSetting['str'.$strKode.'Amount']['value']."\" size=20 maxlength=20 class=numeric ></td>\n";
      $strResult .= "<td>&nbsp</td>".$strAttribute;

      $strResult .= " </tr>\n";
    }

    //Info tunjangan yang di link ke page data master
    foreach ($arrFixDeduction2 AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrDeductionAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "<td nowrap>&nbsp;" .getWords($arrTmp[0])."</td>\n";
       $strResult .= "<td>".$arrSetting['str'.$strKode.'Name']['value']."</td>\n";
       $strResult .= "<td align=left><a href=\"".$arrTmp[1]."\">";
       $strResult .= $arrSetting['str'.$strKode.'Name']['value']."</a></td>\n";
       $strResult .= "<td nowrap>&nbsp;</td>".$strAttribute;
       $strResult .= " </tr>\n";
    }

    $strSQL  = "SELECT * FROM hrd_deduction_type ORDER BY seq ";
    $resDb = $db->execute($strSQL);
    $intRows = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strChecked = ($rowDb[$arrAttribute['code']] == 't') ? "checked" : "";
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Deduction_$intRows\" value='t' $strChecked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataDeduction"."_$intRows'>\n";
      $strResult .= "  <td align=center><input type=checkbox name=\"cbxDeduc"."_$intRows\" value='".$rowDb['id']."' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqDeduction"."_$intRows size=5 value=\"" .$rowDb['seq']."\">";
      $strResult .= "  <input type=hidden name=dataIDDeduction"."_$intRows value=\"" .$rowDb['id']."\">";
      $strResult .= "  <td><input type=text id=dataCodeDeduction"."_$intRows name=dataCodeDeduction"."_$intRows value=\"" .$rowDb['code']."\" size=20 maxlength=40 onChange=\"changeCode('Deduction',$intRows)\" class='string-empty' onkeyup=\"validCode('Deduction',$intRows)\" onkeypress=\"validCode('Deduction',$intRows)\"></td>\n";
      $strResult .= "  <td><input type=text name=dataNameDeduction"."_$intRows value=\"" .$rowDb['name']."\" size=30 maxlength=40 class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountDeduction"."_$intRows value=\"" .(float)$rowDb['amount']."\" size=20 maxlength=20 class=numeric></td>\n";
      $strResult .= "  <td><input type=text name=dataMaxlinkDeduction"."_$intRows value=\"" .$rowDb['maxlink']."\" size=20 maxlength=20 class='string-empty'></td>\n";
      $strResult .= $strAttribute;
//      $strResult .= "<td align=center>&nbsp;</td>\n";
      $strResult .= "</tr>\n";
    }
    $intNumShow = $intRows;

    // tambahkan detail tambahan
    while ($intRows <= $intMaxDetail) {
      $intRows++;
      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Deduction_$intRows\" value='t' checked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataDeduction"."_$intRows' style=\"display:none\">\n";
      $strResult .= "  <td align=center><input type=checkbox name=\"cbxDeduc"."_$intRows\" value='' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqDeduction"."_$intRows size=5 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text id=dataCodeDeduction"."_$intRows name=dataCodeDeduction"."_$intRows size=20 maxlength=40 disabled onChange=\"changeCode('Deduction',$intRows)\" class='string-empty' onkeyup=\"validCode('Deduction',$intRows)\" onkeypress=\"validCode('Deduction',$intRows)\"></td>\n";
      $strResult .= "  <td><input type=text name=dataNameDeduction"."_$intRows size=30 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountDeduction"."_$intRows  size=20 maxlength=20 class=numeric disabled value=0></td>\n";
      $strResult .= "  <td><input type=text name=dataMaxlinkDeduction"."_$intRows  size=20 maxlength=20 class='string-empty' disabled value=0></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "<td align=center>&nbsp;</td>\n";
      $strResult .= "</tr>\n";

    }

    // tambahkan hidden value
    $strHiddenDeduction .= "<input type=hidden name='numShowDeduction' value=$intNumShow>";
    $strHiddenDeduction .= "<input type=hidden name='maxDetailDeduction' value=$intMaxDetail>";


    return $strResult;
  }//getDataDeduction

  // fungsi untuk menyimpan data
  function saveData($db, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    global $arrSetting;
    global $arrBreakTime;
    global $messages;
    $strmodified_byID = $_SESSION['sessionUserID'];
    $strSQL = "";
    $strSQL  = "SELECT value FROM all_setting where code='template_name' ";
    $resDb = $db->execute($strSQL);
    $rowDb = $db->fetchrow($resDb);
    $CurrentActiveTemplate = $rowDb['value'];

    $strSQL = "";
    foreach ($arrSetting AS $kode => $arrData) {
      if (isset($_REQUEST[$kode])) {
        $strValue = $_REQUEST[$kode];
        $strSQL .= "UPDATE all_setting SET modified_by = '$strmodified_byID', ";
        $strSQL .= "created = now(), value = '$strValue' ";
        $strSQL .= "WHERE code = '" .$arrData['code']. "'AND template_name IS NULL; ";

      }
    }

    //
    // handle untuk yang tipenya boolean, tapi nilainya false
    // untuk saat ini, cirinya ada DEFAULT = t/f ;)
    foreach ($arrSetting AS $kode => $arrData) {
      if ($arrData['default'] == 't' || $arrData['default'] == 'f') {
        if ((strpos($kode, "Multival") == false) && !isset($_REQUEST[$kode])) {
          $strSQL .= "UPDATE all_setting SET modified_by = '$strmodified_byID', ";
          $strSQL .= "value = 'f' WHERE code = '" .$arrSetting[$kode]['code']."' AND template_name IS NULL; ";
        }
      }
    }
    $resExec = $db->execute($strSQL);

    $strSQL="";
    if(isset($_SESSION['currentActiveTemplate'])) $strSQL .= "UPDATE all_setting SET value = '".$_SESSION['currentActiveTemplate']."' WHERE code = 'template_name'";
    else {
      $strtempSQL  = "SELECT value FROM all_setting where code='template_name' ";
      $resDb = $db->execute($strtempSQL);
      $rowDb = $db->fetchrow($resDb);
      $strSQL .= "UPDATE all_setting SET value = '".$rowDb['value']."' WHERE code = 'template_name'";
    }
    $resExec = $db->execute($strSQL);

    // simpan data tunjangan lain-lain
    $intMax = (isset($_REQUEST['maxDetailAllowance'])) ? $_REQUEST['maxDetailAllowance'] : 0;
    for ($i = 1;$i<=$intMax;$i++) {
      $strID     = (isset($_REQUEST['dataIDAllowance_'.$i])) ? $_REQUEST['dataIDAllowance_'.$i] : "";
      $strSeq   = (isset($_REQUEST['dataSeqAllowance_'.$i])) ? $_REQUEST['dataSeqAllowance_'.$i] : "";
      $strCode   = (isset($_REQUEST['dataCodeAllowance_'.$i])) ? $_REQUEST['dataCodeAllowance_'.$i] : "";
      $strName   = (isset($_REQUEST['dataNameAllowance_'.$i])) ? $_REQUEST['dataNameAllowance_'.$i] : "";
      $strAmount = (isset($_REQUEST['dataAmountAllowance_'.$i])) ? $_REQUEST['dataAmountAllowance_'.$i] : "0";
      //if (!is_numeric($strAmount)) $strAmount = 0;

      $strActive = (isset($_REQUEST['dataActiveAllowance_'.$i])) ? "t" : "f";
      $strIr     = (isset($_REQUEST['dataIrAllowance_'.$i])) ? "t" : "f";
      $strBen    = (isset($_REQUEST['dataBenAllowance_'.$i])) ? "t" : "f";
      $strShow   = (isset($_REQUEST['dataShowAllowance_'.$i])) ? "t" : "f";
      $strProrate   = (isset($_REQUEST['dataProrateAllowance_'.$i])) ? "t" : "f";
      $strOT     = (isset($_REQUEST['dataOTAllowance_'.$i])) ? "t" : "f";
      $strTax    = (isset($_REQUEST['dataTaxAllowance_'.$i])) ? "t" : "f";
      $strJams   = (isset($_REQUEST['dataJamsAllowance_'.$i])) ? "t" : "f";
      $strDaily  = (isset($_REQUEST['dataDailyAllowance_'.$i])) ? "t" : "f";
      $strHidezero  = (isset($_REQUEST['dataHidezeroAllowance_'.$i])) ? "t" : "f";

      if ($strCode == "") { // ada kemungkinan ndihapus
        if ($strID != "") {
          //hapus data
          $strSQL  = "DELETE FROM hrd_allowance_type WHERE id = '$strID' AND template_name='$CurrentActiveTemplate'";
          $resExec = $db->execute($strSQL);
        }
      } else {
        if ($strID == "") { // insert new
          $strSQL  = "INSERT INTO hrd_allowance_type (created,modified_by,created_by, ";
          $strSQL .= "seq,code,name, amount, active, \"show\", prorate, ot, irregular, benefit, tax, jams, daily, hidezero, template_name) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', ";
          $strSQL .= "'$strSeq','$strCode', '$strName', '$strAmount', '$strActive', '$strShow', '$strProrate', ";
          $strSQL .= "'$strOT', '$strIr', '$strBen','$strTax', '$strJams', '$strDaily', '$strHidezero', '$CurrentActiveTemplate') ";
          $resExec = $db->execute($strSQL);
        } else {//update
          $strSQL  = "UPDATE hrd_allowance_type SET modified_by = '$strmodified_byID', ";
          $strSQL .= "seq = '$strSeq', code = '$strCode', name = '$strName', amount = '$strAmount', active = '$strActive', ";
          $strSQL .= "\"show\" = '$strShow', prorate = '$strProrate', ot = '$strOT', tax = '$strTax', ";
          $strSQL .= "jams = '$strJams', irregular = '$strIr', benefit = '$strBen', daily = '$strDaily', hidezero = '$strHidezero'";
          $strSQL .= "WHERE id = '$strID' AND template_name='$CurrentActiveTemplate'";
          $resExec = $db->execute($strSQL);
        }
      }
    }

    // simpan data potongan lain-lain
    $intMax = (isset($_REQUEST['maxDetailDeduction'])) ? $_REQUEST['maxDetailDeduction'] : 0;

    for ($i = 1;$i<=$intMax;$i++) {
      $strID        = (isset($_REQUEST['dataIDDeduction_'.$i])) ? $_REQUEST['dataIDDeduction_'.$i] : "";
      $strSeq       = (isset($_REQUEST['dataSeqDeduction_'.$i])) ? $_REQUEST['dataSeqDeduction_'.$i] : "";
      $strCode      = (isset($_REQUEST['dataCodeDeduction_'.$i])) ? $_REQUEST['dataCodeDeduction_'.$i] : "";
      $strName      = (isset($_REQUEST['dataNameDeduction_'.$i])) ? $_REQUEST['dataNameDeduction_'.$i] : "";
      $strAmount    = (isset($_REQUEST['dataAmountDeduction_'.$i])) ? $_REQUEST['dataAmountDeduction_'.$i] : "0";
      $strMaxlink   = (isset($_REQUEST['dataMaxlinkDeduction_'.$i])) ? $_REQUEST['dataMaxlinkDeduction_'.$i] : "";
      $strActive    = (isset($_REQUEST['dataActiveDeduction_'.$i])) ? "t" : "f";
      $strShow      = (isset($_REQUEST['dataShowDeduction_'.$i])) ? "t" : "f";
      $strProrate   = (isset($_REQUEST['dataProrateDeduction_'.$i])) ? "t" : "f";
      $strOT        = (isset($_REQUEST['dataOTDeduction_'.$i])) ? "t" : "f";
      $strTax       = (isset($_REQUEST['dataTaxDeduction_'.$i])) ? "t" : "f";
      $strJams      = (isset($_REQUEST['dataJamsDeduction_'.$i])) ? "t" : "f";
      $strDaily     = (isset($_REQUEST['dataDailyDeduction_'.$i])) ? "t" : "f";
      $strHidezero  = (isset($_REQUEST['dataHidezeroDeduction_'.$i])) ? "t" : "f";

      if (!is_numeric($strAmount)) $strAmount = 0;
      if ($strCode == "") { // ada kemungkinan ndihapus
        if ($strID != "") {
          //hapus data
          $strSQL  = "DELETE FROM hrd_deduction_type WHERE id = '$strID' AND template_name='$CurrentActiveTemplate'";
          $resExec = $db->execute($strSQL);
        }
      } else {
        if ($strID == "") { // insert new
          $strSQL  = "INSERT INTO hrd_deduction_type (created,modified_by,created_by, ";
          $strSQL .= "seq,code,name, amount, maxlink,  active, \"show\", prorate, ot, tax, jams, daily, hidezero, template_name) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', ";
          $strSQL .= "'$strSeq', '$strCode', '$strName', '$strAmount', '$strMaxlink', '$strActive', '$strShow', '$strProrate', ";
          $strSQL .= "'$strOT', '$strTax', '$strJams', '$strDaily', '$strHidezero', '$CurrentActiveTemplate') ";
          $resExec = $db->execute($strSQL);
        } else {//update
          $strSQL  = "UPDATE hrd_deduction_type SET modified_by = '$strmodified_byID', ";
          $strSQL .= "seq = '$strSeq', code = '$strCode', name = '$strName', amount = '$strAmount', maxlink = '$strMaxlink', active = '$strActive', ";
          $strSQL .= "\"show\" = '$strShow', prorate = '$strProrate', ot = '$strOT', tax = '$strTax', daily = '$strDaily',";
          $strSQL .= "jams = '$strJams', hidezero = '$strHidezero' ";
          $strSQL .= "WHERE id = '$strID' AND template_name='$CurrentActiveTemplate'";
          $resExec = $db->execute($strSQL);
        }
      }
    }

    writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"",0);
    $strError = $messages['data_saved']." ". date("d-M-y H:i:s");
    return true;
  } // saveData

  // fungsi untuk menyimpan data sebagai template
  /*
    1. Cek existence
    2. If exist, delete
    3. else insert
    4. tables involved: all_setting hrd_deduction_type_template hrd_allowance_type_template
    5. save ke default template - itu reference pastinya
  */
  function saveTemplateData($db, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    global $arrSetting;
    global $arrBreakTime;
    global $messages;

    //tarik informasi nama template yang akan disimpan
    $strTemplateName = $_REQUEST['inputTemplateName'];

    //bila tidak diisi nama template, perlakuannya sama dengan save data
    if($strTemplateName == "") return saveData($db,$strError);

    //Simpan ke session, nama template yang saat ini aktif
    $_SESSION['currentActiveTemplate'] = $strTemplateName;

    //cek apakah template ini sudah pernah dibuat sebelumnya.
    //bila sudah, hapus semua: all_setting, hrd_allowance_type_template, hrd_deduction_type_template
    $strTempSQL = "SELECT COUNT(*) FROM salary_settings WHERE template_name = '".$strTemplateName."' ";
    $resTempDb  = $db->execute($strTempSQL);
    $rowTempDb  = $db->fetchrow($resTempDb);
    $intTemp    = $rowTempDb['count'];
    if($intTemp != 0){
      $strTempSQL = "DELETE FROM salary_settings WHERE template_name = '".$strTemplateName."' ";
      $db->execute($strTempSQL);
      $strTempSQL = "DELETE FROM hrd_allowance_type_template WHERE template_name = '".$strTemplateName."' ";
      $db->execute($strTempSQL);
      $strTempSQL = "DELETE FROM hrd_deduction_type_template WHERE template_name = '".$strTemplateName."' ";
      $db->execute($strTempSQL);
    }

    $strmodified_byID = $_SESSION['sessionUserID'];

    $strSQL = "";
    foreach ($arrSetting AS $kode => $arrData) {
      if (isset($_REQUEST[$kode])) {
        $strValue = $_REQUEST[$kode];
        $strSQL .= "INSERT INTO salary_settings(modified_by,created,value,code,template_name) VALUES('$strmodified_byID', ";
        $strSQL .= "now(),'$strValue' ";
        $strSQL .= ",'" .$arrData['code']. "','".$strTemplateName."'); ";
      }

    }


    // handle untuk yang tipenya boolean, tapi nilainya false
    // untuk saat ini, cirinya ada DEFAULT = t/f ;)
    foreach ($arrSetting AS $kode => $arrData) {
      if ($arrData['default'] == 't' || $arrData['default'] == 'f') {
        if ((strpos($kode, "Multival") == false) && !isset($_REQUEST[$kode])) {
          $strSQL .= "INSERT INTO salary_settings(modified_by,value,code,template_name) VALUES('$strmodified_byID', ";
          $strSQL .= "'f','".$arrSetting[$kode]['code']."','".$strTemplateName."') ; ";
        }
      }
    }
    $resExec = $db->execute($strSQL);

    // simpan data tunjangan lain-lain
    $intMax = (isset($_REQUEST['numShowAllowance'])) ? $_REQUEST['numShowAllowance'] : 0;
    for ($i = 1;$i<=$intMax;$i++) {
      $strID     = (isset($_REQUEST['dataIDAllowance_'.$i])) ? $_REQUEST['dataIDAllowance_'.$i] : "";
      $strSeq   = (isset($_REQUEST['dataSeqAllowance_'.$i])) ? $_REQUEST['dataSeqAllowance_'.$i] : "";
      $strCode   = (isset($_REQUEST['dataCodeAllowance_'.$i])) ? $_REQUEST['dataCodeAllowance_'.$i] : "";
      $strName   = (isset($_REQUEST['dataNameAllowance_'.$i])) ? $_REQUEST['dataNameAllowance_'.$i] : "";
      $strAmount = (isset($_REQUEST['dataAmountAllowance_'.$i])) ? $_REQUEST['dataAmountAllowance_'.$i] : "0";
      //if (!is_numeric($strAmount)) $strAmount = 0;

      $strActive = (isset($_REQUEST['dataActiveAllowance_'.$i])) ? "t" : "f";
      $strIr     = (isset($_REQUEST['dataIrAllowance_'.$i])) ? "t" : "f";
      $strBen    = (isset($_REQUEST['dataBenAllowance_'.$i])) ? "t" : "f";
      $strShow   = (isset($_REQUEST['dataShowAllowance_'.$i])) ? "t" : "f";
      $strProrate   = (isset($_REQUEST['dataProrateAllowance_'.$i])) ? "t" : "f";
      $strOT     = (isset($_REQUEST['dataOTAllowance_'.$i])) ? "t" : "f";
      $strTax    = (isset($_REQUEST['dataTaxAllowance_'.$i])) ? "t" : "f";
      $strJams   = (isset($_REQUEST['dataJamsAllowance_'.$i])) ? "t" : "f";
      $strDaily  = (isset($_REQUEST['dataDailyAllowance_'.$i])) ? "t" : "f";
      $strHidezero  = (isset($_REQUEST['dataHidezeroAllowance_'.$i])) ? "t" : "f";

      /*
      //sudah dihapus di atas
      if (false){//$strCode == "") { // ada kemungkinan ndihapus
        if ($strID != "") {
          //hapus data
          $strSQL  = "DELETE FROM hrd_allowance_type_template WHERE id = '$strID' ";
          $resExec = $db->execute($strSQL);
        }
      } else {
        $strTempSQL = "SELECT COUNT(*) FROM hrd_allowance_type_template WHERE template_name = '".$strTemplateName."' ";
        $resTempDb  = $db->execute($strTempSQL);
        $rowTempDb  = $db->fetchrow($resTempDb);
        $intTemp    = $rowTempDb['count'];
        if($intTemp != 0){
            $strTempSQL = "DELETE FROM hrd_allowance_type_template WHERE template_name = '".$strTemplateName."' ";
            $db->execute($strTempSQL);
        }
        */
        //pasti baru, kalau ada sudah dihapus di atas
        //if (true){//$strID == "") { // insert new
          $strSQL  = "INSERT INTO hrd_allowance_type_template (created,modified_by,created_by, ";
          $strSQL .= "seq,code,name, amount, active, \"show\", prorate, ot, irregular, benefit, tax, jams, daily, hidezero, template_name) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', ";
          $strSQL .= "'$strSeq','$strCode', '$strName', '$strAmount', '$strActive', '$strShow', '$strProrate', ";
          $strSQL .= "'$strOT', '$strIr', '$strBen','$strTax', '$strJams', '$strDaily', '$strHidezero', '$strTemplateName') ";
          $resExec = $db->execute($strSQL);
        /*} else {//update
          $strSQL  = "UPDATE hrd_allowance_type_template SET modified_by = '$strmodified_byID', ";
          $strSQL .= "seq = '$strSeq', code = '$strCode', name = '$strName', amount = '$strAmount', active = '$strActive', ";
          $strSQL .= "\"show\" = '$strShow', prorate = '$strProrate', ot = '$strOT', tax = '$strTax', ";
          $strSQL .= "jams = '$strJams', irregular = '$strIr', benefit = '$strBen', daily = '$strDaily', hidezero = '$strHidezero'";
          $strSQL .= "WHERE id = '$strID' ";
          $resExec = $db->execute($strSQL);
        }
      }*/
    }

    // simpan data potongan lain-lain
    $intMax = (isset($_REQUEST['numShowDeduction'])) ? $_REQUEST['numShowDeduction'] : 0;

    for ($i = 1;$i<=$intMax;$i++) {
      $strID        = (isset($_REQUEST['dataIDDeduction_'.$i])) ? $_REQUEST['dataIDDeduction_'.$i] : "";
      $strSeq      = (isset($_REQUEST['dataSeqDeduction_'.$i])) ? $_REQUEST['dataSeqDeduction_'.$i] : "";
      $strCode      = (isset($_REQUEST['dataCodeDeduction_'.$i])) ? $_REQUEST['dataCodeDeduction_'.$i] : "";
      $strName      = (isset($_REQUEST['dataNameDeduction_'.$i])) ? $_REQUEST['dataNameDeduction_'.$i] : "";
      $strAmount    = (isset($_REQUEST['dataAmountDeduction_'.$i])) ? $_REQUEST['dataAmountDeduction_'.$i] : "0";
      $strMaxlink   = (isset($_REQUEST['dataMaxlinkDeduction_'.$i])) ? $_REQUEST['dataMaxlinkDeduction_'.$i] : "";
      $strActive    = (isset($_REQUEST['dataActiveDeduction_'.$i])) ? "t" : "f";
      $strShow      = (isset($_REQUEST['dataShowDeduction_'.$i])) ? "t" : "f";
      $strProrate   = (isset($_REQUEST['dataProrateDeduction_'.$i])) ? "t" : "f";
      $strOT        = (isset($_REQUEST['dataOTDeduction_'.$i])) ? "t" : "f";
      $strTax       = (isset($_REQUEST['dataTaxDeduction_'.$i])) ? "t" : "f";
      $strJams      = (isset($_REQUEST['dataJamsDeduction_'.$i])) ? "t" : "f";
      $strDaily     = (isset($_REQUEST['dataDailyDeduction_'.$i])) ? "t" : "f";
      $strHidezero  = (isset($_REQUEST['dataHidezeroDeduction_'.$i])) ? "t" : "f";

      //if (!is_numeric($strAmount)) $strAmount = 0;
      //sudah dihapus
      /*if (false){//$strCode == "") { // ada kemungkinan ndihapus
        if ($strID != "") {
          //hapus data
          $strSQL  = "DELETE FROM hrd_deduction_type_template WHERE id = '$strID' ";
          $resExec = $db->execute($strSQL);
        }
      } else {*/
        //pasti baru, kalau ada sudah dihapus
        //if (true){//$strID == "") { // insert new
          $strSQL  = "INSERT INTO hrd_deduction_type_template (created,modified_by,created_by, ";
          $strSQL .= "seq,code,name, amount, maxlink,  active, \"show\", prorate, ot, tax, jams, daily, hidezero, template_name) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', ";
          $strSQL .= "'$strSeq', '$strCode', '$strName', '$strAmount', '$strMaxlink', '$strActive', '$strShow', '$strProrate', ";
          $strSQL .= "'$strOT', '$strTax', '$strJams', '$strDaily', '$strHidezero', '$strTemplateName') ";
          $resExec = $db->execute($strSQL);
        /*} else {//update
          $strSQL  = "UPDATE hrd_deduction_type_template SET modified_by = '$strmodified_byID', ";
          $strSQL .= "seq = '$strSeq', code = '$strCode', name = '$strName', amount = '$strAmount', maxlink = '$strMaxlink', active = '$strActive', ";
          $strSQL .= "\"show\" = '$strShow', prorate = '$strProrate', ot = '$strOT', tax = '$strTax', daily = '$strDaily',";
          $strSQL .= "jams = '$strJams', hidezero = '$strHidezero' ";
          $strSQL .= "WHERE id = '$strID' ";
          $resExec = $db->execute($strSQL);*/
        //}
      //}
    }
    writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"",0);
    $strError = $messages['data_saved']." ". date("d-M-y H:i:s");
    //return saveTemplateData($db,$strError);
    return true;
  } // saveTemplateData

  function getTemplateData($db)
  {
    global $words;
    global $_REQUEST;
    global $_SESSION;
    global $strModule;
    global $arrSetting;

    //tarik informasi nama template yang akan di-load
    $strTemplateName = $_REQUEST['loadTemplateName'];
    //Simpan ke session, nama template yang saat ini aktif
    $_SESSION['currentActiveTemplate'] = $strTemplateName;
    $intIDmodified_by = $_SESSION['sessionUserID'];
    $tblSetting = new cModel("salary_settings");
    foreach ($arrSetting AS $kode => $arrData)
    {
      if ($arrData['code'] != "")
      {
        if ($arrHasil = $tblSetting->find("code='".$arrData['code']."' AND template_name = '".$strTemplateName."'"))
        {
          $arrSetting[$kode]["value"] = $arrHasil['value'];
        }
        else
        {
          $data = array("code"   => $arrData['code'],
                        "value"  => $arrData['default'],
                        "note"   => $arrData['note'],
                        "module" => $strModule,
                        "template_name" => $strTemplateName
                        );
          $tblSetting->insert($data);
        }
      }
    }
    $tblATT = new cModel("hrd_allowance_type_template");
    $tblAT = new cModel("hrd_allowance_type");
    $tblAT->delete("");
    $dataAtt = $tblATT->findAll("template_name = '$strTemplateName'");
    unset($dataAtt['template_name']);
    foreach($dataAtt as $n => $data)
    {
      $tblAT->insert($data);
    }
    $tblATT = new cModel("hrd_deduction_type_template");
    $tblAT = new cModel("hrd_deduction_type");
    $tblAT->delete("");
    $dataAtt = $tblATT->findAll("template_name = '$strTemplateName'");
    unset($dataAtt['template_name']);
    foreach($dataAtt as $n => $data)
    {
      $tblAT->insert($data);
    }

    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    return true;
  } // getTemplateData

  // fungsi untuk mengambil data tunjangan dari template
  function getTemplateDataAllowance($db) {
    //tarik informasi nama template yang akan di-load
    $strTemplateName = $_REQUEST['loadTemplateName'];

    $arrAllowanceAttributes;
    // inisialisasi
    $intMaxDetail = 100;
    $strResult = "";

    //ambil data basic salary dulu
    /*
    $strResult .= " <tr valign=top>\n";
    $strResult .= "  <td>&nbsp;</td>\n";
    $strResult .= "  <td nowrap>&nbsp;" .getWords('basic salary')."</td>\n";
    $strResult .= "  <td align=center><input type=text name=\"strBasicName\" size=30 maxlength=40 value=\"" .$arrSetting['strBasicName']['value']."\"></td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&radic;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= "  <td align=center>&nbsp;</td>\n";
    $strResult .= " </tr>\n";*/

    // ambil info tunjangan yang punya perlakukan khusus
    foreach ($arrFixAllowance AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrAllowanceAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "  <td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "  <td nowrap>&nbsp;" .$arrTmp[0]." (".$arrTmp[1].")</td>\n";
       $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string'></td>\n";
       $strResult .= "  <td><input type=text name=\"str".$strKode."Amount\" value=\"" .(float)$arrSetting['str'.$strKode.'Amount']['value']."\" size=20 maxlength=20 class=numeric class='numeric'></td>\n";

       $strResult .= $strAttribute;
       $strResult .= " </tr>\n";
    }

    //Info tunjangan yang di link ke page data master
    foreach ($arrFixAllowance2 AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrAllowanceAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "<td nowrap>&nbsp;" .$arrTmp[0]."</td>\n";
       $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string'></td>\n";
       $strLink = ($arrTmp[1] != "") ? "<a href=\"".$arrTmp[1]."\">".$arrSetting['str'.$strKode.'Name']['value']."</a>" : "";
       $strResult .= "<td align=left>&nbsp;$strLink</td>\n";
       $strResult .= $strAttribute;
       $strResult .= " </tr>\n";
    }


    //data Tunjangan Tambahan
    $strSQL  = "SELECT * FROM hrd_allowance_type_template WHERE template_name = '".$strTemplateName."' ORDER BY seq ";
    $resDb = $db->execute($strSQL);
    $intRows = 0;
    while ($rowDb = $db->fetchrow($resDb))
    {
      $intRows++;
      $strAttribute = "";
      foreach($arrAllowanceAttributes AS $arrAttribute)
      {
        $strChecked = ($rowDb[$arrAttribute['code']] == 't') ? "checked" : "";
        $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Allowance_$intRows\" value='t' $strChecked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataAllowance"."_$intRows'>\n";

      $strTempSQL = "SELECT * FROM hrd_allowance_type_template WHERE template_name IS NULL AND code ='".$rowDb['code']."' ";
      $resTempDb  = $db->execute($strTempSQL);
      $rowTempDb  = $db->fetchrow($resTempDb);

      $strResult .= "  <td align=center><input type=checkbox name=\"cbxAllow"."_$intRows\" value='".$rowDb['id']."' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqAllowance"."_$intRows size=5 value=\"" .$rowDb['seq']."\">";
      $strResult .= "  <td><input type=hidden name=dataIDAllowance"."_$intRows value=\"" .$rowTempDb['id']."\">";
      $strResult .= "<input type=text name=dataCodeAllowance"."_$intRows value=\"" .$rowDb['code']."\" size=20 maxlength=40 onChange=\"changeCode('Allowance',$intRows)\" class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataNameAllowance"."_$intRows value=\"" .$rowDb['name']."\" size=30 maxlength=40 class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountAllowance"."_$intRows value=\"" .$rowDb['amount']."\" size=20 ></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "</tr>\n";
    }

    $intNumShow = $intRows;
    // tambahkan detail tambahan
    while ($intRows <= $intMaxDetail) {
      $intRows++;
      $strAttribute = "";
      foreach($arrAllowanceAttributes AS $arrAttribute)
      {
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Allowance_$intRows\" value='t' checked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataAllowance"."_$intRows' style=\"display:none\">\n";
      $strResult .= "  <td align=center><input type=checkbox name=\"cbxAllow"."_$intRows\" value='' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqAllowance"."_$intRows size=5 maxlength=40 disabled   class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataCodeAllowance"."_$intRows size=20 maxlength=40 disabled  onChange=\"changeCode('Allowance',$intRows)\" class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataNameAllowance"."_$intRows size=30 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountAllowance"."_$intRows  size=20  disabled value=0></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "<td align=center>&nbsp;</td>\n";

      $strResult .= "</tr>\n";

    }

    // tambahkan hidden value
    $strHiddenAllowance .= "<input type=hidden name='numShowAllowance' value=$intNumShow>";
    $strHiddenAllowance .= "<input type=hidden name='maxDetailAllowance' value=$intMaxDetail>";


    return $strResult;
  }//getTemplateDataAllowance

  // fungsi untuk mengambil data potongan dari template
  function getTemplateDataDeduction($db) {
    global $strHiddenDeduction;
    global $arrSetting;
    global $arrFixDeduction;
    global $arrFixDeduction2;
    global $arrDeductionAttributes;
    global $words;
    global $_REQUEST;
    //tarik informasi nama template yang akan di-load
    $strTemplateName = $_REQUEST['loadTemplateName'];
    // inisialisasi
    $intMaxDetail = 100;
    $strResult = "";

    // ambil info potongan yang punya perlakukan khusus
    foreach ($arrFixDeduction AS $strKode => $arrTmp) {

      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
         $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
      }

      $strResult .= " <tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;</td><td nowrap>&nbsp;</td>";
      $strResult .= "  <td nowrap>&nbsp;" .getWords($arrTmp[0])." (".$arrTmp[1].")</td>\n";

      $strResult .= "  <td align=center><input type=text name=\"str".$strKode."Name\" size=30 maxlength=40 value=\"" .$arrSetting['str'.$strKode.'Name']['value']."\" class='string' ></td>\n";
      $strResult .= "  <td><input type=text name=\"str".$strKode."Amount\" value=\"" .(float)$arrSetting['str'.$strKode.'Amount']['value']."\" size=20 maxlength=20 class=numeric ></td>\n";
      $strResult .= "<td>&nbsp</td>".$strAttribute;

      $strResult .= " </tr>\n";
    }

    //Info tunjangan yang di link ke page data master
    foreach ($arrFixDeduction2 AS $strKode => $arrTmp)
    {
       $strAttribute = "";
       foreach($arrDeductionAttributes AS $arrAttribute)
       {
          $strChecked = ($arrSetting['str'.$strKode.$arrAttribute['name']]['value'] == 't') ? "checked" : "";
          $strAttribute .= " <td align=center><input type=checkbox name=\"str".$strKode.$arrAttribute['name']."\" value='t' $strChecked></td>\n";
       }

       $strResult .= "<tr valign=top>\n";
       $strResult .= "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
       $strResult .= "<td nowrap>&nbsp;" .getWords($arrTmp[0])."</td>\n";
       $strResult .= "<td>".$arrSetting['str'.$strKode.'Name']['value']."</td>\n";
       $strResult .= "<td align=left><a href=\"".$arrTmp[1]."\">";
       $strResult .= $arrSetting['str'.$strKode.'Name']['value']."</a></td>\n";
       $strResult .= "<td nowrap>&nbsp;</td>".$strAttribute;
       $strResult .= " </tr>\n";
    }

    $strSQL  = "SELECT * FROM hrd_deduction_type_template WHERE template_name = '".$strTemplateName."' ORDER BY seq ";
    $resDb = $db->execute($strSQL);
    $intRows = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strChecked = ($rowDb[$arrAttribute['code']] == 't') ? "checked" : "";
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Deduction_$intRows\" value='t' $strChecked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataDeduction"."_$intRows'>\n";
      $strTempSQL  = "SELECT id FROM hrd_deduction_type_template WHERE template_name IS NULL AND code = '".$rowDb['code']."' ";
      $resTempDb = $db->execute($strTempSQL);
      $rowTempDb = $db->fetchrow($resTempDb);

      $strResult .= "  <td align=center><input type=checkbox name=\"cbxDeduc"."_$intRows\" value='".$rowDb['id']."' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqDeduction"."_$intRows size=5 value=\"" .$rowDb['seq']."\">";
      $strResult .= "  <td><input type=hidden name=dataIDDeduction"."_$intRows value=\"" .$rowTempDb['id']."\">";
      $strResult .= "<input type=text name=dataCodeDeduction"."_$intRows value=\"" .$rowDb['code']."\" size=20 maxlength=40 onChange=\"changeCode('Deduction',$intRows)\" class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataNameDeduction"."_$intRows value=\"" .$rowDb['name']."\" size=30 maxlength=40 class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountDeduction"."_$intRows value=\"" .(float)$rowDb['amount']."\" size=20 maxlength=20 class=numeric></td>\n";
      $strResult .= "  <td><input type=text name=dataMaxlinkDeduction"."_$intRows value=\"" .$rowDb['maxlink']."\" size=20 maxlength=20 class='string-empty'></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "<td align=center>&nbsp;</td>\n";
      $strResult .= "</tr>\n";
    }
    $intNumShow = $intRows;

    // tambahkan detail tambahan
    while ($intRows <= $intMaxDetail) {
      $intRows++;
      $strAttribute = "";
      foreach($arrDeductionAttributes AS $arrAttribute)
      {
         $strAttribute .= " <td align=center><input type=checkbox name=\"data".$arrAttribute['name']."Deduction_$intRows\" value='t' checked></td>\n";
      }
      $strResult .= "<tr valign=top id='detailDataDeduction"."_$intRows' style=\"display:none\">\n";
      $strResult .= "  <td align=center><input type=checkbox name=\"cbxDeduc"."_$intRows\" value='' ></td>\n";
      $strResult .= "  <td><input type=text name=dataSeqDeduction"."_$intRows size=5 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataCodeDeduction"."_$intRows size=20 maxlength=40 disabled onChange=\"changeCode('Deduction',$intRows)\" class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataNameDeduction"."_$intRows size=30 maxlength=40 disabled class='string-empty'></td>\n";
      $strResult .= "  <td><input type=text name=dataAmountDeduction"."_$intRows  size=20 maxlength=20 class=numeric disabled value=0></td>\n";
      $strResult .= "  <td><input type=text name=dataMaxlinkDeduction"."_$intRows  size=20 maxlength=20 class='string-empty' disabled value=0></td>\n";
      $strResult .= $strAttribute;
      $strResult .= "<td align=center>&nbsp;</td>\n";
      $strResult .= "</tr>\n";

    }

    // tambahkan hidden value
    $strHiddenDeduction .= "<input type=hidden name='numShowDeduction' value=$intNumShow>";
    $strHiddenDeduction .= "<input type=hidden name='maxDetailDeduction' value=$intMaxDetail>";


    return $strResult;
  }//getTemplateDataDeduction

  //fungsi untuk me-list template yang ada
  function getTemplateList($db){
    $strResult = "<select id='loadTemplateName' name='loadTemplateName' >";
    $strSQL = "SELECT DISTINCT template_name FROM salary_settings ";
    $resDb = $db->execute($strSQL);
    while($rowDb = $db->fetchrow($resDb)){
      if($rowDb['template_name'] == NULL)continue;
      $strResult.="
      <option value='".$rowDb['template_name']."'>".$rowDb['template_name']."</option>
      ";
    }
    $strResult .= "</select>";
    return $strResult;
  }

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    if ($bolCanView) {
      if ($bolCanEdit) {
        if (isset($_REQUEST['btnTemplateSave'])) {
          $bolOK = saveTemplateData($db, $strError);
          if ($strError != "") {
            $strMessages = $strError;
            $strMsgClass = ($bolOK) ? "class=bgOK" : "class=bgError";
          }
        }

       // d($_REQUEST['btnSave'],$_POST);
        if (isset($_REQUEST['btnSave'])) {

          if(isset($_SESSION['currentActiveTemplate'])) {
            if(isset($_SESSION['first_save'])){
              unset($_SESSION['currentActiveTemplate']);
              unset($_SESSION['first_save']);
             }
            else $_SESSION['first_save'] = true;
          }

         $bolOK = saveData($db, $strError);
          if ($strError != "") {
            //echo "<script>alert(\"$strError\")</script>";
            $strMessages = $strError;
            if(isset($_SESSION['first_save']) && $bolOK) $strMessages = "Template Loaded";
            $strMsgClass = ($bolOK) ? "class=bgOK" : "class=bgError";
          }
        }
        if (isset($_REQUEST['btnDelete'])) {

          $bolOK = deleteData($db, $strError);
          if ($strError != "") {
            //echo "<script>alert(\"$strError\")</script>";
            $strMessages = $strError;
            $strMsgClass = ($bolOK) ? "class=bgOK" : "class=bgError";
          }
        }
        if (isset($_REQUEST['btnTemplateLoad'])) {
          unset($_SESSION['first_save']);
          getTemplateData($db);
          $strDetailAllowance = getDataAllowance($db);
          $strDetailDeduction = getDataDeduction($db);
        }
        if (isset($_REQUEST['btnTemplateDelete'])) {
          deleteTemplateData($db);
        }
      }
      if (!isset($_REQUEST['btnTemplateLoad'])) {
        getData($db);
        $strDetailAllowance = getDataAllowance($db);
        $strDetailDeduction = getDataDeduction($db);
        //tampilkan default baris pertama 0
      }
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

  }

  // tampilkan data
  $strHour = $arrSetting['strHour']['value'];
  $strDays = $arrSetting['strDays']['value'];
  $strDaysAllDay = $arrSetting['strDaysAllDay']['value'];
  $strDaysSatIn = $arrSetting['strDaysSatIn']['value'];
  $strRound = $arrSetting['strRound']['value'];
  $strHalfOTMax = $arrSetting['strHalfOTMax']['value'];
  $strHalfOTRate = $arrSetting['strHalfOTRate']['value'];
  $strOTBreakfastAllowance = $arrSetting['strOTBreakfastAllowance']['value'];
  $intOTPercent = $arrSetting['intOTPercent']['value'];
  $strFullAttendanceAllowance = $arrSetting['strFullAttendanceAllowance']['value'];
  $strHalfAttendanceAllowance = $arrSetting['strHalfAttendanceAllowance']['value'];
  $strBasicSalaryCode = $arrSetting['strBasicSalaryCode']['value'];
  $strBPJSMax = $arrSetting['strBPJSMax']['value'];
  $strPensionMax = $arrSetting['strPensionMax']['value'];
  $strNishab = $arrSetting['strNishab']['value'];
  $strLateCoefficient = $arrSetting['strLateCoefficient']['value'];
  $strTaxMethod = "";
  $strUseUMKForOT = "";
  $strUseUMKForBPJSTK = "";
  $strUseUMKForBPJSKS = "";
  $strValueForOT = $arrSetting['strValueForOT']['value'];
  $strValueForBPJSTK = $arrSetting['strValueForBPJSTK']['value'];
  $strValueForBPJSKS = $arrSetting['strValueForBPJSKS']['value'];
  if($arrSetting['strTaxMethod']['value'] == "t") $strTaxMethod = "checked";
  if($arrSetting['strUseUMKForOT']['value'] == "t") $strUseUMKForOT = "checked";
  if($arrSetting['strUseUMKForBPJSKS']['value'] == "t") $strUseUMKForBPJSKS = "checked";
  if($arrSetting['strUseUMKForBPJSTK']['value'] == "t") $strUseUMKForBPJSTK = "checked";

  $strInputSalaryDateFrom = getDayList("strSalaryDateFrom",$arrSetting['strSalaryDateFrom']['value']);
  $strInputSalaryDateThru = getDayList("strSalaryDateThru",$arrSetting['strSalaryDateThru']['value']);
  $strInputSalaryDate = getDayList("strSalaryDate",$arrSetting['strSalaryDate']['value']);
  if(isset($_SESSION['currentActiveTemplate'])) $strCurrentActiveTemplate = "CURRENT TEMPLATE = ". $_SESSION['currentActiveTemplate'];
  else {
      $strSQL  = "SELECT value FROM all_setting where code='template_name' ";
      $resDb = $db->execute($strSQL);
      $rowDb = $db->fetchrow($resDb);
      $strCurrentActiveTemplate = "CURRENT TEMPLATE = ". $rowDb['value'];
  }

  $helperScript = "noerr";
//  if (isset($_REQUEST['btnTemplateLoad'])) {
//    $helperScript = "
//    <script>
//      document.getElementById('btnSave').click();
//    </script>
//    ";
//  }

  $templateList = getTemplateList($db);
  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master.html") ;
  $tbsPage->Show() ;

 function deleteData($db, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,3) == 'cbx') {
        if ( substr($strIndex,3,5) == 'Allow') {
          if ( $strValue != "" ) {
            $strSQL = "DELETE FROM hrd_allowance_type WHERE id = $strValue; ";
            $resExec = $db->execute($strSQL);

          }
        } else {
          if ( $strValue != "" ) {
            $strSQL = "DELETE FROM hrd_deduction_type WHERE id = $strValue; ";
            $resExec = $db->execute($strSQL);
          }
        }
      }
    }
 }

 function deleteTemplateData($db)
  {
    global $words;
    global $_REQUEST;
    global $_SESSION;
    global $strModule;
    global $arrSetting;

    //tarik informasi nama template yang akan di-delete
    $strTemplateName = $_REQUEST['loadTemplateName'];

    //cek apakah template ini sudah pernah dibuat sebelumnya.
    //bila sudah, hapus semua: all_setting, hrd_allowance_type_template, hrd_deduction_type_template
    $strTempSQL = "SELECT COUNT(*) FROM salary_settings WHERE template_name = '".$strTemplateName."' ";
    $resTempDb  = $db->execute($strTempSQL);
    $rowTempDb  = $db->fetchrow($resTempDb);
    $intTemp    = $rowTempDb['count'];
    if($intTemp != 0){
      $strTempSQL = "DELETE FROM salary_settings WHERE template_name = '".$strTemplateName."' ";
      $db->execute($strTempSQL);
      //$strTempSQL = "DELETE FROM hrd_allowance_type_template WHERE template_name = '".$strTemplateName."' ";
      //$db->execute($strTempSQL);
      //$strTempSQL = "DELETE FROM hrd_deduction_type_template WHERE template_name = '".$strTemplateName."' ";
      //$db->execute($strTempSQL);
    }
  }
?>
