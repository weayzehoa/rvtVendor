<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\GateMainmenu as MainmenuDB;

class GateSubmenu extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'submenus';

    public function mainmenu(){
        return $this->belongsTo(MainmenuDB::class,'mainmenu_id','id');
    }
}
