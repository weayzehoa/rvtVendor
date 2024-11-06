<?php

return [

    'default' => env('TNTSEARCH_TOKENIZER', 'phpanalysis'),

    'storage' => storage_path('tntsearch_indices'),

    'stemmer' => TeamTNT\TNTSearch\Stemmer\NoStemmer::class,

    'tokenizers' => [
        'phpanalysis' => [
            'driver' => Vanry\Scout\Tokenizers\PhpAnalysisTokenizer::class,
            'to_lower' => true, //把英文單詞全部轉小寫
            'unit_word' => true, //嘗試合併單字(即是新詞識別)
            'differ_max' => true, //使用最大切分模式對二元詞進行消岐
            'differ_freq' => true, //使用熱門詞優先模式進行消岐
            'result_type' => 1, //生成的分詞結果資料型別(1 為全部， 2為 詞典詞彙及單箇中日韓簡繁字元及英文， 3 為詞典詞彙及英文)
        ],

        'jieba' => [
            'driver' => Vanry\Scout\Tokenizers\JiebaTokenizer::class,
            'dict' => 'small',
            //'user_dict' => resource_path('dicts/mydict.txt'),
        ],

        'scws' => [
            'driver' => Vanry\Scout\Tokenizers\ScwsTokenizer::class,
            'multi' => 1,
            'ignore' => true,
            'duality' => false,
            'charset' => 'utf-8',
            'dict' => '/usr/local/scws/etc/dict.utf8.xdb',
            'rule' => '/usr/local/scws/etc/rules.utf8.ini',
        ],
    ],

    'stopwords' => [
        //
    ],

];
