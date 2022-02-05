<?php

//uncomment to show all errors for debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?><!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"><!-- Bootstrap CSS -->
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
	<title>Hello, Wordle!</title>
	<style>
	       td, th {
	           padding: 0 0 0 0 !important;
	       }
	       .table td, .table th {
	           border-top: 1px solid #5252525c !important;
	       }
	       body{
	           font-family: monospace !important;
	       }
           input[type=radio]{
               margin-left:5px;
           }
	</style>
</head>
<body>
	<div class="container">
		<h1 class="text-center">Hello, Wordle!</h1>
		
			<hr>






<?php
    $debug = true;
    $debug_output = '';
    $start = microtime( true ); //timer to check that we aren't running too long.
    if ( count( $_GET ) >= 1 ) { //check to see if we got anything in the form
        foreach ( $_GET as $box => $form_input ) { //look at each square from the form
            if ( stripos( $box, 'radio' ) !== false ) //check to see what kind of input we're looking at, if it says 'radio' it's the type of letter, ie exclude, include, correct
                {
                $radio_data                            = $form_input;
                $radio_data                            = clean_input( $radio_data ); // make sure the input is a single letter
                $box                                   = str_replace( 'radio_', '', $box ); // strip the 'radio/ from the box
                $box                                   = explode( '_', $box ); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                $letters_type[ $box[ 0 ] ][ $box[ 1 ] ] = $radio_data; //add the validated input to the array
            } else {
                $form_input                        = clean_input( $form_input ); // make sure the input is a single letter
                $box                               = explode( '_', $box ); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                $letters_input[ $box[ 0 ] ][ $box[ 1 ] ] = $form_input; //add the validated input to the array
            }
        }
        if ( $debug ) {
            echo "\n\n<!-- Time Elapsed: " . ( microtime( true ) - $start ) . " seconds -->\n\n";
        }
        $no_post_input = false;
    } else {
        $no_post_input = true; // record that we didnt get any input so we can tweak some of the bits below to do better anaylis 
    }
    echo '<div class="row"><div class="col-md-4"><form action="index.php" method="get"> ';
    //print out the form
    for ( $i = 1; $i <= 6; $i++ ) { //6 rows
        echo '<div class="row mb-3">';
        for ( $j = 1; $j <= 5; $j++ ) { //by 5 letters
            if ( isset( $letters_input[ $i ][ $j ] ) ) { //make sure the letter variable is set before we reference
                $form_letter = strtoupper( $letters_input[ $i ][ $j ] );
            } else {
                $form_letter = '';
            }
            $exclude = ''; //reset variables up for showing which box is checked
            $include = '';
            $correct = '';
            if ( isset( $letters_type[ $i ][ $j ] ) ) { //check to see which box is checked and set the correct one
                switch ( $letters_type[ $i ][ $j ] ) {
                    case 'e':
                        $exclude = ' checked';
                        break;
                    case 'i':
                        $include = ' checked';
                        break;
                    case 'c':
                        $correct = ' checked';
                        break;
                }
            } else {
                $exclude = ' checked'; //by default set the check to 'exclude'
            }
            echo "\n\n" . '<div class="col-xs-2 ml-1 m-r1"><input maxlength="1" name="' . $i . '_' . $j . '" size="1" type="text" value="' . $form_letter . '"><br>
<div style="background-color:#787c7e;"><input type="radio" name="radio_' . $i . '_' . $j . '" value="exclude"' . $exclude . '></div>
<div style="background-color:#c9b458;"><input type="radio" name="radio_' . $i . '_' . $j . '" value="include"' . $include . '></div>
<div style="background-color:#6aaa64;"><input type="radio" name="radio_' . $i . '_' . $j . '" value="correct"' . $correct . '></div>
</div>'; //output the form row with the valid entries
        }
        echo '</div>';
    }
    if ( $debug ) {
        echo "\n\n<!-- Time Elapsed: " . ( microtime( true ) - $start ) . " seconds -->\n\n";
    }
    echo '<input type="submit"></form></div>';


    //get the wordlist and convert it to an array
    $word_list   = explode( ",", file_get_contents( 'list.txt' ) );
    $total_words = count( $word_list );
    
    //if we didnt get any input we're gonna skip all the words that have two of the same letters
    if ( $no_post_input ) {
        $regex = '/.*(.).*\1.*/'; 
        $word_list = preg_grep($regex,$word_list, PREG_GREP_INVERT );
    }

// add postionally aware matching for all these
////////////////////////////////////
    if ( count( $word_list ) > 1 && !$no_post_input ) { //make sure there are words to strip still
        //var_dump($letters_input); 
        //var_dump($letters_type);
        $collected_grays = '';
        $collected_yellows_greens = '';
        $double_keep_one = '';
        $double_keep_all = '';
        for($i = 1; $i <= 6; $i++){
            $collected_yellows_greens_raw = '';
            for($j = 1; $j <= 5; $j++){
                switch ( $letters_type[ $i ][ $j ] ) {
                    case 'e':
                        $collected_grays .= $letters_input [ $i ][ $j ];
                        break;
                    case 'i':
                        if(isset($regex_yellow[$j])){
                            $regex_yellow[$j] .= $letters_input [ $i ][ $j ];
                        }else{
                            $regex_yellow[$j] = $letters_input [ $i ][ $j ];
                        }
                        $collected_yellows_greens_raw .= $letters_input [ $i ][ $j ];
                        break;
                    case 'c':
                        $regex_green[$j]=$letters_input [ $i ][ $j ];
                        $collected_yellows_greens_raw .= $letters_input [ $i ][ $j ];
                        break;
                    }      
            }


            $regex = '';
            for($k=1;$k <= 5; $k++){
                if(isset($regex_green[$k])){
                    $regex .=$regex_green[$k];
                }elseif(isset($regex_yellow[$k])){
                    $regex .="[^" . $regex_yellow[$k] . $collected_grays . "]";
                }else{
                    $regex .="[^" .  $collected_grays . "]";
                }
            }
            $exclude_include_intersection = implode(array_intersect(str_split($collected_yellows_greens_raw),str_split($collected_grays))) ;
            if(strlen($exclude_include_intersection)>0){
                //echo 'double letter';
               $collected_grays = str_replace($exclude_include_intersection,'',$collected_grays);
               $collected_yellows_greens = str_replace($exclude_include_intersection,'',$collected_yellows_greens_raw);
            }else{
                $collected_yellows_greens .= $collected_yellows_greens_raw;
            }



            $debug_output .= "<br><br>$i:<br>";//grays - $collected_grays<br> green/yellows - $collected_yellows_greens<br>";
           
            $regex         = "/^$regex$/";
            $debug_output .= "words left: ".count($word_list)."<br>&nbsp;&nbsp;&nbsp;";
            $debug_output .= $regex ."<br>";
            
            $word_list     = preg_grep( $regex, $word_list);
            $debug_output .= "words left: ".count($word_list)."<br>&nbsp;&nbsp;&nbsp;";
            //generate regex for must contain charecters
            $regex='';
            if(strlen($collected_yellows_greens)>=2){

                foreach(array_unique(str_split($collected_yellows_greens)) as $letter){
                    $regex .= "(?=.*$letter)";
                }

                $regex  = "/.*$regex.*/";
            }elseif(strlen($collected_yellows_greens)>=1){
                $regex  = "/^.*$collected_yellows_greens.*$/";
            }else{
                $regex='';
            }

            if(strlen($regex)>1){
                $debug_output .= $regex ."<br>";
                $word_list     = preg_grep( $regex, $word_list);
                $debug_output .= "words left: ".count($word_list)."<br>";
            }
//.*(?=([ing]{3,}))(?=[ng]*i)(?=[ig]*n)(?=[in]*g)\1.*
//https://stackoverflow.com/questions/9761346/regex-match-each-character-at-least-once/9761719#9761719

        }
    }


//begin computing the data for the graphs and scoring data for the word position score
////////////////////////////////////////////////////////////////
    //do the counting for positional frequency
    foreach ( $word_list as $word ) { //look at each word individually
        $letters = str_split( $word ); // turn the word into an array
        for ( $i = 0; $i < count( $letters ); $i++ ) { //loop through the letters of each word
            //do a frequnecy analasis for each letter psosition
             if ( isset( $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ] ) ) { //if the array key doesn't exist we need to set it to zero before incrementing
                $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ]++;
            } else {
                $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ] = 1;
            }
        }
    }
    //take the raw positional frequency data and compute decimal percentages
    foreach($frequency_position_raw as $word_position => $column){
        foreach( $column as $letter => $letter_count){
            $frequency_position [ $word_position ] [ $letter ] = $letter_count / array_sum($column);
        }
        ksort( $frequency_position [ $word_position ]); // sort each column by letter
    }

    //generate the list of words that matches position frequency the best
    foreach ( $word_list as $word ) { //work through each word to grade it's quality based on positional probablity
        $letters     = str_split(' ' . $word ); //split the word into letters (padding start of word so that the array indices match the word positions)
        unset($letters[0]);
        $word_score = 0; //reset for calculating the word score
        for ( $i = 1; $i <= count( $letters ); $i++ ) { //loop through word
            $word_score += $frequency_position[ $i ][ $letters[ $i ] ]; //add score up (frequency position's offset starts at 1 rather than zero)
        }
        $words_scored_position[ $word ] = $word_score / 5; //collect scores for each word and divide by 5 to get an average
    }
    arsort($words_scored_position);
    
   //work through each word to grade it's quality based on how many words it eliminates if guessed

//begin grading words on how many words they eliminate   
//////////////////////////////////////////////////////////   
   //doing the full comparison is really slow 13,000^2 
   //if the list is really long we trim it. 
   //seems like my home computer can do 30million comparisons in ~0.6 seconds which seems like a good limit.
    if(pow(count($word_list),2) > 30000000){
            $word_list_short = array_rand(array_flip($word_list), 1000);//trim downwords to compare to. 
        }else{
            $word_list_short = $word_list;
        }
  
        //work through each word in the list seeing how many words it'd eliminate if none of it's letters match
        foreach (  $word_list as $word ) {
            $raw_output                        = words_remaining_no_matches( $word, $word_list_short );
            $words_scored_elimination[ $word ] = 1 - ( $raw_output / count( $word_list ) ); //collect scores
        }
        arsort( $words_scored_elimination );

        $temp_frequency = array_reverse( array_keys( $words_scored_position ) ); // get an array with just a simple number ranking key and the word as the value
        $temp_exclude   = array_reverse( array_keys( $words_scored_elimination ) ); // get an array with just a simple number ranking key and the word as the value
        //work through each word and generate a rank based on the rating systems, above
        for ( $i = 0; $i < count( $temp_exclude ); $i++ ) { 
            $frequency_and_exclude_score[ $temp_exclude[ $i ] ] = $i;
        }

        //when we get low on words, use commonness as a factor in ordering the words
        //collect the common words ranking list
         //tweak this number to make the word commonness score lower= more effect, higher= less effect.
        if(count($word_list)<150){
            $commonness_factor = 2;
        }else{
            $commonness_factor = 8;
        }
            $common = file_get_contents('list_common.json');
            $common = array_reverse(json_decode($common,true));
        
            //work through the combined score list
            foreach( $frequency_and_exclude_score as $word => $rank){
                //make sure that the word in the word list actually exists in the commonness list-- it's only about 8k words where the wordle list is 13k, so some very uncommon words don't have rankings
                if(isset($common[$word])){
                    //add the commonness to the score
                    $frequency_and_exclude_score [ $word ] = $rank + (( count($frequency_and_exclude_score) * $common[$word] ) / count($common) / $commonness_factor ) ;  //the math here scales the commonness score to something that makes sense in the context of the remaining word counts
                }
            }
        

        //work through each word and generate a rank based on the rating systems, above
        for ( $i = 0; $i < count( $temp_frequency ); $i++ ) { 
            $frequency_and_exclude_score[ $temp_frequency[ $i ] ] += $i;
            $frequency_and_exclude_score[ $temp_frequency[ $i ] ] = $frequency_and_exclude_score[ $temp_frequency[ $i ] ] / ( count( $temp_frequency ) *2 );
        }
        unset($temp_frequency);
        unset($temp_exclude);
        arsort( $frequency_and_exclude_score );

        //when the remaining word list gets short get the rannking of most common words to least common words
        if(count($word_list)<150){
            $common = file_get_contents('list_common.json');
            $common = json_decode($common,true);
        }
        
        $canidate_word_list_number = 20;

        echo '<div class="col-md-8"><div class="row">';

        print_list_words($words_scored_position ,"Frequency",$canidate_word_list_number);
        print_list_words($words_scored_elimination,"Excludes",$canidate_word_list_number);
        print_list_words( $frequency_and_exclude_score , "Combined" , $canidate_word_list_number);

        echo "</div>";


        echo "<hr><div class=\"text-center\"><strong>Words Remaining</strong>: " . count($word_list) . " | <strong>Words Total</strong>: $total_words<br><hr><strong>Processing Time</strong>: " . round (( microtime( true ) - $start ),2) . " seconds</div>";
        
        /////////////////////////////////////////////////////////
        // work on graphs



    //create the graphs of positional letter frequency
    echo '<div class="col-md-12">
            <div class="text-center" >
                <table class="table table-sm">
                    <tr>
                        <th colspan="7">Positional Frequency</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>&nbsp;</th>';
    $letters_left =''; //get all the remaining letters in case one of the letter positions below doesn't have a letter in it's corpus
    $i=1;
    foreach( $frequency_position as $column ){
        $letters_left .= implode(array_keys($column));
        sort($column);
       // var_dump($column);
       // echo array_pop($column);
       $highest_frequency_position[ $i ]  = array_pop($column);
       $i++;
    }

    $letters_left = array_unique(str_split($letters_left));

    sort( $letters_left ); //sort the letters so they're alphabetical

    foreach ( $letters_left as $letter ) { //loop through remaining letters
        echo "<tr><th>" . strtoupper( $letter ) . "</th>"; //print the start of the row with the letter
        for ( $i = 1; $i <= 5; $i++ ) {
            if ( !isset( $frequency_position[ $i ][ $letter ] ) ) { //if a value doesn't exist set it to zero
                $frequency_position[ $i ][ $letter ] = 0;
            }
            echo '<td style="background-color:hsl(' . round( $frequency_position[ $i ][ $letter ] * ( 100 / $highest_frequency_position[ $i ] ) ) . 'deg 100% 50% / 50%);">' . round( $frequency_position[ $i ][ $letter ] *100 , 2 ) . '%</td>'; //output the prercentage and color for a cell
        }
        echo "<th>" . strtoupper( $letter ) . "</th></tr>\n"; //print the end of the row with the letter
    }
    if ( $debug ) {
        echo "\n\n<!-- Time Elapsed: " . ( microtime( true ) - $start ) . " seconds -->\n\n";
    }
    echo '</table></div></div></div>';






///////////////////////////////////////////////////////////////////////////////
//helper functions
///////////////////////////////////////////////////////////////////////////////    

    function clean_input( $input ) { //sanatize input
        $input = strtolower( trim( $input ) ); // remove spaces around the letter and lowercase the letter
        $input = preg_replace( '/[^a-z]/', '', $input ); // remove anything that isn't a lowercase letter
        $input = substr( $input, 0, 1 ); // trim the string to one letter
        if ( strlen( $input ) != 1 ) { //after the above validation if we don't have one letter left, set the string to be blank
            return ( '' );
        } else {
            return ( $input );
        }
    }

    function words_remaining_no_matches( $word, $word_list ) { //takes an input word and assuming the input had no matches -- all gray-- how many words it would eliminate
        $re          = "/[$word]/";
        $matches     = preg_grep( $re, $word_list, PREG_GREP_INVERT );
        $match_count = count( $matches );
        return ( $match_count );
    }

    //print bulleted list of words and percentages
    function print_list_words($list,$title,$items){
        echo "<div class=\"col-md-4\">\n<h2>$title</h2>\n<ul style=\" list-style:none;\">\n";
        $list = array_slice($list, 0, $items);
        foreach ( $list as $word => $score ) { // loop through the sorted words
            echo "<li>" . ucfirst( $word ) . " - " . number_format( $score * 100 , 2, '.', '')  . "% </li>\n"; //pretty print a list
        }
        echo "</ul></div>";
    }
?>








        </div></div>
         <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js">
         </script> 
         <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js">
         </script> 
         <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js">
         </script>

<?php if(isset($debug_output)){echo "<div class=\"container\">$debug_output</div>";} ?>
     </body>
     </html>







     