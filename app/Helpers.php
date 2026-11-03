<?php

if (!function_exists('statusHarga')) {
    function statusHarga($harga)
    {
        if ($harga >= 20000) {
            return 'Premium';
        } else {
            return 'Reguler';
        }
    }
}