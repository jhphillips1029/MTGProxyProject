<?php
namespace MTGProject;

require("Deck.php");

$imgDir = 'cardImages/';
$files = scandir($imgDir);

$Deck = new Deck();
$decklist = strval(trim(file('deckChoice.txt')[0])).".txt";
$decklist = file("decks/".$decklist);
$Deck::set_deckList($decklist);
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="formatting.css">
    </head>

    <body style="/**/background: url('backgrounds/<?php print(trim(file('boardBackground.txt')[0]))?>.jpg') no-repeat center center fixed; background-size:cover;/**/">
        <?php
            $options = array('creature','land','other','graveyard','stack','exile','hand');
            for($i=0;$i<count($options);$i++) {
                if( isset($_POST[$options[$i]]) ) {
                    $postIndex = $_POST[$options[$i]];

                    $Deck::move($postIndex,$options[$i],"exile");
                }
            }

            if( isset($_POST['sendToLibrary']) ) {
                $Deck::sendtoLibrary($_POST['name'],$_POST['placement']);
            }
        ?>

        <p><form method="post">
            <span style="font-size:30px;cursor:pointer;color:#f1f1f1;" onclick="openNav()">&#9776;</span>
            <input type="submit" name="refresh" value="Refresh"/>
        </form></p>
        <p><form method="post" action="BoardDisp.php">
            <input type="submit" name="return" value="Return"/>
        </form></p>

        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <form method="post">
                <label>Name </label>
                <input type="text" name="name"/>
                <label>X cards from top </label>
                <input type="text" name="placement"/>
                <input type="submit" name="sendToLibrary" value="Send to Library"/>
            </form>
        </div>

        <div id="main">
            <div class='row'>
                <?php
                    $Deck::dispCards("exileView",$decklist,$Deck::read_field("exile"),$files,$imgDir);
                ?>
            </div>
        </div>

        <script>
            function openNav() {
                document.getElementById("mySidenav").style.width = "250px";
                document.getElementById("main").style.marginLeft = "250px";
            }

            function closeNav() {
                document.getElementById("mySidenav").style.width = "0";
                document.getElementById("main").style.marginLeft= "0";
            }
        </script>
    </body>
</html>
