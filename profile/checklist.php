<?php

require_once('../fpdf/rotation.php');

class PDF extends PDF_Rotate {
	function RotatedText($x,$y,$txt,$angle) {
		//Text rotated around its origin
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}
}

$pdf = new PDF('L','in','Letter');
$pdf->AddPage();
$pdf->SetMargins(.5,0,0);
$pdf->SetFillColor(230,230,230);

$pdf->SetFont('Arial','',18);
$pdf->text(.5,.7,$_SESSION['course_name']);

$pdf->SetFont('Arial','',14);
$pdf->text(.5,1.1,'Facilitator: ' . $_SESSION['facilitator']);
$pdf->text(.5,2.3,'Roster');

$pdf->SetFont('Arial','',11);

$total_pages = $_SESSION['course']['qty'] + 1;
$current_page = $pdf->PageNo();
$pdf->text(9.5,.5,'page ' . $current_page .' of ' . $total_pages);

// COURSE INFO **************************************

$pdf->SetXY(.46,1.35);
$pdf->cell(1.2,.2,'Course Number:');
$pdf->cell(.8,.2,$_SESSION['course_num']);

$pdf->cell(1,.2,'Course Start:');
$pdf->cell(1.9,.2,$_SESSION['course_start_date'],'','1');

$pdf->SetX(.46);
$pdf->cell(1.2,.2,'Course Code:');
$pdf->cell(.8,.2,$_SESSION['course_code']);

$pdf->cell(1,.2,'Course End:');
$pdf->cell(1.9,.2,$_SESSION['course_end_date']);

// END COURSE INFO ***********************************

// TABLE ********************************************

$pdf->SetXY(.5,2.4);

// HEADINGS
$pdf->SetFont('Arial','B',11); //bold font
$pdf->cell(.25,.4,'#','LTB','0','C');
$pdf->cell(1.6,.4,'Last Name','LTB');
$pdf->cell(1.2,.4,'First Name','LTB');
$pdf->cell(2.7,.4,'Email','LTB');
$pdf->cell(.5,.4,'Cert.','LTB');
$pdf->cell(.5,.4,'Score','LTB');
$pdf->cell(.5,.4,'Credit','LTB');
$pdf->cell(2.7,.4,'Other Notes','LTRB',1);

$pdf->SetFont('Arial','',11); //normal font

for ($student_num=1; $student_num <=20; $student_num++) {
	if (isset($_SESSION['student'][$student_num]['last_name'])) //check last name to determine if we should enter a number
		$pdf->cell(.25,.2,$student_num,'LTB');
	else
		$pdf->cell(.25,.2,'','LTB');
	
	if (isset($_SESSION['student'][$student_num]['last_name']))
		$pdf->cell(1.6,.2,$_SESSION['student'][$student_num]['last_name'],'LTB');
	else
		$pdf->cell(1.6,.2,'','LTB');
	
	if (isset($_SESSION['student'][$student_num]['first_name']))	
		$pdf->cell(1.2,.2,$_SESSION['student'][$student_num]['first_name'],'LTB');
	else
		$pdf->cell(1.2,.2,'','LTB');
		
	if (isset($_SESSION['student'][$student_num]['email']))	
		$pdf->cell(2.7,.2,$_SESSION['student'][$student_num]['email'],'LTB');
	else
		$pdf->cell(2.7,.2,'','LTB');
	
	if (isset($_SESSION['student'][$student_num]['certificate_status']))	
		$pdf->cell(.5,.2,$_SESSION['student'][$student_num]['certificate_status'],'LTB',0,'C');
	else
		$pdf->cell(.5,.2,'','LTB');
	
	if (isset($_SESSION['student'][$student_num]['scoring_status']))	
		$pdf->cell(.5,.2,$_SESSION['student'][$student_num]['scoring_status'],'LTB',0,'C');
	else
		$pdf->cell(.5,.2,'','LTB');
	
	if (isset($_SESSION['student'][$student_num]['registration_type']))	
		$pdf->cell(.5,.2,$_SESSION['student'][$student_num]['registration_type'],'LTB',0,'C');
	else
		$pdf->cell(.5,.2,'','LTB');
	
	$pdf->cell(2.7,.2,'','LTRB',1); // other notes field always blank
} //end for

// END TABLE ****************************************

$pdf->SetXY(6,7);

// BOTTOM TEXT
$pdf->SetFont('Arial','B',9); //bold font
$pdf->MultiCell(.5,.15,"Cert:\nScore:\nCredit:");
$pdf->SetXY(6.45,7);
$pdf->SetFont('Arial','',9); //normal font
$pdf->MultiCell(4,.15,
				"Indicates if student is in a certificate program that requires this class.\nIndicates whether or not student wishes to be graded for the class.\nType of credits awarded upon successful completion of class.\nCEU = Continuing Education Units. UGC = Undergraduate Credit.");









// BEGIN COURSE SESSIONS PAGE(S) ************************

for ($course_session_num = 1; $course_session_num <= $_SESSION['course']['qty']; $course_session_num++) {

	$pdf->AddPage();
	
	$pdf->SetFont('Arial','',18);
	$pdf->text(.5,.7,$_SESSION['course_name']);
	
	$pdf->SetFont('Arial','',14);
	$pdf->text(.5,1.1,'Facilitator: ' . $_SESSION['facilitator']);
	$pdf->text(.5,3.2,'Roster');
	
	$pdf->SetFont('Arial','',11);
	
	$current_page = $pdf->PageNo();
	$pdf->text(9.5,.5,'page ' . $current_page .' of ' . $total_pages);
	
	
	
	
	// COURSE INFO **************************************
	
	$pdf->SetXY(.46,1.35);
	$pdf->cell(1.2,.2,'Course Number:');
	$pdf->cell(.8,.2,$_SESSION['course_num']);
	
	$pdf->cell(1,.2,'Course Start:');
	$pdf->cell(1.9,.2,$_SESSION['course_start_date']);
	$pdf->SetFont('Arial','B',11); //bold font
	$pdf->cell(.65,.2,'Session:');
	$pdf->cell(.2,.2,$course_session_num,'','1');
	
	$pdf->SetX(.46);
	$pdf->SetFont('Arial','',11); //normal font
	$pdf->cell(1.2,.2,'Course Code:');
	$pdf->cell(.8,.2,$_SESSION['course_code']);
	
	$pdf->cell(1,.2,'Course End:');
	$pdf->cell(1.9,.2,$_SESSION['course_end_date']);
	$pdf->SetFont('Arial','B',11); //bold font
	$pdf->cell(5,.2,$_SESSION['course'][$course_session_num]['session_name']);
	
	// END COURSE INFO ***********************************
	
	
	
	// TABLE ********************************************
	
	//60 degree angle boxes
	$x1 = 3.55;
	$x2 = 4.3;
	$x_offset = .6;
	
	for ($i=1; $i<=11; $i++) {
	$pdf->Line($x1,3.3, $x2,2);
	$x1 += $x_offset;
	$x2 += $x_offset;
	}
	
	$pdf->Line(4.3,2, 10.3,2);
	// end angls boxes
	
	
	// ANGLED TEXT
	$pdf->SetFont('Arial','B',10); //bold font
	
	$rotate_x = 3.9;
	$rotate_x_offset = .6;
	
	for ($i=1; $i<=10; $i++) {
		if (isset($_SESSION['course'][$course_session_num][$i]['task_name']))
			$pdf->RotatedText($rotate_x,3.25,$_SESSION['course'][$course_session_num][$i]['task_name'],60);
		$rotate_x += $rotate_x_offset;
	}
	// END ANGLED TEXT
	
	
	
	
	
	
	$pdf->SetXY(.5,3.3);
	
	// HEADINGS
	$pdf->SetFont('Arial','B',11); //bold font
	$pdf->cell(.25,.2,'#','LTB','0','C');
	$pdf->cell(1.6,.2,'Last Name','LTB');
	$pdf->cell(1.2,.2,'First Name','LTB');
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTB',0,'',true);
	$pdf->cell(.6,.2,'','LTRB',1,'',true);
	
	//SAMPLE DATA
	$pdf->cell(.25,.2,'','LTB');
	$pdf->cell(1.6,.2,'Example','LTB');
	$pdf->cell(1.2,.2,'Eve','LTB');
	$pdf->cell(.6,.2,'X','LTB','0','C');
	$pdf->cell(.6,.2,'X X X','LTB','0','C');
	$pdf->cell(.6,.2,'X X X','LTB','0','C');
	$pdf->cell(.6,.2,'X X','LTB','0','C');
	$pdf->cell(.6,.2,'X','LTB','0','C');
	$pdf->cell(.6,.2,'','LTB');
	$pdf->cell(.6,.2,'','LTB');
	$pdf->cell(.6,.2,'','LTB');
	$pdf->cell(.6,.2,'','LTB');
	$pdf->cell(.6,.2,'','LTRB',1);
	
	
	$pdf->SetFont('Arial','',11); //normal font
	
	for ($student_num=1; $student_num <=20; $student_num++) {
		if (isset($_SESSION['student'][$student_num]['last_name'])) //check last name to determine if we should enter a number
			$pdf->cell(.25,.2,$student_num,'LTB');
		else
			$pdf->cell(.25,.2,'','LTB');
		
		if (isset($_SESSION['student'][$student_num]['last_name']))
			$pdf->cell(1.6,.2,$_SESSION['student'][$student_num]['last_name'],'LTB');
		else
			$pdf->cell(1.6,.2,'','LTB');
		
		if (isset($_SESSION['student'][$student_num]['first_name']))	
			$pdf->cell(1.2,.2,$_SESSION['student'][$student_num]['first_name'],'LTB');
		else
			$pdf->cell(1.2,.2,'','LTB');
	
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTB');
		$pdf->cell(.6,.2,'','LTRB',1);

} //end for

// END TABLE ****************************************

} // END SESSION TABLES LOOP







$pdf->Output();
?>