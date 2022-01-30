<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Hello, Wordle!</title>
    <style>
        td, th {
            padding: 0 0 0 0 !important;
        }
        .table td, .table th {
            border-top: 1px solid #5252525c !important;
        }
        </style>
  </head>
  <body>
      <div class="container">
<h1>Hello, Wordle!</h1>



<div class="row">
<?php
    $word_list     = explode( PHP_EOL, file_get_contents( 'list.txt' ) );
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
    echo '
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
    foreach($letters as $letter){
        echo "<tr><th>".strtoupper($letter)."</th>";
        for ($i=1;$i<=5;$i++){
            echo '<td style="background-color:hsl(' . round( ( $frequency_position[$i][$letter] * 100 ) * (1/$frequency_position[$i]['highest']) ) . 'deg 100% 50% / 50%);">' . round( $frequency_position[$i][$letter] * 100, 2) . '%</td>';
        }
        echo "<th>".strtoupper($letter)."</th></tr>";
    } 
    echo '</table></div></div></div>';

//var_dump($frequency_position);

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
        $input ['highest'] = $highest;
        return ( $input );
    }
?>


<hr>


</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>
