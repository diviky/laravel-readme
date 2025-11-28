<?php

declare(strict_types=1);

namespace Diviky\Readme\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Document extends Model
{
    use Searchable;

    protected $table = 'readme_documents';

    protected $fillable = [
        'version',
        'page',
        'title',
        'content',
        'html_content',
        'file_path',
        'file_hash',
        'indexed_at',
    ];

    protected $casts = [
        'indexed_at' => 'datetime',
    ];

    public function searchableAs(): string
    {
        return 'readme_documents';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'page' => $this->page,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return !empty($this->content);
    }
}

