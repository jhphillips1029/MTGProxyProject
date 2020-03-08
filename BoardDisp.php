<?php
namespace MTGProject;

require("Deck.php");

$imgDir = 'cardImages/';
$files = scandir($imgDir);

$Deck = new Deck();
$decklist = trim(file('deckChoice.txt')[0]).".txt";
$decklist = file("decks/".$decklist);
$Deck::set_deckList($decklist);
$sleeveArt = trim(file('sleeveBackground.txt')[0]);
?>

<!DOCTYPE html>
<html>

    <head>
        <link rel="stylesheet" href="formatting.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body style="/**/background: url('backgrounds/<?php print(trim(file('boardBackground.txt')[0]))?>.jpg') no-repeat center center fixed; background-size:cover;/**/">

        <p id="testPara"></p>

        <?php
            if( isset($_POST['initDeck']) ) {
                $Deck::init_deck();
            }
            if( isset($_POST['shuffle']) ) {
                $Deck::set_field("deck",$Deck::read_field("deck"));
                $Deck::shuffleDeck();
            }
            if( isset($_POST['drawHand']) ) {
                $Deck::set_field("deck",$Deck::read_field("deck"));
                $Deck::makeHand(7);
            }
            if( isset($_POST['untapAll']) ) {
                $Deck::untapAll();
            }
            if( isset($_POST['draw']) ) {
                $Deck::set_field("deck",$Deck::read_field("deck"));
                $Deck::set_field("hand",$Deck::read_field("hand"));
                $Deck::draw();
                $Deck::printToFile('boardState/handCheck.txt',array('Drawn'));
            }
            if( isset($_POST['flip']) ) {
                $Deck::flip($_POST['from'],$_POST['to']);
            }
            if( isset($_POST['attach']) ) {
                $Deck::attach($_POST['attachMe'],$_POST['toAttach']);
            }
            if( isset($_POST['counter']) ) {
                $Deck::addCounter($_POST['counterTarget'],$_POST['counterVal']);
            }
            if( isset($_POST['removeCounter']) ) {
                $Deck::removeCounters($_POST['removeTarget']);
            }
            if( isset($_POST['tapAllLand']) ) {
                $Deck::tapAllLand();
            }
            if( isset($_POST['refresh']) ) {
                $Deck::printToFile('boardState/handCheck.txt',array());
            }
            if( isset($_POST['fromDeck']) ) {
                $Deck::printToFile('boardState/deckOpen.txt',array());
            }

            $options = array('creature','other','graveyard','exile','hand','command');
            $field = $_POST['field'];
            $fieldCards = [];
            switch($field) {
                case 'creature':
                    $fieldCards = $Deck::read_field("creature");
                    break;
                case 'land':
                    $fieldCards = $Deck::read_field("land");
                    break;
                case 'other':
                    $fieldCards = $Deck::read_field("other");
                    break;
                case 'stack':
                    $fieldCards = $Deck::read_field("stack");
                    break;
                case 'command':
                    $fieldCards = $Deck::read_field("command");
                    break;
            }
            for($i=0;$i<count($options);$i++) {
                if( isset($_POST[$options[$i]]) ) {
                    $postIndex = $_POST[$options[$i]];
                    $absIndex = 0;
                    for($j=0;$j<count($fieldCards);$j++) {
                        if( $fieldCards[$j] == $postIndex ) {
                            $absIndex = $fieldCards[$j];
                        }
                    }

                    $Deck::move($absIndex,$options[$i],$field);
                }
            }

            if( isset($_POST['tap']) ) {
                $postIndex = $_POST['tap'];
                $absIndex = 0;
                for($j=0;$j<count($fieldCards);$j++) {
                    if( $fieldCards[$j] == $postIndex ) {
                        $absIndex = $fieldCards[$j];
                    }
                }

                $Deck::tap($absIndex);
            }

            if( isset($_POST['scry']) ) {
                if( is_numeric($_POST['scryX']) ) {
                    $scry = array();
                    $Deck::set_field("deck",$Deck::read_field("deck"));
                    for($i=0;$i<$_POST['scryX'];$i++) {
                        array_push($scry,array_pop($Deck::$deck));
                    }
                    $Deck::printToFile("boardState/deck.txt",$Deck::$deck);
                    $Deck::printToFile("boardState/scry.txt",$scry);
                    $Deck::printToFile('boardState/handCheck.txt',array('Drawn'));
                }
                
            }

            if( isset($_POST['mill']) ) {
                if( is_numeric($_POST['millX']) ) {
                    $deck = $Deck::read_field('deck');
                    $graveyard = $Deck::read_field('graveyard');
                    $millX = intval($_POST['millX']);

                    for($i=0;$i<$millX;$i++) {
                        array_push($graveyard,array_pop($deck));
                    }

                    $Deck::printToFile('boardState/graveyard.txt',$graveyard);
                    $Deck::printToFile('boardState/deck.txt',$deck);
                }
                
            }

            if( isset($_POST['exileGraveyard']) ) {
                $grave = $Deck::read_field('graveyard');
                $exile = $Deck::read_field('exile');
                for($i=0;$i<count($grave);$i++) {
                    array_push($exile,array_pop($grave));
                }
                $Deck::printToFile('boardState/graveyard.txt',$grave);
                $Deck::printToFile('boardState/exile.txt',$exile);
            }
        ?>

        <p id="deckIsOpen" style="color:red;"></p>

        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <?php $ip_addr=$_SERVER['HTTP_HOST']; print("<p>Hand at: $ip_addr/HandDisp.php</p>") ?>
            <form method="post">
                <input type="submit" name="tapAllLand" value="Tap All Land"/><br>
                <label>Mill</label>
                <input type="text" name="millX" value="X"/>
                <input type="submit" name="mill" value="Mill!"/><br>
                <label for="to">Scry</label>
                <input type="text" name="scryX" value="X"/>
                <input type="submit" name="scry" value="Scry!"/><br>
                <label>From: </label>
                <input type="text" name="from"/><br>
                <label>To: </label>
                <input type="text" name="to"/>
                <input type="submit" name="flip" value="Flip!"/>
                <label>Attach </label>
                <input type="text" name="attachMe"/>
                <label>To </label>
                <input type="text" name="toAttach"/>
                <input type="submit" name="attach" value="Attach!"/><br>
                <label>Card </label>
                <input type="text" name="counterTarget"/>
                <label>Counter </label>
                <input type="text" name="counterVal"/>
                <input type="submit" name="counter" value="Add!"/><br>
                <label>Card </label>
                <input type="text" name="removeTarget"/>
                <input type="submit" name="removeCounter" value="Remove Counters"/><br>
                <input type="submit" name="exileGraveyard" value="Exile Graveyard"/><br>
                <input type="submit" name="viewDeck" formaction="DeckDisp.php" value="View Deck"/><br>
                <a id="revealOption" href="javascript:void(0)" onclick="revealTopCard()">Reveal Top Card</a><br>
                <input type="submit" name="initDeck" value="Reset Deck"/>
                <input type="submit" name="shuffle" value="Shuffle"/>
                <input type="submit" name="drawHand" value="Draw Hand"/><br>
            </form>
            <form action="DeckSelector.php">
                <input type="submit" name="deckSelector" value="Deck Selector"/>
                <p>(Will Reset Board & Hand!)</p>
            </form>
            <br><br><br>
        </div>

        <div id="main">
            <div class="g-block-1">
                <div class="options">
                    <p>
                        <form method="post" name="myForm">
                            <span style="font-size:30px;cursor:pointer;color:#f1f1f1;" onclick="openNav()">&#9776;</span>
                            <!--<input type="submit" name="draw" value="Draw"/>-->
                            <input type="submit" name="untapAll" value="Untap All"/>
                            <input type="submit" name="refresh" value="Refresh"/>
                    </form>
                    <from id="burner">
                        <input type="hidden" name="refresh"/>
                    </form></p>
                </div>
                <div>
                    <div class="deck">
                        <p>Deck</p>
                        <script>
                            function myFunction(a) {
                                a.parentNode.getElementsByClassName("dropdown-content")[0].classList.toggle("show");
                                a.classList.toggle("selected");
                            }
                        </script>
                        <?php
                            print("<img id=\"deckImg\" src=\"backgrounds/$sleeveArt.jpg\" alt=\"$sleeveArt\" class=\"sleeve\" onclick=\"myFunction(this)\"/>");
                            print("<form id=\"myDropdown\" class=\"dropdown-content\" method=\"post\">");

                            print("<input type=\"submit\" name=\"draw\" value=\"Draw\"/>");
                            print("</form>");

                        ?>
                    </div>
                </div>
                <div>
                    <div class="commandZone">
                        <p>Command Zone</p>
		                    <?php
                                $Deck::dispCards("command",$decklist,array($Deck::read_field("command")[count($Deck::read_field("command"))-1]),$files,$imgDir);
		                    ?>
                    </div>
                    <div class="stack">
                        <p>Stack</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("stack",$decklist,array($Deck::read_field("stack")[count($Deck::read_field("stack"))-1]),$files,$imgDir);
		                    ?>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="exile">
                        <p>Exile</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("exile",$decklist,array($Deck::read_field("exile")[count($Deck::read_field("exile"))-1]),$files,$imgDir);
		                    ?>
                        </div>
                    </div>
                    <div class="graveyard">
                        <p>Graveyard</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("graveyard",$decklist,array($Deck::read_field("graveyard")[count($Deck::read_field("graveyard"))-1]),$files,$imgDir);
		                    ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="g-block-2">
                <div id="creature" class="creature">
                    <p>Creatures</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("creature",$decklist,$Deck::read_field("creature"),$files,$imgDir);
		                    ?>
                        </div>
                </div>
                <div id="land" class="land">
                    <p>Lands</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("land",$decklist,$Deck::read_field("land"),$files,$imgDir);
		                    ?>
                        </div>
                </div>
                <div id="artifact" class="artifact">
                    <p>Planeswalkers, Enchantments, and Artifacts</p>
                        <div class='row'>
		                    <?php
                                $Deck::dispCards("other",$decklist,$Deck::read_field("other"),$files,$imgDir);
		                    ?>
                        </div>
                </div>
            </div>
        </div>

        <script>
            // window.setTimeout(function() { document.myForm.submit(); }, 10000);

            function openNav() {
                document.getElementById("mySidenav").style.width = "250px";
                document.getElementById("main").style.marginLeft = "250px";
            }

            function closeNav() {
                document.getElementById("mySidenav").style.width = "0";
                document.getElementById("main").style.marginLeft= "0";
            }

            function revealTopCard() {
                var deckImg = document.getElementById('deckImg');
                var deckRevealOption = document.getElementById('revealOption');

                if( deckImg.getAttribute("class")==="sleeve" ) {
                    deckImg.setAttribute("class","card");
                    deckImg.setAttribute("src",<?php print("\"cardImages/".$Deck::readTopCardOfDeck($files)."\"") ?>);
                    deckRevealOption.innerHTML = "Hide Top Card";
                } else {
                    deckImg.setAttribute("class","sleeve");
                    deckImg.setAttribute("src","backgrounds/"+<?php print("\"".$sleeveArt."\"") ?>+".jpg");
                    deckRevealOption.innerHTML = "Reveal Top Card";
                }
            }

            function checkHandMod() {
                var oXHR = new XMLHttpRequest();

                oXHR.open("GET","boardState/handCheck.txt",true);

                oXHR.onreadystatechange = function (oEvent) {
                    if( this.readyState === 4 ) {
                        if( this.status === 200 ) {
                            console.log(oXHR.responseText);
                            if( (this.responseText.trim())===('Modified') ) {
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

            function checkDeckOpen() {
                var oXHR = new XMLHttpRequest();

                oXHR.open("GET","boardState/deckOpen.txt",true);

                oXHR.onreadystatechange = function (oEvent) {
                    if( this.readyState === 4 ) {
                        if( this.status === 200 ) {
                            console.log(oXHR.responseText);
                            if( (this.responseText.trim())===('Open') ) {
                                document.getElementById('deckIsOpen').innerHTML = "DECK IS OPEN!";
                            }
                        }
                    }
                };

                oXHR.send();
            }

            window.setInterval(checkDeckOpen,5000);
        </script>
    </body>

</html>
