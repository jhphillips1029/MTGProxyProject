<?php
namespace MTGProject;

class Deck {
    public static $NUM_OF_COMMANDERS = 1;
    public static $NUM_CARDS_PER_ROW = 4;
    public static $deck = array();
    public static $decklist = array();
    public static $hand = array();
    public static $creature = array();
    public static $land = array();
    public static $other = array();
    public static $exile = array();
    public static $graveyard = array();
    public static $stack = array();
    public static $command = array();
    public static $flips = array();

    public static function set_deckList($decklist) {
        self::$decklist = $decklist;
    }
    public static function init_deck() {
        $start = 0;
        if( count(self::$decklist) >= 100 ) {
            array_push(self::$command,$start);
            $start++;
            if( $NUM_OF_COMMANDERS == 2 ) {
                array_push(self::$command,$start);
                $start++; 
            }
            while((count(self::$decklist) - $start) > (99-$NUM_OF_COMMANDERS)) {
                array_push(self::$flips,$start);
                $start++;
            }
        }
        for($i=$start;$i<count(self::$decklist);$i++) {
            array_push(self::$deck,$i);
        }
        self::printToFile("boardState/deck.txt",self::$deck);
        self::printToFile("boardState/command.txt",self::$command);
        self::printToFile("boardState/flips.txt",self::$flips);
        self::printToFile("boardState/hand.txt",array());
        self::printToFile("boardState/stack.txt",array());
        self::printToFile("boardState/creature.txt",array());
        self::printToFile("boardState/land.txt",array());
        self::printToFile("boardState/other.txt",array());
        self::printToFile("boardState/exile.txt",array());
        self::printToFile("boardState/graveyard.txt",array());
        self::printToFile("boardState/tapped.txt",array());
        self::printToFile("boardState/scry.txt",array());
        self::printToFile("boardState/attach.txt",array());
        self::printToFile("boardState/counters.txt",array());
    }
    public static function shuffleDeck() {
        shuffle(self::$deck);
        self::printToFile("boardState/deck.txt",self::$deck);
    }

    public static function makeHand($handSize) {
        $tempHand = [];
        for($i=0;$i<$handSize;$i++) {
            array_push($tempHand,array_pop(self::$deck));
        }
        self::printToFile("boardState/hand.txt",$tempHand);
        self::printToFile("boardState/deck.txt",self::$deck);
        self::$hand = $tempHand;
    }
    public static function draw() {
        self::set_field("deck",self::read_field("deck"));
        self::set_field("hand",self::read_field("hand"));
        array_push(self::$hand,array_pop(self::$deck));
        self::printToFile("boardState/hand.txt",self::$hand);
        self::printToFile("boardState/deck.txt",self::$deck);
    }
    public static function play($index,$option) {
        $h = self::read_field("hand");
        $absIndex = $h[$index];
        $to = $option;
        $from = "hand";
        self::move($absIndex,$to,$from);
    }

    public static function move($absIndex,$to,$from,$front=true) {
        // create temp destination array
        // read in destination array
        // add absolute index to destination array
        // create temp source array
        // read in source array
        // cycle through source array
            // if source array [i] == absolut index, skip
            // else add source array [i] to temp source array
        // update source and destination arrays

        $tempDest = [];
        $tempDest = array_map('intval',file("boardState/".$to.".txt"));
        if( $front ) {
            array_push($tempDest,$absIndex);
        } else {
            array_unshift($tempDest,$absIndex);
        }
        $tempSrc = [];
        $src = array_map('intval',file("boardState/".$from.".txt"));
        for($i=0;$i<count($src);$i++) {
            if( $src[$i] == $absIndex ) {
                continue;
            }
            else {
                array_push($tempSrc,$src[$i]);
            }
        }
        self::printToFile("boardState/".$from.".txt",$tempSrc);
        self::printToFile("boardState/".$to.".txt",$tempDest);
    }

    public static function tap($absIndex) {
        $tapped = array_map('intval',file("boardState/tapped.txt"));
        $tempTapped = [];

        for($i=0;$i<count($tapped);$i++) {
            if( $absIndex == $tapped[$i] ) {
                continue;
            } else {
                array_push($tempTapped,$tapped[$i]);
            }
        }

        if( count($tapped) != count($tempTapped) ) {
            self::printToFile("boardState/tapped.txt",$tempTapped);
        } else {
            array_push($tapped,$absIndex);
            self::printToFile("boardState/tapped.txt",$tapped);
        }
    }
    public static function untapAll() {
        self::printToFile("boardState/tapped.txt",array());
    }
    public static function isTapped($absIndex) {
        $tapped = array_map('intval',file("boardState/tapped.txt"));
        for($i=0;$i<count($tapped);$i++) {
            if( $absIndex == $tapped[$i] ) {
                return True;
            }
        }
        return False;
    }
    public static function readTopCardOfDeck($files) {
        $deck = self::read_field('deck');
        $topCard = $deck[count($deck)-1];
        $topCardName = self::$decklist[$topCard];
        foreach($files as $file) {
            if( self::strContains($file,$topCardName) ) {
                return $file;
            }
        }
    }
    public static function flip($from,$to) {
        // use strContains() to search decklist for indices of from and to
        // search battlefield fields to find from index and swap with to index
        $toIndex = -1;
        $fromIndex = -1;

        for($i=0;$i<count(self::$decklist);$i++) {
            if( self::strContains(self::$decklist[$i],$from) ) {
                print("Is ".$from." in ".self::$decklist[$i]."?");
                $fromIndex = $i;
            }
            if( self::strContains(self::$decklist[$i],$to) ) {
                print("Is ".$from." in ".self::$decklist[$i]."?");
                $toIndex = $i;
            }
        }

        if( $toIndex < 0 || $fromIndex < 0 ) {
            return;
        }

        foreach(array("creature","land","other") as $field) {
            foreach(self::read_field($field) as $testIndex) {
                if( $testIndex == $fromIndex ) {
                    $oriArr = self::read_field($field);
                    $newArr = array();
                    for($i=0;$i<count($oriArr);$i++) {
                        if( $oriArr[$i] == $fromIndex ) {
                            array_push($newArr,$toIndex);
                        } else {
                            array_push($newArr,$oriArr[$i]);
                        }
                    }
                    self::printToFile("boardState/$field.txt",$newArr);
                    break;
                }
            }
        }
    }

    public static function attach($attachMe,$toThis) {
        // read through decklist to find indices
        // write indices to file in format "attachMeIndex=>toThisIndex"
        $attachMeIndex = -1;
        $toThisIndex = -1;
        $removing = false;

        for($i=0;$i<count(self::$decklist);$i++) {
            if( self::strContains(self::$decklist[$i],$attachMe) ) {
                $attachMeIndex = $i;
            }
            if( self::strContains(self::$decklist[$i],$toThis) ) {
                $toThisIndex = $i;
            }
        }

        if( $attachMeIndex < 0 || $toThisIndex < 0 ) {
            return;
        }

        $currAttachments = array_map('trim',file("boardState/attach.txt"));
        $newAttachments = array();
        for($i=0;$i<count($currAttachments);$i++) {
            print($currAttachments[$i]." ?= \"".strval($attachMeIndex)."=>".strval($toThisIndex)."\"<br>");
            if( $currAttachments[$i]==strval($attachMeIndex)."=>".strval($toThisIndex) ) {
                $removing=true;
                continue;
            } else {
                array_push($newAttachments,$currAttachments[$i]);
            }
        }

        $currOther = self::read_field("other");
        $newOther = array();
        if( !$removing ) {
            array_push($newAttachments,strval($attachMeIndex)."=>".strval($toThisIndex));
            for($i=0;$i<count($currOther);$i++) {
                if( $currOther[$i]==$attachMeIndex ) {
                    continue;
                } else {
                    array_push($newOther,$currOther[$i]);
                }
            }
        
        } else {
            $newOther = $currOther;
            array_push($newOther,$attachMeIndex);
        }

        self::printToFile("boardState/attach.txt",$newAttachments);
        self::printToFile("boardState/other.txt",$newOther);
    }

    public static function sendToLibrary($name,$placement) {
        $exileCards = self::read_field('exile');
        $indexOfCard = -1;

        for($i=0;$i<count($exileCards);$i++) {
            if( self::strContains(self::$decklist[$exileCards[$i]],$name) ) {
                $indexOfCard = $exileCards[$i];
            } else {
                array_push($newExile,$exileCards[$i]);
            }
        }

        if( $indexOfCard < 0 ) {
            return;
        }

        $deckCards = self::read_field('deck');
        $newDeck = array();

        for($i=0;$i<count($deckCards);$i++) {
            if( count($deckCards)-$placement+1 == $i ) {
                array_push($newDeck,$indexOfCard);
            }
            array_push($newDeck,$deckCards[$i]);
        }

        self::printToFile("boardState/deck.txt",$newDeck);
        self::printToFile("boardState/exile.txt",$newExile);
    }
    public static function addCounter($target,$value) {
        // read in current counters
        // create array for new counters
        // cycle through the current counters
            // if target appears in the current counters and corresponding counter matches regex of value, increment/decrement current counter by value
            // otherwise, add counter=>target
        // replace current counters with new counters

        $targetIndex = -1;
        for($i=0;$i<count(self::$decklist);$i++) {
            if( self::strContains(self::$decklist[$i],$target) ) {
                $targetIndex = $i;
                break;
            }
        }

        if( $targetIndex < 0 ) {
            return;
        }

        $currCounters = file('boardState/counters.txt');
        $newCounters = array();
        $needToAdd = true;
        $needToRemove = false;
        $regexUsed = '';

        for($i=0;$i<count($currCounters);$i++) {
            if( intval(explode("=>",$currCounters[$i])[1]) == $targetIndex ) {
                $single = '/[\+|\-][0-9]+/';
                $double = '/[\+|\-][0-9]+\/[\+|\-][0-9]+/';
                if( preg_match($double,explode("=>",$currCounters[$i])[0]) ) {
                    $regexUsed = $double;
                } else if( preg_match($single,explode("=>",$currCounters[$i])[0]) ) {
                    $regexUsed = $single;
                } else {
                    return;
                }

                if( preg_match($regexUsed,$value) ) {
                    if( $regexUsed == $single ) {
                        $currVal = intval(explode("=>",$currCounters[$i])[0]);
                        $newVal = intval($value) + $currVal;
                        $sign = '';
                        if( $newVal < 0 ) {
                            $sign = '-';
                        } else {
                            $sign = '+';
                        }
                        $counter = $sign.strval($newVal)."=>".strval($targetIndex);
                        if( $newVal != 0 ) {
                            array_push($newCounters,$counter);
                        } else {
                            $needToRemove = true;
                        }
                    } else {
                        $currCounter = explode("=>",$currCounters[$i])[0];
                        $currValLeft = intval(explode("/",$currCounter)[0]);
                        $currValRight = intval(explode("/",$currCounter)[1]);
                        $propValLeft = intval(explode("/",$value)[0]);
                        $propValRight = intval(explode("/",$value)[1]);
                        $newValLeft = $currValLeft + $propValLeft;
                        $newValRight = $currValRight + $propValRight;
                        $signLeft = '';
                        if( $newValLeft < 0 ) {
                            $signLeft = '-';
                        } else {
                            $signLeft = '+';
                        }
                        $signRight = '';
                        if( $newValRight < 0 ) {
                            $signRight = '-';
                        } else {
                            $signRight = '+';
                        }
                        $newVal = $signLeft.strval($newValLeft)."/".$signRight.strval($newValRight);
                        $counter = $newVal."=>".strval($targetIndex);
                        if( $newVal != "+0/+0" ) {
                            array_push($newCounters,$counter);
                        } else {
                            $needToRemove = true;
                        }
                    }
                    $needToAdd = false;
                }
            } else {
                array_push($newCounters,trim($currCounters[$i]));
            }
        }

        if( $needToAdd ) {
            array_push($newCounters,$value."=>".strval($targetIndex));
        }

        if( $needToRemove ) {
            $tmpCounters = array();
            for($i=0;$i<count($newCounters);$i++) {
                if( preg_match($regexUsed,explode("=>",$newCounters[$i])[0]) && $targetIndex==explode("=>",$newCounters[$i])[1] ) {
                    continue;
                } else {
                    array_push($tmpCounters,$newCounters[$i]);
                }
            }
            $newCounters = $tmpCounters;
        }

        self::printToFile('boardState/counters.txt',$newCounters);
    }
    public static function removeCounters($target) {
        $targetIndex = -1;
        for($i=0;$i<count(self::$decklist);$i++) {
            if( self::strContains(self::$decklist[$i],$target) ) {
                $targetIndex = $i;
                break;
            }
        }

        if( $targetIndex < 0 ) {
            return;
        }

        $currCounters = file("boardState/counters.txt");
        $newCounters = array();
        for($i=0;$i<count($currCounters);$i++) {
            if( intval(explode("=>",$currCounters[$i])[1]) == $targetIndex ) {
                continue;
            } else {
                array_push($newCounters,$currCounters[$i]);
            }
        }

        self::printToFile('boardState/counters.txt',$newCounters);
    }
    public static function tapAllLand() {
        $currTapped = self::read_field('tapped');
        $currLand = self::read_field('land');

        $newTapped = array_merge($currTapped,$currLand);
        self::printToFile('boardState/tapped.txt',$newTapped);
    }

    public static function read_field($field) {
        return array_map('intval',file("boardState/".$field.".txt"));
    }
    public static function set_field($field,$cards) {
        switch($field) {
            case 'deck':
                self::$deck = $cards;
                break;
            case 'hand':
                self::$hand = $cards;
                break;
            case 'graveyard':
                self::$graveyard = $cards;
                break;
            case 'exile':
                self::$exile = $cards;
                break;
            case 'command':
                self::$command = $cards;
                break;
            case 'stack':
                self::$stack = $cards;
                break;
            case 'flips':
                self::$flips = $cards;
                break;
            case 'creature':
                self::$creature = $cards;
                break;
            case 'land':
                self::$land = $cards;
                break;
            case 'other':
                self::$other = $cards;
                break;
        }
    }

    public static function strContains($phrase,$check) {
        $test1 = (similar_text($check,$phrase) >= strlen($check)-1);
        if($test1) {
            return (strpos(trim($phrase),trim($check)) !== false);
        } else {
            return false;
        }
    }
    public static function printToFile($fileName,$array) {
        $file = fopen($fileName,"w");
        ftruncate($file,0);
        for($i=0;$i<count($array);$i++) {
            fwrite($file,strval($array[$i])."\n");
        }
        fclose($file);
    }

    public static function dispCards_cond($index,$stardex,$endex) {
        if( $index < 0 ) {
            return false;
        }
        if( $stardex < $endex ) {
            return $index < $endex;
        }
        return $index >= $endex;
    }
    public static function dispCards($zone,$decklist,$cards,$files,$imgDir) {
        if( empty($cards) ) {
            return;
        }

        echo <<<TEXT
        <script>
            function myFunction(a) {
                a.parentNode.getElementsByClassName("dropdown-content")[0].classList.toggle("show");
            }
        </script>
TEXT;

        $stardex = 0;
        $endex = 0;
        $direc = 0;
        if( $zone == "deck" || $zone == 'land' ) {
            $stardex = 0;
            $endex = count($cards);
            $direc++;
        } else {
            $stardex = count($cards)-1;
            $endex = 0;
            $direc--;
        }

        $attachments = file("boardState/attach.txt");
        $counters = file("boardState/counters.txt");
        $single = '[+|-][0-9]+';

        for($j=$stardex;self::dispCards_cond($j,$stardex,$endex);$j+=$direc) {
            for($i=0;$i<count($files);$i++) {
                $file = $files[$i];
                if(self::strContains($file,$decklist[$cards[$j]])) {
                    $index = $cards[$j];
                    print("<div class=\"column");
                        if( $zone == 'land' ) {
                            print(" land land".strval($j%3 + 1));
                        }
                        print("\"");
                        if( $zone == 'land' ) {
                            print("style=\"left: -");
                                $offset = 155*($j%3);
                                $offset += intval($j/3)*185;
                                print($offset);
                            print("px\"");
                        }
                        print(">");

                    if( $zone == "deck" ) {
                        print("<figure>");
                    }

	                print("<img src=\"$imgDir$file\" alt=\"$file\" class=\"card");
                        if( $zone == "creature" || $zone == "land" || $zone == "other" ) {
                            if( self::isTapped($cards[$j]) && $decklist==self::$decklist ) {
                                print(" tapped");
                            }
                        }
                    print("\" onclick=\"myFunction(this)\"/>");

                    for($k=0;$k<count($attachments);$k++) {
                        $attachers = explode("=>",$attachments[$k]);
                        $attachThis = intval($attachers[0]);
                        $toThis = intval($attachers[1]);
                        if( $toThis != $index ) {
                            continue;
                        }
                        $attachFile = "";
                        for($x=0;$x<count($files);$x++) {
                            if(self::strContains($files[$x],$decklist[$attachThis]) ) {
                                $attachFile = $files[$x];
                            }
                        }
                        print("<img src=\"$imgDir$attachFile\" alt=\"$attachFile\" class=\"card attached\" onclick=\"myFunction(this)\"/>");
                    }

                    for($k=0;$k<count($counters);$k++) {
                        $target = intval(explode("=>",$counters[$k])[1]);
                        if( $target == $index ) {
                            $counter = explode("=>",$counters[$k])[0];
                            if( preg_match($single,$counter) ) {
                                print("<div class=\"singleCounter\">");
                            } else {
                                print("<div class=\"doubleCounter\">");
                            }

                            print("<p style=\"color:#f1f1f1;\">$counter</p>");

                            print("</div>");
                        }
                    }

                    if( $zone == "deck" ) {
                        print("<figcaption>$file</figcaption>");
                        print("<form id=\"myDropdown\" class=\"dropdown-content\" method=\"post\">");

                        print("<input type=\"radio\" name=\"hand\" value=\"$index\"/>");
                        print("<label for \"hand\">Hand</label><br>");

                        print("<input type=\"radio\" name=\"graveyard\" value=\"$index\"/>");
                        print("<label for \"graveyard\">Graveyard</label><br>");

                        print("<input type=\"radio\" name=\"creature\" value=\"$index\"/>");
                        print("<label for \"creature\">Creature</label><br>");

                        print("<input type=\"radio\" name=\"land\" value=\"$index\"/>");
                        print("<label for \"land\">Land</label><br>");

                        print("<input type=\"radio\" name=\"other\" value=\"$index\"/>");
                        print("<label for \"other\">Other</label><br>");

                        print("<input type=\"submit\" value=\"Play\"/>");
                        print("</form>");

                        print("</figure>");
                    } else {
                        print("<form id=\"myDropdown\" class=\"dropdown-content\" method=\"post\"");
                        switch($zone) {
                            case 'graveyard':
                                print("action=\"GraveyardDisp.php\"");
                                break;
                            case 'exile':
                                print("action=\"ExileDisp.php\"");
                                break;
                        }
                        print(">");
                        $index = $cards[$j];

                        if( ($zone == "creature" || $zone == "graveyardView" || $zone == "other" || $zone == "exileView" || $zone == "stack" || $zone == "hand" ) && $index == 0 ) {
                            print("<input type=\"radio\" name=\"command\" value=\"$index\"/>");
                            print("<label for \"command\">Command</label><br>");
                        }
                        if( $zone == "stack" || $zone == "command" || $zone == "graveyardView" || $zone == "exileView" ) {
                            print("<input type=\"radio\" name=\"creature\" value=\"$index\"/>");
                            print("<label for \"creature\">Creature</label><br>");
                        }
                        if( $zone == "creature" || $zone == "land" || $zone == "other" || $zone == "graveyardView" || $zone == "hand" || $zone == "stack") {
                            print("<input type=\"radio\" name=\"exile\" value=\"$index\"/>");
                            print("<label for \"exile\">Exile</label><br>");
                        }
                        if( $zone == "creature" || $zone == "exileView" || $zone == "hand" || $zone == "land" || $zone == "other" || $zone == "stack") {
                            print("<input type=\"radio\" name=\"graveyard\" value=\"$index\"/>");
                            print("<label for \"graveyard\">Graveyard</label><br>");
                        }
                        if( $zone == "creature" || $zone == "exileView" || $zone == "graveyardView" || $zone == "land" || $zone == "other" || $zone == "stack" ) {
                            print("<input type=\"radio\" name=\"hand\" value=\"$index\"/>");
                            print("<label for \"hand\">Hand</label><br>");
                        }
                        if( $zone == "graveyardView" || $zone == "hand" ) {
                            print("<input type=\"radio\" name=\"land\" value=\"$index\"/>");
                            print("<label for \"land\">Land</label><br>");
                        }
                        if( $zone == "exileView" || $zone == "graveyardView" || $zone == "stack" ) {
                            print("<input type=\"radio\" name=\"other\" value=\"$index\"/>");
                            print("<label for \"other\">Other</label><br>");
                        }
                        if( $zone == "hand" || $zone == "graveyardView" || $zone == "exileView" ) {
                            print("<input type=\"radio\" name=\"stack\" value=\"$index\"/>");
                            print("<label for \"stack\">Stack</label><br>");
                        }
                        if( $zone == "graveyard" || $zone == "exile" ) {
                            print("<input type=\"radio\" name=\"view\" value=\"$index\"/>");
                            print("<label for \"view\">View</label><br>");
                        }
                        if( $zone == "creature" || $zone == "land" || $zone == "other" ) {
                            print("<input type=\"radio\" name=\"tap\" value=\"$index\"/>");
                            print("<label for \"tap\">Tap/Untap</label><br>");
                        }
                        if( $zone == "scry" ) {
                            print("<input type=\"radio\" name=\"top\" value=\"$index\"/>");
                            print("<label for \"top\">Top</label><br>");

                            print("<input type=\"radio\" name=\"bottom\" value=\"$index\"/>");
                            print("<label for \"bottom\">Bottom</label><br>");
                        }

                        print("<input type=\"hidden\" name=\"field\" value=\"$zone\"/>");

                        print("<input type=\"submit\" value=\"Play\"/>");
                        print("</form>");
                    }

                    print("</div>");
                }
            }
            if( (($j%self::$NUM_CARDS_PER_ROW==0 && $zone!='land') || ($j%(self::$NUM_CARDS_PER_ROW*3)==0 && $zone=='land')) && $j!=0 ) {
                // print(strval($j));
                echo "</div>\n<div class=\"row\">";
            }
        }
    }
}
?>
