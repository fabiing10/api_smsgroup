<?php

use App\User;
use App\Configuracion;
use Illuminate\Http\Response as HttpResponse;

/**
 * Displays Angular SPA application
 */
Route::get('/', function () {
    return view('spa');
});

/**
 * Registers a new user and returns a auth token
 */
Route::post('/signup', function () {
    $credentials = Input::only('username', 'password');

    try {
        $user = User::create($credentials);
    } catch (Exception $e) {
        return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
    }

    $token = JWTAuth::fromUser($user);
    return Response::json(compact('token'));

});

/**
 * Signs in a user using JWT
 */
Route::post('/signin', function () {
    $credentials = Input::only('username', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return Response::json(false, HttpResponse::HTTP_UNAUTHORIZED);
    }

    //return Response::json(compact('token'));
    $user = JWTAuth::toUser($token);
    $config = Configuracion::where('usuario_id','=',$user->id)->get();

    $conjunto = DB::table('usuarios as usuario')
        ->join('usuario_apartamento as u_a', 'usuario.id', '=', 'u_a.usuario_id')
        ->join('apartamentos as apartamento', 'u_a.apartamento_id', '=', 'apartamento.id')
        ->join('zonas as zona', 'apartamento.zona_id', '=', 'zona.id')
        ->join('conjuntos as conjunto', 'zona.conjunto_id', '=', 'conjunto.id')
        ->select('conjunto.id','conjunto.tipo','conjunto.nombre','conjunto.localidad','conjunto.barrio','conjunto.direccion','conjunto.img_perfil','conjunto.banner_conjunto'
            ,'conjunto.telefono','conjunto.telefono_cuadrante','conjunto.horario_administracion','conjunto.estrato','conjunto.facebook','conjunto.twitter','apartamento.apartamento','zona.tipo','zona.value','conjunto.map_latitud','conjunto.map_longitud')
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


    return Response::json(array('token'=>$token,'usuario'=>$user,'config'=>$config,'conjunto'=>$conjunto,'administrador'=>$administrador));

});


Route::get('/mensajes', 'MensajeController@index');
Route::get('/mensajes/{id}', 'MensajeController@mensajeId');
Route::get('/mensajes/last/{id}', 'MensajeController@ultimosMensajes');
Route::get('/leido/{id}', 'MensajeController@leidoMensajeId');
Route::get('/conjunto','UsuarioController@conjunto');
Route::get('/anuncios','UsuarioController@obtenerNoticiasUsuario');
Route::get('/anuncios/{id}', 'UsuarioController@obtenerNoticiaId');
Route::post('/register', 'UsuarioController@register');
Route::post('/registerDevice', 'UsuarioController@registerDevice');
Route::get('/getToken','UsuarioController@getToken');
Route::get('/logout','UsuarioController@logout');
Route::get('/adjunto/{id}','MensajeController@adjuntoMensajeId');



Route::get('/getDataMessage/{data}','UsuarioController@getDataMessage');
Route::get('/getCountData/{data}','UsuarioController@getCountData');



/**
 * Fetches a restricted resource from the same domain used for user authentication
 */
Route::get('/restricted', [
    'before' => 'jwt-auth',
    function () {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);

        return Response::json([
            'data' => [
                'email' => $user->email,
                'registered_at' => $user->created_at->toDateTimeString()
            ]
        ]);
    }
]);

/*Route::get('/posts-all/', 'PostController@index');
Route::get('/posts/', [
    'before' => 'jwt-auth',
    'uses' => 'PostController@index_user'
]);*/
