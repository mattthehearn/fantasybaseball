<?php

include('functions.php');

session_start();

var_dump($_SESSION);

start_html();
print_header();

if ($_SESSION['user_id']>0) {
  $_SESSION['user_id']=0;
  print "Logged out.";
} else {
  print "Not logged in.";
}

end_html();
?>
