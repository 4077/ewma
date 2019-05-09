<?php

function process()
{
    $process = app()->process;

    if ($process->getPid()) {
        return $process;
    } else {
        return new \BlackHole;
    }
}
