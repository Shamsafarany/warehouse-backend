<?php

namespace Database\Factories;

use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Infrastructure\Models\StockMovement;
use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['restock', 'sale', 'return', 'adjustment']);

        $notesMap = [
            'restock' => 'توريد مخزون جديد من المورد',
            'sale' => 'صرف مخزون لصالح طلب مبيعات',
            'return' => 'إرجاع بضاعة من العميل إلى المستودع',
            'adjustment' => 'تعديل يدوي للمخزون نتيجة الجرد الدوري',
        ];

        return [
            'inventory_id' => Inventory::factory(),
            'type' => $type,
            'quantity' => fake()->numberBetween(5, 50),
            'reference_id' => strtoupper(fake()->randomLetter()) . 'O-' . fake()->randomNumber(5),
            'notes' => $notesMap[$type],
        ];
    }
}
