<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mensaje;
use App\MensajeUsuario;
use JWTAuth;
use Response;
use DB;


class MensajeController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $query = DB::table('mensajes as mensaje')
            ->join('mensaje_usuario as m_u', 'mensaje.id', '=', 'm_u.mensaje_id')
            ->select('mensaje.id','mensaje.tipo','mensaje.asunto','mensaje.fecha','mensaje.importancia','mensaje.Adjunto','m_u.leido')
            ->where('m_u.to_id', '=', $user->id)
            ->orderBy('mensaje.created_at', 'desc')
            ->take(10)
            ->get();

        return $query;

    }

    /*Funcion que lista los ultimos mensajes*/
    public function ultimosMensajes($UltimoId)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $query = DB::table('mensajes as mensaje')
            ->join('mensaje_usuario as m_u', 'mensaje.id', '=', 'm_u.mensaje_id')
            ->select('mensaje.id','mensaje.tipo','mensaje.asunto','mensaje.mensaje','mensaje.fecha','mensaje.importancia','mensaje.Adjunto','m_u.leido')
            ->where('m_u.to_id', '=', $user->id)
            ->where('mensaje.id', '<', $UltimoId)
            ->orderBy('mensaje.created_at', 'desc')
            ->take(10)
            ->get();

        return $query;

    }

    //Lista mensajes mediante ID
    public function mensajeId($id){
        $mensaje = Mensaje::find($id);
        return $mensaje;
    }
    //Lista mensaje leido ID
    public function leidoMensajeId($id){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $mensaje = DB::table('mensajes as mensaje')
            ->join('mensaje_usuario as m_u', 'mensaje.id', '=', 'm_u.mensaje_id')
            ->select('m_u.id')
            ->where('mensaje.id', '=',$id)
            ->where('m_u.to_id', '=',$user->id)
            ->get();

        foreach($mensaje as $data){
            $mu_id = $data->id;
        }

        $update = MensajeUsuario::find($mu_id);
        $update->leido = 1;
        $update->fecha_leido = date('Y-m-d');
        $update->save();

        return $update;
    }
    //Muestra url del archivo adjunto
    public function adjuntoMensajeId($mensajeId){
        $adjunto = DB::table('mensajes as mensaje')
            ->join('adjunto_mensaje as adj_men', 'mensaje.id', '=', 'adj_men.mensaje_id')
            ->join('adjuntos as adjunto', 'adjunto.id', '=', 'adj_men.adjunto_id')
            ->select('adjunto.nombre','adjunto.tipo')
            ->where('mensaje.id', '=', $mensajeId)
            ->get();
        return $adjunto;
    }



}
