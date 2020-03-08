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

    <body style="background: url('backgrounds/<?php print(trim(file('handBackground.txt')[0]))?>.jpg') no-repeat center center fixed; background-size:cover;" id="body" onload="makeFullscreen(this)">
        <?php
            if( isset($_POST['refresh']) ) {
                $Deck::printToFile('boardState/handCheck.txt',array());
            }

            $options = array('land','stack','graveyard','exile');
            for($i=0;$i<count($options);$i++) {
                if( isset($_POST[$options[$i]]) ) {
                    $Deck::set_field("hand",$Deck::read_field("hand"));

                    $postIndex = $_POST[$options[$i]];
                    $index = 0;
                    for($j=0;$j<count($Deck::read_field("hand"));$j++) {
                        if( $Deck::read_field("hand")[$j] == $postIndex ) {
                            $index = $j;
                        }
                    }

                    // print("Option: ".$options[$i]);
                    // print(" Index: ".$index."\t");
                    $Deck::play($index,$options[$i]);
                    $Deck::printToFile('boardState/handCheck.txt',array('Modified'));
                }
            }

            if( isset($_POST['top']) ) {
                $Deck::move($_POST['top'],"deck","scry");
                $Deck::printToFile('boardState/handCheck.txt',array('Modified'));
            }
            if( isset($_POST['bottom']) ) {
                $Deck::move($_POST['bottom'],"deck","scry",false);
                $Deck::printToFile('boardState/handCheck.txt',array('Modified'));
            }
        ?>

        <h1>Cards In Hand:</h1>
        <form method="post" name="myForm">
            <input type="submit" name="refresh" value="Refresh"/>
        </form>

        <div class='row'>
		    <?php
                $Deck::dispCards("hand",$decklist,$Deck::read_field("hand"),$files,$imgDir);
		    ?>
        </div>

        <h1>Scry Cards:</h1>
        <div class='row'>
            <?php
                $Deck::dispCards("scry",$decklist,$Deck::read_field("scry"),$files,$imgDir);
            ?>
        </div>

        <script>
            // window.setTimeout(function() { document.myForm.submit(); }, 30000);

            function makeFullscreen(elem) {
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.mozRequestFullScreen) { /* Firefox */
                    elem.mozRequestFullScreen();
                }
            }

            function checkHandMod() {
                var oXHR = new XMLHttpRequest();

                oXHR.open("GET","boardState/handCheck.txt",true);

                oXHR.onreadystatechange = function (oEvent) {
                    if( this.readyState === 4 ) {
                        if( this.status === 200 ) {
                            // document.getElementById("testPara").innerHTML = this.responseText;
                            console.log(oXHR.responseText);
                            // alert(oXHR.responseText.trim());
                            if( (this.responseText.trim())===('Drawn') ) {
                                // alert("Submitting");
                                var frm = document.createElement("FORM");
                                frm.method = "post";
                                var refresh = document.createElement("INPUT");
                                refresh.type = "hidden";
                                refresh.name = "refresh";
                                frm.appendChild(refresh);
                                document.body.appendChild(frm);
                                frm.submit();
                            }
                        }
                    }
                };

                oXHR.send();
            }

            window.setInterval(checkHandMod,5000);
        </script>
    </body>
</html>
