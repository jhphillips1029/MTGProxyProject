<?php
    namespace MTGProject;

    require("Deck.php");

    $imgDir = 'cardImages/';
    $files = scandir($imgDir);

    $Deck = new Deck();
    $decklist = strval(trim(file('deckChoice.txt')[0])).".txt";
    $decklist = file("decks/".$decklist);
    $Deck::set_deckList($decklist);

    $Deck::printToFile('boardState/deckOpen.txt',array('Open'));
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="formatting.css">
    </head>

    <body>
        <?php
        ?>

        <h1>Cards In Deck:</h1>
        <form method="post">
            <input type="submit" name="refresh" value="Refresh"/>
        </form>
        <form method="post"action="BoardDisp.php">
            <input type="hidden" name="fromDeck"/>
            <input type="submit" value="Return"/>
        </form>

        <?php
            $options = array('hand','graveyard','creature','land','other');
            for($i=0;$i<count($options);$i++) {
                if( isset($_POST[$options[$i]]) ) {
                    $Deck::set_field("deck",$Deck::read_field("deck"));

                    $postIndex = $_POST[$options[$i]];
                    for($j=0;$j<count($Deck::read_field("deck"));$j++) {
                        if( $Deck::read_field("deck")[$j] == $postIndex ) {
                            $index = $j;
                        }
                    }

                    $Deck::move($postIndex,$options[$i],"deck");
                }
            }
        ?>

        <div class='row'>
		    <?php
                $allCards = $Deck::read_field("deck");
                // for($i=0;$i<$allCards[0];$i++) {
                    // array_push($allCards,$i);
                // }
                $Deck::dispCards("deck",$decklist,$allCards,$files,$imgDir);
		    ?>
        </div>
    </body>
</html>
