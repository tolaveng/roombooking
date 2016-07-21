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
			// select data
			case 'select':
				break;
			// insert data
			case 'insert':
				break;
			// update data
			case 'update':
				break;
			// delete data
			case 'delete':
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
		<li class="active">Reservation</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
</div>
<!-- end of main container -->


<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
