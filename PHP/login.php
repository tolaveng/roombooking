<?php
require_once('includes/token.php');

// attempt log in
$attMax = 7;



// validate user credential
if( isset($_GET['email']) && isset($_GET['password']) ){
	// response array of error and data to client
	$response = array('error'=>'undefined','data'=>null);

	// check form token
	if(!tokenCheck()){
		exit();
	}

	// check attempted log in
	if(!isset($_SESSION['attempted'])){
		$_SESSION['attempted'] = 0;
	}else{
		$_SESSION['attempted']++;
	}
	if($_SESSION['attempted']>=$attMax){
		$response['error'] = 'You reach to the maximum attempt log in.';
		echo json_encode($response);
		exit();
	}

	// includes helper function
	require_once('./includes/functions.php');
	require_once('./includes/dbhandler.php');
	$db = new dbhandler();

	// get variables
	$email = $db->clean($_GET['email']);
	$password = passwordHash($db->clean($_GET['password']));

	// query
	$query = "Select user_id,password,role,blocked from tb_user where user_id=".$db->quote($email);
	$data = $db->selectQuery($query);

	if($db->error!=""){
		$response['error'] = $db->error;
	}else{
		if(count($data)>0){
			if(1!=(int)$data[0]['blocked']){
				if( strcmp($data[0]['password'],$password)==0 ){
					$_SESSION['user_id'] = $data[0]['user_id'];
					$response['error'] = '';
					$_SESSION['attempted'] = 0;
					if( (int)$data[0]['role']==1){
						$response['data'] = 'admin/index.php';
					}else{
						$response['data'] = 'index.php';
					}
				}else{
					// invalid password
					$response['error'] = 'Invalid email or password';
				}
			}else{
				// email is blocked
				$response['error'] = 'The email is blocked';
			}
		}else{
			// invalid user id
			$response['error'] = 'The email is not register. <a href="register.php">Click here</a> to register.';
		}
	} // ./database
	echo json_encode($response);
	exit();
} // ./isset
?>

<?php /* ----- start html ----- */ ?>
<?php
	// include header
	include('./includes/header.php');
?>

<style type="text/css">
	#form_login{
		width : 600px;
		margin: 0 auto;
	}
</style>
<script type="text/javascript">
	function checkForm(evt,frm){
		var isError = false;
		var e = evt || this.event;
		e.preventDefault();

		if(isValidEmail(frm.email.value)){
				jQuery('#email-group').removeClass('has-error');
		}else{
			jQuery('#email-group').addClass('has-error');
			isError = true;
		}

		if(frm.password.value==""){
			jQuery('#password-group').addClass('has-error');
			isError = true;
		}
		if(isError){
			jQuery('#message_bar').html("Please correct information below");
      jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
		}else{
			jQuery('#message_bar').html('Log in ...');
      jQuery('#message_bar').removeClass().addClass('alert alert-info').show();
      jQuery('#btnSubmit').attr('disabled','disabled').addClass('disabled');
			// call ajax from java.js
			// ajaxUrl is defined in header.php
			ajaxForm({
				'url':ajaxUrl, 'form':frm, 'log':false,
				'complete':function(response){
					if(response.error!=""){
						// error occured
						jQuery('#message_bar').html(response.error);
						jQuery('#message_bar').removeClass().addClass('alert alert-danger').show();
						jQuery('#btnSubmit').removeAttr('disabled').removeClass('disabled');
					}else{
						// success
						jQuery('#message_bar').html('Redirect ...');
			      jQuery('#message_bar').removeClass().addClass('alert alert-success').show();
						jQuery('#btnSubmit').attr('disabled','disabled').addClass('disabled');
						window.location.href = response.data;
					}
				}
			});
		}
		return false;
	}
</script>

<!-- html -->
<div class="container">
	<form id="form_login" action="login.php" method="get" class="form-horizontal" onsubmit="checkForm(event,this); return false;">
		<div class="form-group" id="email-group">
			<label for="email" class="col-sm-2 control-label">Email</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" id="email" name="email">
			</div>
		</div>
		<div class="form-group" id="password-group">
			<label for="password" class="col-sm-2 control-label">Password</label>
			<div class="col-sm-10">
				<input type="password" class="form-control" id="password" name="password">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" id="btnSubmit" class="btn btn-default">Log in</button>
			</div>
		</div>
		<input type="hidden" name="task" value="select">
		<?php echo tokenForm(); ?>
	</form>
</div>
<!-- end of html -->

<?php
// include footer
include('./includes/footer.php');
?>

<?php /* ----- end html ----- */ ?>
