<?php
  $db = mysql_connect("localhost", "matthear_hearnmd", "PASSWORD");
  mysql_select_db("matthear_fantasybaseball",$db);
  $query="select * from Players where PosString like '1B%'";
  $result=mysql_query($query);
  $row_count=mysql_num_rows($result); 
  
  //If you expect more than one row of results, you can parse the rows with a while loop:
  while ($row=mysql_fetch_array($result)) {
    print "Player Name: ".$row['Name']."<BR>";
      //Obviously you replace "Name" in $row['Name'] with whatever field you're trying to get.
  } //while

  //If you did a search that should return only one row, you can just access the
  //one row without the while loop:
  $row=mysql_fetch_array($result);
  
?>
