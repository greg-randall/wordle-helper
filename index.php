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
                $start = microtime( true ); //timer to check that we aren't running too long.
                if ( count( $_POST ) >= 1 ) { //check to see if we got anything in the form
                    foreach ( $_POST as $box => $letter ) { //look at each square from the form
                        if ( stripos( $box, 'radio' ) !== false ) //check to see what kind of input we're looking at, if it says 'radio' it's the type of letter, ie exclude, include, correct
                            {
                            $radio_data                            = $letter;
                            $radio_data                            = clean_input( $radio_data ); // make sure the input is a single letter
                            $box                                   = str_replace( 'radio_', '', $box ); // strip the 'radio/ from the box
                            $box                                   = explode( '_', $box ); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                            $letter_type[ $box[ 0 ] ][ $box[ 1 ] ] = $radio_data; //add the validated input to the array
                        } else {
                            $letter                            = clean_input( $letter ); // make sure the input is a single letter
                            $box                               = explode( '_', $box ); //get the row & column from the box variable ie '5_3' to array(5, 3) for building the 2d array
                            $letters[ $box[ 0 ] ][ $box[ 1 ] ] = $letter; //add the validated input to the array
                        }
                    }
                }
            ?>
            <form action="index.php" method="post"> 
            <?php //print out the form
                for ( $i = 1; $i <= 6; $i++ ) { //6 rows
                    echo '<div class="row">';
                    for ( $j = 1; $j <= 5; $j++ ) { //by 5 letters
                        if ( isset( $letters[ $i ][ $j ] ) ) { //make sure the letter variable is set before we reference
                            $form_letter = strtoupper( $letters[ $i ][ $j ] );
                        } else {
                            $form_letter = '';
                        }
                        $exclude = ''; //reset variables up for showing which box is checked
                        $include = '';
                        $correct = '';
                        if ( isset( $letter_type[ $i ][ $j ] ) ) { //check to see which box is checked and set the correct one
                            switch ( $letter_type[ $i ][ $j ] ) {
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
                        echo '<div class="col-xs-2 ml-1 m-r1"><input maxlength="1" name="' . $i . '_' . $j . '" size="1" type="text" value="' . $form_letter . '"><br>
                                        <div style="background-color:#787c7e;"><input style="margin-left:-5px;" type="radio" name="radio_' . $i . '_' . $j . '" value="exclude"' . $exclude . '></div>
                                        <div style="background-color:#c9b458;"><input style="margin-left:-5px;" type="radio" name="radio_' . $i . '_' . $j . '" value="include"' . $include . '></div>
                                        <div style="background-color:#6aaa64;"><input style="margin-left:-5px;" type="radio" name="radio_' . $i . '_' . $j . '" value="correct"' . $correct . '></div>
                                        </div>'; //output the form row with the valid entries
                    }
                    echo '</div><br>';
                }
            ?>
      
                <br>
				<input type="submit">
			</form>


        <?php
            $word_list    = explode( PHP_EOL, file_get_contents( 'list.txt' ) ); //get the wordlist and convert it to an array
            $total_words  = count( $word_list );
            $exclude_list = '';
            $include_list = '';
            for ( $i = 1; $i <= 6; $i++ ) { //6 rows
                for ( $j = 1; $j <= 5; $j++ ) { //by 5 letters
                    if ( isset( $letter_type[ $i ][ $j ] ) ) {
                        switch ( $letter_type[ $i ][ $j ] ) {
                            case 'e':
                                $exclude_list = $exclude_list . $letters[ $i ][ $j ];
                                break;
                            case 'i':
                                $include_list = $include_list . $letters[ $i ][ $j ];
                                break;
                            case 'c':
                                $correct_letter[ $j ] = $letters[ $i ][ $j ];
                                break;
                        }
                    }
                }
            }
            //strip words from list that have excluded letters
            if ( strlen( $exclude_list ) !== 0 ) {
                //$exclude_list = array_diff( $exclude_list, $p ); //remove all the greens from the excludes
                //remove words with gray letters from word list
                foreach ( $word_list as $key => $word ) {
                    foreach ( str_split( $exclude_list ) as $letter ) { //go through each letter of each word
                        if ( substr_count( $word, $letter ) > 0 ) { // if we find a letter from the exclude list remove the word
                            unset( $word_list[ $key ] );
                        }
                    }
                }
            }
            //strip words from list that have do not have included letters
            if ( strlen( $include_list ) !== 0 ) {
                if ( isset( $correct_letter ) && count( $correct_letter ) >= 1 ) { // make sure there are correct positioned letters before we reference the variable
                    $include_list = $include_list . implode( $correct_letter ); // include list has both green and gold letters
                }
                $include_list = implode( array_diff( str_split( $include_list ), str_split( $exclude_list ) ) ); //remove all the excludes from the includes (turn each variable into an array then diff them them turn back into a string)
                //make sure that words have letters from the green list
                foreach ( $word_list as $key => $word ) {
                    foreach ( str_split( $include_list ) as $letter ) { //go through each letter of each word
                        if ( substr_count( $word, $letter ) < 1 ) { // if we dont find a letter from the include list remove the word
                            unset( $word_list[ $key ] );
                        }
                    }
                }
            }
            $total_letters = 0;
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
                    $total_letters++;
                }
            }
            $highest_frequency = 0;
            foreach ( $frequency_raw as $letter => $count ) { // generate overall proabilities for all words
                $frequency[ $letter ] = ( $count / $total_letters ) * 100; //calcuate actual probablity
                if($frequency[ $letter ]>$highest_frequency){//collect the highest frequency percentage for creating a color scale below
                    $highest_frequency = $frequency[ $letter ];
                }
            }
            arsort( $frequency );//sort by highest percentage first
//var_dump($frequency);
            
            foreach ( $frequency_position_raw as $index => $position ) { //look at the first position probablites
                $highest_frequency_position[$index] = 0;
                foreach ( $position as $letter => $count ) { // look at each letter in the postional probablites
                    $frequency_position[ $index ][ $letter ] = ( $count / ( $total_letters / 5 ) ) * 100; //calcuate actual probablity
                    if($frequency_position[ $index ][ $letter ]>$highest_frequency_position[$index]){//collect the highest frequency percentage for each letter position for creating a color scale below
                        $highest_frequency_position[$index] =$frequency_position[ $index ][ $letter ];
                    }
                }
                ksort( $frequency_position[ $index ] );//sort the letter frequency position tables by the letter
           }
//var_dump( $highest_frequency_position );
//var_dump( $frequency_position);
            
            foreach ( $word_list as $word ) { //work through each word to grade it's quality based on positional probablity
                $letter     = str_split( $word ); //split the word into letters
                $word_score = 0; //reset for calculating the word score
                for ( $i = 0; $i < count( $letter ); $i++ ) { //loop through word
                    $word_score += $frequency_position[ $i + 1 ][ $letter[ $i ] ]; //add score up (frequency position's offset starts at 1 rather than zero)
                }
                $words_scored[ $word ] = $word_score; //collect scores
            }
            arsort( $words_scored ); //sort the words by the scores 
            echo "<div class=\"\"><ul style=\" list-style:none;columns: 3;-webkit-columns: 3;-moz-columns: 3;\">";
            $number_of_words = 1; //
            foreach ( $words_scored as $word => $score ) { // loop through the sorted words
                echo "<li>" . ucfirst( $word ) . " - " . round( $score * 100 ) . " </li>"; //pretty print a list
                $number_of_words++;
                if ( $number_of_words > 15 ) { //stop looping through words after a certain number
                    break;
                }
            }
            echo "</ul></div>";
            echo " <div class=\"text-center\">Possible Words Based on Limiters: " . count( $word_list ) . "<br>Total Words in Word List: $total_words<hr>";
           
            //create the graphs of letter frequency based on current word list
            echo '<div class="row"> 
		    <div class="col-md-3">
		    <div class="text-center">
		        <table class="table table-sm">
		            <tr>
		                <th colspan="2">Overall Frequency</th>
		            </tr>
		            <tr>
		                <th>Letter</th>
		                <th>Percentage</th>
		            </tr>';
                    $color_scale = 100 / $highest_frequency; //we're doing a basic color range for our table cells to help show which letters are more frequent. HSV color makes this easy, 0 = red & 100=green. this scales the highest frequency to be the greenest
                    foreach($frequency as $letter => $percent){ //run through each letter in the overall frequency count and print our table
                        echo '<tr><th>' . strtoupper( $letter ) . '</th><td style="background-color:hsl(' . round(  $percent  * $color_scale ) . 'deg 100% 50% / 50%);">' . round( $percent , 1 ) . '%</td></tr>'; //output the prercentage and color for a cell
                    }
            echo '</table></div></div>';

            //create the graphs of positional letter frequency
		    echo '<div class="col-md-9">
		    <div class="text-center" >
		        <table class="table table-sm">
		            <tr>
		                <th colspan="7">Positional Frequency</th>
		            </th>
		            <tr>
		                <th>&nbsp;</th>
		                <th>1</th>
		                <th>2</th>
		                <th>3</th>
		                <th>4</th>
		                <th>5</th>
		                <th>&nbsp;</th>';
		    
            $letters_left = array_keys($frequency);//get all the remaining letters in case one of the letter positions below doesn't have a letter in it's corpus
            sort($letters_left); //sort the letters so they're alphabetical
//var_dump($letters_left); 

		    foreach ( $letters_left as $letter ) { //loop through remaining letters
		        echo "<tr><th>" . strtoupper( $letter ) . "</th>";//print the start of the row with the letter
		        for ( $i = 1; $i <= 5; $i++ ) {
		            if ( !isset( $frequency_position[ $i ][ $letter ] ) ) { //if a value doesn't exist set it to zero
		                $frequency_position[ $i ][ $letter ] = 0;
		            }
		            echo '<td style="background-color:hsl(' . round( $frequency_position[ $i ][ $letter ]  * (100/$highest_frequency_position[$i]) ) . 'deg 100% 50% / 50%);">' . round( $frequency_position[ $i ][ $letter ], 2 ) . '%</td>'; //output the prercentage and color for a cell
		        }
		        echo "<th>" . strtoupper( $letter ) . "</th></tr>";//print the end of the row with the letter
		    }
		    echo '</table></div></div></div>';
           
           
           
           
          
           
           
           
            echo "<br><div class=\"text-center\"><strong>Time Elapsed: " . ( microtime( true ) - $start ) . " seconds</div>";
        ?>
         </div>
         <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js">
         </script> 
         <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js">
         </script> 
         <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js">
         </script>
     </body>
     </html>

<?php
    function clean_input( $input ) { //sanatize input
        $input = strtolower( trim( $input ) ); // remove spaces around the letter and lowercase the letter
        $input = substr( $input, 0, 1 ); // trim the string to one letter
        $input = preg_replace( '/[^a-z]/', '', $input ); // remove anything that isn't a lowercase letter
        if ( strlen( $input ) != 1 ) { //after the above validation if we don't have one letter left, set the string to be blank
            return ( '' );
        } else {
            return ( $input );
        }
    }
?>


