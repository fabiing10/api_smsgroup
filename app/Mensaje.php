<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Mensaje extends Model {

    protected $table = 'mensajes';
    protected $fillable = array();

    // Many to many relationship Mensaje-Usuario Table
    public function usuarios(){
        return $this->belongsToMany('App\User','mensaje_usuario','mensaje_id','usuario_id');
    }

}
