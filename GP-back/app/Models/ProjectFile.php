<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'folder_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}
