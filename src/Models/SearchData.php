<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Reno\Cms\Helpers\TablePrefixHelper;

class SearchData extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('search_data');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'context_id',
        'resource_id',
        'search_text',
    ];
}
