<?php
// restricted access
require_once('./includes/access.php');
?>

<?php
	/* ----- server side ----- */
	$dataBooking = $db->selectQuery("SELECT room_num,booking_id,booking_date,booking_time,booking_session FROM tb_booking INNER JOIN tb_room ON tb_room.room_id = tb_booking.room_id WHERE user_id='".$user['user_id']."' ORDER BY room_num,booking_date,booking_time");
	// make booking list for countting attendee
	$bookingIdList = "";
	for($i=0; $i<count($dataBooking); $i++){
		if($i>0){ $bookingIdList.=","; }
		$bookingIdList.= "'".$dataBooking[$i]['booking_id']."'";
	}
	$sql = "SELECT COUNT(*) AS att_count,booking_id FROM tb_attendant WHERE booking_id in(".$bookingIdList.") GROUP BY booking_id";
	$dataAttendee = $db->selectQuery($sql);
	// --- response to any AJAX action task request ---
	if(isset($_REQUEST['task']) && trim($_REQUEST['task'])!=''){
		// response array of error and data to client
		$response = array('error'=>'undefined task','data'=>null);

		// check token
		// ...
		// switch actiion task request
		switch ($_REQUEST['task']) {
			// select data
			case 'selectattendee':
				if(empty($_GET['bookingid'])){
					$response['error'] = 'Unknown booking ID';
					break;
				}
				$sql = 'SELECT att_id as id,att_firstname as firstname,att_lastname as lastname,att_more as more FROM `tb_attendant` WHERE `booking_id`='.$db->quote($db->clean($_GET['bookingid'])).' ORDER BY att_firstname, att_lastname';
				$data = $db->selectQuery($sql);

				$response['error'] = '';
				if(count($data)>0){
					// prepare data for json unterminated string error
					for($i=0; $i<count($data); $i++){
						if(!get_magic_quotes_gpc()){
							$data[$i]['more'] = stripcslashes($data[$i]['more']);
						}else{
							$data[$i]['more'] = $data[$i]['more'];
						}
						$data[$i]['more'] = htmlentities($data[$i]['more']);
					}
					$response['data'] = $data;
				}else{
					$response['data'] = 0;
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
						$response['error'] = "Sorry, unexpected error occured.";
						$response['data'] = '';
					}else{
						$id = $db->deleteQuery('Delete from tb_booking where booking_id='.$db->quote($db->clean($_GET['bookingid'])));
						if($db->error){
							$response['error'] = "Sorry, unexpected error occured.";
							$response['data'] = '';
						}else{
							$response['error'] = '';
							$response['data'] = $id;
						}
					}
					break;
			default:
				break;
		}
		echo json_encode($response);
		exit();
	}// .task
	// --- end response to any AJAX action ---



	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('./includes/header.php');
?>

<!-- script -->
<script type="text/javascript">
	var g_is_busy = false;
	function cancelBooking(bookingId){
		if(g_is_busy){ return false; }
		if(!confirm('Are you sure to cancel this booking?')){ return false; }
		if(!bookingId){
			return false;
		}
		g_is_busy = modalWait(true,'Canceling booking ...');
		jQuery('#message_bar').html('Updating ... ').removeClass().addClass('alert alert-info').show();
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'deletebooking','token':'','bookingid':bookingId},'log':false,
			'complete':function(response){
				if(response.error!=""){
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					jQuery('#'+bookingId).remove();
					jQuery('#message_bar').html('You have successfully cancel the booking');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
				}
				g_is_busy = modalWait(false);
			}// .complete
		});
	} //.cancelBooking

	function showAttendee(bookingId){
		jQuery('#modal_attendee').modal({'show':true,'keyboard':false,'backdrop':'static'});
		jQuery('#modal_attendee_tbody').html("<tr><td colspan='3'>Loading ...</td></tr>");
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'selectattendee','token':'','bookingid':bookingId}, 'log':false,
			'complete':function(response){
				if(response.error!=""){
					jQuery('#modal_attendee_tbody').html("<tr><td colspan='3'>Loading failed</td></tr>");
				}else if(response.data==0){
					jQuery('#modal_attendee_tbody').html("<tr><td colspan='3'>No attendee</td></tr>");
				}else{
					// add to table
					var tr = "";
					jQuery.each(response.data,function(key,value){
						tr+= "<tr><td>"+value.firstname+"</td><td>"+value.lastname+"</td><td>"+value.more+"</td></tr>";
					});
					jQuery('#modal_attendee_tbody').html(tr);
				}
			}// .complete
		});// .ajaxData
	}//.showAttendee
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
	table tr:nth-child(even) {
		background: #F5F5F5;
	}
	table tr:nth-child(odd) {
		background: #FFFFFF;
	}
	table tr > td{
		cursor: pointer;
	}
	table tr > td.icons{
		text-align: right;
		visibility: hidden;;
	}
	table tr:hover > td.icons{
		visibility: visible;
	}
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container">
	<!-- breadcrumb -->
	<ol class="breadcrumb">
		<li><a href="index.php">Home</a></li>
		<li class="active">My booking</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<div>
		<?php if(count($dataBooking)==0): ?>
			You haven't booked any room yet. Please go to <a href="room-select.php">room booking</a> to book a room.
		<?php else: ?>
			<table class="table table-bordered table-hover">
				<tr>
					<th>Room Number</th>
					<th>Date</th>
					<th>Time</th>
					<th>Attendees</th>
					<th style="width:100px">&nbsp;</th>
				</tr>
				<?php for($i=0; $i<count($dataBooking); $i++): ?>
					<tr id="<?=$dataBooking[$i]['booking_id'];?>">
						<td><?=$dataBooking[$i]['room_num'];?></td>
						<td><?=date('D d-m-Y',strtotime($dataBooking[$i]['booking_date']));?></td>
						<?php
							$session = (int)$dataBooking[$i]['booking_session'];
							$session*= 60; // minute -> second
						?>
						<td><?=date('H:i',strtotime($dataBooking[$i]['booking_time']));?> - <?=date('H:i',strtotime($dataBooking[$i]['booking_time'])+$session);?></td>
						<?php
							// select count of attendees
							$attendee = '';
						 	for($att=0; $att<count($dataAttendee); $att++){
									if($dataAttendee[$att]['booking_id']==$dataBooking[$i]['booking_id']){
										$attendee = $dataAttendee[$att]['att_count'];
										break;
									}
							}
						?>
						<?php if($attendee!=""): ?>
							<td><a href="#" onclick="showAttendee('<?=$dataBooking[$i]['booking_id'];?>'); return false;"><?=$attendee;?></a></td>
						<?php else: ?>
							<td>&nbsp;</td>
						<?php endif; ?>
						<td class="icons"><a href="#" onclick="cancelBooking('<?=$dataBooking[$i]['booking_id'];?>'); return false;" title="Cancel"><span class="glyphicon glyphicon-remove"></span></a></td>
					</tr>
				<?php endfor; ?>
			</table>
		<?php endif; ?>
	</div>
</div>
<!-- end of main container -->

<!-- ----- form modal ----- -->
<!-- ----- attendees details ----- -->
<div class="modal" id="modal_attendee">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Attendees</h4>
      </div>
      <div class="modal-body">
				<table id="modal_attendee_table" class="table table-bordered table-hover">
					<thead>
						<tr><th>First Name</th><th>Last Name</th><th>More</th></tr>
					</thead>
					<tbody id="modal_attendee_tbody">
					</tbody>
				</table>
      </div>
      <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- ----- end form modal ----- -->

<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
