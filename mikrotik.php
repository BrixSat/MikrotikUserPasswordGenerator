<?php

//require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

function ssh_run($ssh_command)
{

    global $SSH_PASS, $SSH_USER, $SSH_PORT, $MT_IP;
    $connection = ssh2_connect($MT_IP, $SSH_PORT);
    if (!ssh2_auth_password($connection, $SSH_USER, $SSH_PASS))
    {
        die('Login Failed');
    }
    echo ssh2_exec($connection, $ssh_command);
}

function insertIntoRouter($vouchers, $dryrun = false)
{
    foreach ($vouchers as $voucher)
       {
            $cmd1 ='/user-manager/user add name="'. $voucher. '"  password="'. $voucher. '" otp-secret="" group=default shared-users=4 attributes=""';
            $cmd2 ='/user-manager/user-profile add user="'. $voucher. '"  profile="Camping"';
            if ($dryrun == false )
            {
                ssh_run($cmd1);
                ssh_run($cmd2);
            }
            else
            {
                file_put_contents("comandos.txt", $cmd1 . PHP_EOL, FILE_APPEND | LOCK_EX);
                file_put_contents("comandos.txt", $cmd2 . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
       }
}
