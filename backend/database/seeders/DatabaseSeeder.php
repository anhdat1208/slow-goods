<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@slowgoods.test'],
            [
                'name' => 'Slow Goods Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'phone' => '+10000000000',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@slowgoods.test'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'phone' => '+10000000001',
            ]
        );

        $categories = [
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Thoughtful titles for quiet evenings and deeper attention.',
                'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800&q=80',
            ],
            [
                'name' => 'Writing',
                'slug' => 'writing',
                'description' => 'Pens, paper, and tools that make handwriting feel intentional.',
                'image_url' => 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=800&q=80',
            ],
            [
                'name' => 'Slow Living',
                'slug' => 'slow-living',
                'description' => 'Objects that invite presence, ritual, and unhurried days.',
                'image_url' => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=800&q=80',
            ],
            [
                'name' => 'Craft & DIY',
                'slug' => 'craft-diy',
                'description' => 'Kits and materials for making with your hands.',
                'image_url' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80',
            ],
            [
                'name' => 'Outdoor',
                'slug' => 'outdoor',
                'description' => 'Light essentials for walks, camps, and open air.',
                'image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800&q=80',
            ],
            [
                'name' => 'Desk',
                'slug' => 'desk',
                'description' => 'Calm desk companions for reading, writing, and focus.',
                'image_url' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=800&q=80',
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $model = Category::updateOrCreate(['slug' => $category['slug']], $category);
            $categoryIds[$category['slug']] = $model->id;
        }

        $products = [
            ['category' => 'books', 'name' => 'Philosophy of Enough', 'slug' => 'philosophy-of-enough', 'sku' => 'BK-001', 'price' => 24.00, 'stock' => 40, 'featured' => true, 'short' => 'A slender essay on living with less noise.', 'desc' => 'A calm companion on attention, consumption, and choosing a quieter life. Ideal for evening reading without a screen.', 'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=800&q=80'],
            ['category' => 'books', 'name' => 'Walking as Thinking', 'slug' => 'walking-as-thinking', 'sku' => 'BK-002', 'price' => 22.00, 'stock' => 35, 'featured' => false, 'short' => 'Essays on movement, attention, and place.', 'desc' => 'Short essays that make ordinary walks feel like a practice. Carry it in a jacket pocket.', 'image' => 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?w=800&q=80'],
            ['category' => 'books', 'name' => 'The Quiet Workshop', 'slug' => 'the-quiet-workshop', 'sku' => 'BK-003', 'price' => 28.00, 'stock' => 25, 'featured' => true, 'short' => 'Stories of makers who work by hand.', 'desc' => 'Portraits of craftspeople and the rooms where they work slowly and carefully.', 'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80'],
            ['category' => 'books', 'name' => 'Letters Without Urgency', 'slug' => 'letters-without-urgency', 'sku' => 'BK-004', 'price' => 19.00, 'stock' => 50, 'featured' => false, 'short' => 'A book about correspondence and patience.', 'desc' => 'Why writing letters still matters — and how to begin again.', 'image' => 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=800&q=80'],
            ['category' => 'books', 'name' => 'Morning Pages Companion', 'slug' => 'morning-pages-companion', 'sku' => 'BK-005', 'price' => 18.00, 'stock' => 45, 'featured' => false, 'short' => 'Gentle prompts for early writing.', 'desc' => 'Soft prompts and blank space for a daily handwritten ritual.', 'image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=800&q=80'],

            ['category' => 'writing', 'name' => 'Reading Journal', 'slug' => 'reading-journal', 'sku' => 'WR-001', 'price' => 26.00, 'stock' => 60, 'featured' => true, 'short' => 'Track books, quotes, and quiet notes.', 'desc' => 'Ruled pages with soft sections for title, thoughts, and favorite lines. Cloth-bound cover.', 'image' => 'https://images.unsplash.com/photo-1531346680769-a1d79b57de5c?w=800&q=80'],
            ['category' => 'writing', 'name' => 'Field Notes Notebook', 'slug' => 'field-notes-notebook', 'sku' => 'WR-002', 'price' => 14.00, 'stock' => 80, 'featured' => true, 'short' => 'Pocket notebook for walks and lists.', 'desc' => 'Compact softcover notebook sized for bags and coat pockets. Dot-grid pages.', 'image' => 'https://images.unsplash.com/photo-1517842645767-c639042777db?w=800&q=80'],
            ['category' => 'writing', 'name' => 'Fountain Pen', 'slug' => 'fountain-pen', 'sku' => 'WR-003', 'price' => 48.00, 'stock' => 30, 'featured' => true, 'short' => 'A smooth everyday fountain pen.', 'desc' => 'Balanced steel-nib pen with a warm resin barrel. Includes one converter and cartridges.', 'image' => 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?w=800&q=80'],
            ['category' => 'writing', 'name' => 'Mechanical Pencil', 'slug' => 'mechanical-pencil', 'sku' => 'WR-004', 'price' => 16.00, 'stock' => 70, 'featured' => false, 'short' => 'Precise 0.5mm drafting pencil.', 'desc' => 'Metal-bodied mechanical pencil for sketching, lists, and field notes.', 'image' => 'https://images.unsplash.com/photo-1597484662317-9bd7bdda2907?w=800&q=80'],
            ['category' => 'writing', 'name' => 'Travel Journal', 'slug' => 'travel-journal', 'sku' => 'WR-005', 'price' => 32.00, 'stock' => 40, 'featured' => false, 'short' => 'Hardbound journal for trips and days out.', 'desc' => 'Cream pages with a ribbon marker and elastic closure. Built for trains, parks, and cafés.', 'image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=800&q=80'],
            ['category' => 'writing', 'name' => 'Linen Bookmark Set', 'slug' => 'linen-bookmark-set', 'sku' => 'WR-006', 'price' => 12.00, 'stock' => 90, 'featured' => false, 'short' => 'Three soft linen bookmarks.', 'desc' => 'A set of three hand-finished linen bookmarks in muted earth tones.', 'image' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=800&q=80'],

            ['category' => 'slow-living', 'name' => 'Analog Alarm Clock', 'slug' => 'analog-alarm-clock', 'sku' => 'SL-001', 'price' => 42.00, 'stock' => 35, 'featured' => true, 'short' => 'Wake without a phone.', 'desc' => 'Quiet analog clock with a soft bell and warm wood case. Designed for bedside calm.', 'image' => 'https://images.unsplash.com/photo-1563861826100-9cb6681837d0?w=800&q=80'],
            ['category' => 'slow-living', 'name' => 'Stoneware Mug', 'slug' => 'stoneware-mug', 'sku' => 'SL-002', 'price' => 28.00, 'stock' => 55, 'featured' => true, 'short' => 'Handmade mug for slow mornings.', 'desc' => 'Wheel-thrown stoneware mug with a matte glaze. Holds a generous morning pour.', 'image' => 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=800&q=80'],
            ['category' => 'slow-living', 'name' => 'Beeswax Candle', 'slug' => 'beeswax-candle', 'sku' => 'SL-003', 'price' => 22.00, 'stock' => 50, 'featured' => false, 'short' => 'Unscented beeswax pillar.', 'desc' => 'Clean-burning beeswax candle for evening wind-down without screens.', 'image' => 'https://images.unsplash.com/photo-1602602670223-408c37954cae?w=800&q=80'],
            ['category' => 'slow-living', 'name' => 'Linen Tea Towel', 'slug' => 'linen-tea-towel', 'sku' => 'SL-004', 'price' => 18.00, 'stock' => 65, 'featured' => false, 'short' => 'Stone-washed everyday linen.', 'desc' => 'A soft linen towel for kitchen rituals and slow cooking days.', 'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80'],
            ['category' => 'slow-living', 'name' => 'Ceramic Incense Holder', 'slug' => 'ceramic-incense-holder', 'sku' => 'SL-005', 'price' => 24.00, 'stock' => 40, 'featured' => false, 'short' => 'Minimal tray for incense sticks.', 'desc' => 'Hand-formed ceramic holder with a quiet, grounded silhouette.', 'image' => 'https://images.unsplash.com/photo-1602874801006-e26c4c8b0c5c?w=800&q=80'],

            ['category' => 'craft-diy', 'name' => 'Handmade Model Kit', 'slug' => 'handmade-model-kit', 'sku' => 'CD-001', 'price' => 36.00, 'stock' => 30, 'featured' => true, 'short' => 'Wood model kit for a quiet afternoon.', 'desc' => 'Precision-cut wooden pieces for a small architectural model. No glue required.', 'image' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?w=800&q=80'],
            ['category' => 'craft-diy', 'name' => 'Sketchbook', 'slug' => 'sketchbook', 'sku' => 'CD-002', 'price' => 20.00, 'stock' => 70, 'featured' => true, 'short' => 'Heavyweight paper sketchbook.', 'desc' => 'A4 sketchbook with 120gsm paper suited to pencil, ink, and light wash.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80'],
            ['category' => 'craft-diy', 'name' => 'Watercolor Mini Set', 'slug' => 'watercolor-mini-set', 'sku' => 'CD-003', 'price' => 29.00, 'stock' => 40, 'featured' => false, 'short' => 'Travel watercolor pans and brush.', 'desc' => 'Twelve muted tones in a metal tin with a pocket brush.', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80'],
            ['category' => 'craft-diy', 'name' => 'Embroidery Starter Kit', 'slug' => 'embroidery-starter-kit', 'sku' => 'CD-004', 'price' => 34.00, 'stock' => 28, 'featured' => false, 'short' => 'Hoop, thread, and simple patterns.', 'desc' => 'Everything needed for a first evening of slow stitching.', 'image' => 'https://images.unsplash.com/photo-1528422168321-9c2e2f0f6d2c?w=800&q=80'],
            ['category' => 'craft-diy', 'name' => 'Woodcarving Starter Block', 'slug' => 'woodcarving-starter-block', 'sku' => 'CD-005', 'price' => 27.00, 'stock' => 32, 'featured' => false, 'short' => 'Basswood block with beginner guide.', 'desc' => 'Soft basswood blank and printed guide for simple hand-carved forms.', 'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800&q=80'],

            ['category' => 'outdoor', 'name' => 'Camping Mug', 'slug' => 'camping-mug', 'sku' => 'OD-001', 'price' => 24.00, 'stock' => 55, 'featured' => true, 'short' => 'Enamel mug for trails and porches.', 'desc' => 'Classic enamel camping mug that holds tea, coffee, or soup by a fire.', 'image' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=800&q=80'],
            ['category' => 'outdoor', 'name' => 'Pocket Compass', 'slug' => 'pocket-compass', 'sku' => 'OD-002', 'price' => 18.00, 'stock' => 45, 'featured' => false, 'short' => 'Brass pocket compass.', 'desc' => 'A small brass compass for orientation walks and analog navigation.', 'image' => 'https://images.unsplash.com/photo-1526772662000-3f88f10405ff?w=800&q=80'],
            ['category' => 'outdoor', 'name' => 'Waxed Canvas Tote', 'slug' => 'waxed-canvas-tote', 'sku' => 'OD-003', 'price' => 58.00, 'stock' => 25, 'featured' => true, 'short' => 'Rugged tote for books and tools.', 'desc' => 'Waxed canvas tote with leather handles — for markets, libraries, and day walks.', 'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a67437a?w=800&q=80'],
            ['category' => 'outdoor', 'name' => 'Trail Water Bottle', 'slug' => 'trail-water-bottle', 'sku' => 'OD-004', 'price' => 26.00, 'stock' => 60, 'featured' => false, 'short' => 'Stainless bottle, no plastic taste.', 'desc' => 'Single-wall stainless bottle with a simple screw cap. No screens, just water.', 'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&q=80'],
            ['category' => 'outdoor', 'name' => 'Pocket Field Guide', 'slug' => 'pocket-field-guide', 'sku' => 'OD-005', 'price' => 15.00, 'stock' => 50, 'featured' => false, 'short' => 'Local trees and birds, illustrated.', 'desc' => 'A compact illustrated guide for noticing what grows and flies nearby.', 'image' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&q=80'],

            ['category' => 'desk', 'name' => 'Wooden Book Stand', 'slug' => 'wooden-book-stand', 'sku' => 'DK-001', 'price' => 38.00, 'stock' => 35, 'featured' => true, 'short' => 'Adjustable oak reading stand.', 'desc' => 'Solid oak stand that holds cookbooks, journals, and heavy hardcovers at a comfortable angle.', 'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80'],
            ['category' => 'desk', 'name' => 'Desk Lamp', 'slug' => 'desk-lamp', 'sku' => 'DK-002', 'price' => 64.00, 'stock' => 22, 'featured' => true, 'short' => 'Warm task lamp for reading.', 'desc' => 'Adjustable desk lamp with a warm, dimmable glow — kinder than blue light.', 'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800&q=80'],
            ['category' => 'desk', 'name' => 'Brass Paperweight', 'slug' => 'brass-paperweight', 'sku' => 'DK-003', 'price' => 21.00, 'stock' => 40, 'featured' => false, 'short' => 'Solid brass desk weight.', 'desc' => 'A simple cast brass form that keeps letters and pages in place.', 'image' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800&q=80'],
            ['category' => 'desk', 'name' => 'Ceramic Pen Cup', 'slug' => 'ceramic-pen-cup', 'sku' => 'DK-004', 'price' => 19.00, 'stock' => 48, 'featured' => false, 'short' => 'Unglazed ceramic cup for tools.', 'desc' => 'A quiet cylinder for pens, pencils, and scissors on a calm desk.', 'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80'],
            ['category' => 'desk', 'name' => 'Cork Desk Mat', 'slug' => 'cork-desk-mat', 'sku' => 'DK-005', 'price' => 45.00, 'stock' => 30, 'featured' => false, 'short' => 'Natural cork writing surface.', 'desc' => 'A soft cork mat that protects the desk and softens the sound of writing.', 'image' => 'https://images.unsplash.com/photo-1593062096033-9a26b09da705?w=800&q=80'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categoryIds[$product['category']],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['desc'],
                    'short_description' => $product['short'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'sku' => $product['sku'],
                    'image_url' => $product['image'],
                    'is_featured' => $product['featured'],
                    'is_active' => true,
                ]
            );
        }
    }
}
