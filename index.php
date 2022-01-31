<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title >Hello, Wordle!</title>
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
    $start        = microtime( true );
    $form_entries = false;
    if ( isset( $_POST[ 'gray' ] ) ) {
        $gray = strtolower( $_POST[ 'gray' ] );
        $gray = preg_replace( '/[^a-z]/i', '', $gray );
        if ( strlen( $gray ) !== 0 ) {
            $exclude_list = $gray;
            $form_entries = true;
        } else {
            $exclude_list = '';
        }
    } else {
        $exclude_list = '';
    }
    if ( isset( $_POST[ 'gold' ] ) ) {
        $gold = strtolower( $_POST[ 'gold' ] );
        $gold = preg_replace( '/[^a-z]/i', '', $gold );
        if ( strlen( $gold ) !== 0 ) {
            $include_list = $gold;
            $form_entries = true;
        } else {
            $include_list = '';
        }
    } else {
        $include_list = '';
    }
    for ( $i = 1; $i <= 5; $i++ ) {
        if ( isset( $_POST[ "p" . $i ] ) ) {
            $p[ $i ] = strtolower( $_POST[ "p" . $i ] );
            $p[ $i ] = preg_replace( '/[^a-z]/i', '', $p[ $i ] );
        } else {
            $p[ $i ] = '';
        }
    }
    $word_list   = explode( PHP_EOL, file_get_contents( 'list.txt' ) );
    $total_words = count( $word_list );
    if ( strlen( $exclude_list ) !== 0 ) {
        $exclude_list = array_unique( str_split( $exclude_list ) );
        if ( is_array( $exclude_list ) ) {
            $exclude_list = array_diff( $exclude_list, $p ); //remove all the greens from the excludes
        }
        foreach ( $word_list as $key => $word ) {
            foreach ( $exclude_list as $letter ) {
                if ( substr_count( $word, $letter ) > 0 ) {
                    unset( $word_list[ $key ] );
                }
            }
        }
    }
    if ( strlen( $include_list ) !== 0 ) {
        $include_list = array_unique( str_split( $include_list ) );
        if ( is_array( $exclude_list ) ) {
            $include_list = array_diff( $include_list, $exclude_list ); //remove all the excludes from the includes
        }
        foreach ( $word_list as $key => $word ) {
            foreach ( $include_list as $letter ) {
                if ( substr_count( $word, $letter ) < 1 ) {
                    unset( $word_list[ $key ] );
                }
            }
        }
    }
    if ( strlen( trim( implode( $p ) ) ) !== 0 ) {
        foreach ( $word_list as $key => $word ) {
            $word = str_split( $word );
            for ( $i = 0; $i < 5; $i++ ) {
                if ( $p[ $i + 1 ] !== '' && $p[ $i + 1 ] != $word[ $i ] ) {
                    unset( $word_list[ $key ] );
                }
            }
        }
    }
    if ( count( $word_list ) == 0 ) {
        echo "<h2>Error: No Words Match the Entered Gray/gold/green Letters. Resetting.</h2>";
        $word_list    = explode( PHP_EOL, file_get_contents( 'list.txt' ) );
        $exclude_list = '';
        $include_list = '';
    }
?>

    <form action="index.php" method="post">
    Exclude (Gray): <input type="text" name="gray" value="<?php if(is_array( $exclude_list)){ echo implode($exclude_list);} ?>"><br><br>Include (Gold): <input type="text" name="gold" value="<?php if(is_array( $include_list)){ echo implode($include_list);} ?>"><br><br>
    
    Correct Postion (Green):<br><input type="text" name="p1" maxlength="1" size="1" value="<?php echo $p[1]; ?>"> &nbsp; <input type="text" name="p2" maxlength="1" size="1" value="<?php echo $p[2]; ?>"> &nbsp; <input type="text" name="p3" maxlength="1" size="1" value="<?php echo $p[3]; ?>"> &nbsp; <input type="text" name="p4" maxlength="1" size="1" value="<?php echo $p[4]; ?>"> &nbsp; <input type="text" name="p5" maxlength="1" size="1" value="<?php echo $p[5]; ?>">
    <br><br>
    <input type="submit">
    </form></div>
    <hr>
    <?php
    $total_letters = 0;
    foreach ( $word_list as $word ) {
        $letters = str_split( $word );
        for ( $i = 0; $i < count( $letters ); $i++ ) {
            if ( isset( $frequency_raw[ strtolower( $letters[ $i ] ) ] ) ) {
                $frequency_raw[ strtolower( $letters[ $i ] ) ]++;
            } else {
                $frequency_raw[ strtolower( $letters[ $i ] ) ] = 1;
            }
            if ( isset( $frequency_position_raw[ $i + 1 ][ strtolower( $letters[ $i ] ) ] ) ) {
                $frequency_position_raw[ $i + 1 ][ strtolower( $letters[ $i ] ) ]++;
            } else {
                $frequency_position_raw[ $i + 1 ][ strtolower( $letters[ $i ] ) ] = 1;
            }
            $total_letters++;
        }
    }
    ob_start();
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
    $frequency = generate_frequency_and_table( $frequency_raw, $total_letters, true, true );
    echo '</table></div></div>';
    $i = 1;
    foreach ( $frequency_position_raw as $frequency_position_raw_single ) {
        $frequency_position[ $i ] = generate_frequency_and_table( $frequency_position_raw_single, $total_letters / 5, false, true );
        $i++;
    }
    echo '
    <div class="col-md-9">
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
    $letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
    if ( is_array( $exclude_list ) ) {
        $letters = array_diff( $letters, $exclude_list );
    }
    foreach ( $letters as $letter ) {
        echo "<tr><th>" . strtoupper( $letter ) . "</th>";
        for ( $i = 1; $i <= 5; $i++ ) {
            if ( !isset( $frequency_position[ $i ][ $letter ] ) ) {
                $frequency_position[ $i ][ $letter ] = 0;
            }
            echo '<td style="background-color:hsl(' . round( ( $frequency_position[ $i ][ $letter ] * 100 ) * ( 1 / $frequency_position[ $i ][ 'highest' ] ) ) . 'deg 100% 50% / 50%);">' . round( $frequency_position[ $i ][ $letter ] * 100, 2 ) . '%</td>';
        }
        echo "<th>" . strtoupper( $letter ) . "</th></tr>";
    }
    echo '</table></div></div></div>';
    function generate_frequency_and_table( $input_raw, $total_letters, $echo = false, $sort_by_key = false ) {
        $highest = 0;
        foreach ( $input_raw as $letter => $count ) {
            $input[ $letter ] = $count / $total_letters;
            if ( $input[ $letter ] > $highest ) {
                $highest = $input[ $letter ];
            }
        }
        if ( $sort_by_key ) {
            ksort( $input );
        } else {
            arsort( $input );
        }
        $color_scale = 1 / $highest;
        if ( $echo ) {
            foreach ( $input as $letter => $percent ) {
                echo '<tr><th>' . strtoupper( $letter ) . '</th><td style="background-color:hsl(' . round( ( $percent * 100 ) * $color_scale ) . 'deg 100% 50% / 50%);">' . round( $percent * 100, 1 ) . "%</td></tr>";
            }
        }
        $input[ 'highest' ] = $highest;
        return ( $input );
    }
    $tables = ob_get_contents();
    ob_end_clean();
    $break = 0;
    foreach ( $word_list as $word ) {
        if ( !$form_entries ) {
            $skip_word = false;
            foreach ( str_split( $word ) as $letter ) {
                if ( substr_count( $word, $letter ) >= 2 ) {
                    $skip_word = true;
                }
            }
        } else {
            $skip_word = false;
        }
        if ( !$skip_word ) {
            $letter     = str_split( " " . $word );
            $word_score = 0;
            for ( $i = 1; $i < count( $letter ); $i++ ) {
                $word_score += $frequency_position[ $i ][ $letter[ $i ] ];
            }
            $words_scored[ $word ] = $word_score;
        }
    }
    arsort( $words_scored );
    echo "<div class=\"\"><p>High Quality Guesses Based on Positional Frequency:</p><ul style=\" columns: 3;
   -webkit-columns: 3;
   -moz-columns: 3;\">";
    $i = 1;
    foreach ( $words_scored as $word => $score ) {
        echo "<li>" . ucfirst( $word ) . "</li>";
        $i++;
        if ( $i > 45 ) {
            break;
        }
    }
    echo "</ul></div>";
    echo "<hr>
   <div class=\"text-center\">Possible Words Based on Limiters: " . count( $word_list ) . "<br>Total Words in Word List: $total_words<br>
   <hr>
   $tables";
    echo "<br><div class=\"text-center\"><strong>Time Elapsed: " . ( microtime( true ) - $start ) . " seconds</div>";
?>

</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>