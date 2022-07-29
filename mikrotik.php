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

function insertIntoRouter($vouchers)
{
    foreach ($vouchers as $voucher)
       {
            ssh_run('/user-manager/user add name="'. $voucher. '"  password="'. $voucher. '" otp-secret="" group=default shared-users=4 attributes=""');
            ssh_run('/user-manager/user-profile add user="'. $voucher. '"  profile="Camping"');
       }
}
