<?php
$allowedExts = array("pdf");
$extension = end(explode(".", $_FILES["file"]["name"]));

if ((($_FILES["file"]["type"] == "application/pdf") )
&& ($_FILES["file"]["size"] < 10000000)
&& in_array($extension, $allowedExts))
  {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    }
  else
    {
    echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    echo "Type: " . $_FILES["file"]["type"] . "<br>";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

    /*if (file_exists("upload/" . $_FILES["file"]["name"]))
      {
      echo $_FILES["file"]["name"] . " already exists. ";
      }
    else
      {
      move_uploaded_file($_FILES["file"]["tmp_name"],
      "upload/companyregulation.pdf");
      echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
      }*/
 	move_uploaded_file($_FILES["file"]["tmp_name"],
      "newsletter/newsletter.pdf");
      header('Location:main.php');

    }
  }
else
  {
  echo "Invalid file";
  
  }
?> 