<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaluwaganItem;

class PaluwaganItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Lechon
            ['name' => '1 whole lechon with bopis & sinamak (upgraded)',  'category' => 'Lechon'],
            ['name' => '1 whole lechon with dinuguan (upgraded)',          'category' => 'Lechon'],

            // Chicken
            ['name' => '1 medium tray crispy chicken w/ gravy',           'category' => 'Chicken'],
            ['name' => '1 medium tray crispy garlic chicken w/ gravy',    'category' => 'Chicken'],

            // Seafood
            ['name' => '1 medium tray fish fillet w/ tartar sauce',       'category' => 'Seafood'],
            ['name' => '1 medium tray buttered garlic shrimp',            'category' => 'Seafood'],
            ['name' => '1 medium tray shrimp tempura w/ garlic mayo dip', 'category' => 'Seafood'],
            ['name' => '1 medium tray calamares w/ garlic mayo dip',      'category' => 'Seafood'],

            // Beef
            ['name' => '1 medium tray beef w/ brocolli',                  'category' => 'Beef'],
            ['name' => '1 medium tray beef kare-kare',                    'category' => 'Beef'],
            ['name' => '1 medium tray beef caldereta',                    'category' => 'Beef'],

            // Pork
            ['name' => '1 medium tray pork menudo',                       'category' => 'Pork'],

            // Noodles / Pasta
            ['name' => '1 medium tray special pancit canton guisado',     'category' => 'Noodles'],
            ['name' => '1 medium tray special bihon guisado',             'category' => 'Noodles'],
            ['name' => '1 medium tray special sateme guisado',            'category' => 'Noodles'],
            ['name' => '1 medium tray creamy spaghetti',                  'category' => 'Pasta'],
            ['name' => '1 medium tray creamy macaroni',                   'category' => 'Pasta'],

            // Vegetables
            ['name' => '1 medium tray special chopsuey guisado',          'category' => 'Vegetables'],

            // Lumpia
            ['name' => '1 medium tray lumpia shanghai w/ sweet chili sauce', 'category' => 'Lumpia'],
            ['name' => '1 medium tray fresh lumpia',                         'category' => 'Lumpia'],

            // Others
            ['name' => '1 medium tray fresh fruits w/ cream',            'category' => 'Dessert'],
        ];

        foreach ($items as $item) {
            PaluwaganItem::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}