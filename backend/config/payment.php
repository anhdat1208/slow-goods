<?php

return [
    'bank_transfer' => [
        'bank_name' => env('PAYMENT_BANK_NAME', 'MB Bank'),
        'account_number' => env('PAYMENT_BANK_ACCOUNT', '0945455495'),
        'account_holder' => env('PAYMENT_BANK_HOLDER', 'VO ANH DAT'),
        'qr_image_url' => env(
            'PAYMENT_QR_URL',
            'https://vietqr.app/img?bank=MBBank&acc=0945455495&template=compact&showinfo=true&holder=VO%20ANH%20DAT'
        ),
    ],
];
