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
				$sql = "SELECT room_id,room_num,description,hidden,`session` FROM tb_room WHERE room_id=".$db->quote($id);
				$data = $db->selectQuery($sql);
				if(count($data)>0){
					$response = loadForm($data[0],$data[0]['room_id']);
				}else{
					$response['error'] = "Data is not found";
					break;
				}
				break;
			// insert data
			case 'insert':
					if(empty($_GET['roomnum'])){
						$response['error'] = "Invalid arguments";
						break;
					}
					$room = $db->clean($_GET['roomnum']);
					$description = $db->cleanText($_GET['description']);
					$session = $db->clean($_GET['session']);
					if(!empty($_GET['hidden']) && (int)$_GET['hidden']==1){
						$hidden = 1;
					}else{
						$hidden = 0;
					}
					// insert
					$sql = 'INSERT INTO tb_room(room_num,description,hidden,`session`) VALUES('.$db->quote($room).','.$db->quote($description).','.$db->quote($hidden).','.$db->quote($session).')';
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
				if(empty($_GET['id']) || empty($_GET['roomnum']) ){
					$response['error'] = "Invalid arguments";
					break;
				}
					$id = $db->clean($_GET['id']);
					$room = $db->clean($_GET['roomnum']);
					$description = $db->cleanText($_GET['description']);
					$session = $db->clean($_GET['session']);
					if(!empty($_GET['hidden']) && (int)$_GET['hidden']==1){
						$hidden = 1;
					}else{
						$hidden = 0;
					}
					// update
					$sql = 'UPDATE tb_room SET room_num='.$db->quote($room).',description='.$db->quote($description).',hidden='.$db->quote($hidden).',`session`='.$db->quote($session).' WHERE room_id='.$db->quote($id);
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
				$db->deleteQuery('Delete from tb_room where room_id='.$db->quote($id));
				if($db->error){
					$response['error'] = "Sorry, unexpected database error";
					break;
				}else{
					$response['error'] = '';
					$response = selectTable();
				}
				break;
			case 'updatehidden':
				if(empty($_GET['id'])){
					$response['error'] = "Invalid arguments";
					break;
				}
				// update
				$id = $db->clean($_GET['id']);
				$checked = $db->clean($_GET['checked']);
				$db->updateQuery('Update tb_room set hidden='.$db->quote($checked).' where room_id='.$db->quote($id));
				if($db->error){
					$response['error'] = "Sorry, unexpected database error";
					break;
				}else{
					$response['error'] = '';
					$response['data'] = 1;
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
		$sql = "SELECT room_id,room_num,description,`session`,hidden FROM tb_room ORDER BY room_num";
		$dataRoom = $db->selectQuery($sql);
		if($db->error!=""){
			$response['error'] = "Unexpected database error";
			$response['data'] = 0;
			return false;
		}
		if(count($dataRoom)<=0){
			$html = "There is no room data. <a href='#' onclick='addForm(); return false;'>Click here</a> to add a new room.";
		}else{
			$html='<table class="table table-striped table-bordered table-hover">';
			$html.='<thead>';
			$html.='		<th style="width: 30%;">Room Number</th>';
			$html.='		<th style="width: 20%;">Description</th>';
			$html.='		<th style="width: 20%;">Session</th>';
			$html.='		<th style="width: 20%;" class="td-hidden">Hidden</th>';
			$html.='		<th style="width: 10%;"><button onclick="addForm();" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Add New Room</button></th>';
			$html.='	</thead>';
			$html.='	<tbody>';
			for($i=0; $i<count($dataRoom); $i++){
				$html.='<tr id="'.$dataRoom[$i]['room_id'].'">';
				$html.='	<td>'.htmlentities(stripslashes($dataRoom[$i]['room_num'])).'</td>';
				$html.=' <td>'.htmlentities(stripslashes($dataRoom[$i]['description'])).'</td>';
				$html.='	<td>'.$dataRoom[$i]['session'].'</td>';
				if((int)$dataRoom[$i]['hidden']==1){
					$html.='	<td class="td-hidden"><input type="checkbox" value="'.$dataRoom[$i]['room_id'].'" checked=true onchange="updateHidden(this);"></td>';
				}else{
					$html.='	<td class="td-hidden"><input type="checkbox" value="'.$dataRoom[$i]['room_id'].'" onchange="updateHidden(this);"></td>';
				}
				$html.='	<td class="icons">';
				$html.=' 		<a href="#" onclick="editForm(\''.$dataRoom[$i]['room_id'].'\'); return false;" title="Edit"><span class="glyphicon glyphicon-pencil"></span></a>';
				$html.=' 		&nbsp; <a href="#" onclick="deleteRoom(\''.$dataRoom[$i]['room_id'].'\'); return false;" title="Delete"><span class="glyphicon glyphicon-remove"></span></a>';
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
			$roomnum = htmlentities(stripslashes($frmData['room_num']));
			$description = htmlentities(stripslashes($frmData['description']));
			$session = $frmData['session'];
			$hidden = $frmData['hidden'];
		}else{
			$roomnum = '';
			$description = '';
			$session = '';
			$hidden = '';
		}
		$html = '<form id="modal_form_form" action="'.$action.'" method="'.$method.'" enctype="'.$enctype.'" accept-charset="UTF-8" autocomplete="off" novalidate onsubmit="return false;">';
		$html.= '<div class="form-group row" id="room_group">';
		$html.= '		<div class="col-xs-12 col-sm-3">';
		$html.= '				<label for="room_num" class="control-label">Room Number:</label>';
		$html.= '		</div>';
		$html.= '		<div class="col-xs-12 col-sm-9">';
		$html.= '			<input type="text" class="form-control" id="room_num" name="roomnum" value="'.$roomnum.'" >';
		$html.= '		</div>';
		$html.= '</div>';

		$html.= '<div class="form-group row" id="description_group">';
		$html.= '	<div class="col-xs-12 col-sm-3">';
		$html.= '		<label for="description" class="control-label">Description:</label>';
		$html.= '	</div>';
		$html.= '	<div class="col-xs-12 col-sm-9">';
		$html.= '			<input type="text" class="form-control" id="description" name="description" value="'.$description.'" >';
		$html.= '	</div>';
		$html.= '</div>';

		$html.= '<div class="form-group row" id="session_group">';
		$html.= '	<div class="col-xs-12 col-sm-3">';
		$html.= '		<label for="session" class="control-label">Session:</label>';
		$html.= '	</div>';
		$html.= '	<div class="col-xs-12 col-sm-9">';
		$html.= '		<select class="form-control" id="session" name="session">';
		$html.= '		<option value="60" '.($session==60?'selected':'').'>60 Minutes</option>';
		$html.= '		<option value="90" '.($session==90?'selected':'').'>90 Minutes</option>';
		$html.= '		<option value="120" '.($session==120?'selected':'').'>120 Minutes</option>';
		$html.= '		</select>';
		$html.= '	</div>';
		$html.= '</div>';

		$html.= '<div class="form-group row" id="hidden_group">';
		$html.= '	<div class="col-xs-12 col-sm-3">';
		$html.= '		<label for="hidden" class="control-label">Hidden:</label>';
		$html.= '	</div>';
		$html.= '	<div class="col-xs-12 col-sm-9">';
		$html.= '			<input type="checkbox" class="form-control" id="hidden" name="hidden" value=1 '.($hidden?'checked':'').' >';
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
			jQuery('#room_num').focus();
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

		if(jQuery.trim(frm.roomnum.value)==""){
			jQuery('#room_group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#room_group').removeClass('has-error');
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
						jQuery('#message_bar').html('The record has been updated successfully');
						jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
						displayTable(response.data);
					}
				}//complete
			});
		}
	} //.submitForm

	function deleteRoom(id){
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
					jQuery('#message_bar').html('Room has been deleted successfully');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
					displayTable(response.data);
				}
			}
		});
	}//.deleteRoom

	function updateHidden(obj){
		if(!obj){ return false; }
		var checked = 0;
		if(obj.checked){
			checked = 1;
		}
		id = obj.value;
		isBusy = modalWait(true,"Updating","Please wait",function(){
			if(confirm("Are you sure to abort this process?")){
				ajaxAbort();
				return true;
			}else{
				return false;
			}
		});
		//update
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'updatehidden','checked':checked,'id':id,'token':''}, 'log':true,
			'complete':function(response){
				isBusy = modalWait(false);
				if(response.error!=""){
					// error occured
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
					// rollback
					obj.checked = !obj.checked;
				}else{
					// success
					jQuery('#message_bar').html('Room has been updated successfully');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
				}
			}
		});
	} //.updateHidden
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
		<li class="active">Room Manager</li>
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
        <h4 class="modal-title">Room Management</h4>
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
