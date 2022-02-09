<?PHP

function buildCalendarVacations($empID, $y)
{
	$vac = 0;
	$tHours = array();
	for ($c = 1; $c <= 12; $c++) { //months
		for ($i = 1; $i <= 31; $i++) { //days
			if (cal_days_in_month(CAL_GREGORIAN, $c, $y) >= $i) { //only days within the range of days per month
				if (isset($_GET['sDate']) && isset($_GET['eDate'])) { //restrict the data queried
					if (strtotime($_GET['sDate']) > strtotime("$c/$i/$y") || strtotime($_GET['eDate']) < strtotime("$c/$i/$y"))
						return;
				}
				if (findPunchInfo($_GET['id'], $_GET['y'] . '-' . sprintf("%02s", $c) . '-' . sprintf("%02s", $i), "punch") == 2) $vac++; //deduct vacation
			}
		}
	}
	return $vac;
}

/// c = month
/// i = day
/// y = year
function buildCalendarData($empID, $c, $i, $y)
{
	$vac = 0;
	$tHours = array();
	$payday_hours = 0;
	$vacation_days = 0;
	if (cal_days_in_month(CAL_GREGORIAN, $c, $y) >= $i) { //only days within the range of days per month
		if (isset($_GET['sDate']) && isset($_GET['eDate'])) { //restrict the data queried
			if (strtotime($_GET['sDate']) > strtotime("$c/$i/$y") || strtotime($_GET['eDate']) < strtotime("$c/$i/$y"))
				return;
		}
		$worked_hours = secToHours(findPunchInfo($empID, $_GET['y'] . '-' . sprintf("%02s", $c) . '-' . sprintf("%02s", $i)));
		$punchtype = findPunchInfo($empID, $y . '-' . sprintf("%02s", $c) . '-' . sprintf("%02s", $i), "punch");
		$lunchtype = findPunchInfo($empID, $y . '-' . sprintf("%02s", $c) . '-' . sprintf("%02s", $i), "lunch");
		if (date("m/d/Y", strtotime($c . '/' . $i . '/' . $_GET['y'])) == findPayDay($c . '/' . $i . '/' . $_GET['y'])) {
			$payday_hours = totalHours($tHours, findPayDay($c . '/' . $i . '/' . $_GET['y']), $_GET['id']);
			$vacation_hours = findVacationHours($tHours, findPayDay($c . '/' . $i . '/' . $_GET['y']), $_GET['id']);
		}
		return array($worked_hours, $punchtype, $lunchtype, $payday_hours, $vacation_days);
	}
}

function department($id)
{
	$id = substr($id, 0, 3);
	switch ($id) {
		case 000:
			return "Laser";
		case 110:
			return "Shipping";
		case 120:
			return "Receiving";
		case 130:
			return "Etching";
		case 140:
			return "Ink Jet";
		case 150:
			return "Coating";
		case 210:
			return "Fuji";
		case 310:
			return "Penta";
		case 320:
			return "Ultra";
		case 330:
			return "Compact";
		case 400:
			return "Diamond Periphery";
		case 410:
			return "EasyGrind";
		case 420:
			return "RS-09";
		case 510:
			return "Cheviler";
		case 520:
			return "Manual Grinder";
		case 610:
			return "Packermatic";
		case 620:
			return "OnLine(Blue) Hone";
		case 630:
			return "Palfam";
		case 640:
			return "Witomatic";
		case 650:
			return "Hand Hone";
		case 700:
			return "Diamond Prep";
		case 710:
			return "Wire EDM";
		case 720:
			return "Brazing";
		case 730:
			return "Sandblasting";
		case 740:
			return "Blackening";
		case 810:
			return "Bridgeport";
		case 820:
			return "Lathe";
		case 830:
			return "Sandblaster #2";
		case 850:
			return "Sales";
		case 910:
			return "Comparator";
		case 920:
			return "Inspection";
		case 930:
			return "Inspection";
		case 940:
			return "Inspection";
		case 950:
			return "Inspection";
	}
}

function date_normalizer($d)
{
	if ($d instanceof DateTime)
		$date = $d->getTimestamp();
	else
		$date = $d;

	$date = date("Y-m-d", strtotime($date));
	return $date;
}

function curl_xml($file)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://local.vrwesson.com/' . $file);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

/* CALENDAR STUFF
================================================== */
function EmployeeInfo($empid)
{ //array
	global $conn;
	$q = $conn->query("SELECT * FROM employee WHERE number = '$empid'");
	$r = $q->fetch_assoc();
	return array($r['firstname'], $r['lastname'], $r['type'], $r['hiredate']);
}
function showHolidays($year)
{ //boolean
	global $conn;
	$q = $conn->query("SELECT * FROM holidays WHERE date LIKE '$year-%-%'");
	while ($r = $q->fetch_assoc()) {
		if ($date == $r['date'])
			return true;
	}
	return false;
}
function findHoliday($date)
{ //boolean
	global $conn;
	$q = $conn->query("SELECT * FROM holidays WHERE date LIKE '$date'");
	while ($r = $q->fetch_assoc()) {
		if ($date == $r['date'])
			return true;
	}
	return false;
}
function findVacation($minus, $empid)
{ //array
	global $conn;
	$q = $conn->query("SELECT * FROM employee WHERE number = '$empid'");
	$r = $q->fetch_assoc();

	$birthDate = date("m/d/Y", strtotime($r['hiredate']));
	$birthDate = explode("/", $birthDate);
	$age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y") - $birthDate[2]) - 1) : (date("Y") - $birthDate[2]));

	if ($age >= 7)
		$msg = 15;
	else if ($age >= 2 && $age < 7)
		$msg = 10;
	else if ($age >= 1 && $age < 2)
		$msg = 5;
	else
		$msg = 0;
	$sub = $msg - $minus;

	return array($sub, $msg); //0=vac left,1=total vac
}
function findPayDay($date)
{ //find next pay period
	$date = strtotime($date);
	$knownDate = strtotime('November 7, 2014');
	$diff = $date - $knownDate;
	$weeks = 2 * ceil($diff / (60 * 60 * 24 * 7 * 2));
	return date("m/d/Y", strtotime("$weeks weeks", $knownDate));
}
function setPriorHours($empid)
{ //get prior durations before start of month
	global $tHours, $conn;
	$pDate = findPayDay('1/1/' . $_COOKIE['hoursYear']);

	$sDate = DateTime::createFromFormat('m/d/Y', $pDate);
	$sDate->modify('-2 week');
	$sDate = $sDate->format('m/d/Y');

	$dates = getDates($sDate, $pDate);
	foreach ($dates as $d) {
		$newD = date('Y-m-d', strtotime($d));
		$q = $conn->query("SELECT * FROM timeclock WHERE empid = '$empid' AND startdate = '$newD'");
		$r = $q->fetch_assoc();
		//if (partOrFull() == 1 && $r['duration'] > 28800) //minus lunch
		//	$r['duration'] = $r['duration'] - 1800;
		$tHours[$d] = $r['duration'];
	}
}
function findPunchInfo($empid, $cDate, $data = "hours")
{
	global $conn;
	$q = $conn->query("SELECT * FROM timeclock WHERE empid = '$empid' AND startdate = '$cDate'");
	list($firstname, $lastname, $type, $hiredate) = EmployeeInfo($empid);
	$r = $q->fetch_assoc();
	if ($q->num_rows > 0) {
		if (stristr($data, "hours")) {
			if ($r['lunchtype'] == '0' && $r['punchtype'] != 2 && $type == 1)
				return $r['duration'] - 1800; //minus lunch
			else
				return $r['duration'];
		} else if (stristr($data, "punch"))
			return $r['punchtype'];
		else if (stristr($data, "lunch"))
			return $r['lunchtype'];
	} else
		return 0;
}
function secToHours($dec)
{
	$hours = floor($dec / 3600);
	$minutes = floor(($dec / 60) % 60);
	if ($minutes == 0)
		return $hours;
	else
		return $hours . '.' . sprintf("%02s", $minutes);
}
function twoWeeksAgo($pDate)
{
	$obj_date = DateTime::createFromFormat('m/d/Y', $pDate);

	$sDate = DateTime::createFromFormat('m/d/Y', $pDate);
	$sDate->modify('-2 week');
	return $sDate->format('m/d/Y');
}
function totalHours($array, $pDate, $empid)
{
	$n = 0;

	$obj_date = DateTime::createFromFormat('m/d/Y', $pDate);

	$sDate = DateTime::createFromFormat('m/d/Y', $pDate);
	$sDate->modify('-2 week');

	foreach ($array as $key => $value) {
		if (strtotime($key) > strtotime($sDate->format('m/d/Y')) && strtotime($key) < $obj_date->getTimestamp())
			$n += $value;
	}
	list($firstname, $lastname, $type, $hiredate) = EmployeeInfo($_GET['id']);
	if ($n > 0 && $type == 1) {
		for ($i = strtotime($sDate->format('m/d/Y')); $i <= $obj_date->getTimestamp(); $i = $i + 86400) {
			if (strtotime(date('Y-m-d', $i)) > strtotime($sDate->format('Y-m-d')) && strtotime(date('Y-m-d', $i)) < $obj_date->getTimestamp()) {
				if (findHoliday(date('Y-m-d', $i))) //holiday
					$n += 28800; //8.5 hours (lunch is taken away)
				if (findPunchInfo($_GET['id'], date('Y-m-d', $i), "punch") == '2') //vacation
					$n += 28800; //8 hours
			}
		}
	}

	return secToHours($n);
}
function findVacationHours($array, $pDate, $empid)
{
	$n = 0;

	$obj_date = DateTime::createFromFormat('m/d/Y', $pDate);

	$sDate = DateTime::createFromFormat('m/d/Y', $pDate);
	$sDate->modify('-2 week');

	foreach ($array as $key => $value) {
		if (strtotime($key) > strtotime($sDate->format('m/d/Y')) && strtotime($key) < $obj_date->getTimestamp())
			$n += 0;
	}
	list($firstname, $lastname, $type, $hiredate) = EmployeeInfo($_GET['id']);
	if ($type == 1) {
		for ($i = strtotime($sDate->format('m/d/Y')); $i <= $obj_date->getTimestamp(); $i = $i + 86400) {
			if (strtotime(date('Y-m-d', $i)) > strtotime($sDate->format('Y-m-d')) && strtotime(date('Y-m-d', $i)) < $obj_date->getTimestamp()) {
				if (findPunchInfo($_GET['id'], date('Y-m-d', $i), "punch") == '2') //vacation
					$n += 28800; //8 hours
			}
		}
	}

	return secToHours($n);
}
function difference($hours, $ot = 0)
{
	$diff = $hours - 80;
	if ($ot == 1) {
		if ($diff > 0)
			return $diff;
		else
			return 0;
	} else {
		if ($hours >= 80)
			return 80;
		else
			return $hours;
	}
}
function specialDay($empid, $date, $type = "text")
{ //type=text,type=color
	if (findHoliday($date)) {
		if ($type == "text")
			return 'HOLIDAY';
		else
			return 'cyan';
	} else if (stristr(date("D", strtotime($date)), "Sat")) {
		if ($type != "text")
			return '#DDD';
	} else if (stristr(date("D", strtotime($date)), "Sun")) {
		if ($type != "text")
			return '#DDD';
	} else if (findPunchInfo($empid, $date, "punch") == "2") {
		if ($type == "text")
			return 'VAC';
		else
			return 'yellow';
	} else if (findPunchInfo($empid, $date, "punch") == "1") {
		if ($type == "text")
			return 'SICK';
		else
			return 'green';
	} else if (findPunchInfo($empid, $date, "hours") < 0) {
		if ($type == "text")
			return 'INCOMPLETE';
		else
			return 'red';
	} else {
		if ($type != "text")
			return 'white';
	}
}

function getPunchText($empID)
{
	global $conn;
	//if (!isset($_COOKIE['empid'])) return;
	$g = "SELECT * FROM timeclock WHERE empid = '$empID' ORDER BY id DESC";
	$q = $conn->query($g);
	while ($r = $q->fetch_assoc()) {
		$duration = $r['duration'];
		$startlunch = $r['startlunch'];
		$endlunch = $r['endlunch'];
		break;
	}
	if ($q->num_rows == 0)
		return 'punched out';
	else if ($duration == 0 && $startlunch > 0 && $endlunch == 0)
		return 'out to lunch';
	else if ($duration == 0)
		return 'punched in';
	else
		return 'punched out';
}

function getPunchStatus($empID)
{
	global $conn;
	//if (!isset($_COOKIE['empid'])) return;
	$g = "SELECT * FROM timeclock WHERE empid = '$empID' ORDER BY id DESC";
	$q = $conn->query($g);
	while ($r = $q->fetch_assoc()) {
		$duration = $r['duration'];
		$startlunch = $r['startlunch'];
		$endlunch = $r['endlunch'];
		break;
	}

	if (!isset($_COOKIE["logged"]) || empty($_COOKIE["logged"])) {
		return '';
	}

	if ($q->num_rows == 0)
		return '<div class="alert alert-danger" style="width:140px;padding-top:0px !important;padding-bottom:0px !important;margin-bottom:0px !important;text-align:center;">punched out</div>';
	else if ($duration == 0 && $startlunch > 0 && $endlunch == 0)
		return '<div class="alert alert-warning" role="alert" style="width:140px;padding-top:0px !important;padding-bottom:0px !important;margin-bottom:0px !important;text-align:center;">out to lunch</div>';
	else if ($duration == 0)
		return '<div class="alert alert-success" role="alert" style="width:140px;padding-top:0px !important;padding-bottom:0px !important;margin-bottom:0px !important;text-align:center;">punched in</div>';
	else
		return '<div class="alert alert-danger" style="width:140px;padding-top:0px !important;padding-bottom:0px !important;margin-bottom:0px !important;text-align:center;">punched out</div>';
}