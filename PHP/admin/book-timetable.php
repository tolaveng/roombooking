<?php
// restricted access
require_once('access.php');
?>

<?php
	/* ----- server side ----- */

	// --- response to any AJAX action task request ---
	if(isset($_REQUEST['task']) && trim($_REQUEST['task'])!=''){
		// response array of error and data to client
		$response = array('error'=>'undefined task','data'=>null);

		// check token
		// ...
		// switch actiion task request
		switch ($_REQUEST['task']) {
			// select data
			case 'loadtable':
				if(!empty($_GET['paged'])){
					$_SESSION['paged'] = $_GET['paged'];
				}
				// set default filter date from now
				if(!isset($_SESSION['filter_from'])){
					//$_SESSION['filter_from'] = date('Y-m-d');
				}
				//
				$response = selectTable();
				break;
			case 'filter':
				$_SESSION['paged'] = 1;
				if(isset($_GET['roomid'])){
					$_SESSION['filter_roomid'] = $db->clean($_GET['roomid']);
				}
				if(isset($_GET['datefrom'])){
					$dateFrom = isValidDateFormat($db->clean($_GET['datefrom']));
					if($dateFrom){
							$_SESSION['filter_from'] = $dateFrom;
					}else{
						$_SESSION['filter_from'] = '';
					}
				}
				if(isset($_GET['dateto'])){
					$dateTo = isValidDateFormat($db->clean($_GET['dateto']));
					if($dateTo){
							$_SESSION['filter_to'] = $dateTo;
					}else{
							$_SESSION['filter_to'] = '';
					}
				}
				$response = selectTable();
				break;
			default:
				break;
		}
		echo json_encode($response);
		exit();
	}// .task
	// --- end response to any AJAX action ---

	function selectTable(){
		global $db;
		$response = array('error'=>'Unexpected error','data'=>null,'msg'=>null);
		$html = '';
		$sqlCount = "SELECT count(*) as rows FROM tb_booking b WHERE b.room_id IS NOT NULL";
		$sql = "SELECT b.room_id,room_num,b.booking_id,b.user_id,firstname,lastname,booking_session,date_format(booking_date,'%a %d/%m/%Y') as b_date,date_format(booking_time,'%H:%i') as b_time";
		$sql.= " FROM tb_booking b LEFT JOIN tb_user u ON b.user_id=u.user_id LEFT JOIN tb_room r ON b.room_id=r.room_id";
		// condition
		$sql.= " WHERE room_num IS NOT NULL";
		if(isset($_SESSION['filter_roomid']) && $_SESSION['filter_roomid']!=''){
			$sql.= " AND b.room_id=".$db->quote($_SESSION['filter_roomid']);
			$sqlCount.= " AND b.room_id=".$db->quote($_SESSION['filter_roomid']);
		}
		if(isset($_SESSION['filter_from']) && $_SESSION['filter_from']!=''){
			$sql.= " AND booking_date>=".$db->quote($_SESSION['filter_from']);
			$sqlCount.= " AND booking_date>=".$db->quote($_SESSION['filter_from']);
		}
		if(isset($_SESSION['filter_to']) && $_SESSION['filter_to']!=''){
			$sql.= " AND booking_date<=".$db->quote($_SESSION['filter_to']);
			$sqlCount.= " AND booking_date<=".$db->quote($_SESSION['filter_to']);
		}
		// sort
		$sql.= " ORDER BY booking_date,booking_time,room_num";
		// paginate
		$items = 30; // items per page
		$dataCount = $db->selectQuery($sqlCount);
		$pages = ceil($dataCount[0]['rows']/$items);
		if(empty($_SESSION['paged'])){
			$paged = 1;
		}else{
			$paged = $_SESSION['paged'];
		}
		if($paged>$pages){
			$paged = 1;
		}
		$offset = ($paged-1)*$items;
		$sql.= " limit ".$offset.",".$items;
		// query
		//echo $sql;
		$dataBooking = $db->selectQuery($sql);
		if($db->error!="" || $dataBooking===false){
			$response['error'] = "Unexpected database error";
			$response['data'] = 0;
			return $response;
		}

		if(count($dataBooking)==0){
			$html.= "There is no booking.";
		}else{
			$html.='<table class="table table-striped table-bordered table-hover">';
			$html.='<thead>';
			$html.='		<th style="width: 20%;">Room Number</th>';
			$html.='		<th style="width: 15%;">Date</th>';
			$html.='		<th style="width: 15%;">Time</th>';
			$html.='		<th style="width: 15%;">Booking Name</th>';
			$html.='		<th style="width: 10%;">ID</th>';
			$html.='		<th style="width: 35%;">Other Attendees</th>';
			$html.='	</thead>';
			$html.='	<tbody>';
			for($i=0; $i<count($dataBooking); $i++){
				// sessionTime
				$sessionTime = strtotime($dataBooking[$i]['b_time'])+(60*intval($dataBooking[$i]['booking_session']));
				// attendees
				$dataAtt = $db->selectQuery("SELECT CONCAT(att_firstname,' ',att_lastname) AS att_name FROM tb_attendant WHERE booking_id=".$db->quote($dataBooking[$i]['booking_id']));
				// html
				$html.='<tr id="'.$dataBooking[$i]['room_id'].'">';
				$html.='	<td>'.htmlentities(stripslashes($dataBooking[$i]['room_num'])).'</td>';
				$html.=' <td>'.htmlentities($dataBooking[$i]['b_date']).'</td>';
				$html.='	<td>'.$dataBooking[$i]['b_time'].'-'.date('H:i',$sessionTime).'</td>';
				$html.='	<td>'.htmlentities($dataBooking[$i]['firstname'].' '.$dataBooking[$i]['lastname']).'</td>';
				$html.='	<td>'.explode('@',$dataBooking[$i]['user_id'])[0].'</td>';
				$html.='	<td>';
				$attendees = '';
				for($a=0; $a<count($dataAtt); $a++){
					if($a>0){ $attendees.=', ';}
					$attendees.= htmlentities($dataAtt[$a]['att_name']);
				}
				$html.= $attendees;
				$html.='	</td>';
				$html.='</tr>';
			}
			$html.='	</tbody>';
			$html.='</table>';
			if($pages>1){
				$html.='<nav class="text-center"><ul class="pagination">';
				$html.= '<li><a href="#" onclick="gotoPage(0);" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
				for($p=1; $p<=$pages; $p++){
					$html.= '<li '.($p==$paged?'class="active"':'').'><a href="#" onclick="gotoPage('.$p.');">'.$p.'</a></li>';
				}// .for
				$html.= '<li><a href="#" onclick="gotoPage('.$pages.');" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
				$html.='</ul></nav>';
			}// .page


		} // .data booking
		$response['error'] = '';
		$response['data'] = $html;
		return $response;
	} //.selectTable

	// form values
	$roomId = '';
	$dateFrom = '';
	$dateTo = '';
	if(isset($_SESSION['filter_roomid']) && $_SESSION['filter_roomid']!=''){
		$roomId = $_SESSION['filter_roomid'];
	}
	if(isset($_SESSION['filter_to']) && $_SESSION['filter_to']!=''){
		$dateTo = date('d/m/Y',strtotime($_SESSION['filter_to']));
	}
	if(isset($_SESSION['filter_from']) && $_SESSION['filter_from']!=''){
		$dateFrom = date('d/m/Y',strtotime($_SESSION['filter_from']));
	}

	

	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('header.php');
?>

<!-- script -->
<script type="text/javascript">
	jQuery(function () {
		loadTable();
		jQuery('#filter_date_from').datetimepicker({
			format:'DD/MM/YYYY'
		});
		jQuery('#filter_date_to').datetimepicker({
			format:'DD/MM/YYYY'
		});
	});

	function loadTable(paged){
		isBusy = modalWait(true,"Loading ...","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				document.location.href="index.php";
				return true;
			}else{
				return false;
			}
		});
		if(!paged){
			paged = 1;
		}
		// call ajax from java.js
		// ajaxUrl is defined in header.php
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'loadtable','token':'','paged':paged}, 'log':false,
			'complete':function(response){
				isBusy = modalWait(false);
				if(response.error!=""){
					// error occured
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					// success
					jQuery('#message_bar').html('');
					jQuery('#message_bar').removeClass().hide();
					displayTable(response.data);
				}
			}
		});
	} //.loadTable
	function displayTable(data){
		jQuery('#table_layout').html(data);
	} //.displayTable

	function gotoPage(paged){
		loadTable(paged);
		return false;
	}// .paginate

	function filterTable(){
		var frm = document.getElementById('filter_form');
		if(frm){
			isBusy = modalWait(true,"Loading ...","Please wait",function(){
				if(confirm("Are you sure to abort this process?")){
					ajaxAbort();
					return true;
				}else{
					return false;
				}
			});
			// call ajax from java.js
			// ajaxUrl is defined in header.php
			ajaxForm({
				'url':ajaxUrl, 'form':frm, 'log':false,
				'complete':function(response){
					isBusy = modalWait(false);
					if(response.error!=""){
						// error occured
						jQuery('#message_bar').html(response.error);
						jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
					}else{
						// success
						jQuery('#message_bar').html('');
						jQuery('#message_bar').removeClass().hide();
						displayTable(response.data);
					}
				}
			});
		}// frm
		return false;
	} // .filterTable
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
	#filter_layout{
		font-weight: bold;
		text-align: center;
	}
	#filter_layout select, #filter_layout input{
		margin: 0 auto;
		font-weight: normal;
		padding: 2px;
		width: 150px;
	}
	.form-group{
		position: relative;
		margin: 0 auto;
	}
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container">
	<!-- breadcrumb -->
	<ol class="breadcrumb">
		<li><a href="index.php">Home</a></li>
		<li class="active">Timetable</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<!-- Filter -->
	<div id="filter_layout" class="text-center">
		<form class="form-inline" id="filter_form" method="get" onsubmit="return false;">
			<div class="form-group">
				<label for="filter_room">View By: Room Number</label>
				<select id="filter_room" class="form-control" name="roomid"><option value="">ALL</option>
				<?php
				// dataRoom
				$dataRoom = $db->selectQuery('SELECT room_id,room_num from tb_room order by room_num');
					for($i=0; $i<count($dataRoom); $i++){
						echo '<option value="'.$dataRoom[$i]['room_id'].'" '.($dataRoom[$i]['room_id']==$roomId?'selected':'').' >'.$dataRoom[$i]['room_num'].'</option>';
					}
				?>
				</select>
			</div>
			<div class="form-group">
				<label for="filter_date_from">Date From</label>
				<input type="text" id="filter_date_from" name="datefrom" class="form-control" placeholder="DD/MM/YYYY" value="<?=$dateFrom;?>">
			</div>
			<div class="form-group">
				<label for="filter_date_to">To</label>
				<input type="text" id="filter_date_to" name="dateto" class="form-control" placeholder="DD/MM/YYYY" value="<?=$dateTo;?>">
			</div>
			<button type="button" onclick="filterTable();" class="btn btn-small btn-default">Filter</button>
			<input type="hidden" value="filter" name="task">
		</form>
	</div>
	<br>
	<div id="table_layout">
	</div>
</div>
<!-- end of main container -->


<?php
	// include footer
	include('footer.php');
?>
<?php /* ----- end client side -----> */ ?>
