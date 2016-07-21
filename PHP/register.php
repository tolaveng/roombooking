<?php
require_once('includes/token.php');

require_once('./includes/functions.php');
require_once('./includes/dbhandler.php');
$db = new dbhandler();
if($db->error!=''){
	// error occurred, exit
	echo $db->error;
	exit();
}

	/* ----- server side ----- */

	// --- response to any AJAX action task request ---
	if(isset($_REQUEST['task']) && trim($_REQUEST['task'])!=''){
		// response array of error and data to client
		$response = array('error'=>'undefined','data'=>null);

		// check form token
		if(!tokenCheck()){
			exit();
		}

		// insert new user
		if($_REQUEST['task']=='insert') {
			$email = $db->clean($_GET['email']);
			$firstname = $db->clean($_GET['firstname']);
			$lastname = $db->clean($_GET['lastname']);
			$phone = $db->clean($_GET['phone']);
			$password = passwordHash($db->clean($_GET['password']));
			$verifyCode = rand(100000,999999);
			// check email
			if(!isValidEmail($email)){
				$response['error'] = 'The email is invalid';
				echo json_encode($response);
				exit();
			}
			// check duplicate email
			$sql = "SELECT user_id from tb_user where user_id=".$db->quote($email);
			$data = $db->selectQuery($sql);
			if(count($data)>0){
				$response['error'] = 'The email address has already registered';
			}else{
				// insert new user
				$sql = "INSERT INTO tb_user(user_id,password,firstname,lastname,phone,verify_code,verified,blocked,role) values("
					.$db->quote($email).","
					.$db->quote($password).","
					.$db->quote($firstname).","
					.$db->quote($lastname).","
					.$db->quote($phone).","
					.$db->quote($verifyCode).","
					."0,0,0)";

				$db->insertQuery($sql);
				if($db->error!=''){
					//$response.error = $db->error;
					$response['error'] = "Unexpected error, please register again later";
				}else{
					// successful
					$_SESSION['user_id'] = $email;
					$response['error'] = '';
					//redirect url
					$mustVerify = false; // email must verified
					if($mustVerify){
						$response['data'] = 'verify.php'; // redirect to verify.php
					}else{
						$response['data'] = 'index.php'; // redirect to index.php
					}
				}
			}
		}// .insert
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
	// form submit
	function submitForm(evt,frm){
		var e = evt || this.event;
		e.preventDefault();
    // prevent double submit
    if(isSubmitted){ return false; }
		var isError = false;
    // check form input error
    if(isValidEmail(frm.email.value)){
				jQuery('#email-group').removeClass('has-error');
				jQuery('#email-info').html('');
		}else{
			jQuery('#email-group').addClass('has-error');
			jQuery('#email-info').html('Please use your valid Swinburne email address.')
			isError = true;
		}

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

		if(jQuery.trim(frm.password.value)==""){
			jQuery('#password-group').addClass('has-error');
			isError = true;
		}else if(frm.password.value!=frm.repassword.value){
			jQuery('#repassword-group').addClass('has-error');
			isError = true;
		}else{
			jQuery('#password-group').removeClass('has-error');
			jQuery('#repassword-group').removeClass('has-error');
		}

		if(isError){
			jQuery('#message_bar').html("Please correct information below");
      jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
		}else{
      jQuery('#message_bar').html('Form is submitting ...');
      jQuery('#message_bar').removeClass().addClass('alert alert-info').show();
      jQuery('#btnSubmit').attr('disabled','disabled').addClass('disabled');
			// call ajax from java.js
			// ajaxUrl is defined in header.php
			isSubmitted = true;
			ajaxForm({
				'url':ajaxUrl,
				'form':frm,
				'complete':function(response){
					if(response.error!=""){
						// error occured
						isSubmitted = false;
						jQuery('#message_bar').html(response.error);
						jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
						jQuery('#btnSubmit').removeAttr('disabled').removeClass('disabled');
						// which error
						if(response.error.indexOf('email')>-1){
							jQuery('#email-group').addClass('has-error');
						}
					}else{
						// success
						jQuery('#message_bar').html('Register successfully. You are redirecting to <a href="'+response.data+'">home page</a>.');
			      jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
			      jQuery('#btnSubmit').attr('disabled','disabled').addClass('disabled');
						window.location.href = response.data;
					}
				}
			});
    }
    return false;
	}// .submitForm
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
	#user-register{
		max-width : 780px;
		margin: 0 auto;
	}
	#email-info{
		font-size: 0.8em;
	}
</style>
<!-- end of style -->

<!-- main container -->
<div class="container-fluid">
	<div id="user-register">
		<h3>Register form</h3>
		<form id="form-login" method="post" class="form-horizontal" onsubmit="submitForm(event,this); return false;">
	    <div class="form-group" id="email-group">
				<label for="email" class="col-sm-2 control-label">Email</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="email" name="email">
					<div id="email-info" class="text-danger"></div>
				</div>
			</div>
			<div class="form-group" id="firstname-group">
				<label for="firstname" class="col-sm-2 control-label">First name</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="firstname" name="firstname">
				</div>
			</div>
			<div class="form-group" id="lastname-group">
				<label for="lastname" class="col-sm-2 control-label">Last name</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lastname" name="lastname">
				</div>
			</div>
			<div class="form-group">
				<label for="phone" class="col-sm-2 control-label">Phone Number</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="phone" name="phone">
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
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" id="btnSubmit" class="btn btn-default">Register</button>
				</div>
			</div>
			<input type="hidden" name="task" value="insert">
			<?php echo tokenForm(); ?>
		</form>
	</div>
</div>
<!-- end of main container -->

<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
