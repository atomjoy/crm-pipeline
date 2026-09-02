# Modules

## DB

```php
// 1
Schema::create('shop_products', function (Blueprint $table) {
    $table->id();
    $table->uuid('product_uuid')->unique(); // Wspólny mianownik
    $table->string('name');
    $table->decimal('price', 8, 2);
    $table->timestamps();
});

// 2
Schema::create('warehouse_products', function (Blueprint $table) {
    $table->id();
    $table->uuid('product_uuid')->unique(); // Ten sam mianownik
    $table->integer('stock_quantity')->default(0);
    $table->timestamps();
});

// Lub zamiast uuid użyj sku
$table->string('sku', 50)->unique(); // Zwykły unikalny string (np. 'PROD-12345')
```

## Controller

```php
// 1
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Warehouse\app\Models\Product as WarehouseProduct;

class Product extends Model
{
    protected $table = 'shop_products';

    public function warehouseStock()
    {
        // 1. Klasa docelowa
        // 2. Klucz obcy w tabeli docelowej (warehouse_products)
        // 3. Klucz lokalny w tabeli bieżącej (shop_products)
        return $this->hasOne(WarehouseProduct::class, 'product_uuid', 'product_uuid');
    }
}

// 2
namespace Modules\Warehouse\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shop\app\Models\Product as ShopProduct;

class Product extends Model
{
    protected $table = 'warehouse_products';

    public function shopDetails()
    {
        // 1. Klasa docelowa
        // 2. Klucz obcy w tabeli bieżącej (warehouse_products)
        // 3. Klucz powiązany w tabeli docelowej (shop_products)
        return $this->belongsTo(ShopProduct::class, 'product_uuid', 'product_uuid');
    }
}

// 3
Product::with('warehouseStock')->get()
```

## Event

```php
// 1
namespace Modules\Shop\app\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Shop\app\Models\Product;

class ProductCreated
{
    use SerializesModels;

    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

// 2
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shop\app\Events\ProductCreated;
use Modules\Warehouse\app\Models\Product as WarehouseProduct;

class Product extends Model
{
    protected $table = 'shop_products';

    protected $fillable = ['product_uuid', 'name', 'price'];

    // Mapowanie akcji Eloquent na klasę Zdarzenia
    protected $dispatchesEvents = [
        'created' => ProductCreated::class,
    ];

    public function warehouseStock()
    {
        return $this->hasOne(WarehouseProduct::class, 'product_uuid', 'product_uuid');
    }
}

// 3
namespace Modules\Warehouse\app\Listeners;

use Modules\Shop\app\Events\ProductCreated;
use Modules\Warehouse\app\Models\Product as WarehouseProduct;

class CreateWarehouseStock
{
    public function handle(ProductCreated $event): void
    {
        // Tworzymy rekord w tabeli warehouse_products z tym samym UUID
        WarehouseProduct::create([
            'product_uuid' => $event->product->product_uuid,
            'stock_quantity' => 0, // Domyślny stan zerowy na magazynie
        ]);
    }
}

// 4
namespace Modules\Warehouse\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Shop\app\Events\ProductCreated;
use Modules\Warehouse\app\Listeners\CreateWarehouseStock;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProductCreated::class => [
            CreateWarehouseStock::class,
        ],
    ];
}

// 5
use Modules\Shop\app\Models\Product;
use Illuminate\Support\Str;

Product::create([
    'product_uuid' => (string) Str::uuid(),
    'name' => 'Buty sportowe',
    'price' => 299.99
]);

// W tabeli shop_products pojawi się rekord z nowym butem.
// Automatycznie (w tej samej sekundzie) w tabeli warehouse_products pojawi się rekord z identycznym product_uuid i stanem magazynowym ustawionym na 0.
```

## Event Delete

```php
// 1
namespace Modules\Shop\app\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Shop\app\Models\Product;

class ProductDeleted
{
    use SerializesModels;

    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

// 2 Modules/Shop/app/Models/Product.php
protected $dispatchesEvents = [
    'created' => ProductCreated::class,
    'deleted' => ProductDeleted::class, // <-- Nowa linijka
];

// 3
namespace Modules\Warehouse\app\Listeners;

use Modules\Shop\app\Events\ProductDeleted;
use Modules\Warehouse\app\Models\Product as WarehouseProduct;

class DeleteWarehouseStock
{
    public function handle(ProductDeleted $event): void
    {
        // Szukamy rekordu w magazynie po UUID usuniętego produktu i go kasujemy
        WarehouseProduct::where('product_uuid', $event->product->product_uuid)->delete();
    }
}
```

## Event Warehouse

```php
// 1
namespace Modules\Warehouse\app\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Warehouse\app\Models\Product;

class StockUpdated
{
    use SerializesModels;

    public Product $warehouseProduct;

    public function __construct(Product $warehouseProduct)
    {
        $this->warehouseProduct = $warehouseProduct;
    }
}


// 2
namespace Modules\Warehouse\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Warehouse\app\Events\StockUpdated; // <-- Import zdarzenia

class Product extends Model
{
    protected $table = 'warehouse_products';
    protected $fillable = ['product_uuid', 'stock_quantity'];

    protected $dispatchesEvents = [
        'updated' => StockUpdated::class, // <-- Odpal przy każdej edycji stanu
    ];
}

// 3
namespace Modules\Shop\app\Listeners;

use Modules\Warehouse\app\Events\StockUpdated;
use Modules\Shop\app\Models\Product as ShopProduct;

class UpdateShopProductStatus
{
    public function handle(StockUpdated $event): void
    {
        $shopProduct = ShopProduct::where('product_uuid', $event->warehouseProduct->product_uuid)->first();

        if ($shopProduct) {
            // Przykładowa logika: jeśli stan spadł do 0, zmieniasz status w sklepie
            if ($event->warehouseProduct->stock_quantity === 0) {
                $shopProduct->update(['status' => 'brak_produktu']);
            } else {
                $shopProduct->update(['status' => 'w_sprzedazy']);
            }
        }
    }
}
```

## Providers

```php
// 1 Modules/Warehouse/app/Providers/EventServiceProvider.php
protected $listen = [
    \Modules\Shop\app\Events\ProductCreated::class => [
        \Modules\Warehouse\app\Listeners\CreateWarehouseStock::class,
    ],
    \Modules\Shop\app\Events\ProductDeleted::class => [
        \Modules\Warehouse\app\Listeners\DeleteWarehouseStock::class, // <-- Rejestracja usuwania
    ],
];


// 2 Modules/Shop/app/Providers/EventServiceProvider.php
protected $listen = [
    \Modules\Warehouse\app\Events\StockUpdated::class => [
        \Modules\Shop\app\Listeners\UpdateShopProductStatus::class, // <-- Rejestracja aktualizacji stanu
    ],
];

// 3 Product
$product = Modules\Shop\app\Models\Product::find(1);
$product->delete(); // MySQL czyści shop_products, a Event automatycznie czyści warehouse_products

// 4 Warehouse
$stock = Modules\Warehouse\app\Models\Product::where('product_uuid', '...')->first();
$stock->update(['stock_quantity' => 15]); // Sklep automatycznie przełączy status na 'w_sprzedazy'
```

## Test

```php
// 1
use Illuminate\Support\Facades\Route;
use Modules\Shop\app\Http\Controllers\TestProductController;

Route::prefix('test-products')->group(function () {
    Route::get('/create', [TestProductController::class, 'testCreate']);
    Route::get('/update-stock/{uuid}/{quantity}', [TestProductController::class, 'testUpdateStock']);
    Route::get('/delete/{id}', [TestProductController::class, 'testDelete']);
});

// 2
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\Shop\app\Models\Product as ShopProduct;
use Modules\Warehouse\app\Models\Product as WarehouseProduct;

class TestProductController extends Controller
{
    /**
     * TEST 1: Tworzenie produktu w module Shop.
     * Automatycznie powinno założyć stan 0 w module Warehouse.
     */
    public function testCreate()
    {
        $uuid = (string) Str::uuid();

        // Tworzymy produkt w sklepie
        $product = ShopProduct::create([
            'product_uuid' => $uuid,
            'name'         => 'Testowy But Sportowy ' . rand(100, 999),
            'price'        => 299.99,
            'status'       => 'w_sprzedazy', // status początkowy
        ]);

        // Ładujemy relację, aby upewnić się, że słuchacz założył rekord w magazynie
        $product->load('warehouseStock');

        return response()->json([
            'message' => 'Produkt został utworzony pomyślnie!',
            'shop_product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_uuid' => $product->product_uuid,
                'status' => $product->status,
            ],
            'warehouse_stock' => $product->warehouseStock // dane z tabeli warehouse_products
        ]);
    }

    /**
     * TEST 2: Zmiana stanu magazynowego w module Warehouse.
     * Zmiana stanu na 0 powinna automatycznie zmienić status w sklepie na 'brak_produktu'.
     */
    public function testUpdateStock(string $uuid, int $quantity)
    {
        // Szukamy rekordu bezwzględnie w module magazynu
        $warehouseProduct = WarehouseProduct::where('product_uuid', $uuid)->firstOrFail();

        // Aktualizacja odpali Event 'updated' w modelu Warehouse
        $warehouseProduct->update([
            'stock_quantity' => $quantity
        ]);

        // Pobieramy produkt ze sklepu, aby sprawdzić czy słuchacz zmienił jego status
        $shopProduct = ShopProduct::where('product_uuid', $uuid)->firstOrFail();

        return response()->json([
            'message' => 'Stan magazynowy został zaktualizowany!',
            'new_stock_quantity' => $warehouseProduct->stock_quantity,
            'updated_shop_status' => $shopProduct->status // Powinien być 'brak_produktu' dla 0 lub 'w_sprzedazy' dla > 0
        ]);
    }

    /**
     * TEST 3: Usunięcie produktu z modułu Shop.
     * Powinno automatycznie wyczyścić rekord z tabeli warehouse_products.
     */
    public function testDelete(int $id)
    {
        $product = ShopProduct::findOrFail($id);
        $uuid = $product->product_uuid;

        // Usunięcie odpala Event 'deleted'
        $product->delete();

        // Sprawdzamy czy w magazynie rekord faktycznie zniknął
        $warehouseRecordExists = WarehouseProduct::where('product_uuid', $uuid)->exists();

        return response()->json([
            'message' => 'Produkt usunięty ze sklepu.',
            'warehouse_record_still_exists' => $warehouseRecordExists ? 'Błąd: Rekord nadal istnieje' : 'Sukces: Rekord z magazynu usunięty kaskadowo przez PHP!'
        ]);
    }
}

//3 Url
http://127.0.0{TUTAJ_WKLEJ_UUID}/15
http://127.0.0{TUTAJ_WKLEJ_UUID}/0
http://127.0.0{ID_PRODUKTU}
```

## Jobs

```php
// 1 Modules/Shop/app/Http/Controllers/TestProductController.php
use Illuminate\Support\Facades\DB; // <-- Pamiętaj o imporcie fasady DB

public function testCreate()
{
    $uuid = (string) Str::uuid();

    // Otwieramy transakcję MySQL. Jeśli cokolwiek wewnątrz bloku (lub w Listenerach)
    // rzuci błąd (Exception), baza automatycznie cofnie (ROLLBACK) wszystkie zmiany.
    $product = DB::transaction(function () use ($uuid) {
        return ShopProduct::create([
            'product_uuid' => $uuid,
            'name'         => 'Bezpieczny But ' . rand(100, 999),
            'price'        => 199.99,
            'status'       => 'w_sprzedazy',
        ]);
    }); // Tutaj transakcja zostaje zatwierdzona (COMMIT) w MySQL

    $product->load('warehouseStock');

    return response()->json([
        'message' => 'Produkt i magazyn utworzone bezpiecznie w jednej transakcji!',
        'shop_product' => $product,
        'warehouse_stock' => $product->warehouseStock
    ]);
}

// 2
namespace Modules\Shop\app\Listeners;

use Modules\Warehouse\app\Events\StockUpdated;
use Modules\Shop\app\Models\Product as ShopProduct;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- 1. Importujesz interfejs

// 2. Dodajesz "implements ShouldQueue" do klasy
class UpdateShopProductStatus implements ShouldQueue
{
    // 3. Opcjonalnie: definiujesz nazwę kolejki (np. 'high', 'default')
    public $queue = 'listeners';

    public function handle(StockUpdated $event): void
    {
        // Ta logika wykona się już w tle, poza czasem oczekiwania użytkownika
        $shopProduct = ShopProduct::where('product_uuid', $event->warehouseProduct->product_uuid)->first();

        if ($shopProduct) {
            if ($event->warehouseProduct->stock_quantity === 0) {
                $shopProduct->update(['status' => 'brak_produktu']);
            } else {
                $shopProduct->update(['status' => 'w_sprzedazy']);
            }
        }
    }
}

// 3
QUEUE_CONNECTION=database
php artisan queue:table
php artisan migrate
php artisan queue:work
```

## Variants

```txt
attrinutes: {"size": "M", "color": "red", "ram": "16GB"}

shop_products – ogólne informacje o produkcie.
id | product_uuid | name | description

shop_variants – konkretne kombinacje atrybutów. To tutaj przypisana jest cena.
id | variant_uuid | product_id | price | attributes (typ JSON)

warehouse_variants – fizyczne sztuki na półkach. Moduł magazynu nie musi wiedzieć, czy produkt jest czerwony, czy niebieski. Interesuje go tylko unikalny identyfikator wariantu.
id | variant_uuid | stock_quantity | bin_location
```

## DB

```php
Schema::create('shop_variants', function (Blueprint $table) {
    $table->id();
    $table->uuid('variant_uuid')->unique(); // Wspólny mianownik dla wariantu
    $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
    $table->decimal('price', 8, 2);
    $table->json('attributes'); // Zapis: {"size": "42", "color": "czarny", "ram": "16GB"}
    $table->timestamps();
});

Schema::create('warehouse_variants', function (Blueprint $table) {
    $table->id();
    $table->uuid('variant_uuid')->unique(); // Ten sam mianownik
    $table->integer('stock_quantity')->default(0);
    $table->string('bin_location')->nullable();
    $table->timestamps();
});
```

## Modele

```php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shop\app\Events\VariantCreated;
use Modules\Warehouse\app\Models\Variant as WarehouseVariant;

class Variant extends Model
{
    protected $table = 'shop_variants';
    protected $fillable = ['variant_uuid', 'product_id', 'price', 'attributes'];

    // Rzutujemy kolumnę JSON na tablicę PHP automatycznie
    protected $casts = [
        'attributes' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => VariantCreated::class,
    ];

    // Relacja do stanu magazynowego tego konkretnego wariantu
    public function warehouseStock()
    {
        return $this->hasOne(WarehouseVariant::class, 'variant_uuid', 'variant_uuid');
    }
}

// 2
use Modules\Shop\app\Models\Product;

// Pobieramy produkt, jego wszystkie warianty z atrybutami oraz stany z magazynu
$product = Product::with('variants.warehouseStock')->find($id);

foreach ($product->variants as $variant) {
    echo "Kolor: " . $variant->attributes['color'];
    echo " RAM: " . $variant->attributes['ram'];
    echo " Cena: " . $variant->price;
    echo " Stan na magazynie: " . $variant->warehouseStock->stock_quantity;
}
```

## Filter

```php
// 1
use Modules\Shop\app\Models\Variant;

$variants = Variant::where('attributes->color', 'czarny')
    ->where('attributes->ram', '16GB')
    ->get();

// 2
use Modules\Shop\app\Models\Product;

$products = Product::whereHas('variants', function ($query) {
    $query->where('attributes->color', 'czarny')
          ->where('attributes->ram', '16GB');
})->with('variants')->get();

// 3
// Znajdź buty o rozmiarze większym lub równym 42
$variants = Variant::where('attributes->size', '>=', 42)->get();

// 4
// Pobierz tylko te warianty, które mają zdefiniowany parametr 'ram'
$variants = Variant::whereJsonContains('attributes', ['ram' => true])->get();
```

## Index virtualny

```php
Schema::create('shop_variants', function (Blueprint $table) {
    $table->id();
    $table->uuid('variant_uuid')->unique();
    $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
    $table->json('attributes');

    // TWORZENIE INDEKSÓW DLA JSON (Wymagane MySQL 5.7+ / 8.0)
    // Tworzy wirtualną kolumnę wyciągającą 'color' i nakłada na nią klasyczny indeks b-tree
    $table->string('computed_color')->virtualAs('attributes->"$.color"')->index();
    $table->string('computed_ram')->virtualAs('attributes->"$.ram"')->index();

    $table->timestamps();
});
```

## Struktura tabel w MySQL (Model EAV)

Potrzebujemy 3 nowych tabel w module Shop, aby obsłużyć cechy produktów:

shop_attributes – definicje cech (np. Rozmiar, Kolor, RAM).
shop_attribute_values – konkretne słownikowe opcje (np. 42, Czarny, 16GB).
shop_variant_attribute_value – tabela łącząca (Pivot) konkretny wariant z jego wartościami atrybutów.

```php
// 1. Tabela słownikowa nazw cech
Schema::create('shop_attributes', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // np. "Kolor", "RAM", "Rozmiar"
    $table->string('slug')->unique(); // np. "kolor", "ram", "rozmiar"
    $table->timestamps();
});

// 2. Tabela wartości słownikowych
Schema::create('shop_attribute_values', function (Blueprint $table) {
    $table->id();
    $table->foreignId('attribute_id')->constrained('shop_attributes')->onDelete('cascade');
    $table->string('value'); // np. "Czarny", "16GB", "42"
    $table->string('slug');  // np. "czarny", "16gb", "42"
    $table->timestamps();
});

// 3. Tabela wariantów (usunięto z niej kolumnę JSON)
Schema::create('shop_variants', function (Blueprint $table) {
    $table->id();
    $table->uuid('variant_uuid')->unique();
    $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
    $table->decimal('price', 8, 2);
    $table->timestamps();
});

// 4. Tabela łącząca (Pivot) wariant z jego cechami
Schema::create('shop_variant_attribute_value', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variant_id')->constrained('shop_variants')->onDelete('cascade');
    $table->foreignId('attribute_value_id')->constrained('shop_attribute_values')->onDelete('cascade');

    // Unikalność, aby jeden wariant nie miał przypisanych dwóch kolorów na raz
    $table->unique(['variant_id', 'attribute_value_id'], 'variant_value_unique');
});
```

## Modele

```php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Shop\app\Models\AttributeValue;

class Variant extends Model
{
    protected $table = 'shop_variants';
    protected $fillable = ['variant_uuid', 'product_id', 'price'];

    // Powiązanie wariantu z jego cechami przez tabelę pivot
    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'shop_variant_attribute_value',
            'variant_id',
            'attribute_value_id'
        );
    }
}
```

## Filter

```php
use Modules\Shop\app\Models\Product;

$products = Product::whereHas('variants', function ($variantQuery) {
    // 1. Filtrujemy warianty, które mają wartość "czarny" przypisaną do cechy "kolor"
    $variantQuery->whereHas('attributeValues', function ($valueQuery) {
        $valueQuery->where('slug', 'czarny')
                   ->whereHas('attribute', function ($attrQuery) {
                       $attrQuery->where('slug', 'kolor');
                   });
    })
    // 2. ORAZ ten sam wariant musi mieć wartość "16gb" przypisaną do cechy "ram"
    ->whereHas('attributeValues', function ($valueQuery) {
        $valueQuery->where('slug', '16gb')
                   ->whereHas('attribute', function ($attrQuery) {
                       $attrQuery->where('slug', 'ram');
                   });
    });
})->with('variants.attributeValues.attribute')->get();
```

## Filter scope

Product::filterByAttributes(['kolor' => 'czarny', 'ram' => '16gb'])->get()

```php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $table = 'shop_products';

    /**
     * Uniwersalny scope do filtrowania po wielu atrybutach jednocześnie.
     * Expected format: ['kolor' => 'czarny', 'ram' => '16gb']
     */
    public function scopeFilterByAttributes(Builder $query, array $filters): Builder
    {
        // Jeśli nie przekazano żadnych filtrów, zwracamy nienaruszone zapytanie
        if (empty($filters)) {
            return $query;
        }

        // Szukamy produktów, które mają warianty spełniające WSZYSTKIE kryteria
        return $query->whereHas('variants', function ($variantQuery) use ($filters) {

            // Iterujemy po każdym filtrze (np. klucz: 'kolor', wartość: 'czarny')
            foreach ($filters as $attributeSlug => $valueSlug) {

                // Każdy atrybut sprawdzamy osobnym warunkiem whereHas,
                // aby upewnić się, że JEDEN wariant spełnia wszystkie te warunki na raz
                $variantQuery->whereHas('attributeValues', function ($valueQuery) use ($attributeSlug, $valueSlug) {
                    $valueQuery->where('slug', $valueSlug)
                               ->whereHas('attribute', function ($attrQuery) use ($attributeSlug) {
                                   $attrQuery->where('slug', $attributeSlug);
                               });
                });

            }
        });
    }

    // Twoja dotychczasowa relacja do wariantów
    public function variants()
    {
        return $this->hasMany(Variant::class, 'product_id');
    }
}
```

## Kontroller

```php
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shop\app\Models\Product;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Pobieramy filtry z adresu URL, np. /products?kolor=czarny&ram=16gb
        // Metoda only() odrzuci inne parametry, jak np. ?page=2
        $activeFilters = $request->only(['kolor', 'ram', 'rozmiar']);

        // 2. Wywołujemy nasz Scope. Czysto i czytelnie!
        $products = Product::filterByAttributes($activeFilters)
            ->with(['variants.attributeValues.attribute', 'variants.warehouseStock'])
            ->get();

        return response()->json($products);
    }
}
```

## Filter po cenie

```php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $table = 'shop_products';

    /**
     * Zaawansowany scope do filtrowania produktów.
     */
    public function scopeFilterByAttributes(Builder $query, array $filters): Builder
    {
        // Szukamy produktów, których przynajmniej JEDEN wariant spełnia kryteria
        return $query->whereHas('variants', function ($variantQuery) use ($filters) {

            // 1. FILTROWANIE PO ATRYBUTACH (EAV)
            // Wyciągamy tylko te filtry, które nie są ceną ani dostępnością
            $attributeFilters = collect($filters)->except(['cena_min', 'cena_max', 'dostepny']);

            foreach ($attributeFilters as $attributeSlug => $valueSlug) {
                if (empty($valueSlug)) continue;

                $variantQuery->whereHas('attributeValues', function ($valueQuery) use ($attributeSlug, $valueSlug) {
                    $valueQuery->where('slug', $valueSlug)
                               ->whereHas('attribute', function ($attrQuery) use ($attributeSlug) {
                                   $attrQuery->where('slug', $attributeSlug);
                               });
                });
            }

            // 2. FILTROWANIE PO CENIE (Warianty mają kolumnę 'price')
            if (!empty($filters['cena_min'])) {
                $variantQuery->where('price', '>=', $filters['cena_min']);
            }

            if (!empty($filters['cena_max'])) {
                $variantQuery->where('price', '<=', $filters['cena_max']);
            }

            // 3. FILTROWANIE PO STANIE MAGAZYNOWYM (Relacja cross-module do Warehouse)
            // Sprawdzamy stan przez relację 'warehouseStock' zdefiniowaną w modelu Variant
            if (isset($filters['dostepny']) && $filters['dostepny'] == true) {
                $variantQuery->whereHas('warehouseStock', function ($warehouseQuery) {
                    $warehouseQuery->where('stock_quantity', '>', 0);
                });
            }
        });
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'product_id');
    }
}
```

## Kontroler

```php
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shop\app\Models\Product;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // Przechwytujemy standardowe atrybuty, ceny oraz checkbox dostępności
        $filters = $request->only(['kolor', 'ram', 'rozmiar', 'cena_min', 'cena_max', 'dostepny']);

        // Wywołujemy zaktualizowany, potężny Scope
        $products = Product::filterByAttributes($filters)
            ->with(['variants.attributeValues.attribute', 'variants.warehouseStock'])
            ->get();

        return response()->json($products);
    }
}
```

## Sortowanie

```php
namespace Modules\Shop\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $table = 'shop_products';

    // ... Twój poprzedni scopeFilterByAttributes ...

    /**
     * Scope do sortowania produktów na podstawie wariantów.
     */
    public function scopeSortBy(Builder $query, ?string $sortBy): Builder
    {
        return match ($sortBy) {
            // Sortowanie po cenie od najtańszego
            'cena_rosnaco' => $query->withMin('variants as min_price', 'price')
                                    ->orderBy('min_price', 'asc'),

            // Sortowanie po cenie od najdroższego
            'cena_malejaco' => $query->withMax('variants as max_price', 'price')
                                     ->orderBy('max_price', 'desc'),

            // Domyślne sortowanie od najnowszych produktów
            'najnowsze' => $query->latest(),

            // Jeśli parametr jest pusty lub nieznany, zwracamy bez zmian
            default => $query,
        };
    }

    public function variants()
    {
        return $this->hasMany(Variant::class, 'product_id');
    }
}
```

## Kontroler

```php
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Shop\app\Models\Product;

class ProductCatalogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Pobieramy filtry z adresu URL
        $filters = $request->only(['kolor', 'ram', 'rozmiar', 'cena_min', 'cena_max', 'dostepny']);

        // 2. Pobieramy parametr sortowania (np. ?sortuj_po=cena_rosnaco)
        $sortBy = $request->input('sortuj_po');

        // 3. Łączymy filtrowanie, sortowanie oraz stronicowanie (Pagination)
        $products = Product::filterByAttributes($filters)
            ->sortBy($sortBy)
            ->with(['variants.attributeValues.attribute', 'variants.warehouseStock'])
            ->paginate(15); // Automatyczne stronicowanie po 15 produktów na stronę

        return response()->json($products);
    }
}
```

# Show products

```php
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Shop\app\Models\Product;

class ProductDisplayController extends Controller
{
    public function show(int $id)
    {
        // 1. Pobieramy produkt z wariantami, ich cechami oraz stanem magazynowym
        $product = Product::with([
            'variants.attributeValues.attribute',
            'variants.warehouseStock'
        ])->findOrFail($id);

        // 2. Budujemy mapę atrybutów dla selectów (np. Kolor -> [Czarny, Biały])
        $selectableAttributes = [];

        foreach ($product->variants as $variant) {
            // Pomijamy warianty niedostępne w magazynie (opcjonalnie)
            if (!$variant->warehouseStock || $variant->warehouseStock->stock_quantity <= 0) {
                continue;
            }

            foreach ($variant->attributeValues as $value) {
                $attrName = $value->attribute->name; // np. "Kolor"
                $attrSlug = $value->attribute->slug; // np. "kolor"

                // Zapisujemy unikalne wartości
                $selectableAttributes[$attrSlug]['name'] = $attrName;
                $selectableAttributes[$attrSlug]['values'][$value->slug] = $value->name;
            }
        }

        // 3. Przekazujemy dane do widoku Blade
        return view('shop::product.show', compact('product', 'selectableAttributes'));
    }
}
```

## Dane

```json
[
    {
        "id": 12,
        "price": "299.99",
        "stock": 5,
        "options": { "kolor": "czarny", "ram": "16gb" }
    }
]
```

## Blade product

```php
<div class="product-page">
    <h1>{{ $product->name }}</h1>

    <!-- Dynamiczna cena i stan magazynowy -->
    <h3 id="product-price">Wybierz opcje...</h3>
    <p id="product-stock"></p>

    <!-- Formularz z selectami generowanymi dynamicznie -->
    <form id="add-to-cart-form">
        @foreach($selectableAttributes as $slug => $attribute)
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="attr-{{ $slug }}"><strong>{{ $attribute['name'] }}:</strong></label>
                <select id="attr-{{ $slug }}" class="variant-selector" name="options[{{ $slug }}]" required>
                    <option value="">-- Wybierz {{ $attribute['name'] }} --</option>
                    @foreach($attribute['values'] as $valSlug => $valName)
                        <option value="{{ $valSlug }}">{{ $valName }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <input type="hidden" id="selected-variant-id" name="variant_id" value="">
        <button type="submit" id="cart-button" disabled>Dodaj do koszyka</button>
    </form>
</div>

<!-- Logika JS obsługująca kombinacje wariantów -->
<script>
    // 1. Mapujemy warianty z PHP bezpośrednio do tablicy JS
    const variants = [
        @foreach($product->variants as $variant)
        {
            id: {{ $variant->id }},
            price: "{{ $variant->price }} zł",
            stock: {{ $variant->warehouseStock->stock_quantity ?? 0 }},
            options: {
                @foreach($variant->attributeValues as $val)
                    "{{ $val->attribute->slug }}": "{{ $val->slug }}",
                @endforeach
            }
        },
        @endforeach
    ];

    const selectors = document.querySelectorAll('.variant-selector');
    const priceTag = document.getElementById('product-price');
    const stockTag = document.getElementById('product-stock');
    const cartButton = document.getElementById('cart-button');
    const variantIdInput = document.getElementById('selected-variant-id');

    // 2. Funkcja sprawdzająca, jaki wariant odpowiada wybranym selectom
    function updateVariant() {
        let currentSelection = {};
        let allSelected = true;

        // Zbierz aktualnie wybrane wartości z każdego selecta
        selectors.forEach(select => {
            const attr = select.id.replace('attr-', '');
            currentSelection[attr] = select.value;
            if (!select.value) allSelected = false;
        });

        // Jeśli użytkownik nie wybrał jeszcze wszystkich cech
        if (!allSelected) {
            priceTag.innerText = "Wybierz wszystkie opcje...";
            stockTag.innerText = "";
            cartButton.disabled = true;
            variantIdInput.value = "";
            return;
        }

        // Szukamy wariantu, którego opcje idealnie pasują do wyboru użytkownika
        const matchedVariant = variants.find(variant => {
            return Object.keys(currentSelection).every(key => variant.options[key] === currentSelection[key]);
        });

        // Znalazło wariant -> aktualizujemy widok strony
        if (matchedVariant && matchedVariant.stock > 0) {
            priceTag.innerText = "Cena: " . concat(matchedVariant.price);
            stockTag.innerText = `Dostępność: ${matchedVariant.stock} szt.`;
            stockTag.style.color = "green";
            variantIdInput.value = matchedVariant.id;
            cartButton.disabled = false;
        } else {
            priceTag.innerText = "Niedostępny";
            stockTag.innerText = "Brak wybranej kombinacji w magazynie.";
            stockTag.style.color = "red";
            variantIdInput.value = "";
            cartButton.disabled = true;
        }
    }

    // Nasłuchuj zmian na każdym selectcie
    selectors.forEach(select => select.addEventListener('change', updateVariant));
</script>
```

## Wypas

```php
<div class="product-page">
    <h1>{{ $product->name }}</h1>

    <!-- Dynamiczna cena i stan magazynowy -->
    <h3 id="product-price">Wybierz opcje...</h3>
    <p id="product-stock"></p>

    <!-- Formularz z selectami generowanymi dynamicznie dla każdego atrybutu -->
    <form id="add-to-cart-form">
        @foreach($selectableAttributes as $slug => $attribute)
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="attr-{{ $slug }}"><strong>{{ $attribute['name'] }}:</strong></label>
                <select id="attr-{{ $slug }}" class="variant-selector" name="options[{{ $slug }}]" required>
                    <option value="">-- Wybierz {{ $attribute['name'] }} --</option>
                    @foreach($attribute['values'] as $valSlug => $valName)
                        <option value="{{ $valSlug }}">{{ $valName }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <!-- Ukryte pole przesyłające konkretne ID wariantu do koszyka -->
        <input type="hidden" id="selected-variant-id" name="variant_id" value="">
        <button type="submit" id="cart-button" disabled>Dodaj do koszyka</button>
    </form>
</div>

<script>
    // 1. Bezpieczne zmapowanie wariantów z PHP do tablicy JavaScript
    const variants = [
        @foreach($product->variants as $variant)
        {
            id: {{ $variant->id }},
            price: "{{ $variant->price }} zł",
            stock: {{ $variant->warehouseStock->stock_quantity ?? 0 }},
            options: {
                @foreach($variant->attributeValues as $val)
                    "{{ $val->attribute->slug }}": "{{ $val->slug }}",
                @endforeach
            }
        },
        @endforeach
    ];

    const selectors = document.querySelectorAll('.variant-selector');
    const priceTag = document.getElementById('product-price');
    const stockTag = document.getElementById('product-stock');
    const cartButton = document.getElementById('cart-button');
    const variantIdInput = document.getElementById('selected-variant-id');

    // 2. Logika szukania wariantu pasującego do kombinacji (np. czarny + 16gb + rozmiar 42)
    function updateVariant() {
        let currentSelection = {};
        let allSelected = true;

        // Zbierz aktualnie wybrane wartości ze wszystkich selectów na stronie
        selectors.forEach(select => {
            const attr = select.id.replace('attr-', '');
            currentSelection[attr] = select.value;
            if (!select.value) allSelected = false;
        });

        // Jeśli użytkownik pominął chociaż jeden select (np. wybrał kolor, ale nie rozmiar)
        if (!allSelected) {
            priceTag.innerText = "Wybierz wszystkie opcje...";
            stockTag.innerText = "";
            cartButton.disabled = true;
            variantIdInput.value = "";
            return;
        }

        // Szukamy wariantu, który spełnia WSZYSTKIE wybrane kryteria jednocześnie
        const matchedVariant = variants.find(variant => {
            return Object.keys(currentSelection).every(key => variant.options[key] === currentSelection[key]);
        });

        // Jeśli znaleziono wariant i jego stan magazynowy w MySQL jest większy niż 0
        if (matchedVariant && matchedVariant.stock > 0) {
            priceTag.innerText = "Cena: " + matchedVariant.price;
            stockTag.innerText = `Dostępność: ${matchedVariant.stock} szt.`;
            stockTag.style.color = "green";
            variantIdInput.value = matchedVariant.id; // Ustawiamy ID dla koszyka
            cartButton.disabled = false;
        } else {
            // Kombinacja istnieje w bazie (np. czerwony XL), ale magazyn zgłosił 0 sztuk
            priceTag.innerText = "Niedostępny";
            stockTag.innerText = "Wybrana kombinacja (rozmiar/kolor) jest chwilowo wyprzedana.";
            stockTag.style.color = "red";
            variantIdInput.value = "";
            cartButton.disabled = true;
        }
    }

    // Nasłuchiwanie zmian na każdym z wyrenderowanych selectów
    selectors.forEach(select => select.addEventListener('change', updateVariant));
</script>
```

## Checkout

```php
namespace Modules\Shop\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Shop\app\Models\Variant as ShopVariant;
use Modules\Warehouse\app\Models\Variant as WarehouseVariant;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request)
    {
        // 1. Walidacja przesłanego ID wariantu
        $request->validate([
            'variant_id' => 'required|integer'
        ]);

        $variantId = $request->input('variant_id');

        try {
            // 2. Uruchamiamy bezpieczną transakcję MySQL
            $order = DB::transaction(function () use ($variantId) {

                // Pobieramy wariant ze sklepu, aby poznać jego unikalny UUID oraz cenę
                $shopVariant = ShopVariant::findOrFail($variantId);
                $uuid = $shopVariant->variant_uuid;

                // 3. BLOKADA WIERSZA (FOR UPDATE) w module Warehouse.
                // MySQL blokuje ten konkretny rekord w bazie przed odczytem/zapisem przez inne procesy,
                // dopóki ta transakcja się nie zakończy. Kluczowe przy ostatniej sztuce towaru!
                $warehouseStock = WarehouseVariant::where('variant_uuid', $uuid)
                    ->lockForUpdate()
                    ->firstOrFail();

                // 4. Sprawdzenie, czy towar fizycznie jest dostępny
                if ($warehouseStock->stock_quantity < 1) {
                    throw new \Exception("Przepraszamy, ten wariant został wyprzedany sekundy temu.");
                }

                // 5. Zmniejszamy stan magazynowy w module Warehouse
                $warehouseStock->decrement('stock_quantity', 1);

                // 6. Tworzymy zamówienie w module Shop (Przykładowy kod zapisu)
                // $order = Order::create([ ... ]);

                return [
                    'status' => 'success',
                    'message' => 'Zamówienie zostało złożone pomyślnie!',
                    'variant_uuid' => $uuid,
                    'remaining_stock' => $warehouseStock->stock_quantity
                ];
            });

            return response()->json($order);

        } catch (\Exception $e) {
            // Jeśli wystąpił błąd lub brakło towaru, transakcja automatycznie robi ROLLBACK w MySQL.
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
```

## Route

```php
use Modules\Shop\app\Http\Controllers\CheckoutController;

Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.order');
```

## Blade

```php
<!-- Dodajemy atrybut action i metodę POST -->
<form id="add-to-cart-form" action="{{ route('checkout.order') }}" method="POST">
    @csrf
    <!-- ... selecty z poprzedniego kroku ... -->
    <input type="hidden" id="selected-variant-id" name="variant_id" value="">
    <button type="submit" id="cart-button" disabled>Kupuję teraz</button>
</form>

<script>
// ... dotychczasowy kod JavaScript obsługujący selecty ...

// NOWY KOD: Obsługa wysyłki zamówienia przez AJAX
document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Blokujemy standardowe przeładowanie strony

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Sukces! Zamówienie złożone. Stan magazynu zmniejszony.');
            // Tutaj możesz przekierować klienta na stronę podziękowania:
            // window.location.href = '/order-success';
            updateVariant(); // Odświeżamy stan wariantów na stronie
        } else {
            alert('Błąd: ' + data.message);
        }
    })
    .catch(error => {
        alert('Wystąpił błąd podczas przetwarzania zamówienia.');
    });
});
</script>
```
