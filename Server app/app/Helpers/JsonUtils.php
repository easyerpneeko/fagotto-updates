<?php namespace App\Helpers;

class JsonUtils {
  static public function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }
}
