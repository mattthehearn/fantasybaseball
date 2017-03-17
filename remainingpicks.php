<?php
  session_start();
  $starttime=time();
  //print $starttime;
  include('simple_html_dom.php');
  include('functions.php');
  include('db_pass.php');
  $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
  mysql_select_db("matthear_fantasybaseball",$db);

  start_html("Remaining Picks");
  print_header();

  #var_dump($_POST);
  #var_dump($_SESSION);

  if ($_SESSION['admin']==1 && isset($_POST['pick_id']) && isset($_POST['fantteam'])) {
    $fantteam=$_POST['fantteam'];
    $pick_id=$_POST['pick_id'];
    $update_query="update DraftPicks set FantasyTeam=$fantteam where ID=$pick_id";
    #print $update_query."<P>\n";
    $update_result=mysql_query($update_query,$db); 
  }
  if ($_SESSION['admin']==1 && isset($_POST['pick_id']) && $_POST['delete']=='yes') {
    $pick_id=$_POST['pick_id'];
    $update_query="delete from DraftPicks where ID=$pick_id";
    #print $update_query."<P>\n";
    $update_result=mysql_query($update_query,$db); 
  }
 
  if (isset($_GET['season'])) {
    $season=$_GET['season'];
  } else {
    $season=date('Y');
  }
  if (isset($_GET['league'])) {
    $league=$_GET['league'];
  } else {
    $league=1;
  }

  #get number of teams:
  $team_query="select ID from FantasyTeams where Season=$season and League=$league";
  #print $team_query."<P>\n";
  $team_result=mysql_query($team_query);
  $numteams=mysql_num_rows($team_result);
  
  $draft_query="select *,DraftPicks.ID as PickID from DraftPicks,FantasyTeams where ((DraftPicks.Season=$season) and (DraftPicks.Pick>((select Pick from DraftResult where Season=$season order by pick desc limit 1))) and FantasyTeams.ID=DraftPicks.FantasyTeam)";
  $draft_result=mysql_query($draft_query);
 
  print "<TABLE border=1>" ;
  while ($draft_row=mysql_fetch_array($draft_result)) {
    print "<TR>";
    $pick=$draft_row['Pick'];
    $round=floor($pick/$numteams)+1;
    $rpick=$pick % $numteams;
    if ($rpick==0) {
      $rpick=12;
      $round--;
    }
    print "<TD>".$round."-".$rpick." (#".$pick.")</TD>";
    $teamid=$draft_row['FantasyTeam'];
    print "<TD><A HREF=http://www.matthearn.com/fantasybaseball/team.php?teamid=$teamid>".$draft_row['Owner']."</A></TD>"; 
    print "<TD><A HREF=http://www.matthearn.com/fantasybaseball/changepick.php?pick_id=".$draft_row['PickID'].">Change</A></TD>"; 

    #var_dump($draft_row);
    
    print "</TR>";
  }
  end_html();

?>
