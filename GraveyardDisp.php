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

                    $Deck::move($postIndex,$options[$i],"graveyard");
                }
            }
        ?>

        <p><form method="post">
            <input type="submit" name="refresh" value="Refresh"/>
        </form></p>
        <p><form method="post" action="BoardDisp.php">
            <input type="submit" name="return" value="Return"/>
        </form></p>

        <div class='row'>
            <?php
                $Deck::dispCards("graveyardView",$decklist,$Deck::read_field("graveyard"),$files,$imgDir);
            ?>
        </div>

    </body>
</html>
