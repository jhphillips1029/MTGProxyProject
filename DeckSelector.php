<?php
    namespace MTGProject;

    require('Deck.php');

    $imgDir = 'cardImages/';
    $imgs = scandir($imgDir);
    $decksDir = 'decks/';
    $decks = scandir($decksDir);
    $artDir = 'backgrounds/';
    $arts = scandir($artDir);

    $Deck = new Deck();

    function dispCard($deckName,$decklist,$imgs,$imgDir) {
        for($i=0;$i<count($imgs);$i++) {
            $img = $imgs[$i];
            if(Deck::strContains($img,$decklist[0])) {
                print("<div class='column'>");
                print("<figure>");
                print("<img src=\"$imgDir$img\" alt=\"$img\" class=\"card\" onclick=\"myFunction(this)\"/>");

                print("<figcaption>".pathinfo($deckName,PATHINFO_FILENAME)."</figcaption>");
                print("<form id=\"myDropdown\" class=\"dropdown-content\" method=\"post\">");

                print("<input type=\"hidden\" name=\"deck\" value=\"$deckName\"/>");
                print("<input type=\"submit\" value=\"Use\"/>");
                print("</form>");
                print("</figure>");
                print("</div>");
                return;
            }
        }
    }
    function dispArt($artName,$arts,$artDir) {
        for($i=0;$i<count($arts);$i++) {
            $art = $arts[$i];
            if(Deck::strContains($art,$artName)) {
                print("<div class='column'>");
                print("<figure>");
                print("<img src=\"$artDir$art\" alt=\"$art\" class=\"card\" onclick=\"myFunction(this)\"/>");

                print("<figcaption>".pathinfo($artName,PATHINFO_FILENAME)."</figcaption>");
                print("<form id=\"myDropdown\" class=\"dropdown-content\" method=\"post\">");

                print("<input type=\"radio\" name=\"handArt\" value=\"$artName\"/>");
                print("<label for=\"handArt\">Hand</label><br>");

                print("<input type=\"radio\" name=\"boardArt\" value=\"$artName\"/>");
                print("<label for=\"boardArt\">Board</label><br>");

                print("<input type=\"radio\" name=\"sleeveArt\" value=\"$artName\"/>");
                print("<label for=\"sleeveArt\">Sleeves</label><br>");

                print("<input type=\"submit\" value=\"Use\"/>");
                print("</form>");
                print("</figure>");
                print("</div>");
                return;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="formatting.css">
    </head>

    <body>
        <?php
            if( isset($_POST['deck']) ) {
                $deckname = pathinfo($_POST['deck'],PATHINFO_FILENAME);
                $Deck::printToFile("deckChoice.txt",array($deckname));
            }
            if( isset($_POST['boardArt']) ) {
                $art = pathinfo($_POST['boardArt'],PATHINFO_FILENAME);
                $Deck::printToFile("boardBackground.txt",array($art));
            }
            if( isset($_POST['handArt']) ) {
                $art = pathinfo($_POST['handArt'],PATHINFO_FILENAME);
                $Deck::printToFile("handBackground.txt",array($art));
            }
            if( isset($_POST['sleeveArt']) ) {
                $art = pathinfo($_POST['sleeveArt'],PATHINFO_FILENAME);
                $Deck::printToFile("sleeveBackground.txt",array($art));
            }
        ?>

        <script>
            function myFunction(a) {
                a.parentNode.getElementsByClassName("dropdown-content")[0].classList.toggle("show");
            }
        </script>
        <h1>Decks:</h1>
        <?php
            print("<div class=\"column\">");
            for($i=0;$i<count($decks);$i++) {
                $deckname = $decks[$i];
                if( $deckname == "." || $deckname == ".." ) {
                    continue;
                }
                $decklist = file($decksDir.$deckname);
                dispCard($deckname,$decklist,$imgs,$imgDir);
                if( $i%4==0 ) {
                    print("</div>");
                    print("<div class=\"column\">");
                }
            }
            print("</div>");
        ?>
        <br>
        <h1>Art:</h1>
        <?php
            print("<div class=\"column\">");
            for($i=0;$i<count($arts);$i++) {
                $art = $arts[$i];
                if( $art == "." || $art == ".." ) {
                    continue;
                }
                dispArt($art,$arts,$artDir);
                if( $i%4==0 ) {
                    print("</div>");
                    print("<div class=\"column\">");
                }
            }
            print("</div>");
        ?>
        <br>
        <form method="post" action="BoardDisp.php">
            <input type="hidden" name="initDeck">
            <input type="hidden" name="shuffle">
            <input type="hidden" name="drawHand">
            <input type="submit" value="Play!">
        </form>
    </body>
</html>
