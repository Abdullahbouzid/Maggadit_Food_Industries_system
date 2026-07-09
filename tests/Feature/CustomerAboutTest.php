<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Organization;
use App\Models\OrganizationAbout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerAboutTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_shows_the_active_organization_about(): void
    {
        Schema::create('customers', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('fcm_token')->nullable();
            $table->unsignedBigInteger('active_organization_id')->nullable();
            $table->timestamps();
        });

        Schema::create('organizations', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('organization_type')->nullable();
            $table->string('gln')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_organization', function ($table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('organization_abouts', function ($table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->json('values')->nullable();
            $table->string('image')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('statistics')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $customer = Customer::create([
            'name' => 'Test Customer',
            'username' => 'test-customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);

        $organization = Organization::create([
            'name' => 'Test Organization',
            'code' => 'ORG1',
        ]);

        $customer->organizations()->attach($organization->id, ['is_active' => true]);
        $customer->update(['active_organization_id' => $organization->id]);

        OrganizationAbout::create([
            'organization_id' => $organization->id,
            'title' => 'نبذة تجريبية',
            'description' => 'وصف تجريبي',
            'vision' => 'رؤية تجريبية',
            'mission' => 'رسالة تجريبية',
            'values' => ['الابتكار'],
            'statistics' => ['العملاء' => 10],
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer, 'customer')->get('/about-us');

        $response->assertOk();
        $response->assertSee('نبذة تجريبية');
    }
}
