<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()->first()->id;
        $tenant = Tenant::query()->findOrFail($tenantId);
        $tenant->makeCurrent();
        $tenantName = $tenant->name ?? 'Unknown';
        $this->command->info("Seeding Payment Methods for Tenant: {$tenant->id} ({$tenantName})");

        $methods = [
            [
                // Bank Transfer
                'title_en' => 'Bank Transfer',
                'title_ar' => 'تحويل بنكي',
                'title_fr' => 'Virement Bancaire',
                'title_de' => 'Banküberweisung',

                'slug_en' => 'bank-transfer',
                'slug_ar' => 'تحويل-بنكي',
                'slug_fr' => 'virement-bancaire',
                'slug_de' => 'bank-uberweisung',

                'code' => 'bank_transfer',
                'status' => 1,

                'banner_en' => 'payment-methods/bank.png',
                'banner_ar' => 'payment-methods/bank.png',
                'banner_fr' => 'payment-methods/bank.png',
                'banner_de' => 'payment-methods/bank.png',

                'config' => [
                    'bank_name' => 'test bank name',
                    'account_name' => 'test account name',
                    'account_number' => '12345678901234',
                    'iban' => 'EG120002000123456789012345',
                    'swift_code' => 'NBEGXCX',
                    'bank_address' => 'test bank address',
                    'instructions' => 'please upload proof !',
                ],
            ],
            [
                // Credit Card
                'title_en' => 'Credit Card',
                'title_ar' => 'بطاقة ائتمان',
                'title_fr' => 'Carte de Crédit',
                'title_de' => 'Kreditkarte',

                'slug_en' => 'credit-card',
                'slug_ar' => 'بطاقة-ائتمان',
                'slug_fr' => 'carte-de-credit',
                'slug_de' => 'kreditkarte',

                'code' => 'creditcard',
                'status' => 1,

                'banner_en' => 'payment-methods/credit-card.png',
                'banner_ar' => 'payment-methods/credit-card.png',
                'banner_fr' => 'payment-methods/credit-card.png',
                'banner_de' => 'payment-methods/credit-card.png',

                'config' => [
                    'live' => [
                        'url' => 'https://live.wemisc.net/api/payment',
                    ],
                    'test' => [
                        'url' => 'https://test.wemisc.net/api/payment',
                    ],
                    'mode' => 'test',
                ],
            ],
            // [
            //    'title_en' => 'InstaPay',
            //    'title_ar' => 'إنستا باى',
            //    'title_fr' => 'InstaPay',
            //    'title_de' => 'InstaPay',
            //    'slug_en' => 'instapay',
            //    'slug_ar' => 'إنستاباى',
            //    'slug_fr' => 'instapay',
            //    'slug_de' => 'instapay',
            //    'code' => 'instapay',
            //    'status' => 1,
            //    'banner_en' => 'payment-methods/instapay.webp',
            //    'banner_ar' => 'payment-methods/instapay.webp',
            //    'banner_fr' => 'payment-methods/instapay.webp',
            //    'banner_de' => 'payment-methods/instapay.webp',
            //    'config' => [
            //        'live' => [
            //            'url' => 'https://ipn.eg/S/mo7amedsa3ed00451/instapay/1RJvNt',
            //        ],
            //        'test' => [
            //            'url' => 'https://ipn.eg/S/mo7amedsa3ed00451/instapay/1RJvNt',
            //        ],
            //        'mode' => 'test',
            //    ],
            // ],
        ];

        $codes = collect($methods)->pluck('code')->toArray();
        PaymentMethod::query()->whereNotIn('code', $codes)->delete();

        foreach ($methods as $method) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('Payment methods seeded successfully with FR and DE!');
    }
}
