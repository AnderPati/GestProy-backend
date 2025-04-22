<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
