<?php

namespace App\Services;

class VerifFilm
{
    private  $key;

    function __construct($key){
        $this->key = $key;
    }

    public function exist($name){
        $name = str_replace(" ", "+", $name);
        $url = "http://www.omdbapi.com/?apikey=".$this->key."&t=".$name;
        $response = file_get_contents($url);

        return json_decode($response, true)["Response"];
    }

    public function getDesc($name){
        $name = str_replace(" ", "+", $name);
        $url = "http://www.omdbapi.com/?apikey=".$this->key."&t=".$name;
        $response = file_get_contents($url);

        return json_decode($response, true)["Plot"];
    }

}