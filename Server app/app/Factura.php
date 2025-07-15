<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
  protected $connection = 'mysql';
  protected $fillable = [
    'orden',
    'folio',
    'folioAsign',
    'url',
    'typeFolio'
  ];
}
