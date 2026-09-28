<?php

namespace App\Support;

class ExcelColumn
{
    public static function index($letters)
    {
        $index = 0;
        $letters = strtoupper((string) $letters);
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index;
    }

    public static function letter($index)
    {
        $letter = '';
        $index = (int) $index;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int) (($index - $mod) / 26);
        }

        return $letter;
    }
}
