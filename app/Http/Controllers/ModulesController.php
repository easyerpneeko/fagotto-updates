<?php

namespace App\Http\Controllers;
use App\Modules;
use App\DataBase;
use App\Helpers\ConectionDB;
use Illuminate\Support\Facades\DB;
use App\Helpers\MPage;
use App\Aplication;

use Illuminate\Http\Request;
use App\Relations\Modules_apps;

class ModulesController extends Controller
{

  public function getModules(Request $request){
    $pquery = DB::table('modules');

    if (!$pquery) return response()->json('Error del servidor',500);

    //Ordenamientos
    $orders = ['id','name'];
    //Filtrados
    $filters = ['name'];

    //Procesamiento individual de items
    $Paginated = MPage::paginate($pquery, $request,10,'','modules',$orders,$filters, null);

    return response()->json($Paginated);
  }

  public function getModulesList(){
    $modules = DB::table('modules')->select('id','name')->get();
    if (!$modules) return response()->json('Error del servidor',500);
    return response()->json($modules);
  }

  public function getModulesAplication($id){
    $app = Aplication::find($id);
    $modules = $app->getApp();
    $modules = $modules['Modules'];
    return response()->json($modules);
  }

  public function executeMigration($relid) {
    $moduleapp = Modules_apps::find($relid);
    if (!$moduleapp) return response()->json('Modulo no instalado o no encontrado',404);
    $moduleapp->executateMigration();
    return response()->json('Migraciones completadas!',200);
  }

  public function updateModule($relid) {
    $moduleapp = Modules_apps::find($relid);
    if (!$moduleapp) return response()->json('Modulo no instalado o no encontrado',404);
    $moduleapp->updateModule();
    return response()->json('Actualizado!',200);
  }

  public function uninstallModule($relid){
    $module_apps = Modules_apps::find($relid);
    if (!$module_apps) return response()->json('El modulo ya fue desinstalado o nunca existio',404);

    $uninstall = $module_apps->uninstall();
    if (!$uninstall[0])
      return response()->json($uninstall[1],$uninstall[2]);

    return response()->json('Modulo desinstalado correctamente',200);
  }

}
