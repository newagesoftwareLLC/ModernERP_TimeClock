<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Employee Website</title>
<style>
tr { line-height:13px; }
tr td:hover { background-color:cyan !important; cursor:pointer; }
</style>
<?PHP
if (isset($_POST['year'])){ setcookie("hoursYear",$_POST['year'],0); header("Location: hours.php?id=$_GET[id]"); }

$year = date("Y");

if (!isset($_COOKIE['hoursYear']))
	setcookie("hoursYear",date("Y"),  time()+86400);
else
	$year = $_COOKIE['hoursYear'];

if (!isset($_GET['id']))
		$_GET['id'] = 1;

if (!isset($_GET['id']) && !isset($_GET['n'])) header("Location: hours.php?id=3");
?>
</head>

<body class="twoColHybLtHdr">

<div id="container">
<?PHP	
	//echo "[DEBUG] XML Curl: employee/scripts/hours.xml.php?id=$_GET[id]&y=$year";
	$xmlCode = simplexml_load_string(curl_xml("employee/scripts/hours.xml.php?id=".$_GET['id']."&y=".$year));
	?>
<div style="margin-left:auto;margin-right:auto;width:1200px;overflow:auto;">

<?PHP
if (isAdmin()==false) {
	echo "<p>Only website administrators can access this page. You need to login.</p>";
	echo '<div style="text-align:center;padding:50px;"><h2><a id="fancybox-url" href="javascript:;" rel=".././login_popup.php"><span class="glyphicon glyphicon-user"></span> Login Required</a></h2></div>';
	return;
}

if (isset($xmlCode))
	$empInfo = $xmlCode->xpath('//XML/employee_info'); 
?>

<div style="width:230px;padding:10px;background-color:#CCC;border:thin solid black;float:left;margin-right:5px;">
<div style="text-align:center;border-bottom:thin black solid;padding-bottom:5px;margin-bottom:5px;">
<form method="post">
<b style="color:white;">YEAR</b> <select name="year" style="width:70px;">
<?PHP for($i = date("Y");$i >= 2014;$i--) { ?>
<option value="<?PHP echo $i; ?>" <?PHP if ($i == $year) { echo "selected=selected"; } ?>><?PHP echo $i; ?></option>
<?PHP } ?></select>
<button type="submit" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-repeat"></span></button>
</form>
[<a id="fancybox-url" href="javascript:;" rel="../scripts/edit_holidays.php?y=<?PHP if (isset($_COOKIE['hoursYear'])) echo $_COOKIE['hoursYear']; ?>">EDIT HOLIDAYS</a>]<br />[<a href="print_payweek.php?y=<?PHP if (isset($_COOKIE['hoursYear'])) echo $_COOKIE['hoursYear']; ?>" target="_BLANK">PRINT PAYWEEK</a>]
</div>
<?PHP
$q = $conn->query("SELECT * FROM employee WHERE active = '1' ORDER BY lastname");
$empType = 0;
while ($r = $q->fetch_assoc()){
	echo "<p>";
	if (isset($_GET['id']))
		if ($r['number'] == $_GET['id']) echo '<b>&rArr;</b> <b>'; 
	echo "<a href=\"hours.php?id=$r[number]\">$r[lastname], $r[firstname]</a> ($r[number])"; 
	if (isset($_GET['id']))
		if ($r['number'] == $_GET['id']) echo '</b>'; echo '</p>';
	$hireDate = $r['hiredate'];
}
$tHours = array();
?>
</div>

<div style="float:left;position:relative;width:879px;height:30px;">
<div style="position:absolute;left:10px;top:10px;width:285px;text-align:left;font-weight:bold;"><?PHP echo $empInfo[0]->LastName; ?>, <?PHP echo $empInfo[0]->FirstName; ?></div>
<div style="position:absolute;right:10px;top:10px;width:285px;text-align:right;font-weight:bold;">Vacation Remaining: <?PHP echo $empInfo[0]->CurrentVacation; ?>/<?PHP echo $empInfo[0]->TotalVacation; ?> (<?PHP echo $empInfo[0]->HireDate; ?>)</div>
</div>

<table border="1" cellspacing="0" cellpadding="0" style="min-height:823px;">
  <tr>
    <td class="tableHeader" style="width:40px;background-color:#CCC;"></td>
    <?PHP for ($c=1;$c<=12;$c++){ $m = date('M', mktime(0, 0, 0, $c, 10)); ?>
    <td align="center" class="tableHeader" style="width:70px;background-color:#CCC;"><?PHP echo strtoupper($m); ?></td>
    <?PHP } ?>
  </tr>
  <?PHP $d=1; for ($i=1;$i<=31;$i++){ ?>
  <tr>
    <td align="center" class="tableHeader" style="background-color:#CCC;"><?PHP echo $i; ?></td>
    <?PHP for ($c=1;$c<=12;$c++){ ?>
    <?PHP if (cal_days_in_month(CAL_GREGORIAN, $c, $year) < $i) { ?>
    <td style="background-color:#ccc;text-align:center;" class="tableHeader">
    <?PHP } else { ?>
    <td id="fancybox-tdurl" style="font-size:9px;text-align:center;background-color:<?PHP echo specialDay($_GET['id'],$year.'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i),"color"); ?>;">
    <?PHP $pretest = $xmlCode->xpath('//XML/calendar[@month="'.$c.'"][@day="'.$i.'"]'); 
	if ($pretest[0]->worked_hours > 0) echo $pretest[0]->worked_hours; 
	if ($pretest[0]->payday_hours > 0) echo ' <span class="badge" style="font-size:9px;">'.$pretest[0]->payday_hours.'</span>';
	echo specialDay($_GET['id'],$year.'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i),"text"); ?>
    <?PHP echo '<a rel=".././scripts/calendar.edit.php?id='.$_GET['id'].'&m='.$c.'&d='.$i.'&y='.$year.'" class="goto"></a>'; ?>
    </td>
<?PHP } } ?>
</tr>
<?PHP } ?>
</table>
</div>
</div>

</div>

</div>
	<!-- footer -->
</div>

</body>
</html>