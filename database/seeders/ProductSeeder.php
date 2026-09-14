<?php

namespace Database\Seeders;

use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Infrastructure\Models\ProductImage;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Infrastructure\Models\StockMovement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $coils = Category::where('name', 'لفائف ورولات الصلب')->first();
        $pipes = Category::where('name', 'الأنابيب والقطاعات المعدنية')->first();
        $aluminum = Category::where('name', 'ألواح وشرائح الألمنيوم')->first();
        $stainless = Category::where('name', 'الحديد المقاوم للصدأ (ستانلس ستيل)')->first();
        $rebar = Category::where('name', 'حديد التسليح والجسور')->first();

        $products = [
            // Coils
            [
                'category_id' => $coils?->id,
                'name' => 'لفائف صلب مدرفل على الساخن 3 مم',
                'description' => 'رول صلب كربوني مدرفل على الساخن، مطابق للمواصفات القياسية الصناعية، العرض 1250 مم.',
                'price' => 3200.00,
                'stock' => 45,
            ],
            [
                'category_id' => $coils?->id,
                'name' => 'لفائف صلب مدرفل على البارد 1.5 مم',
                'description' => 'رول صلب ناعم التشطيب مدرفل على البارد، مناسب للصناعات الهندسية والتطبيقات الدقيقة.',
                'price' => 3800.00,
                'stock' => 30,
            ],

            // Pipes & Profiles
            [
                'category_id' => $pipes?->id,
                'name' => 'أنبوب حديد مجلفن 2 بوصة',
                'description' => 'أنبوب فولاذي مطلي بالبطانة المجلفنة المقاومة للصدأ، الطول 6 أمتار.',
                'price' => 450.00,
                'stock' => 120,
            ],
            [
                'category_id' => $pipes?->id,
                'name' => 'قطاع حديد مربع 50×50 مم',
                'description' => 'تيوب حديد هيكلي مربع الشكل بسمك 3 مم للأعمال الإنشائية.',
                'price' => 520.00,
                'stock' => 95,
            ],

            // Aluminum Sheets
            [
                'category_id' => $aluminum?->id,
                'name' => 'لوح ألمنيوم مضلع (سندويش) 2 مم',
                'description' => 'لوح ألمنيوم خفيف الوزن وعالي التحمل، مقاوم لعوامل الطقس.',
                'price' => 1100.00,
                'stock' => 60,
            ],

            // Stainless Steel
            [
                'category_id' => $stainless?->id,
                'name' => 'لوح ستانلس ستيل 304 مصقول 1 مم',
                'description' => 'ستانلس ستيل عالي الجودة درجة 304، تشطيب مرآة (Mirror Finish) للاستخدامات الديكورية والصحية.',
                'price' => 2400.00,
                'stock' => 40,
            ],

            // Rebar & Beams
            [
                'category_id' => $rebar?->id,
                'name' => 'قضبان حديد تسليح عالي المقاومة 12 مم',
                'description' => 'سيخ حديد تسليح عالي التعرية للبناء والتشييد، الطول 12 متراً.',
                'price' => 310.00,
                'stock' => 500,
            ],
            [
                'category_id' => $rebar?->id,
                'name' => 'عوارض فولاذية هيدروليكية H-Beam 150 مم',
                'description' => 'جسور معدنية صلبة للإنشاءات الثقيلة والهياكل المعدنية الكبرى.',
                'price' => 6500.00,
                'stock' => 15,
            ],
        ];

        foreach ($products as $data) {
            if (!$data['category_id']) {
                continue;
            }

            $stockQuantity = $data['stock'];
            unset($data['stock']);

            $product = Product::create($data);

            // Primary Image for the metal item
            ProductImage::create([
                'product_id' => $product->id,
                'url' => 'https://picsum.photos/seed/' . $product->id . '/600/600',
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            // Create Inventory Record 
            $inventory = Inventory::create([
                'product_id' => $product->id,
                'stock_quantity' => $stockQuantity,
                'reserved_quantity' => 0,
            ]);

            // Stock Movement
            StockMovement::create([
                'inventory_id' => $inventory->id,
                'type' => 'restock',
                'quantity' => $stockQuantity,
                'reference_id' => 'INIT-WH-2026',
                'notes' => 'توريد المخزون الافتتاحي للمستودع الرئيسي للحديد',
            ]);
        }
    }
}
