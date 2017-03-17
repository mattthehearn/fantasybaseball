<?php
  session_start();
  include('functions.php');
  include('db_pass.php');

  $league=1; # FOR NOW

  start_html("Pick confirmed");
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

      $season=$_GET['season'];
      $league=$_GET['league'];
      $fantteam=$_GET['fantasyteam'];
      $player_id=$_GET['player_id'];
      $pick=$_GET['pick']; 
      $update_query="insert into DraftResult (Season, League, FantasyTeam, Player, Pick) values ($season, $league, $fantteam, $player_id, $pick)";
      print $update_query;
      $update_result=mysql_query($update_query,$db);
 
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
