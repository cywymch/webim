<?php
$a = 1;
$data = function () use ($a) {
    echo $a;
};
$data();