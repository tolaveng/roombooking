<?php
// restricted access
require_once('includes/access.php');
?>

<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('includes/header.php');
?>

<!-- script -->
<script type="text/javascript">
</script>
<!-- end of script -->

<!-- style -->
<style type="text/css">
  .control-item{
		display : block;
		text-align : center;
		vertical-align : middle;
		text-decoration : none;
		float : left;
		padding : 10px;
		margin : 20px;
		border : 1px solid #CCCCCC;
		border-radius : 8px;
		width : 120px;
		height : 120px;

	}
	.control-item:link, .control-item:active, .control-item:visited, .control-item:hover{
		text-decoration : none;
		color : #888888;
	}
	.control-item:hover{
		border : 1px solid #0000EE;
    color : #0000EE;
	}
	.control-item img{
		margin : 0 auto;
		margin-bottom : 10px;
		text-align : center;
		vertical-align : middle;
		border : none;
	}
</style>
<!-- end of style -->

<!-- html main container -->
<div class="container">
  <a href="room-select.php" title="Room booking" class="control-item">
		<img src="images/calendar.png" alt="Room booking"><br>
		<span>Room Booking</span>
	</a>
  <a href="mybooking.php" title="My booking" class="control-item">
		<img src="images/bookinglist.png" alt="My booking"><br>
		<span>My Booking</span>
	</a>
	<a href="user-profile.php" title="Profile" class="control-item">
		<img src="images/profile-edit.png" alt="Profile"><br>
		<span>Profile</span>
	</a>
</div>
<!-- end of main container -->


<?php
	// include footer
	include('includes/footer.php');
?>
<?php /* ----- end client side -----> */ ?>
