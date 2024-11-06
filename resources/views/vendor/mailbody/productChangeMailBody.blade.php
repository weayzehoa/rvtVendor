管理者您好,<br>
<br>
這封信是由{{ $details['vendorName'] }}商家管理後台發送的。<br>
您收到這封郵件，是由於{{ $details['vendorName'] }}管理者變更商品狀態為 {{ $details['status'] }}。<br><br>
商品名稱：{{ $details['productName'] }}<br><br>
@if($details['status'] != '恢復銷售')
變更理由：{{ $details['reason'] }}<br>
@endif
<br>
<br>
此信件為系統自動發出，請勿回覆此信件。<br>
<br>
<br>
--<br>
謝謝!!<br>
Best Regards,<br>
<br>
****************************************<br>
直流電通股份有限公司<br>
Http : www.icarry.me<br>
TEL：886-2-2508-2891<br>
FAX：886-2-2508-2902<br>
地址:台北市中山區南京東路三段103號11樓之一
