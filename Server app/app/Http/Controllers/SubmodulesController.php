<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Submodules;
use App\DataBase;
use App\Helpers\ConectionDB;
use Illuminate\Support\Facades\DB;
use App\Aplication;

use App\Relations\Modules_apps;
use App\Relations\Submodules_modules_apps;

class SubmodulesController extends Controller
{

  public function getSubmodules($id){
    $pquery = DB::table('submodules')->where('module_id', $id)->get();
    if(!$pquery) return response()->json('No existe submodulos para este modulo', 404);
    return response()->json($pquery);
  }

  public function getSubModulesList(Request $request){

    if ($request->input('module'))
    $submodules = DB::table('submodules')->where('module_id',$request->input('module'))->select('id','name')->get();
    else $submodules = DB::table('submodules')->select('id','name')->get();

    if (!$submodules) return response()->json('Error del servidor',500);
    return response()->json($submodules);

  }

  //Tranquilo, yo me encargo

  public function getSubmodulesOfModuleApp($id){

    $moduleApp = Modules_apps::find($id);

    $app = Aplication::find($moduleApp->app_id);
    $modules = $app->getApp()['Modules'];
    $submodules = [];
    foreach ($modules as $module) {
      if ($module['relid'] == $id) {
        $submodules = $module['sub'];
      }
    }

    if (!sizeof($submodules)) return response()->json('No se encontro la relacion',404);

    return response()->json($submodules);

  }

  public function getSubmodulesOfApp($moduleid,$appid) {

    $app = Aplication::find($appid);
    $modules = $app->getApp()['Modules'];
    $submodules = [];
    foreach ($modules as $module) {
      if ($module['id'] == $moduleid) {
        $submodules = $module['sub'];
      }
    }

    if (!sizeof($submodules)) return response()->json('No se encontro la coincidencia',404);

    return response()->json($submodules);

  }

  public function executeMigration($relid) {
    $submoduleapp = Submodules_modules_apps::find($relid);
    if (!$submoduleapp) return response()->json('SubModulo no instalado o no encontrado',404);
    $submoduleapp->executateMigration();
    return response()->json('Migraciones completadas!',200);
  }

  public function updateModule($relid) {
    $submoduleapp = Submodules_modules_apps::find($relid);
    if (!$submoduleapp) return response()->json('SubModulo no instalado o no encontrado',404);
    $submoduleapp->updateSubModule();
    return response()->json('Actualizado!',200);
  }

  public function uninstallSubModule($relid){
    $submodule_module_app = Submodules_modules_apps::find($relid);
    if (!$submodule_module_app) return response()->json('El submodulo ya fue desinstalado o nunca existio',404);

    $uninstall = $submodule_module_app->uninstall();
    if (!$uninstall[0])
      return response()->json($uninstall[1],$uninstall[2]);

    return response()->json('SubModulo desinstalado correctamente',200);
  }

}
