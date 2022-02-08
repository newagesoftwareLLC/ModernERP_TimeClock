<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Employee Website</title>
<?php
if (isset($_GET['new'])) {
	setcookie("empid", null, -1);
	setcookie("empid", null, time()-(60*60*24*365), "/");
	setcookie("password", null, time()-(60*60*24*365), "/");
	setcookie("logged", "false", time()-(60*60*24*365), "/");
	 ?><script>window.location.href = "./punch"; </script><?PHP } ?>
<style>
.keyBtn {
	float:left;
	border:thin solid black;
	font-weight:bold;
	font-size:36px;
	padding:30px 50px 30px 50px;
	margin-bottom:5px;
	margin-right:3px;
}
</style>
</head>

<body class="twoColHybLtHdr">

<div id="container">
<div id="mainContent">
<div class="innerContent">
<?PHP
$date = date("Y-m-d");
$time = time();

function punchToggle() {
	global $empID;
	?><script> console.log("punchToggle <?PHP echo $empID.' getPunchText='.getPunchText($empID); ?>"); </script><?PHP
	if (getPunchText($empID) == 'punched out')
		setPunch('punchin');
	else
		setPunch('punchout');
}

function setPunch($punchType) {
	global $conn, $empID, $time, $date;
	$s = "";
	?><script> console.log("setPunch <?PHP echo $punchType.' '.$empID; ?>"); </script><?PHP
	if ($punchType == 'punchin'){
		if (date("H:i", $time) < "06:00" && date("H:i", $time) > "05:00")
			$time = 21600; //6am
		$s = "INSERT INTO timeclock (empid,duration,starttime,startdate) VALUES ('$empID','0','$time','$date')";
	} else if ($punchType == 'punchout' || $punchType == 'lunchout' || $punchType == 'lunchback'){
		$g = "SELECT * FROM timeclock WHERE empid = '$empID' ORDER BY id DESC";
		$q = $conn->query($g);
		while($r = $q->fetch_assoc()) {
			$duration = $time - $r['starttime'];
			if ($punchType == 'punchout')
				$s = "UPDATE timeclock SET duration = '$duration',endtime = '$time' WHERE id = '$r[id]'";
			else if ($punchType == 'lunchout')
				$s = "UPDATE timeclock SET startlunch = '$time' WHERE id = '$r[id]'";
			else if ($punchType == 'lunchback')
				$s = "UPDATE timeclock SET endlunch = '$time' WHERE id = '$r[id]'";
			break;
		}
	}
	$q = $conn->query($s);
	if (isset($_POST['tmplogin'])) {
		?><script>window.location.href = "punch.php?notify&id=<?PHP echo $empID; ?>";</script><?PHP // prevent login popup interference
	} else {
		?><script>window.location.href = "punch.php";</script><?PHP
	}
	if (!$q){
    echo 'QUERY ERROR: '.mysql_error().'<br>';
	}
}

if (isset($_POST['punchtype'])) // logged in and punched
	setPunch($_POST['punchtype']);
if(isset($_POST['tmplogin'])) // not logged in and punched
	punchToggle();

function getPunchTime() {
	global $conn, $empID;
	if (!isset($_COOKIE['empid'])) return;
	$g = "SELECT starttime FROM timeclock WHERE empid = '$empID' ORDER BY id DESC";
	$q = $conn->query($g);
	while($r = $q->fetch_assoc()) {
		$starttime = $r['starttime'];
		break;
	}
	return $starttime;
}

function checkLunch() {
	global $conn, $empID;
	$g = "SELECT endlunch FROM timeclock WHERE empid = '$empID' ORDER BY id DESC";
	$q = $conn->query($g);
	while($r = $q->fetch_assoc()) {
		$endlunch = $r['endlunch'];
		break;
	}
	return $endlunch;
}

// Punch without login
if ( $empID <= 0 || isset($_POST['tmplogin'])) { ?>
	<div style="text-align:center;">
	<form method="post" action="punch.php">

	<div style="width:100%;height:560px;">
	<div style="position:relative;text-align:center;margin-left:auto;margin-right:auto;width:380px;margin-bottom:20px;clear:both;">
		<input type="text" name="empid" id="empid" style="font-size:56px;letter-spacing:12px;width:97%;margin-bottom:20px;text-align:center;" autofocus autocomplete="off">
		<div style="clear:both;">
			<a id="key1" href="javascript:;"><div class="keyBtn">1</div></a><a id="key2" href="javascript:;"><div class="keyBtn">2</div></a><a id="key3" href="javascript:;"><div class="keyBtn" id="key3">3</div></a>
		</div>
		<div style="clear:both;">
			<a id="key4" href="javascript:;"><div class="keyBtn" id="key4">4</div></a><a id="key5" href="javascript:;"><div class="keyBtn">5</div></a><a id="key6" href="javascript:;"><div class="keyBtn">6</div></a>
		</div>
		<div style="clear:both;">
			<a id="key7" href="javascript:;"><div class="keyBtn">7</div></a><a id="key8" href="javascript:;"><div class="keyBtn">8</div></a><a id="key9" href="javascript:;"><div class="keyBtn">9</div></a>
		</div>
		<div style="clear:both;">
			<a id="key0" href="javascript:;"><div class="keyBtn">0</div></a><a id="bkspc" href="javascript:;"><div class="keyBtn" style="padding:30px 60px 30px 60px !important;">BKSPC</div></a>
		</div>
	</div>
	</div>

	<div style="clear:both;width:100%;margin-top:20px;">
		<button name="tmplogin" class="btn btn-lg btn-primary" style="padding:30px;font-size:45px;width:374px;" type="submit" id="enterbtn">ENTER</button>
	</div>

	</form>
	<div style="height:90px;margin-top:10px;">
	<?PHP
	if (isset($_GET['notify'])) {
		?>
		<div id="punch_notification" class="alert alert-success" style="font-size:32px;">
			<?PHP
			$q = $conn->query("SELECT * FROM employee WHERE number = '$_GET[id]'");
			$r = $q->fetch_assoc();
			echo strtoupper(getPunchText($_GET['id'])).' '.$r['lastname'].', '.$r['firstname'];
			?>
		</div>
	<?PHP } ?>
	</div>
</div>
<?PHP } else {
	// view punch information
	$q = $conn->query("SELECT * FROM employee WHERE number = '$empID'");
	$r = $q->fetch_assoc(); ?>
	<div style="text-align:center;">
    <h2>
	<div style="height:80px;">
		<p><a href="./punch?new">Click Here To Change Employee</a></p>
		<?PHP echo $r['lastname'].', '.$r['firstname'].' #'.$empID; ?>
	</div>

		<div id="msg2" style="color:#069;margin:20px 0px 20px;"><?PHP echo date("h:i:s a", time()); ?></div>

    <div style="height:40px;">Currently <?PHP echo getPunchText($empID); ?>.</div>

    </h2>
    </div>
    <div style="text-align:center;margin-top:20px;margin-bottom:40px;">
    <form method="post">
    <?PHP if (checkLunch() > 0) { ?>
    <p><input type="submit" value="" style="width:323px;" disabled="disabled" class="btn btn-lg" /></p>
    <?PHP } else if (getPunchText($empID) == 'out to lunch') { ?>
    <p><button type="submit" value="lunchback" class="btn btn-lg btn-primary" style="width:323px;" name="punchtype">BACK FROM LUNCH</button></p>
    <?PHP } else { ?>
    <p><button type="submit" value="lunchout" style="width:323px;" <?PHP if (getPunchText($empID) == 'punched out') { ?>disabled="disabled" class="btn btn-lg"<?PHP } else { ?>class="btn btn-lg btn-warning"<?PHP } ?> name="punchtype">LEAVING BUILDING FOR LUNCH</button></p>
    <?PHP } ?>
	<button type="submit" value="punchin" <?PHP if (getPunchText($empID) != 'punched out') { ?> class="btn btn-lg" disabled="disabled"<?PHP } else { ?> class="btn btn-lg btn-success" <?PHP } ?> name="punchtype">PUNCH IN</button>
    <button type="submit" value="punchout" <?PHP if (getPunchText($empID) != 'punched in') { ?> class="btn btn-lg" disabled="disabled"<?PHP } else { ?> class="btn btn-lg btn-danger"<?PHP } ?> name="punchtype">PUNCH OUT</button>
    </form>
    <?PHP if (isAdmin()==true) { ?><h1>[<a href="hours.php">Edit Punches</a>]</h1><?PHP } ?>
    </div>
<?PHP }
if (date("m/d/y") == date("m/d/y",getPunchTime()) || getPunchStatus($empID) == "punched in") { ?>
<div style="text-align:center;margin-top:20px;margin-bottom:40px;margin-left:auto;margin-right:auto;width:400px;"><p style="font-weight:bold;margin-bottom:10px;">today's punch logs</p>
<table width="400" border="1">
  <tbody>
    <tr>
      <td style="width:100px;font-weight:bold;background-color:#ccc;">Start</td>
      <td style="width:100px;font-weight:bold;background-color:#ccc;">End</td>
      <td style="width:200px;font-weight:bold;background-color:#ccc;">Lunch</td>
    </tr>
    <?PHP
	$q = $conn->query("SELECT * FROM timeclock WHERE empid = '$empID' AND duration = '0' ORDER BY starttime DESC");
	$duration = 0;
	while($r = $q->fetch_assoc()) {
	?>
    <tr>
      <td><?PHP echo date("h:i:s a", $r['starttime']); ?></td>
      <td><?PHP if ($r['duration'] != 0) echo date("h:i:s a", $r['endtime']); ?></td>
      <td><?PHP if ($r['startlunch'] != 0) echo date("h:i:s a", $r['startlunch']); if ($r['endlunch'] != 0) echo " - ".date("h:i:s a", $r['endlunch']); ?></td>
    </tr>
    <?PHP $duration += $r['duration'];
    }
	$q = $conn->query("SELECT * FROM timeclock WHERE empid = '$empID' AND startdate = '$date' AND duration > '0' ORDER BY starttime DESC");
	while($r = $q->fetch_assoc()) {
	?>
    <tr>
      <td><?PHP echo date("h:i:s a", $r['starttime']); ?></td>
      <td><?PHP if ($r['duration'] != 0) echo date("h:i:s a", $r['endtime']); ?></td>
      <td><?PHP if ($r['startlunch'] != 0) echo date("h:i:s a", $r['startlunch']); if ($r['endlunch'] != 0) echo " - ".date("h:i:s a", $r['endlunch']); ?></td>
    </tr>
    <?PHP $duration += $r['duration']; ?>
    <?PHP } ?>
  </tbody>
</table>
<div style="text-align:right;">Total HH:MM:SS worked <?PHP echo gmdate("H:i:s", $duration); ?></div>
</div>
<?PHP } ?>
</div></div>
	<br class="clearfloat" />
	<!-- footer -->
</div>

<script>
<?PHP for ($i = 0; $i < 10; $i++) { ?>
$('#key<?PHP echo $i; ?>').click(function () {
	$('#empid').val($('#empid').val() + '<?PHP echo $i; ?>');
});
<?php } ?>
$('#bkspc').click(function () {
	$('#empid').val($('#empid').val().slice(0, -1));
});
$("#empid").on('keyup', function (e) {
    if (e.keyCode == 13) {
        $("#enterbtn").click();
    }
});
	var $div2 = $("#punch_notification");
    setTimeout(function() {
        $div2.fadeOut();
    }, 3000);
</script>
</body>
</html>
