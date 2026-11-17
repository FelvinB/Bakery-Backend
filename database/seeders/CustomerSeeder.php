<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Walk-in', 'Regular', 'Bulk Order', 'Online'];
        $streets = ['Mabini St.', 'Rizal Ave.', 'Bonifacio St.', 'Del Pilar St.', 'Luna St.', 'Quezon St.'];
        for ($i = 1; $i <= 50; $i++) {
            Customer::create([
                'customer_name' => "Customer {$i}",
                'customer_type' => $types[$i % count($types)],
                'contact_number' => '09' . str_pad((string)(100000000 + $i * 12345), 9, '0', STR_PAD_LEFT),
                'address' => ($i % 30 + 1) . ' ' . $streets[$i % count($streets)] . ', Barangay ' . (($i % 10) + 1),
            ]);
        }
    }
}
