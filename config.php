<?php
return [
    'mpdf' => [
        'default_config' => [
            'mode' => 'ja',
            'format' => 'A4',
            'default_font' => 'ipag',
        ],
        'fontDir' => getenv('BATCH_ROOT_DIR').'/fonts/IPAG',
        'fonts' => [
            'ipag' => [
                'R' => 'ipag.ttc',
                'TTCfontID' => ['R' => 1],
            ],
            'ipagp' => [
                'R' => 'ipag.ttc',
                'TTCfontID' => ['R' => 2],
            ],
        ],
    ],
    'aws' => [
        's3' => [
            'config' => [
                'version' => getenv('AWS_S3_VERSION') ?: 'latest',
                'region' => getenv('AWS_S3_REGION') ? : 'ap-northeast-1',
            ],
        ],
    ],
];