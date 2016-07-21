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
				$response = selectTable();
				break;
			// delete data
			case 'deleteuser':
				if(empty($_GET['userid'])){
					$response['error'] = 'Unknown User ID';
					break;
				}
				$userId = $db->clean($_GET['userid']);
				// cannot delete admin user
				$dataUser = $db->selectQuery("Select ifnull(role,0) as role from tb_user Where user_id=".$db->quote($userId));
				if($dataUser[0]['role']==1){
					$response['error'] = "Sorry, cannot delete administrator user.";
					break;
				}

				// delete attendee
				$db->deleteQuery("Delete from tb_attendant where booking_id IN (SELECT booking_id FROM tb_booking WHERE user_id=".$db->quote($userId).")");
				//delete booking
				$db->deleteQuery('Delete from tb_booking where user_id='.$db->quote($userId));
				// delete user
				$db->deleteQuery('Delete from tb_user where user_id='.$db->quote($userId));
				if($db->error){
					$response['error'] = "Sorry, unexpected error occured.";
					$response['data'] = '';
				}else{
					$response = selectTable();
				}
				break;
			// block user
			case 'blockuser':
				if(empty($_GET['userid']) || !isset($_GET['blocked'])){
					$response['error'] = 'Unknown variables';
					break;
				}
				$userId = $db->clean($_GET['userid']);
				// cannot block admin user
				$dataUser = $db->selectQuery("Select ifnull(role,0) as role from tb_user Where user_id=".$db->quote($userId));
				if($dataUser[0]['role']==1){
					$response['error'] = "Sorry, cannot block administrator user.";
					break;
				}
				// block unblock
				$blocked = intval($db->clean($_GET['blocked']));
				$sql = "Update tb_user set blocked=".$db->quote($blocked)." Where user_id=".$db->quote($userId);
				$db->updateQuery($sql);
				if($db->error){
					$response['error'] = "Sorry, unexpected error occured.";
					$response['data'] = '';
				}else{
					$response['error'] = '';
					$response['data'] = $blocked;
				}
				break;
			case 'loadEditForm':
				if(empty($_GET['userid'])){
					$response['error'] = 'Unknown variables';
					break;
				}
				$userId = $db->clean($_GET['userid']);
				$sql = "SELECT user_id,firstname,lastname,phone,role FROM tb_user Where user_id=".$db->quote($userId);
				$data = $db->selectQuery($sql);
				if(!$data && count($data)==0){
					$response['error'] = 'Unknown user';
					break;
				}else{
					$response = loadForm($data[0]);
				}
				break;
				// update user role
				case 'updateuserrole':
					if(empty($_GET['userid']) || !isset($_GET['role'])){
						$response['error'] = 'Unknown variables';
						break;
					}
					$userId = $db->clean($_GET['userid']);

					// update
					$role = intval($db->clean($_GET['role']));
					$sql = "Update tb_user set role=".$db->quote($role)." Where user_id=".$db->quote($userId);
					$db->updateQuery($sql);
					if($db->error){
						$response['error'] = "Sorry, unexpected error occured.";
						$response['data'] = '';
					}else{
						$response['error'] = '';
						$response['data'] = $role;
					}
					break;
			default:
				break;
		}
		echo json_encode($response);
		exit();
	}// .task
	// --- end response to any AJAX action ---
	// select table
	function selectTable(){
		global $db;
		$response = array('error'=>'Unexpected error','data'=>null,'msg'=>null);
		$html = '';
		$sqlCount = "SELECT count(*) as rows FROM tb_user";
		$sql = "SELECT user_id,firstname,lastname,phone,blocked,role FROM tb_user ORDER BY firstname,lastname,user_id,phone";
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
		$dataUser = $db->selectQuery($sql);
		// html
		if($db->error!="" || $dataUser===false){
			$response['error'] = "Unexpected database error";
			$response['data'] = 0;
			return $response;
		}

		if(count($dataUser)==0){
			$html.= "There is not any booking.";
		}else{
			$html.='<table class="table table-striped table-bordered table-hover">';
			$html.='<thead>';
			$html.='		<th style="width: 20%;">First Name</th>';
			$html.='		<th style="width: 20%;">Last Name</th>';
			$html.='		<th style="width: 20%;">Email</th>';
			$html.='		<th style="width: 15%;">Phone number</th>';
			$html.='		<th style="width: 15%;">Blocked</th>';
			$html.='		<th style="width: 10%;">&nbsp;</th>';
			$html.='	</thead>';
			$html.='	<tbody>';
			for($i=0; $i<count($dataUser); $i++){
				$html.='<tr>';
				$html.='	<td>'.htmlentities(stripslashes($dataUser[$i]['firstname'])).'</td>';
				$html.='	<td>'.htmlentities(stripslashes($dataUser[$i]['lastname'])).'</td>';
				$html.='	<td>'.htmlentities(stripslashes($dataUser[$i]['user_id'])).'</td>';
				$html.='	<td>'.htmlentities(stripslashes($dataUser[$i]['phone'])).'</td>';
				if($dataUser[$i]['blocked']==1){
						$html.='<td class="blocked"><input type="checkbox" checked="true" onchange="blockUser(this,\''.$dataUser[$i]['user_id'].'\')"></td>';
				}else{
						$html.='<td class="blocked"><input type="checkbox" onchange="blockUser(this,\''.$dataUser[$i]['user_id'].'\')"></td>';
				}
				$html.='	<td class="icons">';
				$html.='		<a href="#" onclick="loadEditForm(\''.$dataUser[$i]['user_id'].'\'); return false;" title="Edit User"><span class="glyphicon glyphicon-pencil"></span></a>';
				$html.='		&nbsp; <a href="#" onclick="deleteUser(\''.$dataUser[$i]['user_id'].'\'); return false;" title="Delete User"><span class="glyphicon glyphicon-remove"></span></a>';
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


	// generate form
	function loadForm($frmData=null){
		$response = array('error'=>'Unexpected error','data'=>null,'msg'=>null);
		$action = htmlspecialchars($_SERVER["PHP_SELF"]);
		if($frmData){
			$fName = $frmData['firstname'];
			$lName = $frmData['lastname'];
			$email = $frmData['user_id'];
			$phone = $frmData['phone'];
			$role = $frmData['role'];
		}else{
			$fName = '';
			$lName = '';
			$email = '';
			$phone = '';
			$role = '';
		}
		$html = '<form id="modal_form_form" action="'.$action.'" method="post" autocomplete="off" novalidate onsubmit="return false;">';
		$html.= '<div class="form-group" id="room_group">';
		$html.= '		<div class="col-xs-12 col-sm-3">';
		$html.= '				<label for="userrole" class="control-label">User Type:</label>';
		$html.= '		</div>';
		$html.= '		<div class="col-xs-12 col-sm-9">';
		$html.= '			<select class="form-control" id="userrole" name="role">';
		$html.= '				<option value="0">User</option>';
		$html.= '				<option value="1" '.($role==1?'selected':'').' >Administrator</option>';
		$html.= '			</select>';
		$html.= '		</div>';
		$html.= '</div>';
		$html.= '<div>&nbsp;</div>';
		$html.= '<input type="hidden" name="task" value="updateuserrole">';
		$html.= '<input type="hidden" name="userid" value="'.$email.'">';
		$html.= '</form>';
		$response['error'] = '';
		$response['data'] = $html;
		return $response;
	} //.loadForm

	// select user table


	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('header.php');
?>

<!-- script -->
<script type="text/javascript">
	var isBusy = false;

	jQuery(function () {
		loadTable();
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
	function gotoPage(paged){
		loadTable(paged);
		return false;
	}// .paginate

	function displayTable(data){
		jQuery('#table_layout').html(data);
	} //.displayTable

	function deleteUser(userid){
		if(isBusy){ return false; }
		if(!confirm('Are you sure to delete the user and the booking?\nThis action cannot be undone!')){ return false; }
		if(!userid){
			return false;
		}
		// delete
		isBusy = modalWait(true,'Deleting user ...');
		jQuery('#message_bar').html('Deleting ... ').removeClass().addClass('alert alert-info').show();
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'deleteuser','token':'','userid':userid},
			'complete':function(response){
				if(response.error!=""){
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
				}else{
					jQuery('#message_bar').html('The user has been deleted successfully.');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
					displayTable(response.data);
				}
				isBusy = modalWait(false);
			}// .complete
		});
	} //.deleteUser


	function blockUser(obj,userid){
		if(isBusy){ return false; }
		if(!obj || !userid){
			return false;
		}
		var blocked;
		if(obj.checked){
			blocked = 1;
		}else{
			blocked = 0;
		}
		//update
		isBusy = modalWait(true,'Updating user ...');
		jQuery('#message_bar').html('Updating ... ').removeClass().addClass('alert alert-info').show();
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'blockuser','token':'','userid':userid,'blocked':blocked},
			'complete':function(response){
				if(response.error!=""){
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
					// revert
					obj.checked = ! obj.checked;
				}else{
					jQuery('#message_bar').html('The user have been updated successfully.');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
				}
				isBusy = modalWait(false);
			}// .complete
		});
	} //.blockUser

	function loadEditForm(userid){
		isBusy = modalWait(true,'Editing user ...');
		ajaxData({
			'url':ajaxUrl, 'data':{'task':'loadEditForm','token':'','userid':userid},'log':false,
			'complete':function(response){
				if(response.error!=""){
					jQuery('#message_bar').html(response.error);
					jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();

				}else{
					jQuery('#message_bar').html('');
					jQuery('#message_bar').removeClass().hide();
					jQuery('#modal_form').modal({'show':true,'keyboard':false,'backdrop':'static'});
					jQuery('#modal_form_body').html(response.data);
					jQuery('#modal_form_alert').html('').removeClass().hide();
					jQuery('#modal_button_submit').removeAttr('disabled').removeClass('disabled');
				}
				isBusy = modalWait(false);
			}// .complete
		});
	} //.loadEditForm

	function submitForm(evt){
		if(evt){
			evt.preventDefault();
		}else if(this.event){
			this.event.preventDefault();
		}
		if(isBusy){ return false; }
		var frm = document.getElementById('modal_form_form');
		if(!frm){ return false; }
		isBusy = true;
		jQuery('#modal_form_alert').html('Saving ...').removeClass().addClass('alert alert-info').show();
		jQuery('#modal_button_submit').attr('disabled','disabled').addClass('disabled');
		// submit
		ajaxForm({
			'url':ajaxUrl, 'form':frm, 'log':false,
			'complete':function(response){
				isBusy = false;
				jQuery('#modal_button_submit').removeAttr('disabled').removeClass('disabled');
				if(response.error!=""){
					// error occured
					jQuery('#modal_form_alert').html(response.error);
					jQuery('#modal_form_alert').removeClass().addClass('alert alert-danger').show();
				}else{
					// success
					jQuery('#modal_form_alert').html('').removeClass().hide();
					jQuery('#modal_form').modal('hide');
					jQuery('#message_bar').html('The user have been updated successfully.');
					jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
				}
			}//complete
		});
	} //.submitForm
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
		<li class="active">User Manager</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<!-- table -->
	<div id="table_layout">
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
