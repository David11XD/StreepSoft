<?php

function asset(string $path = ''): string
{
    return url('public/' . ltrim($path, '/'));
}
