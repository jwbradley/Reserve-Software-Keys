<?php

$logDate = date("Y-m-d");
$logUser = strtoupper(trim($_SERVER['PHP_AUTH_USER'])); // PHP_AUTH_USER captured via LDAP Authentications
$logTCP = $_SERVER['REMOTE_ADDR'];

$giveMeData =  array();

$dbhost = "10.10.10.3";  

$db = "serialdb";
$dbuser = "mysqluser";
$dbpass = "mysqlpass";
$db_connect = mysql_connect($dbhost, $dbuser, $dbpass);

if (!$db_connect) {
	die("Failed to mysql_connect: " . mysql_error());
}

$db_selected = mysql_select_db($db, $db_connect);
if (!$db_selected) {
	die('Can\'t use database: ' . mysql_error());
}

 /* Query to get existing records  */
$sql1 = "SELECT `IntNum`, 
                `SerialNumber`, 
                `UserID`, 
                `First Name`, 
                `Last Name`, 
                `CheckedOut`, 
                `AppVer`, 
                `CompanyName`, 
                `Status` 
           FROM `serialdb`.`AppSN` 
          where `UserID` =  '{$logUser}'  ";

$Check2 = mysql_query($sql1);
$giveMeData = mysql_fetch_array($Check2);

if (!$giveMeData)  {
	/* Does the user already have a software key checked out? */
	$query = 'select count(*) as recs 
	            from `serialdb`.`AppSN` 
	           where (( UserID is Null or UserID = \'\' ) 
	             and ( IntNum is not Null ) 
	             and expiredate < current_date ) 
	              or (UserID is not Null and Status <> \'A\' )'; 
	$Check1 = mysql_query($query);
	$availablerecords = mysql_fetch_array($Check1);
	$newUser = 'Yes';

	if ($availablerecords["recs"] == 0 ) {
		$noRecords = 'Yes';
	} else {
		$noRecords = 'No';
	}

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>App Key Check-out</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

  <style  type="text/css">
   .formCenter {
	    width:60%;
	    margin: 0 auto;
	}
  </style>
</head>
<body>

<div class="container">
	<form role="form" class="formCenter">

<?php
if ( ($newUser == 'Yes') && ($noRecords =='No')  )  {
	// Get next Software Key ready for checkout. 
	$sql3 = 'select min(IntNum) as intrec, 
	                SerialNumber as serial, 
	                AppVer as ver, 
	                CompanyName as cn 
	           from `serialdb`.`AppSN` 
	          where (( UserID is Null or UserID = \'\' ) and ( IntNum is not Null ) )
               or (UserID is not Null and Status <> \'A\' and expiredate < current_date  )';  

	$execute = mysql_query($sql3);
	$IntNum = mysql_fetch_array($execute);

	/* Checkout Software Key   */
	$sql2 = "update `serialdb`.`AppSN` 
	            set UserID = '{$logUser}', 
	                `First Name` = '{$F_Name}', 
	                `Last Name` = '{$L_Name}', 
	                CheckedOut = NOW(), 
	                status = 'A', 
	                expiredate = '".date("Y-m-d", strtotime("+ 1 day"))."'
            where IntNum = ".$IntNum["intrec"].""; 
  $hold = mysql_query($sql2);

	$giveMeData["UserID"] = $logUser;
	$giveMeData["AppVer"] = $IntNum["ver"];
	$giveMeData["First Name"] = $F_Name;
	$giveMeData["Last Name"] = $L_Name;
	$giveMeData["CompanyName"] = $IntNum["cn"];
	$giveMeData["SerialNumber"] = $IntNum["serial"];
}

if (  ($noRecords =='Yes') ) {
	echo '<h1>All Available App Key Have Already Been Assigned.</h1>';
} else {
	if ($giveMeData["AppVer"] == '9.0.x.x') {
		$link = 'AppVer-9.0.exe'; // Executable name for download
	} else {
		$link = 'AppVer-8.5.exe'; // Executable name for download
	}

	echo '<h2>Application Key Details</h2>';
	echo '<div class="form-group">';
	echo '  <label for="userid">User ID:</label>';
	echo '  <input type="text" class="form-control" id="userid" name="userid" value="'.$giveMeData["UserID"].'" readonly>';
	echo '</div>';
	echo '<div class="form-group">';
	echo '  <label for="ver">Version Number:</label>';
	echo '  <input type="text" class="form-control" id="ver" name="ver" value="'.$giveMeData["AppVer"].'" readonly> ';
	echo '</div>';
	echo '<div class="form-group">';
	echo '  <label for="name">Name:</label>';
	echo '  <input type="text" class="form-control" id="name" name="name" value="'.$giveMeData["First Name"].' '.$giveMeData["Last Name"]. '" readonly>';
	echo '</div>';
	echo '<div class="form-group">';
	echo '  <label for="company">Company Name:</label>';
	echo '  <input type="text" class="form-control" id="company" name="company" value="'.$giveMeData["CompanyName"].'" readonly>';
	echo '</div>';
	echo '<div class="form-group">';
	echo '  <label for="sn">Serial Number:</label>';
	echo '  <input type="text" class="form-control" id="sn" name="sn" value="'.$giveMeData["SerialNumber"].'" readonly>';
	echo '</div>';
	echo '<div class="form-group">';
	echo '  <label for="inst">App Download Link:</label><br>';
	echo '  <strong><a href="./'.$link.'" class="form-control" style="background-color:#EEEEEE; color:blue; border-style:solid; border-width:1px;">'.$link.'</a></strong>';
	echo '</div>';
}


  </form>
</div>
</body>
</html>