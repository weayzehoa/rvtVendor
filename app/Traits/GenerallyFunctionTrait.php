<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait GenerallyFunctionTrait
{
    protected function getRealIp(){
        $ip = false;
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ips = explode (', ',$_SERVER['HTTP_X_FORWARDED_FOR']);
            if($ip){
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for($i=0; $i < count($ips); $i++){
                if(!preg_match('/^(10|172.16|192.168)./i',$ips[$i])){
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /*
        整理Servce_fee資料
        1. 檢驗是否存在
        2. 檢驗是否為陣列
        3. 轉換percent空值為0
    */
    protected function serviceFee($input = ''){
        if($input == ''){
            $serviceFees = json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]');
        }elseif(is_array($input)){
            for($i=0;$i<count($input['name']);$i++){
                $serviceFees[$i]['name'] = $input['name'][$i];
                $serviceFees[$i]['percent'] = $input['percent'][$i];
            }
            $serviceFees = json_encode($serviceFees);
        }else{
            $serviceFees = json_decode(str_replace('"percent":}','"percent":0}',$input));
        }
        return $serviceFees;
    }

    function convertAndValidateDate($date, $format = null)
    {
        // 如果格式參數為 null，則預設輸出格式為 "Y-m-d"
        $outputFormat = $format ? $format : "Y-m-d";

        // 驗證輸入的日期格式是否為 8 位數字
        if (!preg_match('/^\d{8}$/', $date)) {
          // 如果不是數字日期格式，則認為是日期格式
          $timestamp = strtotime($date);

          // 驗證轉換後的時間戳是否有效
          if (!$timestamp) {
            return false;
          }
        } else {
          // 將數字日期轉換為 "YYYY-MM-DD" 格式
          $formattedDate = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);

          // 驗證輸入的日期格式是否為 YYYY-MM-DD
          if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $formattedDate)) {
            return false;
          }

          // 轉換日期為時間戳
          $timestamp = strtotime($formattedDate);

          // 驗證轉換後的時間戳是否有效
          if (!$timestamp) {
            return false;
          }

          // 驗證轉換後的日期是否和輸入的日期一致，避免 2020-02-30 這種非法日期被轉換為 2020-03-01
          if (date('Y-m-d', $timestamp) !== $formattedDate) {
            return false;
          }
        }

        // 轉換日期為指定格式
        return date($outputFormat, $timestamp);
    }
}
