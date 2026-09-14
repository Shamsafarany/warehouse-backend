<?php

namespace Database\Seeders;

use App\Domains\Catalog\Infrastructure\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'لفائف ورولات الصلب', 'description' => 'لفائف الصلب المدرفلة على البارد والساخن بمختلف السماكات.'],
            ['name' => 'الأنابيب والقطاعات المعدنية', 'description' => 'أنابيب حديدية دائرية ومربعة ومستطيلة للاستخدامات الهندسية والصناعية.'],
            ['name' => 'ألواح وشرائح الألمنيوم', 'description' => 'ألواح ألمنيوم عالية الجودة ومقاومة للتآكل.'],
            ['name' => 'الحديد المقاوم للصدأ (ستانلس ستيل)', 'description' => 'قضبان وألواح ستانلس ستيل بمختلف الدرجات (304، 316).'],
            ['name' => 'حديد التسليح والجسور', 'description' => 'قضبان حديد التسليح والجسور المعدنية H-Beam و I-Beam.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
