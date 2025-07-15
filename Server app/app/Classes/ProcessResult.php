<?php namespace App\Classes;

class ProcessResult {

  public $ok;
  public $content;

  public function __construct($ok = null, $content = null) {
    $this->ok       = $ok;
    $this->content  = $content;
  }

  public static function Create($ok = null, $content = null) {
    return new Self($ok, $content);
  }

}
