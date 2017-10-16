<?php
return [
    'emails'=>[
      'contact_us'=>env('CONTACT_EMAIL1','info@trendle.io')
    ],
    'tables' => [
        'trialperiod' => 'trial_periods',
        'seller' => 'sellers',
        'regtoken' => 'registration_tokens',
        'user' => 'users',
        'mkp' => 'marketplace_assigns',
        'campaign' => 'campaigns',
        'campaign_temp' => 'campaign_templates',
        'campaign_temp_att' => 'campaign_template_attachments',
        'campaign_eml' => 'campaign_emails',
        'campaign_eml_att' => 'campaign_email_attachments',
    ],
    'amz_keys' => [
      'eu' => [
        'access_key'=>'AKIAI6K3WYU4WYIDVUTA',
        'secret_key'=>'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',
        'marketplaces' => [
            'uk' => [
              'id' => 'A1F83G8C2ARO7P',
              'name' => 'United Kingdom',
              'associate_tag' => 'lspr-21'
            ],
            'de' => [
              'id' => 'A1PA6795UKMFR9',
              'name' => 'Germany',
              'associate_tag' => 'locksour00-21'
            ],
            'es' => [
              'id' => 'A1RKKUPIHCS9HS',
              'name' => 'Spain',
              'associate_tag' => 'lspr03-21'
            ],
            'fr' => [
              'id' => 'A13V1IB3VIYZZH',
              'name' => 'France',
              'associate_tag' => 'locksourprom-21'
            ],
            'it' => [
              'id' => 'APJ6JRA9NG5V4',
              'name' => 'Italy',
              'associate_tag' => 'locksour09-21'
            ]
        ],
      ],
      'na' => [
        'access_key'=>'AKIAIT5UE47EGT2SWSLQ',
        'secret_key'=>'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',
        'marketplaces' => [
            'us' => [
              'id' => 'ATVPDKIKX0DER',
              'name' => 'USA',
              'associate_tag' => 'locksourprom-20'
            ],
            'ca' => [
              'id' => 'A2EUQ1WTGCTBG2',
              'name' => 'Canada',
              'associate_tag' => 'locksourprom-20'
            ]
        ]
      ]
    ],
    'SPARK_POST_CONSTANTS' => [
      'driver' => env('SPARKPOST_MAIL_DRIVER'),
      'host' => env('SPARKPOST_MAIL_HOST'),
      'port' => env('SPARKPOST_MAIL_PORT'),
      'encryption' => env('SPARKPOST_MAIL_ENCRYPTION'),
      'username' => env('SPARKPOST_MAIL_USERNAME'),
      'password' => env('SPARKPOST_MAIL_PASSWORD')
    ],
    'country_list' => [
      'uk'=> 'United Kingdom',
      'de'=> 'Germany',
      'es'=> 'Spain',
      'fr'=> 'France',
      'it'=> 'Italy',
      'ca'=> 'Canada',
      'us'=> 'USA'
    ],
    'currency_list' => [
      'uk'=> 'GBP',
      'de'=> 'EUR',
      'es'=> 'EUR',
      'fr'=> 'EUR',
      'it'=> 'EUR',
      'ca'=> 'CAD',
      'us'=> 'USD'
    ],
    'country_mkp' => [
      'uk'=> 'eu',
      'de'=> 'eu',
      'es'=> 'eu',
      'fr'=> 'eu',
      'it'=> 'eu',
      'ca'=> 'na',
      'us'=> 'na'
    ]
];
