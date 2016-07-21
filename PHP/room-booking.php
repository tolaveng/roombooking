<?php
// restricted access
require_once('./includes/access.php');
?>

<?php
	/* ----- server side ----- */
	// redirect if room are not valid
	if(empty($_REQUEST['room']) ){
		header("location: room-select.php");
		exit();
	}else{
		$dataRoom = $db->selectQuery('SELECT `room_id`,`room_num`,`session` FROM `tb_room` where room_id='.$db->quote($db->clean($_REQUEST['room'])));
		if(count($dataRoom)<=0){
			header("location: room-select.php");
			exit();
		}
	}

	// --- response to any AJAX action task request ---
	if(isset($_REQUEST['task']) && trim($_REQUEST['task'])!=''){
		// response array of error and data to client
		$response = array('error'=>'undefined task','data'=>null);

		// check token
		// ...
		// switch actiion task request
		switch ($_REQUEST['task']) {
			// select data
			case 'select':
				$html = "";
				$html = htmlTable();
				$response['error'] = '';
				$response['data'] = $html;
				break;
			// insert data
			case 'insertbooking':
				// get variables
				$roomid = $db->clean($_GET['room']);
				$bookingId = $db->clean($_GET['bookingid']); //roomid_date_time
				$userid = $user['user_id'];
				$session = $dataRoom[0]['session'];
				$bookingOn = date('Y-m-d H:i',time()).':00';
				$dateandtime = explode('_',$bookingId);
				if(count($dateandtime)!=3){
					$response['error'] = 'Unexpected variables';
					break;
				}
				$bookingDate = $dateandtime[1];
				$bookingTime = str_replace('-',':',$dateandtime[2]).':00';

				// check duplicate
				$sql = "select `booking_id` from `tb_booking` where `booking_id`=".$db->quote($bookingId);
				$data = $db->selectQuery($sql);
				if(count($data)>0){
					$response['error'] = 'Sorry, You cannot schedule this booking. It have been booked by other. <a href="#" onclick="loadData(); return false;">Refresh</a>';
					break;
				}

				// sql
				$sql = 'insert into `tb_booking`(`booking_id`,`room_id`,`user_id`,`booking_session`,`booking_on`,`notified`,`booking_date`,`booking_time`)
				 				values('.$db->quote($bookingId).','.$db->quote($roomid).','.$db->quote($userid).','.$session.','.$db->quote($bookingOn).',0,'.$db->quote($bookingDate).','.$db->quote($bookingTime).')';
				$insertId = $db->insertQuery($sql);
				if($db->error==''){
					$response['error'] = '';
					$response['data'] = $insertId;
				}else{
						//$response['error'] = $db->error;
						$response['error'] = 'Unexpected database error';
				}
				break;
			case 'deletebooking':
				// check id
				if(empty($_GET['bookingid'])){
					$response['error'] = 'Unknown variables';
					break;
				}
				//delete attendee -> booking
				$id = $db->deleteQuery('Delete from tb_attendant where booking_id='.$db->quote($db->clean($_GET['bookingid'])));
				if($db->error){
					$response['error'] = $db->error;
					$response['data'] = '';
				}else{
					$id = $db->deleteQuery('Delete from tb_booking where booking_id='.$db->quote($db->clean($_GET['bookingid'])));
					if($db->error){
						$response['error'] = $db->error;
						$response['data'] = '';
					}else{
						$response['error'] = '';
						$response['data'] = $id;
					}
				}
				break;
			case 'insertattendee':
				// check booking id
				if(empty($_GET['bookingid'])){
					$response['error'] = 'Unknown booking ID';
					break;
				}
				$bookingId = $db->clean($_GET['bookingid']);
				$firstname = $db->clean($_GET['firstname']);
				$lastname = $db->clean($_GET['lastname']);
				$more = $db->cleanText($_GET['more']);
				// insert
				$sql = 'INSERT INTO tb_attendant(booking_id,att_firstname,att_lastname,att_more) values('.$db->quote($bookingId).','.$db->quote($firstname).','.$db->quote($lastname).','.$db->quote($more).')';
				$insertId = $db->insertQuery($sql);
				if($db->error=='' && $insertId){
					$response['error'] = '';
					$sql = 'SELECT att_id as id,att_firstname as firstname,att_lastname as lastname,att_more as more FROM `tb_attendant` WHERE `att_id`='.$insertId;
					$data = $db->selectQuery($sql);
					$response['data'] = $data[0];
				}else{
					$response['error'] = 'Unexpected database error';
				}
				break;
			// select attendees
			case 'selectattendee':
				// check booking id
				if(empty($_GET['bookingid'])){
					$response['error'] = 'Unknown booking ID';
					break;
				}
				$sql = 'SELECT att_id as id,att_firstname as firstname,att_lastname as lastname,att_more as more FROM `tb_attendant` WHERE `booking_id`='.$db->quote($db->clean($_GET['bookingid'])).' ORDER BY att_firstname, att_lastname';
				$data = $db->selectQuery($sql);
				//$response['error'] = $db->error;   ignore error
				$response['error'] = '';
				if(count($data)>0){
					// prepare data for json unterminated string error
					for($i=0; $i<count($data); $i++){
						if(!get_magic_quotes_gpc()){
							$data[$i]['more'] = stripcslashes($data[$i]['more']);
						}else{
							$data[$i]['more'] = $data[$i]['more'];
						}
					}
					$response['data'] = $data;
				}else{
					$response['data'] = '';
				}
				break;
			// delete attendee and load
			case 'deleteattendee':
				// check id
				if(empty($_GET['attid'])){
					$response['error'] = 'Unknown variables';
					break;
				}
				//delete
				$id = $db->deleteQuery('Delete from tb_attendant where att_id='.$db->quote($db->clean($_GET['attid'])));
				if($db->error){
						$response['error'] = $db->error;
						$response['data'] = '';
				}else{
					$response['error'] = '';
					$response['data'] = $id;
				}
				break;
			default:
				break;
		}
		echo json_encode($response);
		exit();
	}// .task
	// --- end response to any AJAX action ---

	// generate html table
	function htmlTable(){
		global $db, $dataRoom,$user;

		// session room
		if($dataRoom[0]['session']===null){
			$session = 60;
		}else{
			$session = (int)$dataRoom[0]['session'];
		}
		$roomId = $dataRoom[0]['room_id'];
		$userId = $user['user_id'];
		// generate calendar
		$toDay = new DateTime();
		if(!empty($_GET['curweek']) && is_numeric($_GET['curweek']) && $_GET['curweek']!=0){
				$toDay->modify($_GET['curweek'].' week');
		}
		$beginTime = strtotime('8:30:00');
		$endTime = strtotime('18:30:00');
		$theDays = array(); // date of (monday-friday)

		$dayofweek = $toDay->format('w');
		if($dayofweek==0){
				// default 0 is sunday, let change sunday to 7
				$dayofweek = 7;
		}
		// what date is the monday
		$beginDay = clone $toDay;
		$beginDay->sub(new DateInterval('P'.($dayofweek-1).'D'));
		// set date to days (monday-friday)
		for($i=0; $i<5; $i++){
			$theDays[$i] = clone $beginDay;
			$beginDay->add(new DateInterval('P1D'));
		}

		// select booked room for this week
		$sql = "SELECT booking_id,user_id,booking_session,booking_date,booking_time FROM tb_booking WHERE room_id=".$roomId." AND booking_date>='".$theDays[0]->format('Y-m-d')."' AND booking_date<='".$theDays[4]->format('Y-m-d')."'";
		$bookedRoom = $db->selectQuery($sql);

		// select blocking room for this week
		$sql = "SELECT room_id,block_date,block_from,block_to FROM tb_block WHERE room_id=".$roomId." AND block_date>='".$theDays[0]->format('Y-m-d')."' AND block_date<='".$theDays[4]->format('Y-m-d')."'";
		$dataBlock = $db->selectQuery($sql);
		// table header
		$html = '
		<table class="table table-bordered">
			<thead>
			<tr>
				<th>Time / Day</th>
				<th style="width: 17%;">Monday <br>'.$theDays[0]->format('d/m/Y').'</th>
				<th style="width: 17%;">Tuesday <br>'.$theDays[1]->format('d/m/Y').'</th>
				<th style="width: 17%;">Wednesday <br>'.$theDays[2]->format('d/m/Y').'</th>
				<th style="width: 17%;">Thursday <br>'.$theDays[3]->format('d/m/Y').'</th>
				<th style="width: 17%;">Friday <br>'.$theDays[4]->format('d/m/Y').'</th>
			</tr>
			</thead>
		';
		// table body
		$html.= '<tbody>';
		// loop the times
		while($beginTime<=$endTime){
			$sessionTime = $beginTime + ($session*60);
			$html.= '<tr>';
			$html.= '<td>'.date('H:i',$beginTime).'-'.date('H:i',$sessionTime).'</td>';
			// loop the five days monday-friday
			for($day=0; $day<count($theDays); $day++){
				$theDate = new DateTime($theDays[$day]->format('Y-m-d').'T'.date('H:i:s',$beginTime));
				//$id = $roomId.'_'.$theDays[$day]->format('Y-m-d').'_'.date('H-i',$beginTime);
				$id = $roomId.'_'.$theDate->format('Y-m-d_H-i');
				//$title = $theDays[$day]->format('d/m/Y').' '.date('H:i',$beginTime);
				$title = $theDate->format('d/m/Y H:i');
				$classname = 'is-available';
				// check if the room is blocked
				for($i=0; $i<count($dataBlock); $i++){
					if($dataBlock[$i]['block_date']==$theDate->format('Y-m-d') && ( (strtotime($dataBlock[$i]['block_from'])<=$beginTime && strtotime($dataBlock[$i]['block_to'])>$beginTime) || (strtotime($dataBlock[$i]['block_from'])<$sessionTime && strtotime($dataBlock[$i]['block_to'])>$sessionTime) ) ){
						$classname = 'is-unavailable';
						break;
					}
				}
				// check if the room is booked or not
				for($i=0; $i<count($bookedRoom); $i++){
					if($bookedRoom[$i]['booking_date']==$theDate->format('Y-m-d') && strtotime($bookedRoom[$i]['booking_time'])==$beginTime ){
						if($bookedRoom[$i]['user_id']==$userId){
							$classname = 'is-booked';
						}else{
							$classname = 'is-unavailable';
						}
						break;
					}
				}
				if($classname == 'is-available'){
					// check if it's past
					$now = new DateTime();
					if($now<$theDate){
						$html.='<td id="'.$id.'" class="'.$classname.'" onclick="selectBooking(event,\''.$id.'\');" title="'.$title.'">';
						$html.='<span class="glyphicon glyphicon-plus"></span>';
						$html.='</td>';
					}else{
						$classname = 'is-past';
						$html.='<td id="'.$id.'" class="'.$classname.'" title="'.$title.'">';
						$html.='<span></span>';
						$html.='</td>';
					}
					$now = null;
				}else if( $classname == 'is-booked'){
					$html.='<td id="'.$id.'" class="'.$classname.'" onclick="selectBooking(event,\''.$id.'\');" title="'.$title.'">';
					$html.='<span>you booked</span>';
					$html.='</td>';
				}else{
					$html.='<td id="'.$id.'" class="'.$classname.'" title="'.$title.'">';
					$html.='<span></span>';
					$html.='</td>';
				}
				$theDate = null;
			}//for days
			$html.= '</tr>';
			$beginTime += ($session*60);
		}// while time
		$html.= '</tbody>';
		// table footer
		$html.= '<tfoot></tfoot>';
		$html.= '</table>';
		return $html;
	}

	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('./includes/header.php');
?>

<!-- script -->
<script type="text/javascript">
	// global variables
	var g_room_id = "<?=$_REQUEST['room'];?>";
	var g_cur_week = 0; // current week
	var g_is_busy = false; // show waiting dialog box
	// --- functons ---
	// Document ready, load data
	jQuery('document').ready(function(){
		loadData();
	})
	// ajax load data table
	function loadData(){
		// call ajax from java.js
		// ajaxUrl is defined in header.php
		g_is_busy = modalWait(true,'Loading ...');
		ajaxData({
			'url':ajaxUrl, 'data':{'room':g_room_id,'task':'select','token':'','curweek':g_cur_week}, 'log':false,
			'complete':function(response){
				g_is_busy = modalWait(false);
				if(response.error!=""){
					// error occured
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					// success
					jQuery('#message_bar').html('');
					jQuery('#message_bar').removeClass().hide();
					jQuery('#table_section').html(response.data);
				}
			}
		});
	}//.loadData

	// Table td click
	function selectBooking(evt,bookingid){
		if(g_is_busy){
			alert('System is processing, please wait.')
			return false;
		}
		var e = evt || this.event;
		e.preventDefault();
		if(!bookingid){
			return;
		}
		var obj = jQuery('#'+bookingid);
		// if room is avariable, allow booking, else confirm to remove booking
		if(obj.hasClass('is-available')){
			obj.removeClass('is-available');
			obj.addClass('is-pending');
			g_is_busy = modalWait(true,'Booking ...');
			// sending data
			ajaxData({
				'url':ajaxUrl, 'data':{'room':g_room_id,'task':'insertbooking','bookingid':bookingid,'token':''}, 'log':false,
				'complete':function(response){
					jQuery('#'+bookingid).removeClass('is-pending');
					g_is_busy = modalWait(false);
					if(response.error!=""){
						// error occured
						jQuery('#message_bar').html(response.error);
						jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
						jQuery('#'+bookingid).addClass('is-available');
					}else{
						// success
						jQuery('#message_bar').html('You have successfully booked the room');
						jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
						// update booking id
						jQuery('#'+bookingid).addClass('is-booked');
						jQuery('#'+bookingid).children('span').html('you booked').removeClass('glyphicon glyphicon-plus');
						// show booking detail
						showBooking(bookingid);
					}
				}
			});// .ajaxData
		}else if(obj.hasClass('is-booked')){
			showBooking(bookingid);
		}
	}// .selected

	function showBooking(bookingid){
		if(!bookingid){
			return false;
		}
		// show date and time
		var ids = bookingid.split('_'); //roomid_yyyy-mm-dd_hh-mm
		var roomid = ids[0];
		var dates = ids[1].split('-');
		var times = ids[2].replace('-',':');
		jQuery('#modal_booking_id').val(bookingid);
		jQuery('#modal_booking_date').html(dates[2]+'/'+dates[1]+'/'+dates[0]);
		jQuery('#modal_booking_time').html(times);
		jQuery('#modal_booking').modal({'show':true,'keyboard':false,'backdrop':'static'});
		// load attendee from database
		loadAttendee(bookingid);
	}// .showBooking

	function cancelBooking(){
		if(!confirm('Are you sure to cancel this booking?')){ return false; }
		var bookingid = jQuery('#modal_booking_id').val();
		if(!bookingid){
			return false;
		}
		jQuery('#modal_booking').modal('hide');
		g_is_busy = modalWait(true,'Cancel booking ...');
		jQuery('#message_bar').html('Updating ... ').removeClass().addClass('alert alert-info').show();
		ajaxData({
			'url':ajaxUrl, 'data':{'room':g_room_id,'task':'deletebooking','token':'','bookingid':bookingid},'log':false,
			'complete':function(response){
				if(response.error!=""){
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					var obj = jQuery('#'+bookingid);
					obj.removeClass().addClass('is-available');
					obj.children('span').html('').addClass('glyphicon glyphicon-plus');
					jQuery('#message_bar').html('You have successfully cancel the booking');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
				}
				g_is_busy = modalWait(false);
			}// .complete
		});
	} //.cancelBooking

	/* ----- Attendee ----- */
	// Attendee Object
	var attendeeList = (function(){
		var list = [];
		var length = 0;
	  var ClassAttendee = function(){
			this.id = 0;
	    this.firstname = '';
	    this.lastname = '';
	    this.more = '';
	  };

	  this.add = function(id,fName,lName,more){
	    var att = new ClassAttendee();
			att.id = id;
	    att.firstname = fName;
	    att.lastname = lName;
	    att.more = more;
			list.push(att);
			length++;
	  }; //.add

	  this.get = function(i){
		  if(i>= 0 && i<length){
	      return list[i];
		  }
	  }; //.get

		this.getById = function(id){
			for(i=0; i<length; i++){
	      if(list[i].id==id){
	      	return list[i];
					break;
				}
		  }
	  }; //.getById

		this.remove = function(i){
		  if(i>= 0 && i<length){
				list.splice(i,1);
				length--;
		  }
	  }; //.remove

	  this.removeById = function(id){
	    for(i=0; i<length; i++){
	      if(list[i].id==id){
	        list.splice(i,1);
					length--;
	        break;
	      }
	    }
	  }; //.remove

	  this.removeByName = function(fName,lName){
	    for(i=0; i<length; i++){
	      if(list[i].firstname==fName && list[i].lastname==lName){
	        list.splice(i,1);
					length--;
	        break;
	      }
	    }
	  }; //.removeByName

	  this.length = function(){
	    return length;
	  }; //.length

		this.empty = function(){
				for(i=0; i<length; i++){
					list[i] = null;
				}
				list = [];
				length = 0;
		}; //.empty

		return this;
	})();
	//. end attendeeList object

	// select attendee from database add to attendeeList
	function loadAttendee(bookingid){
		if(!bookingid){
			return false;
		}
		// clear
		jQuery('#modal_booking_attendee').html('Load attendee ... ');
		ajaxData({
			'url':ajaxUrl, 'data':{'room':g_room_id,'task':'selectattendee','token':'','bookingid':bookingid},
			'complete':function(response){
				if(response.error!=""){
					// error
				}else if(response.data==''){
					// no data
					jQuery('#modal_booking_attendee').html('No attendee');
					attendeeList.empty();
				}else{
					// add to object list
					attendeeList.empty();
					jQuery.each(response.data,function(key,value){
						attendeeList.add(value.id,value.firstname,value.lastname,value.more);
					});
					refreshAttendee();
				}
			}// .complete
		});// .ajaxData
	}// ./loadAttendee

	// show attendee in modal
	function refreshAttendee(){
		var length = attendeeList.length();
		if(length<1){
			jQuery('#modal_booking_attendee').html('No attendee');
			return;
		}
		jQuery('#modal_booking_attendee').html('<div>Attendees:</div>');
		var table = jQuery('#modal_booking_attendee').append('<table></table>');
		for(i=0; i<length; i++){
				value = attendeeList.get(i);
				tr = table.append('<tr></tr>')
				.append('<td>'+value.firstname+'</td>')
				.append('<td>'+value.lastname+'</td>');
				if(jQuery.trim(value.more)!=''){
					tr.append('<td> &nbsp; <a href="#" onclick="showAttendeeDetail(\''+value.id+'\'); return false;">more</a></td>');
				}else{
					tr.append('<td></td>')
				}
				tr.append('<td> &nbsp; &nbsp; <a href="#" onclick="deleteAttendee(\''+value.id+'\'); return false;" title="Remove"><span class="glyphicon glyphicon-remove"></span></a></td>');
		}
	} //.refreshAttendee

	function showAttendeeDetail(id){
		var more = attendeeList.getById(id).more;
		alert(more);
	}// .showAttendeeDetail

	// showing attendee form modal for adding new
	function showAttendee(){
		var bookingid = jQuery('#modal_booking_id').val();
		if(!bookingid){
			return;
		}
		// clear all fields
		var frm = document.getElementById('modal_attendee_form');
		frm.firstname.value ="";
		frm.lastname.value = "";
		frm.more.value ="";
		jQuery('#att_alert').html('').hide();
		// close booking detail modal
		jQuery('#modal_booking').modal('hide');
		jQuery('#modal_attendee').modal({'show':true,'keyboard':false,'backdrop':'static'});
		jQuery('#modal_attendee').on('hidden.bs.modal',function(e){
			jQuery('#modal_booking').modal({'show':true,'keyboard':false,'backdrop':'static'});
		});
	}//. showAttendee

	// add new attendee to database
	function addAttendee(){
		var bookingid = jQuery('#modal_booking_id').val();
		if(!bookingid){
			return false;
		}
		var frm = document.getElementById('modal_attendee_form');
		var firstname = frm.firstname.value;
		var lastname = frm.lastname.value;
		var more = frm.more.value;
		if(jQuery.trim(firstname)=='' || jQuery.trim(lastname)==''){
			jQuery('#att_alert').html('Please complete all required fields').removeClass().addClass('alert alert-danger').show();
			jQuery('#att_firstname_group').addClass('has-error');
			jQuery('#att_lastname_group').addClass('has-error');
			return false;
		}
		jQuery('#att_alert').html('Adding attendee ...').removeClass().addClass('alert alert-info').show();
		jQuery('#att_firstname_group').removeClass('has-error');
		jQuery('#att_lastname_group').removeClass('has-error');
		jQuery('#att_btn_add').attr('disabled','disabled').addClass('disabled');
		g_is_busy = true;
		// sending data
		ajaxData({
			'url':ajaxUrl, 'data':{'room':g_room_id,'task':'insertattendee','token':'','bookingid':bookingid,'firstname':firstname,'lastname':lastname,'more':more}, 'log':false,
			'complete':function(response){
				g_is_busy = false;
				jQuery('#att_btn_add').removeAttr('disabled').removeClass('disabled');
				if(response.error!=""){
					// error occured
					jQuery('#att_alert').html(response.error);
					jQuery('#att_alert').removeClass().addClass('alert alert-danger').show();
				}else{
					// add to attendeeList
					var data = response.data;
					attendeeList.add(data.id,data.firstname,data.lastname,data.more);
					jQuery('#modal_attendee').modal('hide');
					refreshAttendee();
				}
			}
		});// .ajaxData
	}//.addAttendee

	// delete attendee by att_id and load by booking_id
	function deleteAttendee(attid){
		if(!confirm('Are you sure to remove this record from attendee list?')){ return false; }
		if(!attid){
			return false;
		}
		// update
		jQuery('#modal_booking_attendee').html('Update attendee ... ');
		ajaxData({
			'url':ajaxUrl, 'data':{'room':g_room_id,'task':'deleteattendee','token':'','attid':attid},'log':false,
			'complete':function(response){
				if(response.error!=""){
					// error
				}else{
					attendeeList.removeById(attid);
				}
				refreshAttendee();
			}// .complete
		});
	}// .deleteAttendee
/* ----- end Attendee ----- */

function nextWeek(){
	g_cur_week++;
	loadData();
}
function prevWeek(){
	g_cur_week--;
	loadData();
}
function thisWeek(){
	g_cur_week = 0;
	loadData();
}

</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
	#table_section{
		margin: auto;
		padding: 8px;
		min-height: 350px;
	}
	#table_section table{

	}

	#table_section td,#table_section th{
		text-align: center;
		vertical-align: top;
	}

	#table_section td.is-available{
		cursor: pointer;
		color: #66AAFF;
	}
	#table_section td.is-available span{
		visibility : hidden;
	}
	#table_section td.is-available:hover{
			background-color : #99CCFF;
	}
	#table_section td.is-available:hover > span{
		visibility : visible;
	}

	#table_section td.is-booked{
		background-color : #66AAFF;
		cursor: pointer;
	}
	#table_section td.is-booked span{
		visibility : hidden;
	}
	#table_section td.is-booked:hover > span{
		visibility : visible;
	}

	#table_section td.is-unavailable{
		background-color : #CECECE;
		cursor: not-allowed;
	}

	#table_section td.is-pending{
		background-color : #99CCEE;
		cursor: not-allowed;
	}

	#table_section td.is-past{
		cursor: not-allowed;
	}

	#modal_booking_attendee td{
		padding: 8px;
	}

	.nav-calendar{
		text-align: center;
	}
	.nav-calendar-left{
		float: left;
		font-weight: bold;
	}
	.nav-calendar-right{
		float: right;
		font-weight: bold;
	}
	.nav-calendar a{
	}
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container-fluid">
	<!-- breadCrumb -->
	<ol class="breadcrumb">
		<li><a href="index.php">Home</a></li>
		<li><a href="room-select.php">Room</a></li>
		<li class="active">Booking</li>
	</ol>
	<br>
	<!-- end of breadCrumb -->
	<!-- calendar table section -->
	<div class="nav-calendar">
		<span class="nav-calendar-left"><a href="#" onclick="prevWeek(); return false;">&lt; &lt; Previou week</a></span>
		<span class="nav-calendar-center"><a href="#" onclick="thisWeek(); return false;">This week</a></span>
		<span class="nav-calendar-right"><a href="#" onclick="nextWeek(); return false;">Next week &gt; &gt;</a></span>
	</div>

	<div id="table_section">
		Loading ...
	</div>

	<div class="nav-calendar">
		<span class="nav-calendar-left"><a href="#" onclick="prevWeek(); return false;">&lt; &lt; Previou week</a></span>
		<span class="nav-calendar-center"><a href="#" onclick="thisWeek(); return false;">This week</a></span>
		<span class="nav-calendar-right"><a href="#" onclick="nextWeek(); return false;">Next week &gt; &gt;</a></span>
	</div>
	<!-- end of calendar table section -->
</div>
<!-- end of main container -->

<!-- ----- form modal ----- -->
<!-- ----- booking details ----- -->
<div class="modal" id="modal_booking">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modal_booking_title">Room Booking</h4>
      </div>
      <div class="modal-body">
				<form id="modal_booking_form">
        	<div>You have successfully booked on <span id="modal_booking_date"></span> at <span id="modal_booking_time"></span></div>
					<input type="hidden" value="" id="modal_booking_id">
				</form>
				<div id="modal_booking_attendee">
				</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" onclick="showAttendee();"><span class="glyphicon glyphicon-plus"></span> Add Attendee</button>
				<button type="button" class="btn btn-default" onclick="cancelBooking();">Cancel booking</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- ----- Attendee ----- -->
<div class="modal" id="modal_attendee">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="modal_attendee_title">Room Booking Attendee</h4>
      </div>
      <div class="modal-body">
				<form id="modal_attendee_form">
					<div id="att_alert"></div>
					<div class="form-group" id="att_firstname_group">
						<label for="att_firstname" class="control-label">First name:</label>
					  <input type="text" class="form-control" id="att_firstname" name="firstname">
					</div>
					<div class="form-group" id="att_lastname_group">
						<label for="att_lastname" class="control-label">Last name:</label>
					  <input type="text" class="form-control" id="att_lastname" name="lastname">
					</div>
					<div class="form-group">
					  <label for="att_more" class="control-label">More:</label>
						<textarea class="form-control" id="att_more" name="more" rows=2></textarea>
					</div>
				</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="att_btn_add" onclick="addAttendee();">&nbsp; Add &nbsp; </button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- ----- end of form modal ----- -->

<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
