<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Response as HttpResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Request;
use App\User;
use App\Configuracion;
use App\UsuarioApartamento;
use JWTAuth;
use Response;
use DB;


class UsuarioController extends Controller {


    public function __construct(Request $request){
        $this->request = $request;
    }
    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function index()
	{
		//
	}

    //Registra token y device en la DB para push notifications
    public function registerDevice(){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $config = Configuracion::where('usuario_id','=',$user->id)->get();
        foreach($config as $c){
            $id = $c->id;
        }
        $inputs = Input::only('OSDevice', 'Token');

        $configuracion = Configuracion::find($id);
        $configuracion->notificaciones = 1;
        $configuracion->register_device = "active";
        $configuracion->device_type = $inputs["OSDevice"];
        $configuracion->token = $inputs["Token"];
        $configuracion->save();

        return "active";
    }

    //Cierra session del aplicativo
    public function logout(){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $config = Configuracion::where('usuario_id','=',$user->id)->get();
        foreach($config as $c){
            $id = $c->id;
        }
        $configuracion = Configuracion::find($id);
        $configuracion->notificaciones = 0;
        $configuracion->register_device = "";
        $configuracion->device_type = "";
        $configuracion->token = "";
        $configuracion->save();

        return "success";
    }
    //Obtiene ultimo token realizado
    public function getToken(){
        $token = JWTAuth::getToken();
        return $token;
    }

    //Obtiene lista de mensajes
    public function getDataMessage($token){
        $id =  explode('-',$token);
        $query = DB::table('mensajes as mensaje')
            ->join('mensaje_usuario as m_u', 'mensaje.id', '=', 'm_u.mensaje_id')
            ->select('mensaje.id','mensaje.tipo','mensaje.asunto','mensaje.fecha','mensaje.importancia','mensaje.Adjunto','m_u.leido')
            ->where('m_u.to_id', '=', $id[1])
            ->orderBy('mensaje.created_at', 'desc')
            ->take(10)
            ->get();

        return Response::json(array('msg'=>$query));

    }
    //Obtiene count de notificaciones
    public function getCountData($token){

        $id =  explode('-',$token);
        $sin_leer = DB::table('mensajes as mensaje')
            ->join('mensaje_usuario as m_u', 'mensaje.id', '=', 'm_u.mensaje_id')
            ->select('mensaje.id','mensaje.tipo','mensaje.asunto','mensaje.fecha','mensaje.importancia','mensaje.Adjunto','m_u.leido')
            ->where('m_u.to_id', '=', $id[1])
            ->where('m_u.leido', '=', '0')
            ->count();

        return Response::json(array('s_l'=>$sin_leer));

    }
    //Obtiene nuevas notificaciones
    public function setToken($oldToken){
        $newToken = JWTAuth::refresh($oldToken);
        return $newToken;
    }
    //Realiza registro de un nuevo usuario
    public function register(Request $request){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $inputs = Input::only('nombres', 'apellidos', 'genero', 'tipo', 'fecha_nacimiento', 'email', 'telefono', 'celular', 'username');

        $usuario = User::find($user->id);
        $usuario->nombres = $inputs["nombres"];
        $usuario->apellidos = $inputs["apellidos"];
        $usuario->genero = $inputs["genero"];
        $usuario->fecha_nacimiento = $inputs["fecha_nacimiento"];
        $usuario->email = $inputs["email"];
        $usuario->telefono = $inputs["telefono"];
        $usuario->celular = $inputs["celular"];
        /* $usuario->username = $inputs["username"]; */
        $usuario->active = 1;
        $usuario->save();


        $u_a = DB::table('usuario_apartamento as u_a')
            ->select('u_a.id')
            ->where('u_a.usuario_id', '=',$usuario->id)
            ->get();

        foreach($u_a as $b){
            $id_ua = $b->id;
        }

        if($id_ua != ""){
            $u_apartamento = UsuarioApartamento::find($id_ua);
            $u_apartamento->propietario = $inputs["tipo"];
            $u_apartamento->save();
        }

        $config = Configuracion::where('usuario_id','=',$user->id)->get();

        $conjunto = DB::table('usuarios as usuario')
            ->join('usuario_apartamento as u_a', 'usuario.id', '=', 'u_a.usuario_id')
            ->join('apartamentos as apartamento', 'u_a.apartamento_id', '=', 'apartamento.id')
            ->join('zonas as zona', 'apartamento.zona_id', '=', 'zona.id')
            ->join('conjuntos as conjunto', 'zona.conjunto_id', '=', 'conjunto.id')
            ->select('conjunto.id','conjunto.tipo','conjunto.nombre','conjunto.localidad','conjunto.barrio','conjunto.direccion','conjunto.img_perfil','conjunto.banner_conjunto'
                ,'conjunto.telefono','conjunto.telefono_cuadrante','conjunto.estrato','conjunto.facebook','conjunto.twitter','apartamento.apartamento','zona.tipo','zona.value','conjunto.map_latitud','conjunto.map_longitud')
            ->where('usuario.id', '=',$user->id)
            ->get();

        foreach($conjunto as $d){
            $c_id = $d->id;
        }

        $administrador = DB::table('usuarios as usuario')
            ->join('administradores as admin', 'usuario.id', '=', 'admin.usuario_id')
            ->join('administrador_conjunto as admin_c', 'admin.id', '=', 'admin_c.administrador_id')
            ->select('*')
            ->where('admin_c.conjunto_id', '=',$c_id)
            ->get();

        return Response::json(array('usuario'=>$usuario,'config'=>$config,'conjunto'=>$conjunto,'administrador'=>$administrador));

    }
    //Se obtiene las noticias actuales del usuario
    public function obtenerNoticiasUsuario(){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $conjuntos = DB::table('usuario_apartamento as u_apartamento')
            ->join('apartamentos as apto', 'u_apartamento.apartamento_id', '=', 'apto.id')
            ->join('zonas as zona', 'apto.zona_id', '=', 'zona.id')
            ->select('zona.conjunto_id as id')
            ->where('u_apartamento.usuario_id', '=', $user->id)
            ->get();


        foreach($conjuntos as $conjunto){
            $id = $conjunto->id;
        }


        $anuncios = DB::table('noticias')
            ->select('*')
            ->where('conjunto_id', '=', $id)
            ->orderBy('id', 'DESC')
            ->get();


        return $anuncios;
    }
    //Se obtiene una notifica especifica mediante ID
    public function obtenerNoticiaId($id){
        $anuncio = DB::table('noticias')
            ->select('*')
            ->where('id', '=', $id)
            ->orderBy('id', 'DESC')
            ->get();

        return $anuncio;
    }
    //Lista informaciondel conjunto
    public function conjunto(){

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        $data = DB::table('usuarios as usuario')
            ->join('usuario_apartamento as u_a', 'usuario.id', '=', 'u_a.usuario_id')
            ->join('apartamentos as apartamento', 'u_a.apartamento_id', '=', 'apartamento.id')
            ->join('zonas as zona', 'apartamento.zona_id', '=', 'zona.id')
            ->join('conjuntos as conjunto', 'zona.conjunto_id', '=', 'conjunto.id')
            ->select('conjunto.id','conjunto.tipo','conjunto.nombre','conjunto.localidad','conjunto.barrio','conjunto.direccion','conjunto.img_perfil','conjunto.banner_conjunto'
                ,'conjunto.telefono','conjunto.telefono_cuadrante','conjunto.estrato','conjunto.facebook','conjunto.twitter','apartamento.apartamento','zona.tipo','zona.value')
            ->where('usuario.id', '=',$user->id)
            ->get();

        foreach($data as $d){
            $c_id = $d->id;
        }

        $administrador = DB::table('usuarios as usuario')
            ->join('administradores as admin', 'usuario.id', '=', 'admin.usuario_id')
            ->join('administrador_conjunto as admin_c', 'admin.id', '=', 'admin_c.administrador_id')
            ->select('*')
            ->where('admin_c.conjunto_id', '=',$c_id)
            ->get();

        return array('conjunto'=>$data, 'administrador'=> $administrador);
    }





}
