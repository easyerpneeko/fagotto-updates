<?php

namespace App\Helpers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//TODO: HACERLE UN METODO PARA LOS ORDENAMIENTOS POR PROPIEDAD

class MPage
{

  public $page;
  public $perPage;
  public $minPage;
  public $maxPage;
  public $items;
  public $itemsTotal;

  private $model;
  private $request;

  public $id;

  private $frontPages;
  private $backPages;

  /**/
  public $filters = [

  ];

  public $orders = [

  ];

  private $filtersList = [
  ];

  //Lista de atributos que se pueden ordenar
  private $ordersList = [
    "id" => "id",
  ];

  public $ordersKeys = [
    //esto debe estar vaciado
  ];

  private $normalFilters = ['id'];

  private $normalOrders = [
    'id'
  ];

  public $filtersKeys = [];

  private $tableName = '';
  /**/

  private $processItem = null;

  private $alternativeCount = false;

  function __construct($model,Request $request = null , $perPage = 10, $id = '', $tableName = ''){
    $this->page       = 1;
    $this->perPage    = $perPage;

    $this->minPage    = 1;
    $this->maxPage    = 1;

    $this->items      = [];
    $this->itemsTotal = 0;

    $this->request    = $request;
    $this->model      = $model;
    $this->id         = $id;

    $this->frontPages = 4;
    $this->backPages  = 4;

    $this->tableName  = $tableName;
  }

  function setAlternativeCount($value = false) {
    $this->alternativeCount = $value;
    return $this;
  }

  function setTableName($tableName = '') {
    $this->tableName  = $tableName;
  }

  //Lista de atributos que se pueden ordenar
  function setOrdersList($ordersList = ['id'=>'id']) {
    $this->ordersList  = $ordersList;
  }

  function setNormalOrders($normalOrders = ['id']) {
    $this->normalOrders  = $normalOrders;
  }

  function setOrdersParams($orders = ['id']) {

    $ordersList = [];
    $normalOrders = [];

    foreach ($orders as $key => $value) {
      $ordersList[$key] = $value;
      $normalOrders[] = $value;
    }

    $this->setOrdersList($ordersList);
    $this->setNormalOrders($normalOrders);

  }

  function setNormalFilters($normalFilters = ['id']) {
    $this->normalFilters  = $normalFilters;
    foreach ($normalFilters as $filter) {
      $this->filtersList[$filter]    = $filter;
    }
  }

  function setResultParams($orders = ['id'], $filters = ['id']) {
    $this->setOrdersParams($orders);
    $this->setNormalFilters($filters);
    return $this;
  }

  function requestProcess(){
    if ($this->request->input($this->id.'page'))
      $this->page = (integer) $this->request->input($this->id.'page');

    return $this->getFilters()->getOrderby();
  }

  private function cloneActualModel() {
    return clone $this->model;
  }

  private function manuallyCount() {
    $modelCount = $this->cloneActualModel();
    return DB::select('SELECT count(*) as count FROM ('.
    DB::raw($modelCount->distinct($this->tableName.'.id')->toSql()).
    ') as counter')[0]->count;
  }

  private function laravelCount() {
    $modelCount = $this->cloneActualModel();
    return $modelCount->distinct($this->tableName.'.id')->count($this->tableName.'.id');
  }

  function getTotals(){

    $this->itemsTotal = $this->laravelCount();
    if ($this->alternativeCount)
      $this->itemsTotal = $this->manuallyCount();

    $this->maxPage = ceil( $this->itemsTotal / $this->perPage );

    return $this;

  }

  function processFiltersModel(){

    foreach ($this->filters as $key => $value){
      if (!empty($value) || $value == '0')
        if (in_array($key, $this->normalFilters)){
            $this->model->where($key,'=',$value);
        } else {
          switch ($key) {
            case 'subordinado':
              //uploader
              if (Auth::user()->role == 'bo general' || Auth::user()->role == 'backoffice')
                $this->model->where('supervisor','=',$value);

              if (Auth::user()->role == 'supervisor')
                $this->model->where('ejecutivo','=',$value);
            break;
          }
        }
    }

    return $this;
  }

  function processOrdersModel(){

    foreach ($this->orders as $key => $value){
      if (!empty($value) || $value == '0')
        if (in_array($key, $this->normalOrders)){
            $this->model->orderBy($this->tableName!=""?$this->tableName.".".$key:$key ,$value);
        } else {
          switch ($key) {
            /*Ordenamientos no genericos... que no deberian haber por que solo hay DESC y ASC*/
          }
      }
    }

    return $this;
  }

  function getQuery(){

    $this->items =
    $this->model
    ->take($this->perPage)
    ->skip(($this->page - 1) * $this->perPage)
    ->get();

    //var_dump($this->items->take(1000)->skip(0)->count());exit();

    return $this;

  }

  function pageValidate(){
    if ($this->page < $this->minPage) $this->page = (integer) $this->minPage;
    if ($this->page > $this->maxPage) $this->page = (integer) $this->maxPage;

    //var_dump($this->page);exit();

    return $this;
  }

  function execute(){
    $this->requestProcess()->processFiltersModel()->processOrdersModel()->getTotals()->pageValidate()->getQuery();
    return $this;
  }

  function getFilters(){
    $filtersList = $this->filtersList;

    //var_dump($filtersList);exit();

    foreach ($filtersList as $key => $value) {
      $this->filtersKeys[$value] = $this->id.'filters_'.$key;
      if (
        !empty($this->request->input($this->id.'filters_'.$key))
        || $this->request->input($this->id.'filters_'.$key) == '0' //Esto es por que el '0' lo detecta como empty
      )
        $this->filters[$value] = $this->request->input($this->filtersKeys[$value]);
      else
        $this->filters[$value] = null;
    }
    return $this;
  }

  function getOrderby(){
    $ordersList = $this->ordersList;
    foreach ($ordersList as $key => $value) {
      $this->ordersKeys[$value] = $this->id.'orderby_'.$key;
      if (
        !empty($this->request->input($this->id.'orderby_'.$key))
        || $this->request->input($this->id.'orderby_'.$key) == '0' //Esto es por que el '0' lo detecta como empty
      )
        $this->orders[$value] = $this->request->input($this->ordersKeys[$value]);
      else
        $this->orders[$value] = null;
    }
    return $this;
  }

  function getPaginate(){

    $backPages = $this->backPages;
    $frontPages = $this->frontPages;

    $pagesArray = [];

    for ($i=1; $i <= $backPages; $i++) {
      $page = $this->page - $i;
      if (!($page < $this->minPage) && !($page > $this->maxPage))
        array_unshift ($pagesArray, $page);
      else
        $frontPages += 1;
    }

    //var_dump($pages);exit();
    $pagesArray[] = $this->page;

    //var_dump($frontPages);exit();

    for ($i=1; $i <= $frontPages; $i++) {
      $page = $this->page + $i;
      if (!($page < $this->minPage) && !($page > $this->maxPage))
        $pagesArray[] = $page;
      else
        $backPages += 1;
    }

    for ($i=1; $i <= $backPages; $i++) {
      $page = $this->page - $i;
      if (!($page < $this->minPage) && !($page > $this->maxPage) && !in_array($page, $pagesArray))
        array_unshift ($pagesArray, $page);
    }

    $resPages = [];

    $allRequest = $this->request->all();
    $keyRequest = $this->id.'page';
    $allRequestUrl = '';
    foreach ($allRequest as $key => $value) {
      if ($key != $keyRequest)
        $allRequestUrl .= '&'.$key.'='.$value;
    }

    for ($i=0; $i < sizeof($pagesArray); $i++) {
      $resPages[] = [
        'page'    => (integer) $pagesArray[$i],
        'url'     => $keyRequest.'='.$pagesArray[$i].$allRequestUrl,
        'current' => ($pagesArray[$i] == $this->page) ? true : false
      ];
    }

    $backPage = null;
    $nextPage = null;

    //var_dump($resPages[0]['current']);exit();

    if (!$resPages[0]['current'])
      foreach ($resPages as $key => $value)
        if ($value['current'])
          $backPage = $resPages[$key - 1];


    if (!$resPages[sizeof($resPages) - 1]['current'])
      foreach ($resPages as $key => $value)
        if ($value['current'])
          $nextPage = $resPages[$key + 1];

    return [
      'pages' => $resPages,
      'back'  => $backPage,
      'next'  => $nextPage
    ];
  }

  function getResult(){

    if ($this->processItem)
      $itemsProcess = $this->processSuministredItems($this->items);
    else
      $itemsProcess = $this->items;

    return [
      'items'       => $itemsProcess,
      'pages'       => $this->maxPage,
      'total'       => $this->itemsTotal,
      'perpage'     => $this->perPage,
      'page'        => $this->page,
      'id'          => $this->id,
      'paginate'    => $this->getPaginate(),
      'filters'     => $this->filters,
      'filtersKeys' => $this->filtersKeys,
      'ordersKeys'  => $this->ordersKeys,
      'pageKey'     => $this->id.'page'
    ];

  }

  protected function processSuministredItems($items) {
    $function = $this->processItem;
    foreach ($items as $key => $value) {
      $items[$key] = $function($value);
    }
    return $items;
  }

  function setProcessItem($processItem = null) {
    $this->processItem = $processItem;
    return $this;
  }

  static function paginate($model, Request $request = null, $perPage = 10, $id = '', $tableName = '',$orders = ['id'], $filters = ['id'], $alternativeCount = false, $processItem = null){

    $OPaginator = new MPage($model, $request, $perPage, $id, $tableName);

    return $OPaginator->setAlternativeCount($alternativeCount)
                      ->setProcessItem($processItem)
                      ->setResultParams($orders, $filters)
                      ->execute()
                      ->getResult();

  }

}

?>
