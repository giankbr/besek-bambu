<?php

/**
 * Midtrans Snap channel keys → storefront logo chips.
 * Sync selections in Admin → Settings → Payment with Midtrans Dashboard.
 *
 * @see https://docs.midtrans.com/docs/snap-advanced-feature
 */
return [

    'default_display' => [
        'qris',
        'bni_va',
        'bri_va',
        'gopay',
        'echannel',
        'permata_va',
    ],

    'channels' => [
        'credit_card' => [
            'label' => 'Kartu kredit/debit',
            'logos' => [
                ['file' => 'visa.png', 'alt' => 'Visa'],
                ['file' => 'mastercard.png', 'alt' => 'Mastercard'],
                ['file' => 'jcb.png', 'alt' => 'JCB'],
                ['file' => 'amex.png', 'alt' => 'American Express'],
            ],
        ],
        'bca_va' => [
            'label' => 'BCA Virtual Account',
            'logos' => [
                ['file' => 'bca.png', 'alt' => 'BCA'],
            ],
        ],
        'bni_va' => [
            'label' => 'BNI Virtual Account',
            'logos' => [
                ['file' => 'bni.png', 'alt' => 'BNI'],
            ],
        ],
        'bri_va' => [
            'label' => 'BRI Virtual Account',
            'logos' => [
                ['file' => 'bri.png', 'alt' => 'BRI'],
            ],
        ],
        'permata_va' => [
            'label' => 'Permata Virtual Account',
            'logos' => [
                ['file' => 'permata.png', 'alt' => 'PermataBank'],
            ],
        ],
        'cimb_va' => [
            'label' => 'CIMB Niaga Virtual Account',
            'logos' => [
                ['file' => 'cimb.png', 'alt' => 'CIMB Niaga'],
            ],
        ],
        'echannel' => [
            'label' => 'Mandiri Bill Payment',
            'logos' => [
                ['file' => 'mandiri.png', 'alt' => 'Bank Mandiri'],
            ],
        ],
        'gopay' => [
            'label' => 'GoPay',
            'logos' => [
                ['file' => 'gopay.png', 'alt' => 'GoPay'],
            ],
        ],
        'shopeepay' => [
            'label' => 'ShopeePay',
            'logos' => [
                ['file' => 'shopee.png', 'alt' => 'ShopeePay'],
            ],
        ],
        'qris' => [
            'label' => 'QRIS',
            'logos' => [
                ['file' => 'qris.png', 'alt' => 'QRIS'],
            ],
        ],
        'dana' => [
            'label' => 'DANA',
            'logos' => [
                ['file' => 'dana.png', 'alt' => 'DANA'],
            ],
        ],
    ],

];
