<?php namespace App\Helpers;

use Mail;
use App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EmailSender
{

  private $view;
  private $data;
  private $to;
  private $nameSubject;
  private $titulo;
  private $lang;
  private $isTransTitle = false;
  public  $fromName;

  public function __construct($view = null, $data = null, $to = null, $nameSubject = null, $titulo = null, $lang = null) {
    if ($view)
      $this->view = $view;

    if ($data)
      $this->data = $data;

    if ($to)
      $this->to = $to;

    if ($nameSubject)
      $this->nameSubject = $nameSubject;

    if ($titulo)
      $this->titulo = $titulo;

    if ($lang)
      $this->lang = $lang;
    else
      $this->lang = app()->getLocale();

  }

  public static function Ins($view = null, $data = null, $to = null, $nameSubject = null, $titulo = null, $lang = null) {
    return new Self($view, $data, $to, $nameSubject, $titulo, $lang);
  }

  public function setLang($lang) {
    $this->lang = $lang;
    return $this;
  }

  public function send() {
    if (!defined('PHPUNIT_YOURAPPLICATION_TESTSUITE'))
    {

      $to = $this->to;
      $titulo = $this->titulo;
      $lang = $this->lang;

      if ($this->isTransTitle) {
        $titulo = trans($titulo, [], $this->lang);
      }

      $nameSubject = $this->nameSubject;

      $preventEmailSend = env("PREVENT_EMAIL_SEND", false);

    //  Log::info('MAIL SEND: '.$preventEmailSend);

      if ($preventEmailSend && !empty($preventEmailSend) && strtolower($preventEmailSend) !== 'false' && $preventEmailSend !== false && strtolower($preventEmailSend) !== 'no' && strtolower($preventEmailSend) !== 'noprevent')
        return true;


    //  Log::info('MAIL will send.');

      $originalLang = app()->getLocale();
      App::setlocale($this->lang);

      return Mail::send($this->view,$this->data,
        function ($m) use ($to, $titulo, $nameSubject, $lang) {
          $m->to($to,$nameSubject)->replyTo('norepply@easyerp.cl','ERP')/*->locale($lang)*/->subject($titulo);
        }
      );

      App::setlocale($originalLang);


    }
    return true;
  }

  public static function emailSend($view, $data = [], $to = 'example@gmail.com', $nameSubject = 'Sujeto', $titulo = 'Email de aplicacion', $lang = null) {
    //$mailer = Mail::Ins($view, $data, $to, $nameSubject, $titulo);
    $mailer = Self::Ins($view, $data, $to, $nameSubject, $titulo, $lang);
    return $mailer->send();
  }

  public function setView($view) {
    $this->view = $view;
    return $this;
  }

  public function setTo($to) {
    $this->to = $to;
    return $this;
  }

  public function setSubject($nameSubject) {
    $this->nameSubject = $nameSubject;
    return $this;
  }

  public function setTitulo($titulo, $isTrans = false) {
    $this->titulo = $titulo;
    $this->isTransTitle = $isTrans;
    return $this;
  }

  public function setData($data) {
    $this->data = $data;
    return $this;
  }

}
