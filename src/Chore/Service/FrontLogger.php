<?php

namespace App\Chore\Service;

class FrontLogger
{
    /**
     * Show an error message within console when in the development environment.
     *
     * @param $sort
     * @return void
     */
    public static function showError(string $msg, $level = 'warning'): void
    {
        if ($_ENV['APP_ENV'] == 'dev') {
            echo '<script>';
            if($level == 'error') {
                echo 'console.error("'.$msg.'")';
            } else if($level == 'info') {
                echo 'console.info("'.$msg.'")';
            } else if($level == 'log') {
                echo 'console.log("'.$msg.'")';
            } else if($level == 'warning') {
                echo 'console.warn("'.$msg.'")';
            }
            echo '</script>';
        }
    }
}