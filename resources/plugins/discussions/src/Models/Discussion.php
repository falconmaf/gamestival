<?php

namespace Wave\Plugins\Discussions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use SoftDeletes;

    protected $table = 'discussions';
    public $timestamps = true;
    protected $fillable = ['title', 'content', 'category_slug', 'user_id', 'slug', 'color'];
    protected $dates = ['deleted_at', 'last_reply_at'];

    public function user()
    {
        return $this->belongsTo(config('discussions.user.namespace'));
    }

    public function category(): ?object
    {
        if(!isset($this->category_slug)) return null;
        $category_config = config('discussions.categories');
        if(isset($category_config[$this->category_slug])){
            return (object)$category_config[$this->category_slug];
        }
        
        return null;
        //return $this->
    }

    public function posts()
    {
        return $this->hasMany(Models::className(Post::class), 'discussion_id');
    }

    public function post()
    {
        return $this->hasMany(Models::className(Post::class), 'discussion_id')->orderBy('created_at', 'ASC');
    }

    public function postsCount()
    {
        return $this->posts()
            ->selectRaw('discussion_id, count(*)-1 as total')
            ->groupBy('discussion_id');
    }

    public function users()
    {
        $userModel = config('discussions.user.namespace');
        
        return $userModel::whereIn('id', function ($query) {
            $query->select('user_id')
                  ->from('discussion_posts')
                  ->where('discussion_id', $this->id)
                  ->union(
                      $query->newQuery()
                            ->select('user_id')
                            ->from('discussions')
                            ->where('id', $this->id)
                  );
        })->distinct();
    }

    public function subscribers()
    {
        return $this->belongsToMany(config('discussions.user.namespace'), 'discussions_users', 'discussion_id', 'user_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Models::className(Avatar::class), 'avatar_id');
    }
}
