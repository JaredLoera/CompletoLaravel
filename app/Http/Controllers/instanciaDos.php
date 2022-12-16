<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\sendmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\informacion;
use App\Models\persona;
use App\Models\personasinformacion;


class instanciaDos extends Controller
{
    public function AddUser(Request $request){
   
        $validacion = Validator::make($request->all(),[
            'name' => 'required | string | max:50',
            'email'=>'required',
            'password'=>'required',
            'numero_telefono'=>'required',
            'rol'=>'required'
        ]);
        if ($validacion->fails()) {
            Log::error("fallo al validar campos");
            return response()->json([
                "FALTAN VALIDAR CAMPOS"=>[$validacion->errors()]
            ],400);
        }
        $user = new User();
        $user->name     =$request->name;
        $user->email    =$request->email;
        $user->role_id  = $request->rol;
        $user->password = bcrypt($request->password);
        $user->numero_telefono=$request->numero_telefono;
        $user -> save();
 
        $url = URL::temporarySignedRoute('verificar',now()->addMinutes(30),['user'=>$user->id]);
        Mail::to($request->email)->send(new sendmail($user,$url));
        return response()->json([
            "data"=>[$user]
        ],200);
    }
    public function addpersona(Request $request){
        $validator = Validator::make($request->all(), [
            'nombre'         =>  'required|string',
            'ap_paterno'        =>  'required|string',
        ]);
        if ($validator->fails()) {
            Log::error("fallo al validar campos",[
                $validator->errors()
            ]);
            return response()->json([
                "Campos faltantes" => $validator->errors()
            ], 400);
        }
        $persona = new persona();
        $persona->nombre=$request->nombre;
        $persona->ap_paterno=$request->ap_paterno;
        $persona->user_id=$request->user()->id;
        $persona->save();
}
public function add_informacion_persona(Request $request,$id_persona){
$validator = Validator::make($request->all(), [
        'direccion'         =>  'required|string|max:80',
        'numero_casa'        =>  'required|string|max:10',
    'numero_casa_telefono'        =>  'required|string|max:16',
    ]);
    if ($validator->fails()) {
        Log::error("fallo al validar campos",[
            $validator->errors()
        ]);
        return response()->json([
            "Campos faltantes" => $validator->errors()
        ], 400);
    }
    $informacion = new informacion();
    $informacion->direccion=$request->direccion;
    $informacion->numero_casa=$request->numero_casa;
    $informacion->numero_casa_telefono=$request->numero_casa_telefono;
    if ($informacion->save()) {
     $personainfo = new personasinformacion();
     $personainfo->persona_id= $id_persona;
     $personainfo->informacion_id=$informacion->id;
     if ($personainfo->save()) {
        return response()->json([
            "SE A AÑADIDO LA INFORMACION" => $informacion
        ], 201);
     }  
    }
     return response()->json([
            "ALGO FALLO"
        ], 400);
    }
}
