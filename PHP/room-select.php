<?php
// restricted access
require_once('./includes/access.php');
?>

<?php
	/* ----- server side ----- */
	// select room
	$dataRoom = $db->selectQuery('SELECT `room_id`,`room_num` FROM `tb_room` where hidden is null or hidden<>1 order by room_num');

	/* ----- end server side ----- */
?>


<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('./includes/header.php');
?>

<!-- script -->
<script type="text/javascript">
  function submitForm(frm){
    if(frm.room.value!=""){
      return true;
    }
    return false;
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
		<li class="active">Room</li>
	</ol>
	<br>
	<!-- end of breadcrumb -->
	<!-- form -->
	<form class="form-horizontal" action="room-booking.php" method="get" onsubmit="return submitForm(this);">
		<div class="form-group">
			<label for="room" class="col-sm-2 control-label">Select Room</label>
			<div class="col-sm-10">
				<select class="form-control" id="room" name="room">
					<option value="">Room number</option>
					<?php if(count($dataRoom)>0): for($i=0; $i<count($dataRoom); $i++): ?>
					<option value="<?=$dataRoom[$i]['room_id'];?>"><?=$dataRoom[$i]['room_num'];?></option>
				<?php endfor; endif; ?>
				</select>
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary">Select</button>
			</div>
		</div>
	</form>
	<!-- end of form -->
</div>
<!-- end of main container -->

<?php
	// include footer
	include('./includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
