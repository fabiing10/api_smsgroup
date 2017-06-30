<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Post extends Model {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'content', 'user_id'];

    /**
     * Passwords must always be hashed
     *
     * @param $password
     */
    public function user(){
        return $this->belongsTo('App\User');
    }

}
