<?php

function generateToken($partnerServiceId, $presharekey)
{
    return base64_encode(openssl_encrypt($partnerServiceId, 'aes-128-ecb', $presharekey, OPENSSL_RAW_DATA));
}

/**
 * Usage: <?php echo base64_encode_image ('img/logo.png','png'); ?>
 * @param string $filename
 * @param string $filetype
 * @return string
 */
function base64_encode_image ($filename=string,$filetype=string) {
    if ($filename) {
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
    }
}


function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
}