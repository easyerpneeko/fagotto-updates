<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Relations\Setting_submodules_modules_apps;

class SubSettingsController extends Controller
{
    public function putSettingOfSubModule(Request $request,$id){

      $setting = Setting_submodules_modules_apps::find($id);

      if (!$setting) return response()->json('Relacion setting-submodule-module-app no encontrada.',404);
      if (!$request->has('value')) return response()->json('Debe enviar un valor.',400);

      $setting = $setting->setValue($request->input('value'));//<- esta funcionsita que cree no hace la gran vaina, velo tu mismo
      if (!$setting) return response()->json('Error indefinido al modificar setting.',500);

      return response()->json('Setting activado/desactivado correctamente.', 200);

    }
}
