<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class CheckDatabaseConnection
{
    public function handle($request, Closure $next)
    {
        // Test database connection
        try {
            DB::connection('mysql')->getPdo();
            DB::connection('icarry')->getPdo();
            DB::connection('icarryLang')->getPdo();
            DB::connection('iCarrySMERP')->getPdo();
        } catch (\Exception $e) {
            dd("資料庫連線失敗，請聯繫 iCarry LINE 群組 或 請直接來電: 0906-053588");
        }
        return $next($request);
    }
}
