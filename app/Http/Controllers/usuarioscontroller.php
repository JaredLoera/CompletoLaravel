<?php

namespace App\Http\Controllers;

use App\Jobs\procesarmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\codigo;
use App\Mail\sendpasschange;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class usuarioscontroller extends Controller
{
    public function verificar(Request $request){
        $validacion = Validator::make($request->all(),[
            'numero_telefono'=>'required'
        ]);
        if ($validacion->fails()) {
            Log::error("fallo al validar campos");
            return response()->json([
                "FALTAN VALIDAR CAMPOS"=>[$validacion->errors()]
            ],400);
        }

        $user = User::find($request->user);
        $numRand=rand(1000,9999);
        $response = Http::withBasicAuth('AC06fa385aeccb624eab49ef77e40471e7', '424dcb63c5df938086e4ad405b23885d')->asForm()->post('https://api.twilio.com/2010-04-01/Accounts/AC06fa385aeccb624eab49ef77e40471e7/Messages.json',[
            'To'=>'whatsapp:+521' .$request-> numero_telefono,
            'From'=>'whatsapp:+14155238886',
            'Body'=>$numRand
        ]);
        if ($response->successful()) {
            $codigo = new codigo();
            $codigo ->user_id=$user->id;
            $codigo->codigo= $numRand;
            $codigo->save();      
            return response()->json([
                "Se a enviado el codigo"
            ],200);    
        }
    }
    public function verificarPerfil(Request $request){
        $validator = Validator::make($request->all(), [
            'codigo' => 'required | integer | exists:codigos,codigo',
        ], [
            'codigo.required' => 'El codigo es requerido',
            'codigo.integer' => 'El codigo debe ser un número entero',
            'codigo.exist' => 'El codigo es incorrecto',
        ]);
        if($validator->fails()) {
            Log::error("fallo al validar campos");
            return response()->json(["errores" => $validator->errors()], 400);
        }
        $codigo = codigo::where('codigo', $request->codigo)->first();
        $user = User::find($codigo->user_id);
        $user->active=true;
        $user->save();
        return response()->json(["Usuario Activado"],);
    }
    public function login (Request $request){
        $validator = Validator::make($request->all(), [
            'email'         =>  'required|email',
            'password'        =>  'required|string'
        ]);
        if ($validator->fails()) {
            Log::error("fallo al validar campos");
            return response()->json([
                "Campos faltantes" => $validator->errors()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['LAS CREDENCIALES ESTAN INCORRECTAS'], 400);
        }
        $token = $user->createToken("auth_token")->plainTextToken;
        return response()->json(["Usuario Ingresado" => [
            "Usuario" => $request->email,
            "Token" => $token
        ]], 200);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(["Sesion ha cerrado"],);
    }

    public function desactivarUser(Request $request){
        $validator = Validator::make($request->all(), [
            'email'         =>  'required',
        ]);
        if ($validator->fails()) {
            Log::error("fallo al validar campos");
            return response()->json([
                "FALTA EL CORREO A DESACTIVAR" => $validator->errors()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        $user->active=false;
        $user->save();
        return response()->json(["Usuario Desactivado"],);
    }
    public function cambiarContraseña(Request $request){
        $validator = Validator::make($request->all(), [
            'email'         =>  'required|email',
            'password'        =>  'required|string',
             'new_password' =>  'required|string'
        ]);
        if ($validator->fails()) {
            Log::error("fallo al validar campos",[
                $validator->errors()
            ]);
            return response()->json([
                "Campos faltantes" => $validator->errors()
            ], 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::error("CONTRASEÑAS INCORRECTAS");
            return response()->json(['LAS CREDENCIALES ESTAN INCORRECTAS'], 400);
        }
        $user->password = bcrypt($request->new_password);
        $user->save();
        procesarmail::dispatch($user)->delay(now()->addSeconds(15))->onQueue('nombre');
        Log::channel('slack')->info('LA CONTRASEÑA A CAMBIADO',[
            "Usuario"=>$user
        ]);
        return response()->json([
            "La contraseña se a actualizado"
        ],200);
    }
}
