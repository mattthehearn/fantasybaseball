<?php
  session_start();
  include('functions.php');
  include('db_pass.php');

  $league=1; # FOR NOW

  start_html("Draft Player");
  print_header();

  $thispage="draftplayer.php?".$_SERVER['QUERY_STRING'];

  #var_dump($_POST);
  #var_dump($_SESSION);
  #print "<PRE>";
  #var_dump($_SERVER);
  #print "</PRE>";

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
  #print "aw yeah brah";
  if (isset($_SESSION['user_id']) and $_SESSION['admin']==1) {
    #print "yep";
    $season=$_GET['season'];
    $league=$_GET['league'];
    $player_id=$_GET['player_id'];
    #### CHECK AND MAKE SURE HE HASN'T BEEN DRAFTED YET DIPSHIT
    $db = mysql_connect("localhost", $mysql_user, $mysql_pass);
    mysql_select_db("matthear_fantasybaseball");
    $draft_query="SELECT * from DraftResult where Player=$player_id and League=$league and Season=$season";
    #print $draft_query."<P>\n";
    $draft_result = mysql_query($draft_query,$db);
    if (mysql_num_rows($draft_result)>0) {
      # He's already drafted, shitass
      print "He's already drafted, shitass.<P>\n";
    } else {

      $player_query="SELECT * from Players where ID=$player_id";
      $player_result = mysql_query($player_query,$db);
      $player_row = mysql_fetch_array($player_result); #there should only be one, Christ willing
      $player_name = $player_row['Name']; 
      print "Cool, $player_name ain't drafted yet.  Let's see who's up...<P>\n";
      $pick_query="SELECT Pick from DraftResult where League=$league and Season=$season order by Pick desc limit 1";
      $pick_result = mysql_query($pick_query,$db);
      $pick_row = mysql_fetch_array($pick_result); #there should only be one, Christ willing

      $numteams_query="select * from FantasyTeams where League=$league and Season=$season";
      $numteams_result = mysql_query($numteams_query,$db);
      $numteams = mysql_num_rows($numteams_result);

      $nextpick=$pick_row['Pick']+1;

      $nextpick_query = "SELECT FantasyTeam,Pick from DraftPicks where Pick>".($nextpick-1)." and League=$league and Season=$season order by pick limit 1";
      $nextpick_result = mysql_query($nextpick_query,$db);
      $nextpick_row = mysql_fetch_array($nextpick_result); #there should be just one again, jesus fuck
      
      $npfteam=$nextpick_row['FantasyTeam'];
      $truenp=$nextpick_row['Pick'];
      $npround=floor($truenp/$numteams)+1;
      $nppick =$truenp%$numteams;
      if ($nppick==0) { $nppick=$numteams; }
      print "Next pick: $truenp (round $npround, pick $nppick)<P>\n"; 
      print "Team with ID $npfteam<P>\n"; 

      $team_query = "SELECT * from FantasyTeams where ID=$npfteam";
      $team_result = mysql_query($team_query,$db);
      $team_row = mysql_fetch_array($team_result); #there should be just one again, jesus fuck

      $owner=$team_row['Owner'];
      print "Owner: ".$owner."<P>\n"; 

      $update_query="insert into DraftResult (Season, League, FantasyTeam, Player, Pick) values ($season, $league, $npfteam, $player_id, $truenp)";


      print "So, here's the query I plan to run: <BR>\n";
      print $update_query."<P>\n";
      print "We cool?<P>\n";

      print "<A HREF=http://www.matthearn.com/fantasybaseball/draftconfirm.php?season=$season&league=$league&fantasyteam=$npfteam&player_id=$player_id&pick=$truenp>Yep</A>";
     
        
    }
    #var_dump($_GET);
  }

  end_html();
/*
LEAGUE=1
SEASON=2016

OWNER=$1
shift
PICK=$1
shift
PLAYER=$@

echo "$OWNER $PICK \"$PLAYER\""

QUERY="insert into DraftResult (Season, League, FantasyTeam, Player, Pick) values ($SEASON, $LEAGUE, (select ID from FantasyTeams where (Owner='${OWNER}' and Season=${SEASON})), (select ID from Players where (Name=\"${PLAYER}\" and Season=${SEASON})), ${PICK});"

echo $QUERY
*/
?>
