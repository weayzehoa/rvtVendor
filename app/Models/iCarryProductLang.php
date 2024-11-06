<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProduct as ProductDB;

class iCarryProductLang extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product_langs';
    protected $fillable = [
        'product_id',
        'name',
        'lang',
        'brand',
        'serving_size',
        'title',
        'intro',
        'model_name',
        'specification',
        'unable_buy',
        'curation_text_top',
        'curation_text_bottom',
    ];

    public function product(){
        return $this->belongsTo(ProductDB::class);
    }
}
