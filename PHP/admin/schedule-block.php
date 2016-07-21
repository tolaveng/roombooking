<?php
// restricted access
require_once('access.php');
?>

<?php
	/* ----- server side ----- */

	// --- response to any AJAX action task request ---
	if(isset($_REQUEST['task']) && trim($_REQUEST['task'])!=''){
		// response array of error and data to client
		$response = array('error'=>'undefined task','data'=>null,'msg'=>null);

		// check token
		// ...
		// switch actiion task request
		switch ($_REQUEST['task']) {
			// select data
			case 'loadtable':
				$response = selectTable();
				break;
			// load form for adding
			case 'loadformadd':
				$response = loadForm();
				break;
			// loa form for edit
			case 'loadformedit':
				if(empty($_GET['id'])){
					$response['error'] = "Invalid arguments";
					break;
				}
				$id = $db->clean($_GET['id']);
				$sql = "SELECT block_id,room_id,DATE_FORMAT(block_date,'%d/%m/%Y') AS b_date,DATE_FORMAT(block_from,'%H:%i') AS b_from,DATE_FORMAT(block_to,'%H:%i') AS b_to FROM tb_block WHERE block_id=".$db->quote($id);
				$data = $db->selectQuery($sql);
				if(count($data)>0){
					$response = loadForm($data[0],$data[0]['block_id']);
				}else{
					$response['error'] = "Data is not found";
					break;
				}
				break;
			// insert data
			case 'insert':
					if(empty($_GET['room']) || empty($_GET['date'])){
						$response['error'] = "Invalid arguments";
						break;
					}
					$room = $db->clean($_GET['room']);
					$date = $db->clean($_GET['date']);
					$from = $db->clean($_GET['from']);
					$to = $db->clean($_GET['to']);
					// check valid date and time
					$date = isValidDateFormat($date);
					if(!$date){
						$response['error'] = "Invalid date format";
						break;
					}
					$from = isValidTimeFormat($from);
					if(!$from){
						$response['error'] = "Invalid Time format";
						break;
					}
					$to = isValidTimeFormat($to);
					if(!$to){
						$response['error'] = "Invalid Time format";
						break;
					}
					// check time block_to must greater than block_from
					if(strtotime($from)>=strtotime($to)){
						$response['error'] = "The finish time must be greater than the beginning time";
						break;
					}
					// insert
					$sql = 'INSERT INTO tb_block(room_id,block_date,block_from,block_to) VALUES('.$db->quote($room).','.$db->quote($date).','.$db->quote($from).','.$db->quote($to).')';
					$db->insertQuery($sql);
					if($db->error){
						$response['error'] = "Sorry, unexpected database error";
						break;
					}else{
						$response['error'] = '';
						$response = selectTable();
					}
					break;
			// update data
			case 'update':
				if(empty($_GET['id']) || empty($_GET['room']) || empty($_GET['date'])){
					$response['error'] = "Invalid arguments";
					break;
				}
					$id = $db->clean($_GET['id']);
					$room = $db->clean($_GET['room']);
					$date = $db->clean($_GET['date']);
					$from = $db->clean($_GET['from']);
					$to = $db->clean($_GET['to']);
					// check valid date and time
					$date = isValidDateFormat($date);
					if(!$date){
						$response['error'] = "Invalid date format";
						break;
					}
					$from = isValidTimeFormat($from);
					if(!$from){
						$response['error'] = "Invalid Time format";
						break;
					}
					$to = isValidTimeFormat($to);
					if(!$to){
						$response['error'] = "Invalid Time format";
						break;
					}
					// check time block_to must greater than or equal block_from
					if(strtotime($from)>strtotime($to)){
						$response['error'] = "The beginning time must be less than or equal to the end time";
						break;
					}
					// update
					$sql = 'UPDATE tb_block SET room_id='.$db->quote($room).',block_date='.$db->quote($date).',block_from='.$db->quote($from).',block_to='.$db->quote($to).' WHERE block_id='.$db->quote($id);
					$db->updateQuery($sql);
					if($db->error){
						$response['error'] = "Sorry, unexpected database error";
						break;
					}else{
						$response['error'] = '';
						$response = selectTable();
					}
				break;
			// delete data
			case 'delete':
				if(empty($_GET['id'])){
					$response['error'] = "Invalid arguments";
					break;
				}
				// delete
				$id = $db->clean($_GET['id']);
				$db->deleteQuery('Delete from tb_block where block_id='.$db->quote($id));
				if($db->error){
					$response['error'] = "Sorry, unexpected database error";
					break;
				}else{
					$response['error'] = '';
					$response = selectTable();
				}
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
		$sql = "SELECT block_id,room_num,tb_block.room_id AS room_id,DATE_FORMAT(block_date,'%a %d/%m/%Y') AS b_date,DATE_FORMAT(block_from,'%H:%i') as b_from,DATE_FORMAT(block_to,'%H:%i') as b_to FROM tb_block INNER JOIN tb_room WHERE tb_block.room_id=tb_room.room_id ORDER BY room_num,block_date,block_from";
		$dataBlock = $db->selectQuery($sql);
		if($db->error!=""){
			$response['error'] = "Unexpected database error";
			$response['data'] = 0;
			return false;
		}
		if(count($dataBlock)<=0){
			$html = "There is no schedule or block. <a href='#' onclick='addForm(); return false;'>Click here</a> to add new block date time.";
		}else{
			$html='<table class="table table-striped table-bordered table-hover">';
			$html.='<thead>';
			$html.='		<th style="width: 30%;">Room Number</th>';
			$html.='		<th style="width: 20%;">Block Date</th>';
			$html.='		<th style="width: 20%;">From</th>';
			$html.='		<th style="width: 20%;">To</th>';
			$html.='		<th style="width: 10%;"><button onclick="addForm();" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Add Block</button></th>';
			$html.='	</thead>';
			$html.='	<tbody>';
			for($i=0; $i<count($dataBlock); $i++){
				$html.='<tr id="'.$dataBlock[$i]['block_id'].'">';
				$html.='	<td>'.$dataBlock[$i]['room_num'].'</td>';
				$html.=' <td>'.$dataBlock[$i]['b_date'].'</td>';
				$html.='	<td>'.$dataBlock[$i]['b_from'].'</td>';
				$html.='	<td>'.$dataBlock[$i]['b_to'].'</td>';
				$html.='	<td class="icons">';
				$html.=' 		<a href="#" onclick="editForm(\''.$dataBlock[$i]['block_id'].'\'); return false;" title="Edit"><span class="glyphicon glyphicon-pencil"></span></a>';
				$html.=' 		&nbsp; <a href="#" onclick="deleteBlock(\''.$dataBlock[$i]['block_id'].'\'); return false;" title="Delete"><span class="glyphicon glyphicon-remove"></span></a>';
				$html.='	</td>';
				$html.='</tr>';
			}
			$html.='	</tbody>';
			$html.='</table>';
		}
		$response['error'] = '';
		$response['data'] = $html;
		return $response;
	} //.selectTable

	function loadForm($frmData=null,$id='',$method='GET',$action='',$enctype='application/x-www-form-urlencoded'){
		global $db;
		$response = array('error'=>'Unexpected error','data'=>null,'msg'=>null);
		if($action==''){
			$action = htmlspecialchars($_SERVER["PHP_SELF"]);
		}
		if($frmData){
			$bDate = $frmData['b_date'];
			$bTo = $frmData['b_to'];
			$bFrom = $frmData['b_from'];
		}else{
			$bDate = "";
			$bTo = "";
			$bFrom = "";
		}
		$html = '<form id="modal_form_form" action="'.$action.'" method="'.$method.'" enctype="'.$enctype.'" accept-charset="UTF-8" autocomplete="off" novalidate onsubmit="return false;">';
		$html.= '<div class="form-group row" id="room_group">';
		$html.= '		<div class="col-xs-12 col-sm-3">';
		$html.= '				<label for="block_room" class="control-label">Room Number:</label>';
		$html.= '		</div>';
		$html.= '		<div class="col-xs-12 col-sm-9">';
		$html.= '			<select class="form-control" id="block_room" name="room">';
		$html.= '				<option value=""></option>';

		$dataRoom = $db->selectQuery("Select room_num,room_id from tb_room order by room_num");
		for($i=0; $i<count($dataRoom); $i++){
			$selected = '';
			if(!empty($frmData['room_id']) && $frmData['room_id']==$dataRoom[$i]['room_id']){
				$selected = 'selected';
			}
				$html.= '<option value="'.$dataRoom[$i]['room_id'].'" '.$selected.' >'.$dataRoom[$i]['room_num'].'</option>';
		}
		$html.= '			</select>';
		$html.= '		</div>';
		$html.= '</div>';

		$html.= '<div class="form-group row" id="date_group">';
		$html.= '	<div class="col-xs-12 col-sm-3">';
		$html.= '		<label for="block_date" class="control-label">Block Date:</label>';
		$html.= '	</div>';
		$html.= '	<div class="col-xs-12 col-sm-9">';
		$html.= '		<div class="input-group date">';
		$html.= '			<input type="text" class="form-control" id="block_date" name="date" placeholder="DD/MM/YYYY" value="'.$bDate.'" >';
		$html.= '			<span class="input-group-addon">';
		$html.= '					<span class="glyphicon glyphicon-calendar"></span>';
		$html.= '			</span>';
		$html.= '		</div>';
		$html.= '	</div>';
		$html.= '</div>';

		$html.= '<div class="row">';
		$html.= '	<div class="col-xs-12 col-sm-6">';
		$html.= '		<div class="form-group" id="from_group">';
		$html.= '			<label for="block_from" class="control-label">From:</label>';
		$html.= '			<div class="input-group date">';
		$html.= '				<input type="text" class="form-control" id="block_from" name="from" placeholder="HH:MM" value="'.$bFrom.'" >';
		$html.= '				<span class="input-group-addon">';
		$html.= '					<span class="glyphicon glyphicon-time"></span>';
		$html.= '				</span>';
		$html.= '			</div>';
		$html.= '		</div>';
		$html.= '	</div>';
		$html.= '	<div class="col-xs-12 col-sm-6">';
		$html.= '		<div class="form-group" id="to_group">';
		$html.= '			<label for="block_to" class="control-label">To:</label>';
		$html.= '			<div class="input-group date">';
		$html.= '				<input type="text" class="form-control" id="block_to" name="to" placeholder="HH:MM" value="'.$bTo.'" >';
		$html.= '				<span class="input-group-addon">';
		$html.= '					<span class="glyphicon glyphicon-time"></span>';
		$html.= '				</span>';
		$html.= '			</div>';
		$html.= '		</div>';
		$html.= '	</div>';
		$html.= '</div>';
		if($id){
			$html.= '<input type="hidden" name="id" value="'.$id.'">';
			$html.= '<input type="hidden" name="task" value="update">';
		}else{
			$html.= '<input type="hidden" name="task" value="insert">';
		}
		$html.= '</form>';
		$response['error'] = '';
		$response['data'] = $html;
		return $response;
	} //.loadForm

	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('header.php');
?>

<!-- script -->
<script type="text/javascript">
	var isSubmitted = false;
	var isBusy = false;

	jQuery(function () {
		loadTable();
	});

	function loadTable(){
		isBusy = modalWait(true,"Loading ...","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				document.location.href="index.php";
				return true;
			}else{
				return false;
			}
		});
		// call ajax from java.js
		// ajaxUrl is defined in header.php
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'loadtable','token':''}, 'log':false,
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

	function addForm(){
		isBusy = modalWait(true,"Add New","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				ajaxAbort();
				return true;
			}else{
				return false;
			}
		});
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'loadformadd','token':''}, 'log':false,
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
					displayForm(response.data);
				}
			}
		});
	} //.loadFormAdd

	function editForm(id){
		if(!id){ return false; }
		isBusy = modalWait(true,"Edit","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				ajaxAbort();
				return true;
			}else{
				return false;
			}
		});
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'loadformedit','id':id,'token':''}, 'log':false,
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
					displayForm(response.data);
				}
			}
		});
	} //.loadFormEdit

	function displayForm(data){
		jQuery('#modal_form_body').html(data);
		jQuery('#modal_form').on('shown.bs.modal', function (){
			// attach bootstrap datetimepicker
			jQuery('#block_date').datetimepicker({
				format:'DD/MM/YYYY',daysOfWeekDisabled: [0, 6], minDate: new Date()
			});
			jQuery('#block_from').datetimepicker({
				format:'HH:mm'
			});
			jQuery('#block_to').datetimepicker({
				format:'HH:mm'
			});
			jQuery('#block_room').focus();
		});//shown
		jQuery('#modal_form').modal({'show':true,'keyboard':false,'backdrop':'static'});
		// reset
		isSubmitted = false;
		jQuery('#modal_form_alert').html('').removeClass().hide();
		jQuery('#modal_button_submit').removeAttr('disabled').removeClass('disabled');
	} //.displayForm

	function submitForm(evt){
		if(evt){
			evt.preventDefault();
		}else if(this.event){
			this.event.preventDefault();
		}
		if(isSubmitted){ return false; }
		var frm = document.getElementById('modal_form_form');
		if(!frm){ return false; }
		var isError = false;

		if(jQuery.trim(frm.room.value)==""){
			jQuery('#room_group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#room_group').removeClass('has-error');
		}

		if(isValidDateFormat(frm.date.value)){
				jQuery('#date_group').removeClass('has-error');
		}else{
			jQuery('#date_group').addClass('has-error');
			isError = true;
		}

		if(isValidTimeFormat(frm.from.value)){
			jQuery('#from_group').removeClass('has-error');
		}else{
			jQuery('#from_group').addClass('has-error');
			isError = true;
		}
		if(isValidTimeFormat(frm.to.value)){
			jQuery('#to_group').removeClass('has-error');
		}else{
			jQuery('#to_group').addClass('has-error');
			isError = true;
		}

		if(isError){
			jQuery('#modal_form_alert').html('Please input valid data').removeClass().addClass('alert alert-danger').show();
			return false;
		}else{
			isSubmitted = true;
			jQuery('#modal_form_alert').html('Saving ...').removeClass().addClass('alert alert-info').show();
			jQuery('#modal_button_submit').attr('disabled','disabled').addClass('disabled');
			// submit
			ajaxForm({
				'url':ajaxUrl, 'form':frm, 'log':false,
				'complete':function(response){
					isSubmitted = false;
					jQuery('#modal_button_submit').removeAttr('disabled').removeClass('disabled');
					if(response.error!=""){
						// error occured
						jQuery('#modal_form_alert').html(response.error);
						jQuery('#modal_form_alert').removeClass().addClass('alert alert-danger').show();
					}else{
						// success
						jQuery('#modal_form_alert').html('').removeClass().hide();
						jQuery('#modal_form').modal('hide');
						jQuery('#message_bar').html('The room has been updated successfully');
						jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
						displayTable(response.data);
					}
				}//complete
			});
		}
	} //.submitForm

	function deleteBlock(id){
		if(!confirm("Are you to delete this record?")){ return false;}
		if(!id){ return false;}
		isBusy = modalWait(true,"Deleting","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				ajaxAbort();
				return true;
			}else{
				return false;
			}
		});
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'delete','id':id,'token':''}, 'log':false,
			'complete':function(response){
				isBusy = modalWait(false);
				if(response.error!=""){
					// error occured
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					// success
					jQuery('#message_bar').html('The record has been deleted successfully');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
					displayTable(response.data);
				}
			}
		});
	}
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container">
	<!-- breadcrumb -->
	<ol class="breadcrumb">
		<li><a href="index.php">Home</a></li>
		<li class="active">Shedule Block</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<!-- table -->
	<div id="table_layout">
		Loading ...
	</div>
	<!-- end table -->
</div>
<!-- end of main container -->

<!-- ----- form modal ----- -->
<div class="modal" id="modal_form" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Blocking Date Time</h4>
      </div>
      <div class="modal-body">
				<div id="modal_form_alert"></div>
				<div id="modal_form_body"></div>
      </div>
      <div class="modal-footer">
				<button type="button" class="btn btn-primary" id="modal_button_submit" onclick="submitForm();">&nbsp; Save &nbsp; </button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- ----- end form modal ----- -->
<?php
	// include footer
	include('footer.php');
?>
<?php /* ----- end client side -----> */ ?>
