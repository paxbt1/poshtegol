<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'fifa_code',
        'external_team_id',
        'tla',
        'name_fa',
        'name_en',
        'flag_emoji',
        'crest_url',
        'group_name',
        'area_name',
        'area_code',
        'last_synced_at',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }

    public function crestLocalPath(): ?string
    {
        $path = data_get($this->raw_payload, '_local_crest_path');

        return is_string($path) && $path !== '' ? $path : null;
    }

    public function crestDisplayUrl(): ?string
    {
        $local = $this->crestLocalPath();

        if ($local && file_exists(public_path($local))) {
            return asset($local);
        }

        return $this->crest_url ?: null;
    }
}
