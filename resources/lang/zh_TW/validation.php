<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => '必須接受 :attribute。',
    'active_url'      => ':attribute 不是有效的網址。',
    'after'           => ':attribute 必須要晚於 :date。',
    'after_or_equal'  => ':attribute 必須要等於 :date 或更晚。',
    'alpha'           => ':attribute 只能以字母組成。',
    'alpha_dash'      => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。',
    'alpha_num'       => ':attribute 只能以字母及數字組成。',
    'array'           => ':attribute 必須為陣列。',
    'before'          => ':attribute 必須要早於 :date。',
    'before_or_equal' => ':attribute 必須要等於 :date 或更早。',
    'between'         => [
        'numeric' => ':attribute 必須介於 :min 至 :max 之間。',
        'file'    => ':attribute 必須介於 :min 至 :max KB 之間。 ',
        'string'  => ':attribute 必須介於 :min 至 :max 個字元之間。',
        'array'   => ':attribute: 必須有 :min - :max 個元素。',
    ],
    'boolean'        => ':attribute 必須為布林值。',
    'confirmed'      => ':attribute 確認欄位的輸入不一致。',
    'date'           => ':attribute 不是有效的日期。',
    'date_equals'    => ':attribute 必須等於 :date。',
    'date_format'    => ':attribute 不符合 :format 的格式。',
    'different'      => ':attribute 與 :other 必須不同。',
    'digits'         => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須介於 :min 至 :max 位數字。',
    'dimensions'     => ':attribute 圖片尺寸不正確。',
    'distinct'       => ':attribute 已經存在。',
    'email'          => ':attribute 必須是有效的 E-mail。',
    'ends_with'      => ':attribute 結尾必須包含下列之一：:values。',
    'exists'         => ':attribute 不存在。',
    'file'           => ':attribute 必須是有效的檔案。',
    'filled'         => ':attribute 不能留空。',
    'gt'             => [
        'numeric' => ':attribute 必須大於 :value。',
        'file'    => ':attribute 必須大於 :value KB。',
        'string'  => ':attribute 必須多於 :value 個字元。',
        'array'   => ':attribute 必須多於 :value 個元素。',
    ],
    'gte' => [
        'numeric' => ':attribute 必須大於或等於 :value。',
        'file'    => ':attribute 必須大於或等於 :value KB。',
        'string'  => ':attribute 必須多於或等於 :value 個字元。',
        'array'   => ':attribute 必須多於或等於 :value 個元素。',
    ],
    'image'    => ':attribute 必須是一張圖片。',
    'in'       => '所選擇的 :attribute 選項無效。',
    'in_array' => ':attribute 沒有在 :other 中。',
    'integer'  => ':attribute 必須是一個整數。',
    'ip'       => ':attribute 必須是一個有效的 IP 位址。',
    'ipv4'     => ':attribute 必須是一個有效的 IPv4 位址。',
    'ipv6'     => ':attribute 必須是一個有效的 IPv6 位址。',
    'json'     => ':attribute 必須是正確的 JSON 字串。',
    'lt'       => [
        'numeric' => ':attribute 必須小於 :value。',
        'file'    => ':attribute 必須小於 :value KB。',
        'string'  => ':attribute 必須少於 :value 個字元。',
        'array'   => ':attribute 必須少於 :value 個元素。',
    ],
    'lte' => [
        'numeric' => ':attribute 必須小於或等於 :value。',
        'file'    => ':attribute 必須小於或等於 :value KB。',
        'string'  => ':attribute 必須少於或等於 :value 個字元。',
        'array'   => ':attribute 必須少於或等於 :value 個元素。',
    ],
    'max' => [
        'numeric' => ':attribute 不能大於 :max。',
        'file'    => ':attribute 不能大於 :max KB。',
        'string'  => ':attribute 不能多於 :max 個字元。',
        'array'   => ':attribute 最多有 :max 個元素。',
    ],
    'mimes'     => ':attribute 必須為 :values 的檔案。',
    'mimetypes' => ':attribute 必須為 :values 的檔案。',
    'min'       => [
        'numeric' => ':attribute 不能小於 :min。',
        'file'    => ':attribute 不能小於 :min KB。',
        'string'  => ':attribute 不能小於 :min 個字元。',
        'array'   => ':attribute 至少有 :min 個元素。',
    ],
    'multiple_of'          => 'The :attribute must be a multiple of :value',
    'not_in'               => '所選擇的 :attribute 選項無效。',
    'not_regex'            => ':attribute 的格式錯誤。',
    'numeric'              => ':attribute 必須為一個數字。',
    'password'             => '密碼錯誤',
    'present'              => ':attribute 必須存在。',
    'regex'                => ':attribute 的格式錯誤。',
    'required'             => ':attribute 不能留空。',
    'required_if'          => '當 :other 是 :value 時 :attribute 不能留空。',
    'required_unless'      => '當 :other 不是 :values 時 :attribute 不能留空。',
    'required_with'        => '當 :values 出現時 :attribute 不能留空。',
    'required_with_all'    => '當 :values 出現時 :attribute 不能為空。',
    'required_without'     => '當 :values 留空時 :attribute field 不能留空。',
    'required_without_all' => '當 :values 都不出現時 :attribute 不能留空。',
    'same'                 => ':attribute 與 :other 必須相同。',
    'size'                 => [
        'numeric' => ':attribute 的大小必須是 :size。',
        'file'    => ':attribute 的大小必須是 :size KB。',
        'string'  => ':attribute 必須是 :size 個字元。',
        'array'   => ':attribute 必須是 :size 個元素。',
    ],
    'starts_with' => ':attribute 開頭必須包含下列之一：:values。',
    'string'      => ':attribute 必須是一個字串。',
    'timezone'    => ':attribute 必須是一個正確的時區值。',
    'unique'      => ':attribute 已經存在。',
    'uploaded'    => ':attribute 上傳失敗。',
    'url'         => ':attribute 的格式錯誤。',
    'uuid'        => ':attribute 必須是有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'address'               => '地址',
        'age'                   => '年齡',
        'available'             => '可用的',
        'city'                  => '城市',
        'content'               => '內容',
        'country'               => '國家',
        'date'                  => '日期',
        'day'                   => '天',
        'description'           => '描述',
        'email'                 => 'E-mail',
        'excerpt'               => '摘要',
        'first_name'            => '名',
        'gender'                => '性別',
        'hour'                  => '時',
        'last_name'             => '姓',
        'minute'                => '分',
        'mobile'                => '手機號碼',
        'month'                 => '月',
        'name'                  => '名稱/名字',
        'password'              => '密碼',
        'password_confirmation' => '確認密碼',
        'phone'                 => '電話',
        'second'                => '秒',
        'sex'                   => '性別',
        'size'                  => '大小',
        'time'                  => '時間',
        'title'                 => '標題',
        'username'              => '使用者名稱',
        'year'                  => '年',
        'vat_number'            => '統一編號',
        'boss'                  => '負責人',
        'contact_person'        => '聯絡人',
        'contact'               => '聯絡人',
        'tel'                   => '電話號碼',
        'fax'                   => '傳真號碼',
        'categories'            => '類別',
        'shipping_setup'        => '免運門檻',
        'shipping_verdor_percent' => '商家運費補貼',
        'is_on'                 => '啟用/禁用',
        'summary'               => '簡述',
        'service_fee'           => '服務費',
        'shipping_self'         => '商家自行發貨',
        'factory_address'       => '工廠地址',
        'product_sold_country'  => '發貨區域/國家',
        'service_fee.percent'   => '服務費(%)',
        'type'                  => '類型',
        'refer_id'              => '推薦碼',
        'asiamiles_account'     => '亞洲萬里通帳號',
        'other_contact'         => '其他聯絡方式',
        'vendor_memo'           => '供應商說明',
        'express_way'           => '快遞公司',
        'express_no'            => '快遞單號',
        'oldpass'               => '舊密碼',
        'newpass'               => '新密碼',
        'newpass_confirmation'  => '確認密碼',
        'langs.en.name'         => '店名（英文）',
        'langs.en.summary'      => '簡介（英文）',
        'langs.en.description'  => '描述（英文）',
        'img_logo'              => '商家LOGO',
        'img_cover'             => '商家主視覺',
        'img_site'              => '商家滿版圖',
        'from_country_id'       => '發貨地區',
        'ticket_price'          => '票券面額',
        'ticket_group'          => '票券群組',
        'ticket_memo'           => '票券使用說明',
        'vendor_earliest_delivery_date' => '廠商最快出貨日',
        'airplane_days'         => '機場提貨指定天數',
        'hotel_days'            => '旅店提貨指定天數',
        'gross_weight'          => '毛重',
        'net_weight'            => '淨重',
        'unit_name_id'          => '單位名稱',
        'category_id'           => '分類',
        'digiwin_product_category' => '鼎新分類',
        'vendor_price'          => '商家進價',
        'fake_price'            => '原價',
        'TMS_price'             => '廠商外加運費',
        'price'                 => '價格',
        'intro'                 => '簡介',
        'serving_size'          => '規格',
        'eng_name'              => '英文名稱',
        'brand'                 => '廠牌/品牌',
        'specification'         => '規格說明',
        'verification_reason'   => '變更原因',
        'status'                => '狀態',
        'contact_person'        => '聯絡人',
        'sub_categories'        => '次分類',
    ],
];
