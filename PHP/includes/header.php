<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Room Booking">
	<meta name="keywords" content="Room Booking">
	<meta name="author" content="">

	<title>Room Booking</title>

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

	<!-- link to bootstrap external css file -->
	<link rel="stylesheet" href="css/bootstrap.min.css">

	<!-- link to our custom external css file to override bootstrap css -->
	<link rel="stylesheet" href="css/customStyles.css">
	<link rel="stylesheet" href="css/printStyles.css" media="print">

	<!-- jQuery (necessary for Bootstrap's JS plugins) -->
	<script src="js/jquery.min.js"></script>

	<!-- link to required Bootstrap JS files -->
	<script src="js/bootstrap.min.js"></script>

	<!-- link to Bootstrap Datetime picker -->
	<link rel="stylesheet" href="css/bootstrap-datetimepicker.min.css">
	<script src="js/moment.min.js"></script>
	<script src="js/bootstrap-datetimepicker.min.js"></script>

	<!-- Link to custom js -->
	<script src="js/java.js"></script>

	<!--- Global variables --->
	<script type="text/javascript">
		var ajaxUrl = "<?php echo ($_SERVER['PHP_SELF']);?>";
	</script>
</head>

<body>
	<!-- header -->
	<header>
		<div class="container">
			<?php if(isset($_SESSION['user_id']) && $_SESSION['user_id']!=''): ?>
				<a href="logout.php" class="link-loginout"><img src="images/logout.png" style="height:24px;"> &nbsp; Log out</a>
			<?php else: ?>
				<a href="register.php" class="link-loginout"><img src="images/userregister.png" style="height:24px;"> &nbsp; Register</a>
			<?php endif; ?>

			<h2 id="header_heading"><a href="index.php" title="home">Room Booking</a></h2>
			<div class="clear clearfix"></div>
		</div>
		<div class="container">
			<div id="nav_main">
				<?php if(isset($_SESSION['user_id']) && $_SESSION['user_id']!='' && isset($user['role']) && $user['role']==1): ?>
					<div class="dropdown" style="float:right;">
						<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
							View as User
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
							<li><a href="admin/index.php">Admin</a></li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>
	<!-- end of header -->

	<!-- content -->
	<section id="content">
		<!-- message top -->
		<div id="message_bar" role="alert"></div>
