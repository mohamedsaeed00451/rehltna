<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::query()->updateOrCreate([
            'name' => 'CAPS',
        ], [
            'image' => 'caps.webp',
            'db_host' => config('database.connections.mysql.host'),
            'db_port' => config('database.connections.mysql.port'),
            'db_name' => config('database.connections.mysql.database'),
            'db_username' => config('database.connections.mysql.username'),
            'db_password' => config('database.connections.mysql.password'),
            'options' => 'home,social_integration,blogs,ai_settings,sitemaps,items,contact,payment_methods,coupons,orders,subscribes,sliders,custom_pages,testimonials,leads,settings,failed_jobs,events_galleries,members,register_users,payment_links,residency_users,images_uploader'
        ]);
    }
}
