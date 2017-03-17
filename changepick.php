<?php
  session_start();
  include('functions.php');
  include('db_pass.php');

  if (isset($_GET['league'])) {
    $league=$_GET['league'];
  } else {
    $league=1;
  }

  start_html("Change Pick");
  print_header();

  $thispage="changepick.php?".$_SERVER['QUERY_STRING'];

  if (!isset($_SESSION['user_id']) or $_SESSION['user_id']==0) {
    if (!isset($_POST['email'])) {
      login_form($thispage); 
    } else {
      $user_row=process_login($_POST, "$thispage");
      if (isset($user_row['id'])) {
        $_SESSION['user_id']=$user_row['id'];
        $_SESSION['admin']=$user_row['admin'];
      }
    }
  }
  if (isset($_SESSION['user_id']) and $_SESSION['admin']==1 and isset($_GET['pick_id'])) {
    #print "yep";
    $pick_id=$_GET['pick_id'];
    $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
    mysql_select_db("matthear_fantasybaseball");
    $pick_query="SELECT * from DraftPicks where ID=$pick_id";
    $pick_result=mysql_query($pick_query,$db);
    $pick_row=mysql_fetch_array($pick_result);
    $season=$pick_row['Season'];
    $league=$pick_row['League'];
    $pick=  $pick_row['Pick'];
    $teamid=$pick_row['FantasyTeam'];
    
    
    $teams_query="SELECT * from FantasyTeams where Season=$season and League=$league";
    $teams_result=mysql_query($teams_query,$db);
   
    print "<FORM ACTION='remainingpicks.php' method='POST'>\n";
    print "<input type=hidden name=pick_id value=$pick_id>\n";
    print "<select name='fantteam'>\n"; 
    while ($teams_row = mysql_fetch_array($teams_result)) {
      if ($teams_row['ID']==$teamid) {
        print "<option value=".$teams_row['ID']." selected>".$teams_row['Owner']."</option>\n";
      } else {
        print "<option value=".$teams_row['ID'].">".$teams_row['Owner']."</option>\n";
      }
    }
    print "<input type='submit' value='Change'></FORM>";

    print "<FORM ACTION='remainingpicks.php' method='POST'>\n";
    print "<input type=hidden name=pick_id value=$pick_id>\n";
    print "<input type=hidden name=delete value='yes'>\n";
    print "<input type='submit' value='Delete'></FORM>";
    

  }

  end_html();
?>
