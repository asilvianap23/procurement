<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
	protected $table = 'activity_logs';
	
	protected $fillable = ['user_id', 'action', 'model', 'model_id'];
	
	public function user()
	{
		return $this->belongsTo(User::class);
	}
	
	public function model()
	{
		return $this->morphTo();  // Polymorphic relationship
	}
	
}
