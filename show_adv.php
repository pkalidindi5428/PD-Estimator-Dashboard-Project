<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta name="generator" content="PSPad editor" />
<meta name="Author" content="Prasant Kalidindi" />
<meta name="Keywords" content="" />
<meta name="Description" content="" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<script type="text/javascript" src="js/jquery-latest.min.js"></script>
<script type="text/javascript" src="js/highcharts.js"></script>
<script type="text/javascript" src="js/jquery.tablePagination.0.5.js"></script>
<link type="text/css" rel="stylesheet" href="ProTable.css">

<?php
// get our libraries
require_once('../login/isLoggedIn.php');
require_once('./lib/inc.dbase.php');
require_once('./lib/inc.config.php');
require_once('./lib/inc.func.php');

// connect to dbase
$conn=mysqli_connect($defined['db_host'], $defined['db_user'], $defined['db_password'], $defined['db_name']);
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: ".mysqli_connect_error();
}

?>

<title>PD_tree_table</title>
<style type="text/css">
ul, li {
	margin: 0;
	padding: 0;
	list-style: none; }
#qaContent {
	width: 900px;
}
#qaContent h3 {
	width: 900px;
	height: 22px;
	text-indent: -9999px;
}
#qaContent ul.accordionPart {
	margin: 10px 10px 50px 30px;
}
#qaContent ul.accordionPart li {
	border-bottom: solid 1px #e3e3e3;
	padding-bottom: 12px;
	margin-top: 12px;
}
#qaContent ul.accordionPart li .qa_title_A {
	padding-left: 1px;
	color: #2E2EFE;
	cursor: pointer;
}
#qaContent ul.accordionPart li .qa_title_B {
	padding-left: 31px;
	color: #1186ec;
	cursor: pointer;
}
#qaContent ul.accordionPart li .qa_title_A_on {
	text-decoration: underline;
}
#qaContent ul.accordionPart li .qa_title_B_on {
	text-decoration: underline;
}
#qaContent ul.accordionPart li .qa_content{
	margin: 6px 0 0;
	padding-left: 61px;
	color: #666;
}

#TableP{
  width: 450px;
  margin: 6px 0 0; }

#tablePagination { 
	font-size: 0.8em; 
	padding: 0px 5px; 
	height: 20px
}
#tablePagination_paginater { 
	margin-left: auto; 
	margin-right: auto;
}
#tablePagination img { 
	padding: 0px 2px; 
}
#tablePagination_perPage { 
	float: left; 
}
#tablePagination_paginater { 
	float: right; 
}

</style>
<script type="text/javascript">
	$(function(){
    $('#qaContent ul.accordionPart li div.qa_title_A').hover(function(){
			$(this).addClass('qa_title_A_on');
		}, function(){
			$(this).removeClass('qa_title_A_on');
		});
    
    //feature hide or unhide
    $('#qaContent ul.accordionPart li div.qa_title_A').click(function(){
			$(this).siblings('div.qa_title_B').slideToggle();
      $(this).siblings('div.qa_content').slideUp();
		}).siblings('div.qa_title_B').hide();
    
    //detail hide or unhide
		$('#qaContent ul.accordionPart li div.qa_title_B').hover(function(){
			$(this).addClass('qa_title_B_on');
		}, function(){
			$(this).removeClass('qa_title_B_on');
		}).click(function(){
    //hide or unhide
			$(this).next('div.qa_content').slideToggle();
		}).siblings('div.qa_content').hide();
 
   
   //show all
		$('#qaContent .qa_showall').click(function(){
      $('#qaContent ul.accordionPart li div.qa_title_B').slideDown();
      $('#qaContent ul.accordionPart li div.qa_content').slideDown();
			return false;
		});
    
    
    //hide all
		$('#qaContent .qa_hideall').click(function(){
      $('#qaContent ul.accordionPart li div.qa_title_B').slideUp();
      $('#qaContent ul.accordionPart li div.qa_content').slideUp();
			return false;
		});
    
    //close
		$('#qaContent .close_qa').click(function(){
			$(this).parents('.qa_content').prev().click();
			return false;
		});
    
   	$('.menuTable').tablePagination({
			rowsPerPage : 10,
			currPage : 1, 
			optionsForRows : [10,15,20],
			topNav : true
		});    
	});
</script>
</head>

<body>

<form>
Filter
  </select>
    <select name='filter'>
        <option value=''>-- Select a filter --</option>n>
        echo "<option value= 1 >"Test Area"</option>";
        echo "<option value= 2 >"Server Type"</option>";
        echo "<option value= 3 >"Supportive OSes"</option>";
        echo "<option value= 4 >"Automation/Manual"</option>";
    </select>
  <input type='submit' value='Apply'>
</form>
<br>


<div id="qaContent">
	<a href="#" class="qa_showall">Expand all</a> | <a href="#" class="qa_hideall">Hide all</a>
</div>

<?php

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . 'Classes/');

/** PHPExcel_IOFactory */
include 'Classes/PHPExcel/IOFactory.php';

if ( $_GET['history'] == '' ) {
//  $inputFileName = 'uploads/report.xlsm';  // File to read
  $privatedir = '/var/www/pd_estimation/uploads/'.$login->getUsername().'/';
  mkdir($privatedir, 0777);
  $files = scandir($privatedir, 1);
  $inputFileName = $privatedir.$files[0];
} else {
  $inputFileName = $privatedir.$_GET['history'];  // File to read
}

try {
	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
} catch(Exception $e) {
	die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
}

echo '<hr />';
echo "<pre>";
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

//$common_path = $sheetData[2][A];
//print("common -->".$common_path."<br>");

//********************************algorithm*******************************************************************
/** collect phase/pd, feature/pd**/
  $phase = "";
  $a=-1;
  $p=-1;
  $c=-1;
if($_GET['filter'] == 1 || $_GET['filter'] == 2 || $_GET['filter'] == 3 || $_GET['filter'] == 4){
    $filter = 1;
}
else
    $filter = 0;
  if($filter) {
      switch ($_GET['filter']) {
          case 1:
              for ($i = 2; $i <= count($sheetData); $i++) {
                  $path = explode("\\", $sheetData[$i][A]);
                  //print("project -->".$path[3]."<br>");
                  //echo strcmp("$phase","$path[5]");
                  //if (array_search($path[3], $project)) {
                  //  print("project -->".$path[3]."<br>");
                  //}

                  if (in_array($path[3], $project)) {
                      //same project
                      $p = array_search($path[3], $project);
                  } else {
                      //another project
                      $p++;
                  }
                  $project[$p] = $path[3];

                  //print("path3,path5 -->".$path[3]." - ".$path[5]."<br>");
                  $pjt = $path[3] . " - " . $path[5];
                  //print("path3,path5 -->".$pjt."<br>");
                  if (strcmp("$phase", "$pjt") != "0") {
                      //another phase; initialization
                      $project[$p] = $path[3];
                      $b = 0;
                      $phase = $pjt;
                      $feature = $path[6];
                      $a++;
                      $c = 0;
                      $d = 0;
                      $plan_phase[$a][0][11] = 0; //init - filter count
                      $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5]; //init - phase
                      $plan_tree_row[$a][$b][0] = $feature; //init - feature
                      $plan_tree_row[$a][$b][1] = $i; //feature start_row
                      $plan_tree_row[$a][$b][2] = 1; //init - # of feature tc
                      $test_id = $sheetData[$i][D];
                      $PD = LookupTestPD($test_id);
                      $plan_tree_row[$a][$b][3] = $PD; //feature PD

                      $plan_phase[$a][$c][1] = $PD;  //phase PD
                      $plan_phase[$a][$c][2] = 0; //init - Blocked
                      $plan_phase[$a][$c][3] = 0; //init - Passed
                      $plan_phase[$a][$c][4] = 0; //init - Failed
                      $plan_phase[$a][$c][5] = 0; //init - No run
                      $plan_phase[$a][$c][6] = 0; //init - In Progress
                      $plan_phase[$a][$c][7] = 0; //init - N/A
                      $plan_phase[$a][$c][8] = 0; //init - Blocked PD
                      $plan_phase[$a][$c][9] = 0; //init - No run + Inprogress PD
                      //$plan_phase[$a][10] =  0; //init - others

                      $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                      $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                      $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                      $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                      $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                      $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                      $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                      $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A
                      $plan_phase[$a][$c][2] = 0; //init - unattempted PDs
                      $plan_phase[$a][$c][10] = $path[6]; //init - filter
                      $plan_phase[$a][$c][12] = 0;
                      //$c++;
                      $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                  } elseif (strcmp("$feature", "$path[6]") != "0") {
                      $found = 0;
                      for ($x = 0; $x < $c; $x++) {
                          if($path[6] == $plan_phase[$a][$x][10]){
                              $project[$p] = $path[3];
                              //same phase, another feature
                              $b++;
                              //$start_row=$i;
                              $feature = $path[6];

                              $plan_tree_row[$a][$b][0] = $feature;
                              $plan_tree_row[$a][$b][1] = $i;
                              $plan_tree_row[$a][$b][2] = 1;
                              $test_id = $sheetData[$i][D];
                              $PD = LookupTestPD($test_id);
                              $plan_tree_row[$a][$b][3] = $PD;
                              $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                              $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                              $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                              $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                              $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                              $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                              $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                              $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A

                              $plan_phase[$a][$x][1] = $plan_phase[$a][$x][1] + $PD;
                              //print("same phase/feature -->".$feature."<br>");

                              $found = 1;
                              $c = $x;
                              $plan_phase[$a][$c][12] = 0;
                          }
                      }
                      if($found != 1){
                          $project[$p] = $path[3];
                          //same phase, another feature
                          $b++;
                          //$start_row=$i;
                          $feature = $path[6];

                          $plan_tree_row[$a][$b][0] = $feature;
                          $plan_tree_row[$a][$b][1] = $i;
                          $plan_tree_row[$a][$b][2] = 1;
                          $test_id = $sheetData[$i][D];
                          $PD = LookupTestPD($test_id);
                          $plan_tree_row[$a][$b][3] = $PD;
                          $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                          $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                          $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                          $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                          $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                          $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                          $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                          $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A

                          $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5];
                          $plan_phase[$a][$c][1] = $plan_phase[$a][$c][1] + $PD;
                          $plan_phase[$a][$c][2] = 0;
                          $plan_phase[$a][$c][3] = 0;
                          $plan_phase[$a][$c][4] = 0;
                          $plan_phase[$a][$c][5] = 0;
                          $plan_phase[$a][$c][6] = 0;
                          $plan_phase[$a][$c][7] = 0;
                          $plan_phase[$a][$c][8] = 0;
                          $plan_phase[$a][$c][9] = 0;
                          $plan_phase[$a][$c][10] = $path[6]; //init - filter]
                          $plan_phase[$a][$c][12] = 0;
                          //$c++;
                          $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                          //print("same phase/feature -->".$feature."<br>");
                      }
                  } else {
                      //same phase, same featrue
                      $found = 0;
                      for ($x = 0; $x < $c; $x++) {
                          if ($path[6] == $plan_phase[$a][$x][10]) {
                              $plan_tree_row[$a][$b][2] = $plan_tree_row[$a][$b][2] + 1;
                              $test_id = $sheetData[$i][D];
                              $PD = LookupTestPD($test_id);
                              $plan_tree_row[$a][$b][3] = $plan_tree_row[$a][$b][3] + $PD;
                              $plan_phase[$a][$x][1] = $plan_phase[$a][$x][1] + $PD;

                              $found = 1;
                              $c = $x;
                              $plan_phase[$a][$c][12] = 0;
                          }
                      }
                      if ($found != 1) {
                          $plan_tree_row[$a][$b][2] = $plan_tree_row[$a][$b][2] + 1;
                          $test_id = $sheetData[$i][D];
                          $PD = LookupTestPD($test_id);
                          $plan_tree_row[$a][$b][3] = $plan_tree_row[$a][$b][3] + $PD;
                          $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5];
                          $plan_phase[$a][$c][1] = $plan_phase[$a][$c][1] + $PD;
                          $plan_phase[$a][$c][2] = 0;
                          $plan_phase[$a][$c][3] = 0;
                          $plan_phase[$a][$c][4] = 0;
                          $plan_phase[$a][$c][5] = 0;
                          $plan_phase[$a][$c][6] = 0;
                          $plan_phase[$a][$c][7] = 0;
                          $plan_phase[$a][$c][8] = 0;
                          $plan_phase[$a][$c][9] = 0;
                          $plan_phase[$a][$c][10] = $path[6]; //init - filter
                          $plan_phase[$a][$c][12] = 0;
                          //$c++;
                          $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                      }
                  }

                  switch ($sheetData[$i][E]) {
                      case Blocked:
                          $plan_phase[$a][$c][2] = $plan_phase[$a][$c][2] + 1;
                          $plan_phase[$a][$c][8] = $plan_phase[$a][$c][8] + $PD;
                          $plan_tree_row[$a][$b][4] = $plan_tree_row[$a][$b][4] + $PD;
                          $plan_tree_row[$a][$b][6] = $plan_tree_row[$a][$b][6] + 1;
                          break;
                      case Passed:
                          $plan_phase[$a][$c][3] = $plan_phase[$a][$c][3] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][7] = $plan_tree_row[$a][$b][7] + 1;
                          break;
                      case Failed:
                          $plan_phase[$a][$c][4] = $plan_phase[$a][$c][4] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][8] = $plan_tree_row[$a][$b][8] + 1;
                          break;
                      case "No Run":
                          $plan_phase[$a][c][5] = $plan_phase[$a][c][5] + 1;
                          $plan_phase[$a][c][9] = $plan_phase[$a][c][9] + $PD;
                          $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                          $plan_tree_row[$a][$b][9] = $plan_tree_row[$a][$b][9] + 1;
                          break;
                      case "Not Completed":
                          $plan_phase[$a][c][6] = $plan_phase[$a][c][6] + 1;
                          $plan_phase[$a][c][9] = $plan_phase[$a][c][9] + $PD;
                          $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                          $plan_tree_row[$a][$b][10] = $plan_tree_row[$a][$b][10] + 1;
                          break;
                      case "N/A":
                          $plan_phase[$a][c][7] = $plan_phase[$a][c][7] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][11] = $plan_tree_row[$a][$b][11] + 1;
                          break;
                  }
                  $c = $plan_phase[$a][0][11];

              }
              break;
          case 2:
              break;
          case 3:
              break;
          case 4:
              for ($i = 2; $i <= count($sheetData); $i++) {
                  $path = explode("\\", $sheetData[$i][A]);
                  //print("project -->".$path[3]."<br>");
                  //echo strcmp("$phase","$path[5]");
                  //if (array_search($path[3], $project)) {
                  //  print("project -->".$path[3]."<br>");
                  //}

                  if (in_array($path[3], $project)) {
                      //same project
                      $p = array_search($path[3], $project);
                  } else {
                      //another project
                      $p++;
                  }
                  $project[$p] = $path[3];

                  //print("path3,path5 -->".$path[3]." - ".$path[5]."<br>");
                  $pjt = $path[3] . " - " . $path[5];
                  //print("path3,path5 -->".$pjt."<br>");
                  if (strcmp("$phase", "$pjt") != "0") {
                      //another phase; initialization
                      $project[$p] = $path[3];
                      $b = 0;
                      $phase = $pjt;
                      $feature = $path[6];
                      $a++;
                      $c = 0;
                      $plan_phase[$a][0][11] = 0; //init - filter count
                      $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5]; //init - phase
                      $plan_tree_row[$a][$b][0] = $feature; //init - feature
                      $plan_tree_row[$a][$b][1] = $i; //feature start_row
                      $plan_tree_row[$a][$b][2] = 1; //init - # of feature tc
                      $test_id = $sheetData[$i][D];
                      $PD = LookupTestPD($test_id);
                      $plan_tree_row[$a][$b][3] = $PD; //feature PD

                      $plan_phase[$a][$c][1] = $PD;  //phase PD
                      $plan_phase[$a][$c][2] = 0; //init - Blocked
                      $plan_phase[$a][$c][3] = 0; //init - Passed
                      $plan_phase[$a][$c][4] = 0; //init - Failed
                      $plan_phase[$a][$c][5] = 0; //init - No run
                      $plan_phase[$a][$c][6] = 0; //init - In Progress
                      $plan_phase[$a][$c][7] = 0; //init - N/A
                      $plan_phase[$a][$c][8] = 0; //init - Blocked PD
                      $plan_phase[$a][$c][9] = 0; //init - No run + Inprogress PD
                      //$plan_phase[$a][10] =  0; //init - others

                      $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                      $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                      $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                      $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                      $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                      $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                      $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                      $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A
                      $plan_phase[$a][$c][2] = 0; //init - unattempted PDs
                      $plan_phase[$a][$c][10] = $path[6]; //init - filter
                      $plan_phase[$a][$c][12] = 0;
                      //$c++;
                      $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                  } elseif (strcmp("$feature", "$path[6]") != "0") {
                      $found = 0;
                      for ($x = 0; $x < $c; $x++) {
                          if($path[6] == $plan_phase[$a][$x][10]){
                              $project[$p] = $path[3];
                              //same phase, another feature
                              $b++;
                              //$start_row=$i;
                              $feature = $path[6];

                              $plan_tree_row[$a][$b][0] = $feature;
                              $plan_tree_row[$a][$b][1] = $i;
                              $plan_tree_row[$a][$b][2] = 1;
                              $test_id = $sheetData[$i][D];
                              $PD = LookupTestPD($test_id);
                              $plan_tree_row[$a][$b][3] = $PD;
                              $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                              $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                              $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                              $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                              $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                              $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                              $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                              $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A

                              $plan_phase[$a][$x][1] = $plan_phase[$a][$x][1] + $PD;
                              //print("same phase/feature -->".$feature."<br>");

                              $found = 1;
                              $c = $x;
                              $plan_phase[$a][$c][12] = 0;
                          }
                      }
                      if($found != 1){
                          $project[$p] = $path[3];
                          //same phase, another feature
                          $b++;
                          //$start_row=$i;
                          $feature = $path[6];

                          $plan_tree_row[$a][$b][0] = $feature;
                          $plan_tree_row[$a][$b][1] = $i;
                          $plan_tree_row[$a][$b][2] = 1;
                          $test_id = $sheetData[$i][D];
                          $PD = LookupTestPD($test_id);
                          $plan_tree_row[$a][$b][3] = $PD;
                          $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                          $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                          $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                          $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                          $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                          $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                          $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                          $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A

                          $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5];
                          $plan_phase[$a][$c][1] = $plan_phase[$a][$c][1] + $PD;
                          $plan_phase[$a][$c][2] = 0;
                          $plan_phase[$a][$c][3] = 0;
                          $plan_phase[$a][$c][4] = 0;
                          $plan_phase[$a][$c][5] = 0;
                          $plan_phase[$a][$c][6] = 0;
                          $plan_phase[$a][$c][7] = 0;
                          $plan_phase[$a][$c][8] = 0;
                          $plan_phase[$a][$c][9] = 0;
                          $plan_phase[$a][$c][10] = $path[6]; //init - filter]
                          $plan_phase[$a][$c][12] = 0;
                          //$c++;
                          $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                          //print("same phase/feature -->".$feature."<br>");
                      }
                  } else {
                      //same phase, same featrue
                      $found = 0;
                      for ($x = 0; $x < $c; $x++) {
                          if ($path[6] == $plan_phase[$a][$x][10]) {
                              $plan_tree_row[$a][$b][2] = $plan_tree_row[$a][$b][2] + 1;
                              $test_id = $sheetData[$i][D];
                              $PD = LookupTestPD($test_id);
                              $plan_tree_row[$a][$b][3] = $plan_tree_row[$a][$b][3] + $PD;
                              $plan_phase[$a][$x][1] = $plan_phase[$a][$x][1] + $PD;

                              $found = 1;
                              $c = $x;
                              $plan_phase[$a][$c][12] = 0;
                          }
                      }
                      if ($found != 1) {
                          $plan_tree_row[$a][$b][2] = $plan_tree_row[$a][$b][2] + 1;
                          $test_id = $sheetData[$i][D];
                          $PD = LookupTestPD($test_id);
                          $plan_tree_row[$a][$b][3] = $plan_tree_row[$a][$b][3] + $PD;
                          $plan_phase[$a][$c][0] = $path[3] . " - " . $path[5];
                          $plan_phase[$a][$c][1] = $plan_phase[$a][$c][1] + $PD;
                          $plan_phase[$a][$c][2] = 0;
                          $plan_phase[$a][$c][3] = 0;
                          $plan_phase[$a][$c][4] = 0;
                          $plan_phase[$a][$c][5] = 0;
                          $plan_phase[$a][$c][6] = 0;
                          $plan_phase[$a][$c][7] = 0;
                          $plan_phase[$a][$c][8] = 0;
                          $plan_phase[$a][$c][9] = 0;
                          $plan_phase[$a][$c][10] = $path[6]; //init - filter
                          $plan_phase[$a][$c][12] = 0;
                          //$c++;
                          $plan_phase[$a][0][11] = $plan_phase[$a][0][11] + 1;
                      }
                  }

                  switch ($sheetData[$i][E]) {
                      case Blocked:
                          $plan_phase[$a][$c][2] = $plan_phase[$a][$c][2] + 1;
                          $plan_phase[$a][$c][8] = $plan_phase[$a][$c][8] + $PD;
                          $plan_tree_row[$a][$b][4] = $plan_tree_row[$a][$b][4] + $PD;
                          $plan_tree_row[$a][$b][6] = $plan_tree_row[$a][$b][6] + 1;
                          break;
                      case Passed:
                          $plan_phase[$a][$c][3] = $plan_phase[$a][$c][3] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][7] = $plan_tree_row[$a][$b][7] + 1;
                          break;
                      case Failed:
                          $plan_phase[$a][$c][4] = $plan_phase[$a][$c][4] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][8] = $plan_tree_row[$a][$b][8] + 1;
                          break;
                      case "No Run":
                          $plan_phase[$a][c][5] = $plan_phase[$a][c][5] + 1;
                          $plan_phase[$a][c][9] = $plan_phase[$a][c][9] + $PD;
                          $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                          $plan_tree_row[$a][$b][9] = $plan_tree_row[$a][$b][9] + 1;
                          break;
                      case "Not Completed":
                          $plan_phase[$a][c][6] = $plan_phase[$a][c][6] + 1;
                          $plan_phase[$a][c][9] = $plan_phase[$a][c][9] + $PD;
                          $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                          $plan_tree_row[$a][$b][10] = $plan_tree_row[$a][$b][10] + 1;
                          break;
                      case "N/A":
                          $plan_phase[$a][c][7] = $plan_phase[$a][c][7] + 1;
                          //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                          $plan_tree_row[$a][$b][11] = $plan_tree_row[$a][$b][11] + 1;
                          break;
                  }
                  $c = $plan_phase[$a][0][11];

              }
              break;
      }
  }
  else{
          for ($i = 2; $i <= count($sheetData); $i++) {
              $path = explode("\\", $sheetData[$i][A]);
              //print("project -->".$path[3]."<br>");
              //echo strcmp("$phase","$path[5]");
              //if (array_search($path[3], $project)) {
              //  print("project -->".$path[3]."<br>");
              //}

              if (in_array($path[3], $project)) {
                  //same project
                  $p = array_search($path[3], $project);
              } else {
                  //another project
                  $p++;
              }
              $project[$p] = $path[3];

              //print("path3,path5 -->".$path[3]." - ".$path[5]."<br>");
              $pjt = $path[3] . " - " . $path[5];
              //print("path3,path5 -->".$pjt."<br>");
              if (strcmp("$phase", "$pjt") != "0") {
                  //another phase; initialization
                  $b = 0;
                  $phase = $pjt;
                  $feature = $path[6];
                  $a++;
                  $plan_phase[$a][0] = $phase; //init - phase
                  $plan_tree_row[$a][$b][0] = $feature; //init - feature
                  $plan_tree_row[$a][$b][1] = $i; //feature start_row
                  $plan_tree_row[$a][$b][2] = 1; //init - # of feature tc
                  $test_id = $sheetData[$i][D];
                  $PD = LookupTestPD($test_id);
                  $plan_tree_row[$a][$b][3] = $PD; //feature PD

                  $plan_phase[$a][1] = $PD;  //phase PD
                  $plan_phase[$a][2] = 0; //init - Blocked
                  $plan_phase[$a][3] = 0; //init - Passed
                  $plan_phase[$a][4] = 0; //init - Failed
                  $plan_phase[$a][5] = 0; //init - No run
                  $plan_phase[$a][6] = 0; //init - In Progress
                  $plan_phase[$a][7] = 0; //init - N/A
                  $plan_phase[$a][8] = 0; //init - Blocked PD
                  $plan_phase[$a][9] = 0; //init - No run + Inprogress PD
                  $plan_phase[$a][21] = $path[3];
                  //$plan_phase[$a][10] =  0; //init - others

                  $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                  $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                  $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                  $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                  $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                  $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                  $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                  $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A
                  $plan_phase[$a][2] = 0; //init - unattempted PDs
              } elseif (strcmp("$feature", "$path[6]") != "0") {
                  //same phase, another feature
                  $b++;
                  //$start_row=$i;
                  $feature = $path[6];

                  $plan_tree_row[$a][$b][0] = $feature;
                  $plan_tree_row[$a][$b][1] = $i;
                  $plan_tree_row[$a][$b][2] = 1;
                  $test_id = $sheetData[$i][D];
                  $PD = LookupTestPD($test_id);
                  $plan_tree_row[$a][$b][3] = $PD;
                  $plan_tree_row[$a][$b][4] = 0;  //init - [feature] Blocked PD
                  $plan_tree_row[$a][$b][5] = 0;  //init - [feature] No run + Inprogress PD
                  $plan_tree_row[$a][$b][6] = 0;  //init - [feature] - Blocked
                  $plan_tree_row[$a][$b][7] = 0;  //init - [feature] - Passed
                  $plan_tree_row[$a][$b][8] = 0;  //init - [feature] - Failed
                  $plan_tree_row[$a][$b][9] = 0;  //init - [feature] - No run
                  $plan_tree_row[$a][$b][10] = 0; //init - [feature] - In Pragress
                  $plan_tree_row[$a][$b][11] = 0; //init - [feature] - N/A

                  $plan_phase[$a][1] = $plan_phase[$a][1] + $PD;
                  $plan_phase[$a][21] = $path[3];
                  //print("same phase/feature -->".$feature."<br>");

              } else {
                  //same phase, same featrue
                  $plan_tree_row[$a][$b][2] = $plan_tree_row[$a][$b][2] + 1;
                  $test_id = $sheetData[$i][D];
                  $PD = LookupTestPD($test_id);
                  $plan_tree_row[$a][$b][3] = $plan_tree_row[$a][$b][3] + $PD;
                  $plan_phase[$a][1] = $plan_phase[$a][1] + $PD;
                  $plan_phase[$a][21] = $path[3];
              }

              switch ($sheetData[$i][E]) {
                  case Blocked:
                      $plan_phase[$a][2] = $plan_phase[$a][2] + 1;
                      $plan_phase[$a][8] = $plan_phase[$a][8] + $PD;
                      $plan_tree_row[$a][$b][4] = $plan_tree_row[$a][$b][4] + $PD;
                      $plan_tree_row[$a][$b][6] = $plan_tree_row[$a][$b][6] + 1;
                      break;
                  case Passed:
                      $plan_phase[$a][3] = $plan_phase[$a][3] + 1;
                      //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                      $plan_tree_row[$a][$b][7] = $plan_tree_row[$a][$b][7] + 1;
                      break;
                  case Failed:
                      $plan_phase[$a][4] = $plan_phase[$a][4] + 1;
                      //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                      $plan_tree_row[$a][$b][8] = $plan_tree_row[$a][$b][8] + 1;
                      break;
                  case "No Run":
                      $plan_phase[$a][5] = $plan_phase[$a][5] + 1;
                      $plan_phase[$a][9] = $plan_phase[$a][9] + $PD;
                      $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                      $plan_tree_row[$a][$b][9] = $plan_tree_row[$a][$b][9] + 1;
                      break;
                  case "Not Completed":
                      $plan_phase[$a][6] = $plan_phase[$a][6] + 1;
                      $plan_phase[$a][9] = $plan_phase[$a][9] + $PD;
                      $plan_tree_row[$a][$b][5] = $plan_tree_row[$a][$b][5] + $PD;
                      $plan_tree_row[$a][$b][10] = $plan_tree_row[$a][$b][10] + 1;
                      break;
                  case "N/A":
                      $plan_phase[$a][7] = $plan_phase[$a][7] + 1;
                      //$plan_phase[$a][10] =  $plan_phase[$a][10] + $PD;
                      $plan_tree_row[$a][$b][11] = $plan_tree_row[$a][$b][11] + 1;
                      break;
              }
          }
      }
//*****************************************************printing table************************************************************************
$total_phase = count($plan_tree_row,0);
$total_project = count($project);
$project_name = $project[0];
for ($i=1; $i<$total_project; $i++){
    $project_name = $project_name." / ".$project[$i];
}

switch ($_GET['filter']) {
    case 1:
        echo '<div class="ProTable" > ';
        echo "
    <table>        
      <tr>
        <td rowspan='2'>Filter</td> 
        <td rowspan='2'>$project_name</td>
        <td colspan='6'>Number of Test Cases</td>
        <td colspan='4'>Status Percentage</td>
        <td colspan='2'>Estimated PDs</td>
      </tr>
      <tr>
        <td>Planned</td>
        <td>Blocked</td>
        <td>Passed</td>
        <td>Failed</td>
        <td>No Run</td>
        <td>In Progress</td>
        <td>% Blocked</td>
        <td>% Attempted</td>
        <td>% Failed</td>
        <td>% Passed</td>
        <td>Blocked PDs</td>
        <td>No Run/In Progess PDs</td>
      </tr>
    ";
        for ($i=0; $i<$total_phase; $i++) {
            for ($j = 0; $j < $plan_phase[$i][0][11]; $j++) {
                if ($plan_phase[$i][$j][12] == 0) {
                    for ($k = 0; $k < $total_phase; $k++) {
                        for ($l = 0; $l < $plan_phase[$i][0][11]; $l++) {
                            if ($plan_phase[$k][$l][12] == 0) {
                                if($plan_phase[$k][$l][10] == $plan_phase[$i][$j][10]) {
                                    $tc_planned = $plan_phase[$k][$l][2] + $plan_phase[$k][$l][3] + $plan_phase[$k][$l][4] + $plan_phase[$k][$l][5] + $plan_phase[$k][$l][6] + $plan_phase[$k][$l][7];
                                    $tc_rate_blocked = round($plan_phase[$k][$l][2] / $tc_planned, 4) * 100;
                                    $tc_rate_attempted = round(($tc_planned - $plan_phase[$k][$l][2] - $plan_phase[$k][$l][5] - $plan_phase[$k][$l][6]) / $tc_planned, 4) * 100;
                                    $tc_rate_failed = round($plan_phase[$k][$l][4] / $tc_planned, 4) * 100;
                                    $tc_rate_passed = round($plan_phase[$k][$l][3] / $tc_planned, 4) * 100;
                                    echo "
                                        <tr>
                                          <td>" . $plan_phase[$k][$l][10] . "</td>    
                                          <td>" . $plan_phase[$k][$l][0] . "</td>
                                          <td>" . $tc_planned . "</td>
                                          <td>" . $plan_phase[$k][$l][2] . "</td>
                                          <td>" . $plan_phase[$k][$l][3] . "</td> 
                                          <td>" . $plan_phase[$k][$l][4] . "</td> 
                                          <td>" . $plan_phase[$k][$l][5] . "</td> 
                                          <td>" . $plan_phase[$k][$l][6] . "</td> 
                                          <td>" . $tc_rate_blocked . "%" . "</td>
                                          <td>" . $tc_rate_attempted . "%" . "</td>
                                          <td>" . $tc_rate_failed . "%" . "</td>
                                          <td>" . $tc_rate_passed . "%" . "</td>
                                          <td>" . $plan_phase[$k][$l][8] . "</td>
                                          <td>" . $plan_phase[$k][$l][9] . "</td> ";
                                    $plan_phase[$k][$l][12] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        echo "</table>";
        echo "</div >";
        break;
    case 2:
        break;
    case 3:
        break;
    case 4:
             /*
        for($i = 0; $i < $total_phase; $i++) {
           // for ($j = 0; $j < $plan_phase[$i][$j][11]; $j++) {
                //echo $plan_phase[$i][$j][10];
                              echo $plan_phase[$i][0][0] . "            " . $plan_phase[$i][0][10] . "    " . $plan_phase[$i][0][11].  "            " . $plan_phase[$i][1][10] . "    " . $plan_phase[$i][0][11]. "            ". $plan_phase[$i][2][10] . "    " . $plan_phase[$i][0][11]. "            ". $plan_phase[$i][3][10]. "    " . $plan_phase[$i][0][11]. "     ". $plan_phase[$i][4][10]. "       " . $plan_phase[$i][0][11]. "\n";

            //}
        }
                  */
        echo '<div class="ProTable" > ';
        echo "
    <table>        
      <tr>
        <td rowspan='2'>Filter</td> 
        <td rowspan='2'>$project_name</td>
        <td colspan='6'>Number of Test Cases</td>
        <td colspan='4'>Status Percentage</td>
        <td colspan='2'>Estimated PDs</td>
      </tr>
      <tr>
        <td>Planned</td>
        <td>Blocked</td>
        <td>Passed</td>
        <td>Failed</td>
        <td>No Run</td>
        <td>In Progress</td>
        <td>% Blocked</td>
        <td>% Attempted</td>
        <td>% Failed</td>
        <td>% Passed</td>
        <td>Blocked PDs</td>
        <td>No Run/In Progess PDs</td>
      </tr>
    ";
    for ($i=0; $i<$total_phase; $i++) {
        for ($j = 0; $j < $plan_phase[$i][0][11]; $j++) {
            if ($plan_phase[$i][$j][10] != 'Automation') {
                $plan_phase[$i][$j][10] = 'Manual';
            }
        }
    }
        for ($i=0; $i<$total_phase; $i++) {
            for ($j = 0; $j < $plan_phase[$i][0][11]; $j++) {
                if ($plan_phase[$i][$j][12] == 0) {
                    for ($k = 0; $k < $total_phase; $k++) {
                        for ($l = 0; $l < $plan_phase[$i][0][11]; $l++) {
                            if ($plan_phase[$k][$l][12] == 0) {
                                if($plan_phase[$k][$l][10] == $plan_phase[$i][$j][10]) {
                                    $tc_planned = $plan_phase[$k][$l][2] + $plan_phase[$k][$l][3] + $plan_phase[$k][$l][4] + $plan_phase[$k][$l][5] + $plan_phase[$k][$l][6] + $plan_phase[$k][$l][7];
                                    $tc_rate_blocked = round($plan_phase[$k][$l][2] / $tc_planned, 4) * 100;
                                    $tc_rate_attempted = round(($tc_planned - $plan_phase[$k][$l][2] - $plan_phase[$k][$l][5] - $plan_phase[$k][$l][6]) / $tc_planned, 4) * 100;
                                    $tc_rate_failed = round($plan_phase[$k][$l][4] / $tc_planned, 4) * 100;
                                    $tc_rate_passed = round($plan_phase[$k][$l][3] / $tc_planned, 4) * 100;
                                    echo "
                                        <tr>
                                          <td>" . $plan_phase[$k][$l][10] . "</td>    
                                          <td>" . $plan_phase[$k][$l][0] . "</td>
                                          <td>" . $tc_planned . "</td>
                                          <td>" . $plan_phase[$k][$l][2] . "</td>
                                          <td>" . $plan_phase[$k][$l][3] . "</td> 
                                          <td>" . $plan_phase[$k][$l][4] . "</td> 
                                          <td>" . $plan_phase[$k][$l][5] . "</td> 
                                          <td>" . $plan_phase[$k][$l][6] . "</td> 
                                          <td>" . $tc_rate_blocked . "%" . "</td>
                                          <td>" . $tc_rate_attempted . "%" . "</td>
                                          <td>" . $tc_rate_failed . "%" . "</td>
                                          <td>" . $tc_rate_passed . "%" . "</td>
                                          <td>" . $plan_phase[$k][$l][8] . "</td>
                                          <td>" . $plan_phase[$k][$l][9] . "</td> ";
                                    $plan_phase[$k][$l][12] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        echo "</table>";
        echo "</div >";
        break;
    default:

        echo '<div class="ProTable" > ';
        echo "
    <table>        
      <tr>
        <td rowspan='2'>$project_name</td>
        <td colspan='6'>Number of Test Cases</td>
        <td colspan='4'>Status Percentage</td>
        <td colspan='2'>Estimated PDs</td>
      </tr>
      <tr>
        <td>Planned</td>
        <td>Blocked</td>
        <td>Passed</td>
        <td>Failed</td>
        <td>No Run</td>
        <td>In Progress</td>
        <td>% Blocked</td>
        <td>% Attempted</td>
        <td>% Failed</td>
        <td>% Passed</td>
        <td>Blocked PDs</td>
        <td>No Run/In Progess PDs</td>
      </tr>
    ";

        for ($i=0; $i<$total_phase; $i++){
            $tc_planned =  $plan_phase[$i][2] + $plan_phase[$i][3] + $plan_phase[$i][4] + $plan_phase[$i][5] + $plan_phase[$i][6] + $plan_phase[$i][7];
            $tc_rate_blocked = round($plan_phase[$i][2] / $tc_planned, 4) * 100;
            $tc_rate_attempted =  round(( $tc_planned -  $plan_phase[$i][2] -$plan_phase[$i][5] -  $plan_phase[$i][6]) / $tc_planned , 4) * 100;
            $tc_rate_failed =  round($plan_phase[$i][4]  / $tc_planned, 4) * 100;
            $tc_rate_passed =  round($plan_phase[$i][3]  / $tc_planned, 4) * 100;
            echo "
        <tr>
          <td>".$plan_phase[$i][0]."</td>
          <td>".$tc_planned."</td>
          <td>".$plan_phase[$i][2]."</td>
          <td>".$plan_phase[$i][3]."</td> 
          <td>".$plan_phase[$i][4]."</td> 
          <td>".$plan_phase[$i][5]."</td> 
          <td>".$plan_phase[$i][6]."</td> 
          <td>".$tc_rate_blocked."%"."</td>
          <td>".$tc_rate_attempted."%"."</td>
          <td>".$tc_rate_failed."%"."</td>
          <td>".$tc_rate_passed."%"."</td>
          <td>".$plan_phase[$i][8]."</td>
          <td>".$plan_phase[$i][9]."</td>                
      ";
        }
        echo "</table>";
        echo "</div >";
        break;      
}
//************************************************printing table*****************************************
?>

<div id="qaContent" style=" float:left;">
    <?php
    //********************************************printing accordion*********************************************
    for ($i=0; $i<$total_phase; $i++){
        echo '<ul class="accordionPart"> ';
        echo '<li>';
        echo '<div class="qa_title_A">';
        //echo  $plan_phase[$i][0].$plan_phase[$i][1] ;
        print($plan_phase[$i][0]." ( ");
        echo '<font color="#000000">';
        $tc_planned =  $plan_phase[$i][2] + $plan_phase[$i][3] + $plan_phase[$i][4] + $plan_phase[$i][5] + $plan_phase[$i][6] + $plan_phase[$i][7];
        print("Total = ".$tc_planned." [".$plan_phase[$i][1]."]");  //total
        echo '</font>';
        echo ' / ';
        echo '<font color="#AC58FA">';
        $tc_norun = $plan_phase[$i][5]+$plan_phase[$i][6];
        print("No Run = ".$tc_norun." [".$plan_phase[$i][9]."]"); //No run + in progress
        echo '</font>';
        echo ' / ';
        echo '<font color="#FF8000">';
        print(" Blocked = ".$plan_phase[$i][2]." [".$plan_phase[$i][8]."]"); //blocked
        echo '</font>';
        echo ' )';


        echo '</div>';
        $phase_feature_num = count($plan_tree_row[$i],0);
        for ($j=0; $j<$phase_feature_num; $j++){


            $feature_tc_norun = $plan_tree_row[$i][$j][9]+$plan_tree_row[$i][$j][10];

            echo '<div class="qa_title_B">';
            print($plan_tree_row[$i][$j][0]."( ");
            echo '<font color="#000000">';
            print("Total = ".$plan_tree_row[$i][$j][2]." [".$plan_tree_row[$i][$j][3]."]");  //total
            echo '</font>';
            echo ' / ';
            echo '<font color="#AC58FA">';
            print("No Run = ".$feature_tc_norun." [".$plan_tree_row[$i][$j][5]."]"); //No run + in progress
            echo '</font>';
            echo ' / ';
            echo '<font color="#FF8000">';
            print(" Blocked = ".$plan_tree_row[$i][$j][6]." [".$plan_tree_row[$i][$j][4]."]"); //blocked
            echo '</font>';
            echo ' )';
            echo '</div>';
            echo '<div class="qa_content">';
            echo	'<div id="TableP">';
            echo '<div class="menuTable">';
            echo '<table border="1">
    <thead>
      <tr>
        <th>Test Case</th>
        <th>Test Status</th>
        <th>PD</th>
      </tr>
    </thead>
    <tbody>
    ';
            for ( $k=0; $k < $plan_tree_row[$i][$j][2]; $k++){
                $base_start_row=$plan_tree_row[$i][$j][1] + $k;
                $test_id=$sheetData[$base_start_row][D];
                //$feature_tc_planned =  $plan_tree_row[$i][$j][2];
                $PD = LookupTestPD($test_id);

                $short_path = substr($sheetData[$base_start_row][A], indexStr($sheetData[$base_start_row][A],"\\", 7)); //short_path
                $test_set_name = $sheetData[$base_start_row][B];
                //color
                switch ($sheetData[$base_start_row][E]) {
                    case Blocked:
                        $color="#F4FA58";
                        break;
                    case Passed:
                        $color="#81F781";
                        break;
                    case Failed:
                        $color="#FA5882";
                        break;
                    case "No Run":
                        $color="#FFFFFF";
                        break;
                    case "Not Completed":
                        $color="#81F7F3";
                        break;
                    case "N/A":
                        $color="#FFFFFF";
                        break;
                }

                echo "
        <tr align='center'>
          <td title='$short_path.$test_set_name'>".$sheetData[$base_start_row][C]."</td>
          <td bgcolor='$color' title='$short_path.$test_set_name'>".$sheetData[$base_start_row][E]."</td>
          <td title='$short_path.$test_set_name'>".$PD."</td>          
      ";
            }
            echo "</tbody>";
            echo "</table>";
            echo '<a href="#" class="close_qa">Hide</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</li>';
        echo '</ul>';
    }

    //print_r($plan_phase);
    //print_r($plan_tree_row);
    // print_r($project);
    //*********************************printing accordion****************************************************
    ?>
</div>

</body>
</html>

