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
                'title_en' => 'Bank Transfer - AlAhli Bank',
                'title_ar' => 'تحويل بنكي - البنك الأهلي',
                'title_fr' => 'Virement Bancaire - AlAhli',
                'title_de' => 'Banküberweisung - AlAhli',

                'slug_en' => 'bank-transfer-alahli',
                'slug_ar' => 'تحويل-بنكي-الاهلي',
                'slug_fr' => 'virement-bancaire-alahli',
                'slug_de' => 'bank-uberweisung-alahli',

                'code' => 'bank_transfer_alahli',
                'status' => 1,

                'banner_en' => 'payment-methods/alahli.jpeg',
                'banner_ar' => 'payment-methods/alahli.jpeg',
                'banner_fr' => 'payment-methods/alahli.jpeg',
                'banner_de' => 'payment-methods/alahli.jpeg',

                'config' => [
                    'bank_name' => 'البنك الاهلي التجاري',
                    'account_name' => 'مؤسسة رحلتنا لتنظيم الرحلات',
                    'account_number' => '15463733000108',
                    'iban' => 'SA8910000015463733000108',
                    'bank_address' => 'المملكة العربية السعودية',
                    'instructions' => 'الرجاء إرفاق إيصال التحويل بعد إتمام العملية.',
                ],
            ],
            [
                'title_en' => 'Bank Transfer - Al Rajhi Bank',
                'title_ar' => 'تحويل بنكي - بنك الراجحي',
                'title_fr' => 'Virement Bancaire - Al Rajhi',
                'title_de' => 'Banküberweisung - Al Rajhi',

                'slug_en' => 'bank-transfer-alrajhi',
                'slug_ar' => 'تحويل-بنكي-الراجحي',
                'slug_fr' => 'virement-bancaire-alrajhi',
                'slug_de' => 'bank-uberweisung-alrajhi',

                'code' => 'bank_transfer_alrajhi',
                'status' => 1,

                'banner_en' => 'payment-methods/alrajhi.jpg',
                'banner_ar' => 'payment-methods/alrajhi.jpg',
                'banner_fr' => 'payment-methods/alrajhi.jpg',
                'banner_de' => 'payment-methods/alrajhi.jpg',

                'config' => [
                    'bank_name' => 'بنك الراجحى',
                    'account_name' => 'مؤسسة رحلتنا لتنظيم الرحلات',
                    'account_number' => '551608010056607',
                    'iban' => 'SA8680000551608010056607',
                    'bank_address' => 'المملكة العربية السعودية',
                    'instructions' => 'الرجاء إرفاق إيصال التحويل بعد إتمام العملية.',
                ],
            ],
            [
                'title_en' => 'Tamara',
                'title_ar' => 'تمارا',
                'title_fr' => 'Tamara',
                'title_de' => 'Tamara',

                'slug_en' => 'tamara',
                'slug_ar' => 'تمارا',
                'slug_fr' => 'tamara',
                'slug_de' => 'tamara',

                'code' => 'tamara',
                'status' => 1,

                'banner_en' => 'payment-methods/tamara.jpeg',
                'banner_ar' => 'payment-methods/tamara.jpeg',
                'banner_fr' => 'payment-methods/tamara.jpeg',
                'banner_de' => 'payment-methods/tamara.jpeg',

                'config' => [
                    'live' => [
                        'base_url' => 'https://api-tamara.co',
                        'secret_key' => 'live_secret_key',
                        'public_key' => 'live_public_key',
                    ],
                    'test' => [
                        'base_url' => 'https://api-sandbox.tamara.co',
                        'secret_key' => 'sk_test_0190e4e4-432b-0f64-4717-30d5d415747f',
                        'public_key' => 'pk_test_0190e4e4-432b-0f64-4717-30d5109dbe3d',
                    ],
                    'mode' => 'test',
                ],
            ],
        ];

        try {
            PaymentMethod::query()->withTrashed()->forceDelete();
        } catch (\Exception $e) {
            PaymentMethod::query()->delete();
        }

        foreach ($methods as $method) {
            PaymentMethod::query()->create($method);
        }

        $this->command->info('Payment methods seeded successfully with FR and DE!');
    }
}
