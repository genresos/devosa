<?php
 date_default_timezone_set('Asia/Jakarta');
  //the host server address of database
  define("DB_TYPE","postgres");
  define("DB_SERVER","192.168.77.71");
  define("DB_PORT","5432");
  //define("DB_SERVER","invosaserver");

  //the name of database
  define("DB_NAME","adyawinsa-dev");

  //the database's user password
  define("DB_USER","postgres");

  //the database's user name
  define("DB_PWD","sukasapi");
  define("INVERSE_PRORATE", FALSE);

  //the tax method 0=gross 1=gross up
  define("TAX_METHOD", 0);

  //the absolute directory path in local drive
  //define("ABSOLUTE_PATH", $_SERVER['DOCUMENT_ROOT']);

  //the URL
  define("LIVE_SITE","http://192.168.0.15/");

  //application name
  define("APPLICATION_NAME", "deVosa - Human Resource Information System");

  //copyright string/HTML


  define("COPYRIGHT","Copyright &copy; 2024 by PT Tesco Indomaritim.<br>All rights reserved.");


//default ENGLISH
  define("DEFAULT_LANGUAGE","en");

  //default error reporting
  error_reporting(0);
  ini_set("memory_limit","2048M");
  //write user login/logout activity to databaser

  ini_set("display_errors","off");
  define("WRITE_USER_LOG", 0);

  define("CONFIGURATION_LOADED", 1);

  //define("PG_DUMP_PATH", 'C:\Program Files\PostgreSQL\9.3\bin\pg_dump.exe');
  define("PG_DUMP_PATH", '/usr/lib/postgresql/9.3/bin/pg_dump');

?>
