<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\GateSubmenu as SubmenuDB;

class GateMainmenu extends Model
{
    use HasFactory;

    protected $connection = 'erpGate';
    protected $table = 'mainmenus';

    //關聯submenu
    public function submenu(){
        return $this->hasMany(SubmenuDB::class,'mainmenu_id','id')->where('is_on',1);
    }
}
