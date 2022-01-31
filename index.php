<!DOCTYPE html>
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



	</style>
</head>
<body>
	<div class="container">
		<h1 class="text-center">Hello, Wordle!</h1>
		<div class="text-center">
			<hr>
		<?php


           if(count($_POST)>=1){//check to see if we got anything in the form
                foreach($_POST as $box => $letter){ //look at each square from the form
                    if(stripos($box, 'radio') !== false)//check to see what kind of input we're looking at, if it says 'radio' it's the type of letter, ie exclude, include, correct
                    {
                        $radio_data = $letter;
                        $radio_data = clean_input($radio_data); // make sure the input is a single letter

                        $box = str_replace('radio_','',$box); // strip the 'radio/ from the box
                        $box = explode('_',$box); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                        $letter_type[$box[0]][$box[1]] = $radio_data; //add the validated input to the array
                    }else{
                        $letter = clean_input($letter); // make sure the input is a single letter
                        $box = explode('_',$box); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                        $letters[$box[0]][$box[1]] = $letter; //add the validated input to the array
                    }
                }
               
         }
        ?>
        	<form action="index.php" method="post"> 
                <?php //print out the form
				    for($i=1;$i<=6;$i++){ //6 rows
                        echo '<div class="row">';
                        for($j=1;$j<=5;$j++){ //by 5 letters
                            if(isset($letters[$i][$j])){ //make sure the letter variable is set before we reference
                                $form_letter= strtoupper($letters[$i][$j]);
                            }else{
                                $form_letter='';
                            }

                            $exclude = '';//reset variables up for showing which box is checked
                            $include = '';
                            $correct = '';
                            if(isset($letter_type[$i][$j])){ //check to see which box is checked and set the correct one
                                switch ($letter_type[$i][$j]) {
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
                            }
                            echo '<div class="col-xs-2 ml-1 m-r1"><input maxlength="1" name="'.$i.'_'.$j.'" size="1" type="text" value="'.$form_letter.'"><br>
                            <div style="background-color:#787c7e;"><input style="margin-left:-5px;" type="radio" name="radio_'.$i.'_'.$j.'" value="exclude"'.$exclude.'></div>
                            <div style="background-color:#c9b458;"><input style="margin-left:-5px;" type="radio" name="radio_'.$i.'_'.$j.'" value="include"'.$include.'></div>
                            <div style="background-color:#6aaa64;"><input style="margin-left:-5px;" type="radio" name="radio_'.$i.'_'.$j.'" value="correct"'.$correct.'></div>
                            </div>'; //output the form row with the valid entries
                        }
                        echo '</div><br>';
                    }

                   // echo '<div class="row"><div class="col-md-4"><pre>'.print_r($_POST,true).'</pre></div>
                  // <div class="col-md-4"><pre>'.print_r($letters,true).'</pre></div>
                  //  <div class="col-md-4"><pre>'.print_r( $letter_type,true).'</pre></div></div>';
                ?>

      
                <br>
				<input type="submit">
			</form>


            <?php
                $word_list     = explode( PHP_EOL, file_get_contents( 'list.txt' ) ); //get the wordlist and convert it to an array
                $total_words   = count( $word_list );
                                foreach ( $word_list as $word ) { //look at each word individually
                    $letters = str_split( $word ); // turn the word into an array
                    for ( $i = 0; $i < count( $letters ); $i++ ) { //loop through the letters of each word

                        //do a frequnecy analasis for all letters of all words
                        if ( isset( $frequency_raw[ $letters[ $i ] ] ) ) { //if the array key doesn't exist we need to set it to zero before incrementing
                            $frequency_raw[ $letters[ $i ] ]++;
                        } else {
                            $frequency_raw[ $letters[ $i ] ] = 1;
                        }

                        //do a frequnecy analasis for each letter position in each word
                        //count how many times a given letter occurs at a given position in the word
                        if ( isset( $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ] ) ) { //if the array key doesn't exist we need to set it to zero before incrementing
                            $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ]++;
                        } else {
                            $frequency_position_raw[ $i + 1 ][ $letters[ $i ] ] = 1;
                        }

                    }
                }

		        foreach ( $frequency_position_raw as $index => $position) { //look at the first position probablites
                   foreach ($position as $letter => $count ){ // look at each letter in the postional probablites
                        $frequency_position [$index][ $letter ] = ( $count / ( $total_words ) ) * 100; //calcuate actual probablity
                   }
                   ksort($frequency_position [$index]);
		        }

//var_dump( $frequency_position);

                foreach ( $word_list as $word ) { //work through each word to grade it's quality based on positional probablity
                    $letter     = str_split( $word ); //split the word into letters
                    $word_score = 0;//reset for calculating the word score
                    for ( $i = 0; $i < count( $letter ); $i++ ) { //loop through word
                        $word_score += $frequency_position[ $i+1 ][ $letter[ $i ] ]; //add score up (frequency position's offset starts at 1 rather than zero)
                    }
                    $words_scored[ $word ] = $word_score;//collect scores
                }

                arsort( $words_scored ); //sort the words by the scores 

                echo "<div class=\"\"><ul style=\" list-decoration:none;columns: 3;-webkit-columns: 3;-moz-columns: 3;\">";
                $number_of_words = 1; //
                foreach ( $words_scored as $word => $score ) { // loop through the sorted words
                    echo "<li>" . ucfirst( $word ) . " - " . round ( $score * 100 )." </li>"; //pretty print a list
                    $number_of_words++;
                    if ( $number_of_words > 15 ) { //stop looping through words after a certain number
                        break;
                    }
                }

     
                echo "</ul></div>";



                function clean_input( $input ) {
                    $input = strtolower( trim( $input ) ); // remove spaces around the letter and lowercase the letter
                    $input = substr( $input, 0, 1 ); // trim the string to one letter
                    $input = preg_replace( '/[^a-z]/', '', $input ); // remove anything that isn't a lowercase letter
                    if ( strlen( $input ) != 1 ) { //after the above validation if we don't have one letter left, set the string to be blank
                        return ( '' );
                    } else {
                        return ( $input );
                    }
                }