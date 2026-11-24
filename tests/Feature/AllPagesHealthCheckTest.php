<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllPagesHealthCheckTest extends TestCase
{
    use RefreshDatabase;
    protected ?User $adminUser = null;
    protected ?Tenant $tenant = null;
    protected ?Outlet $outlet = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Soto Lamongan Lestari',
            'slug' => 'sll-test',
            'owner_phone' => '08123456789',
        ]);

        $this->outlet = Outlet::first() ?? Outlet::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'SLL Cabang Utama',
            'address' => 'Jl. Lamongan No. 1',
        ]);

        $this->adminUser = User::first() ?? User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'SUPERADMIN',
            'is_active' => true,
        ]);

        $this->adminUser->update(['tenant_id' => $this->tenant->id]);
    }

    public function test_welcome_page_redirects_to_login_when_unauthenticated()
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_welcome_page_redirects_to_dashboard_when_authenticated()
    {
        $response = $this->actingAs($this->adminUser)->get('/');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_page_loads_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_dashboard_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_pos_terminal_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/pos');
        $response->assertStatus(200);
    }

    public function test_customer_display_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/pos/customer-display');
        $response->assertStatus(200);
    }

    public function test_products_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/products');
        $response->assertStatus(200);
    }

    public function test_inventory_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/inventory');
        $response->assertStatus(200);
    }

    public function test_orders_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/orders');
        $response->assertStatus(200);
    }

    public function test_cashflow_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/cashflow');
        $response->assertStatus(200);
    }

    public function test_restock_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/restock');
        $response->assertStatus(200);
    }

    public function test_can_restock_pure_physical_material_without_price_and_preserve_manual_hpp()
    {
        $material = \App\Models\RawMaterial::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Daging Ayam Broiler',
            'unit' => 'gram',
            'average_cost' => 75,
            'minimum_stock' => 500,
        ]);

        // Restock murni bahan (tanpa harga / pure fisik)
        $response = $this->actingAs($this->adminUser)->post('/backoffice/restock', [
            'raw_material_id' => $material->id,
            'purchase_quantity' => 2.5,
            'purchase_unit' => 'kg',
            'total_price' => null,
            'supplier_name' => 'Pasar Tradisional',
        ]);

        $response->assertRedirect(route('backoffice.restock'));
        $response->assertSessionHas('status');

        // Verifikasi stok bertambah 2.5 kg = 2500 gram
        $stock = \App\Models\OutletStock::where('outlet_id', $this->outlet->id)
            ->where('raw_material_id', $material->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(2500, (float) $stock->current_stock);

        // Verifikasi HPP manual TIDAK diubah/ditimpa sistem (tetap 75)
        $material->refresh();
        $this->assertEquals(75, (float) $material->average_cost);

        // Verifikasi tidak ada cashflow tercatat karena restock tanpa harga
        $cashflow = \App\Models\Cashflow::where('outlet_id', $this->outlet->id)
            ->where('category', 'RESTOCK_PURCHASE')
            ->first();
        $this->assertNull($cashflow);
    }

    public function test_can_restock_with_unit_conversion_and_cashflow_deduction()
    {
        $material = \App\Models\RawMaterial::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Daging Sapi Test',
            'unit' => 'gram',
            'average_cost' => 100,
            'minimum_stock' => 500,
        ]);

        $response = $this->actingAs($this->adminUser)->post('/backoffice/restock', [
            'raw_material_id' => $material->id,
            'purchase_quantity' => 1,
            'purchase_unit' => 'kg',
            'total_price' => 120000,
            'supplier_name' => 'Pasar Pagi',
            'source_account' => 'CASH_DRAWER',
            'transaction_date' => now()->toDateString(),
            'record_cashflow' => 1,
        ]);

        $response->assertRedirect(route('backoffice.restock'));
        $response->assertSessionHas('status');

        // Verifikasi stok bertambah 1 kg = 1000 gr
        $stock = \App\Models\OutletStock::where('outlet_id', $this->outlet->id)
            ->where('raw_material_id', $material->id)
            ->first();
        $this->assertNotNull($stock);
        $this->assertEquals(1000, (float) $stock->current_stock);

        // Verifikasi HPP manual tetap terjaga (tidak ter-overwrite)
        $material->refresh();
        $this->assertEquals(100, (float) $material->average_cost);

        // Verifikasi pengeluaran tercatat di Cashflow OUTFLOW pengurang kas
        $cashflow = \App\Models\Cashflow::where('outlet_id', $this->outlet->id)
            ->where('category', 'RESTOCK_PURCHASE')
            ->where('type', 'OUTFLOW')
            ->first();
        $this->assertNotNull($cashflow);
        $this->assertEquals(120000, (float) $cashflow->amount);
    }

    public function test_users_management_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/users');
        $response->assertStatus(200);
    }

    public function test_settings_page_loads_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/settings');
        $response->assertStatus(200);
    }

    public function test_dashboard_excel_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/dashboard/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_cashflow_excel_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/cashflow/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_inventory_excel_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/inventory/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_orders_excel_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/orders/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_products_excel_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/products/export-excel');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    }

    public function test_orders_csv_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/orders/export-csv');
        $response->assertStatus(200);
    }

    public function test_catalog_json_export_streams_successfully()
    {
        $response = $this->actingAs($this->adminUser)->get('/backoffice/catalog/export');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_can_update_outlet_and_qris_settings()
    {
        $response = $this->actingAs($this->adminUser)->post('/backoffice/settings', [
            'name' => 'SLL Cabang Baru',
            'phone' => '081299998888',
            'address' => 'Jl. Baru No. 88',
            'qris_payload' => '000201010211...',
            'qris_merchant_name' => 'SOTO LAMONGAN LESTARI',
            'qris_city' => 'JAKARTA',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('outlets', [
            'id' => $this->outlet->id,
            'name' => 'SLL Cabang Baru',
        ]);
    }

    public function test_can_record_and_delete_cashflow_expense()
    {
        $response = $this->actingAs($this->adminUser)->post('/backoffice/cashflow/expense', [
            'category' => 'OPERATIONAL',
            'amount' => 50000,
            'description' => 'Beli sabun cuci piring',
            'source_account' => 'CASH_DRAWER',
            'transaction_date' => now()->toDateString(),
            'is_h_minus_one_deduction' => true,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('cashflows', [
            'description' => 'Beli sabun cuci piring',
            'amount' => 50000,
        ]);
    }

    public function test_can_store_category()
    {
        $response = $this->actingAs($this->adminUser)->post('/backoffice/categories', [
            'name' => 'Kategori Uji Coba',
            'icon' => 'bowl',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Uji Coba',
        ]);
    }

    public function test_pos_verify_pin_endpoint()
    {
        $this->adminUser->update(['pin_hash' => \Illuminate\Support\Facades\Hash::make('1234')]);

        $response = $this->actingAs($this->adminUser)->postJson('/api/pos/verify-pin', [
            'pin' => '1234',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_can_destroy_test_order()
    {
        $order = \App\Models\Order::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->adminUser->id,
            'order_number' => 'TEST-001',
            'status' => 'PAID',
            'total_amount' => 60000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 60000,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('backoffice.orders.destroy', $order));

        $response->assertRedirect();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
