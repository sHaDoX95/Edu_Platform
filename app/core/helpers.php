<?php
function pluralize($number, $one, $few, $many) {
    $n = abs($number) % 100;
    $n1 = $n % 10;

    if ($n > 10 && $n < 20) return $many;
    if ($n1 > 1 && $n1 < 5) return $few;
    if ($n1 == 1) return $one;
    return $many;
}