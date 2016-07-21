<?php
// restricted access
require_once('./includes/access.php');
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
			// update data
			case 'update':
				// check
				if(!empty($_GET['curpassword'])){
					$curpassword = passwordHash($db->clean($_GET['curpassword']));
					if(strcmp($user['password'],$curpassword)!=0){
							$response['error'] = 'Current password is invalid';
							break;
					}
				}else{
					$response['error'] = 'Unknown variables';
					break;
				}
				// info
				$firstname = $db->clean($_GET['firstname']);
				$lastname = $db->clean($_GET['lastname']);
				$phone = $db->clean($_GET['phone']);
				if(empty($_GET['password'])){
					$password = "";
				}else{
					$password = passwordHash($db->clean($_GET['password']));
				}
				$verifyCode = rand(100000,999999);

				//update
				$sql = "UPDATE tb_user set firstname=".$db->quote($firstname).",lastname=".$db->quote($lastname).",phone=".$db->quote($phone).",verify_code='".$verifyCode."'";
				if($password != ""){
					$sql.= ",password=".$db->quote($password);
				}
				$sql.= "WHERE user_id='".$user['user_id']."'";
				$db->updateQuery($sql);
				if($db->error){
					$response['error'] = "Sorry, unexpected error occured.";
					$response['data'] = '';
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



	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('./includes/header.php');
?>

<!-- script -->
<script type="text/javascript">
	var isSubmitted = false;

	function submitForm(evt,frm){
		var e = evt || this.event;
		e.preventDefault();
    // prevent double submit
    if(isSubmitted){ return false; }
		var isError = false;
    // check form input error
		if(jQuery.trim(frm.firstname.value)==""){
			jQuery('#firstname-group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#firstname-group').removeClass('has-error');
		}

		if(jQuery.trim(frm.lastname.value)==""){
			jQuery('#lastname-group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#lastname-group').removeClass('has-error');
		}

		if(jQuery.trim(frm.password.value)!="" && frm.password.value!=frm.repassword.value){
			jQuery('#password-group').addClass('has-error');
			jQuery('#repassword-group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#password-group').removeClass('has-error');
			jQuery('#repassword-group').removeClass('has-error');
		}

		if(jQuery.trim(frm.curpassword.value)==""){
			jQuery('#curpassword-group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#curpassword-group').removeClass('has-error');
		}

		if(isError){
			jQuery('#message_bar').html("Please correct information below");
      jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
		}else{
      jQuery('#message_bar').html('Form is updating ...');
      jQuery('#message_bar').removeClass().addClass('alert alert-info').show();
      jQuery('#btnSubmit').attr('disabled','disabled').addClass('disabled');
			// call ajax from java.js
			// ajaxUrl is defined in header.php
			isSubmitted = true;
			ajaxForm({
				'url':ajaxUrl,
				'form':frm, 'log':false,
				'complete':function(response){
					if(response.error!=""){
						// error occured
						isSubmitted = false;
						jQuery('#message_bar').html(response.error);
						jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
					}else{
						// success
						jQuery('#message_bar').html('Profile have been updated successfully.');
			      jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
					}
					jQuery('#btnSubmit').removeAttr('disabled').removeClass('disabled');
				}
			});
    }
    return false;
	} //.submitForm
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
	#user-profile{
		max-width : 780px;
		margin: 0 auto;
	}
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container-fluid">
	<!-- breadcrumb -->
	<ol class="breadcrumb">
		<li><a href="index.php">Home</a></li>
		<li class="active">Profile</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<div id="user-profile">
		<form id="form-user" method="post" class="form-horizontal" onsubmit="submitForm(event,this); return false;">
			<div class="form-group" id="firstname-group">
				<label for="firstname" class="col-sm-2 control-label">First name</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="firstname" name="firstname" value="<?=$user['firstname'];?>" >
				</div>
			</div>
			<div class="form-group" id="lastname-group">
				<label for="lastname" class="col-sm-2 control-label">Last name</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lastname" name="lastname" value="<?=$user['lastname'];?>">
				</div>
			</div>
			<div class="form-group">
				<label for="phone" class="col-sm-2 control-label">Phone Number</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="phone" name="phone" value="<?=$user['phone'];?>">
				</div>
			</div>
			<div class="form-group" id="password-group">
				<label for="password" class="col-sm-2 control-label">Password</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="password" name="password">
				</div>
			</div>
			<div class="form-group" id="repassword-group">
				<label for="repassword" class="col-sm-2 control-label">Retype Password</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="repassword" name="repassword">
				</div>
			</div>
			<div class="form-group"> &nbsp; </div>
			<div class="form-group" id="curpassword-group">
				<label for="curpassword" class="col-sm-2 control-label">Current Password</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="curpassword" name="curpassword">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" id="btnSubmit" class="btn btn-default">Save</button>
				</div>
			</div>
			<input type="hidden" name="task" value="update">
		</form>
	</div>
</div>
<!-- end of main container -->


<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
