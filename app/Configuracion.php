<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Configuracion extends Model {

    protected $table = 'configuraciones';
    protected $fillable = array();


    public function usuario(){
        return $this->belongsTo('App\User');
    }

}
