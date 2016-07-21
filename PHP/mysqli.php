<?php
    $host_name  = "db628238005.db.1and1.com";
    $database   = "db628238005";
    $user_name  = "dbo628238005";
    $password   = "vatoLeng@26";


    $connect = mysqli_connect($host_name, $user_name, $password, $database);

    if(mysqli_connect_errno())
    {
    echo '<p>connection error: '.mysqli_connect_error().'</p>';
    }
    else
    {
    echo '<p>connection success.</p>';
    }
?>
