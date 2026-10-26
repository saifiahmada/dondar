<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}