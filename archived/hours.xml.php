<?PHP
header('Content-Type: application/xml; charset=UTF-8');
$writer = new XMLWriter(); 
$writer->openURI('php://output');
$writer->setIndent(true); 
$writer->startDocument('1.0', 'utf-8');

$writer->startElement('XML');
include("functions.php");

$vac = 0;
$tHours = array();
for ($c=1;$c<=12;$c++){ //months
	for ($i=1;$i<=31;$i++){ //days
	if (!isset($_GET['y']))
		break;
		if (cal_days_in_month(CAL_GREGORIAN, $c, $_GET['y']) >= $i) { //only days within the range of days per month
			if (isset($_GET['sDate']) && isset($_GET['eDate'])){ //restrict the data queried
				if (strtotime($_GET['sDate']) > strtotime("$c/$i/$_GET[y]") || strtotime($_GET['eDate']) < strtotime("$c/$i/$_GET[y]"))
					continue;
			}
			$writer->startElement('calendar'); $writer->writeAttribute('month', $c); $writer->writeAttribute('day', $i);
				$writer->writeElement('worked_hours', secToHours(findPunchInfo($_GET['id'],$_GET['y'].'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i))));
				$writer->writeElement('punchtype', findPunchInfo($_GET['id'],$_GET['y'].'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i),"punch"));
				$writer->writeElement('lunchtype', findPunchInfo($_GET['id'],$_GET['y'].'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i),"lunch"));
				$tHours[date("m/d/Y",strtotime($c.'/'.$i.'/'.$_GET['y']))] = findPunchInfo($_GET['id'],$_GET['y'].'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i));
				if (date("m/d/Y",strtotime($c.'/'.$i.'/'.$_GET['y'])) == findPayDay($c.'/'.$i.'/'.$_GET['y'])){
					$writer->writeElement('payday_hours',totalHours($tHours,findPayDay($c.'/'.$i.'/'.$_GET['y']),$_GET['id']));
					$writer->writeElement('vacation_hours',findVacationHours($tHours,findPayDay($c.'/'.$i.'/'.$_GET['y']),$_GET['id']));
				} else {
					$writer->writeElement('payday_hours','0');
					$writer->writeElement('vacation_days','0');
				}
				if (findPunchInfo($_GET['id'],$_GET['y'].'-'.sprintf("%02s",$c).'-'.sprintf("%02s",$i),"punch")==2) $vac++; //deduct vacation
			$writer->endElement();
		}
	}
}

if (isset($_GET['id'])) {
	list($firstname,$lastname,$emptype,$hiredate) = EmployeeInfo($_GET['id']);
	list($currentvacation,$totalvacation) = findVacation($vac,$_GET['id']);
}
if ($emptype == 0){ //part time
	$currentvacation = 0;
	$totalvacation = 0;
}
$writer->startElement('employee_info'); 
	if (isset($_GET['id']))
		$writer->writeElement('Number', $_GET['id']);
	else
		$writer->writeElement('Number', '0');
	$writer->writeElement('FirstName', $firstname);
	$writer->writeElement('LastName', $lastname);
	$writer->writeElement('EmployeeType', $emptype);
	$writer->writeElement('HireDate', $hiredate);
	$writer->writeElement('CurrentVacation', $currentvacation);
	$writer->writeElement('TotalVacation', $totalvacation);
$writer->endElement();

$writer->endElement();
$writer->endDocument();