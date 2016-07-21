<?php
// restricted access
require_once('access.php');

/* auto clean up , delete all records for the past six months */
try{
		$theDay = new DateTime("-6 months");
		$db->deleteQuery("Delete from tb_attendant where booking_id in (SELECT booking_id FROM tb_booking WHERE booking_date<='".$theDay->format('Y-m-d')."')");
		$db->deleteQuery("Delete FROM tb_booking WHERE booking_date<='".$theDay->format('Y-m-d')."'");
		$db->deleteQuery("Delete FROM tb_block WHERE block_date<='".$theDay->format('Y-m-d')."'");
}catch(Exception $e){}
/* end auto clean up */

?>

<?php /* ----- start client side -----> */ ?>
<?php
	// include header
	include('header.php');
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
<div class="container control-panel">
	<a href="book-timetable.php" title="Booking Timetable" class="control-item">
		<img src="../images/calendar.png" alt="Booking Timetable"><br>
		<span>Booking Timetable</span>
	</a>
  <a href="schedule-block.php" title="Schedule Block" class="control-item">
		<img src="../images/calendar-block.png" alt="Schedule Block"><br>
		<span>Schedule Block</span>
	</a>
	<a href="room-manager.php" title="Room Manager" class="control-item">
		<img src="../images/room-manager.png" alt="Room Manager"><br>
		<span>Room Manager</span>
	</a>
	<a href="user-manager.php" title="User Manager" class="control-item">
		<img src="../images/user-manager.png" alt="User Manager"><br>
		<span>User Manager</span>
	</a>
	<a href="profile.php" title="Profile" class="control-item">
		<img src="../images/profile-edit.png" alt="Profile"><br>
		<span>Profile</span>
	</a>
</div>
<!-- end of main container -->


<?php
	// include footer
	include('footer.php');
?>
<?php /* ----- end client side -----> */ ?>
