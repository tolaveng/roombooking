<?php
/* Global variables and functions */
$phpself = htmlspecialchars($_SERVER["PHP_SELF"]);


/* check, validate functions  */

function isValidEmail($email){
  if(!empty($email)){
    if(function_exists('filter_var')){
      // Remove all illegal characters from email
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);
      if(filter_var($email, FILTER_VALIDATE_EMAIL)){
          return true;
      }
    }else{
      $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
      if(preg_match($pattern, $emailaddress) === 1){
          return true;
      }
    }
  }
  return false;
}

function isValidUrl($url){
  return (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$url));
}

// valid date format dd/mm/yyyy only, return yyyy-mm-dd
function isValidDateFormat($date){
  $date = trim($date);
  if(strpos($date,'/')===false){
    return false;
  }
  $dates = explode('/',$date);
  if(count($dates)!=3){
    return false;
  }
  // convert to yyyy-mm-dd
  $strDate = $dates[2].'-'.$dates[1].'-'.$dates[0];
  if(strtotime($strDate)){
    return $strDate;
  }
  return false;
}

// valid time format HH:MM ,if AM/PM, covert to 24
function isValidTimeFormat($time){
  $time = strtoupper(trim($time));
  $is24 = true;
  $ampm = '';
  if(strpos($time,':')===false){
    return false;
  }
  if(strpos($time,'M')){
    $is24 = false;
    // remove AM or PM
    if(strpos($time,'AM')){
      $ampm = 'AM';
      $time = str_replace('AM','',$time);
    }else{
      $ampm = 'PM';
      $time = str_replace('PM','',$time);
    }
  }
  $times = explode(':',$time);
  if(count($times)!=2){
    return false;
  }
  $hour = (int)trim($times[0]);
  $minute = (int)trim($times[1]);
  if($is24){
    if($hour>24){
      return false;
    }
  }else{
    if($hour>12){
      return false;
    }
    // covert to 24
    if($ampm=='AM'){
      if($hour==12){
        // 12AM
        $hour = 0;
      }
    }else{
      if($hour==12){
        // 12PM
      }else{
        // 1PM
        $hour = $hour+12;
      }
    }
  }
  if($minute<0 || $minute>59){
    return false;
  }
  // to string
  $strTime = $hour.':'.$minute;
  if(strtotime($strTime)){
    return $strTime;
  }
  return false;
}// .valid Time

// valid allow only number
function isValidInt($strNum){
  $strNum = trim($strNum);
  // remove all characters
  $strNum = preg_replace('/\D/', '', $strNum);

  if(function_exists('filter_var')){
    if(filter_var($strNum, FILTER_VALIDATE_INT)!==false){
        return filter_var($strNum, FILTER_VALIDATE_INT);
    }
  }elseif(is_numeric($strNum)){
    return $strNum;
  }
  return false;
}
/* end check validate */

/* password hash */
function passwordHash($str){
  return hash('sha256',md5($str));
}

?>
