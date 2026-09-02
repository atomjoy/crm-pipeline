# Permissions Modules

php artisan make:migration create_permission_user_table --table=permission_user

```php
Schema::create($tableNames['permissions'], function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->string('module')->nullable(); // Tracks the source module
    $table->string('guard_name');
    $table->timestamps();
});
```

## BlogPermissionServiceProvider.php

```php
use Spatie\Permission\Models\Permission;

public function boot()
{
    // Define permissions specific to this module
    $permissions = [
        'blog.create',
        'blog.edit',
        'blog.delete',
    ];

    // Add permissions with module name
    foreach ($permissions as $permission) {
        Permission::firstOrCreate([
            'name' => $permission,
            'module' => 'blog',
            'guard_name' => 'web'
        ]);
    }
}
```

## Route, Controller

```php
// Inside Modules/Admin/Routes/web.php
Route::group(['middleware' => ['can:blog.delete']], function () {
    Route::get('/admin/cleanup-blogs', [AdminBlogController::class, 'purge']);
});

// Inside Modules/Billing/Http/Controllers/InvoiceController.php
public function refund()
{
    // Checking a permission defined by the Auth Module
    if (auth()->user()->can('auth.manage-billing')) {
        // Execute cross-module action
    }
}
```

## Group Permissions

```php
use Spatie\Permission\Models\Permission;

// Groups all permissions by their respective modules for your UI grid
$groupedPermissions = Permission::all()->groupBy('module');
```

# Cross module policy

Model w jednym a polityka w innym module.

## app/Modules/Blog/Models/Article.php

```php
namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'content', 'is_premium'];
}
```

## app/Modules/Billing/Policies/PremiumContentPolicy.php

```php
namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Blog\Models\Article;

class PremiumContentPolicy
{
    public function view(User $user, Article $article): bool
    {
        // Jeśli darmowy – każdy widzi
        if (! $article->is_premium) {
            return true;
        }

        // Logika cross-module: czy użytkownik ma opłacony pakiet premium?
        return $user->subscribed('premium-tier') || $user->can('billing.bypass-paywall');
    }
}
```

## Import bootstrap/app.php (nie dobrze)

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Gate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Rejestracja powiązania polityki między modułami
            Gate::policy(
                \App\Modules\Blog\Models\Article::class,
                \App\Modules\Billing\Policies\PremiumContentPolicy::class
            );
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Import app/Modules/Billing/Providers/BillingServiceProvider.php (good)

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Modules\Blog\Models\Article;
use App\Modules\Billing\Policies\PremiumContentPolicy;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Uruchomienie usług modułu.
     */
    public function boot(): void
    {
        // Moduł Billing rejestruje swoją politykę dla modelu z innego modułu
        Gate::policy(Article::class, PremiumContentPolicy::class);
    }

    /**
     * Rejestracja powiązań w kontenerze.
     */
    public function register(): void
    {
        // Tutaj rejestrujesz inne bindowania, konfiguracje itp.
    }
}
```

## app/Modules/Blog/Controllers/ArticleController.php

```php
namespace App\Modules\Blog\Controllers;

use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController
{
    public function show(Article $article)
    {
        // Nowoczesne wywołanie autoryzacji w Laravel 13
        Gate::authorize('view', $article);

        return view('blog::show', compact('article'));
    }
}
```

# Interface Sharing

```php
namespace App\Modules\Shared\Contracts;

interface PurchasableContent
{
    /**
     * Czy dana treść wymaga subskrypcji premium?
     */
    public function isPremium(): bool;
}
```

## app/Models/User.php

```php
// Check subscription or use cashier
public function subscribed(string $plan): bool
{
    return $this->subscriptions()->where('name', $plan)->where('active', true)->exists();
}
```

## app/Modules/Blog/Models/Article.php

```php
namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Contracts\PurchasableContent;

class Article extends Model implements PurchasableContent
{
    protected $fillable = ['title', 'content', 'is_premium'];

    // Realizacja metody z interfejsu
    public function isPremium(): bool
    {
        return (bool) $this->is_premium;
    }
}
```

## app/Modules/Billing/Policies/PremiumContentPolicy.php

```php
namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Shared\Contracts\PurchasableContent;

class PremiumContentPolicy
{
    /**
     * Sprawdź dostęp do dowolnej treści implementującej PurchasableContent.
     */
    public function view(User $user, PurchasableContent $content): bool
    {
        // Sprawdzamy stan obiektu przez interfejs
        if (! $content->isPremium()) {
            return true;
        }

        // Sprawdzamy subskrypcję użytkownika
        return $user->subscribed('premium-tier') || $user->can('billing.bypass-paywall');
    }
}
```

## app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Modules\Shared\Contracts\PurchasableContent;
use App\Modules\Billing\Policies\PremiumContentPolicy;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Laravel 13 powiąże politykę z dowolną klasą implementującą ten interfejs!
        Gate::policy(PurchasableContent::class, PremiumContentPolicy::class);
    }
}
```

## Controller

```php
namespace App\Modules\Blog\Controllers;

use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController
{
    public function show(Article $article)
    {
        // Działa automatycznie, bo Article implementuje PurchasableContent
        Gate::authorize('view', $article);

        return view('blog::show', compact('article'));
    }
}
```

## tests/Feature/Modules/Billing/PremiumContentPolicyTest.php

```php
use App\Models\User;
use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;
use App\Modules\Shared\Contracts\PurchasableContent;

beforeEach(function () {
    // Upewniamy się, że nasz provider modułu jest załadowany,
    // a Gate poprawnie mapuje interfejs na politykę.
    // (Laravel automatycznie to zrobi, jeśli BillingServiceProvider jest zarejestrowany)
});

test('użytkownik bez subskrypcji może wyświetlić darmową treść', function () {
    // 1. Arrange: Tworzymy użytkownika i darmowy artykuł
    $user = User::factory()->create();
    $freeArticle = Article::factory()->create(['is_premium' => false]);

    // 2. Act & Assert: Sprawdzamy, czy bramka pozwala na wyświetlenie
    $response = Gate::forUser($user)->allows('view', $freeArticle);

    expect($response)->toBeTrue();
});

test('użytkownik bez subskrypcji zostaje zablokowany przy próbie wyświetlenia treści premium', function () {
    // 1. Arrange: Użytkownik i artykuł premium
    $user = User::factory()->create();
    // Mockujemy lub symulujemy, że użytkownik NIE ma subskrypcji
    $user->macro('subscribed', fn () => false);

    $premiumArticle = Article::factory()->create(['is_premium' => true]);

    // 2. Act & Assert: Bramka powinna zabronić dostępu (denies)
    $response = Gate::forUser($user)->denies('view', $premiumArticle);

    expect($response)->toBeTrue();
});

test('użytkownik z aktywną subskrypcją może wyświetlić treść premium', function () {
    // 1. Arrange: Użytkownik z aktywną subskrypcją premium
    $user = User::factory()->create();
    // Nadpisujemy metodę subscribed, aby zwracała true (udajemy aktywną płatność)
    $user->macro('subscribed', function ($plan) {
        return $plan === 'premium-tier';
    });

    $premiumArticle = Article::factory()->create(['is_premium' => true]);

    // 2. Act & Assert: Bramka powinna zezwolić na dostęp
    $response = Gate::forUser($user)->allows('view', $premiumArticle);

    expect($response)->toBeTrue();
});

test('polityka działa poprawnie dla każdego innego obiektu implementującego kontrakt', function () {
    // Ten test udowadnia magię interfejsu. Tworzymy "w locie" anonimowy obiekt,
    // który implementuje PurchasableContent, imitując np. nowy moduł z Podcastami.

    $podcastEpisode = new class implements PurchasableContent {
        public function isPremium(): bool {
            return true; // Udajemy, że podcast jest premium
        }
    };

    $userWithoutSubscription = User::factory()->create();
    $userWithoutSubscription->macro('subscribed', fn () => false);

    // Sprawdzamy, czy nasza polityka z modułu Billing bez problemu obsłużyła
    // obiekt, o którego istnieniu w bazie danych w ogóle nie wie!
    $allowed = Gate::forUser($userWithoutSubscription)->allows('view', $podcastEpisode);

    expect($allowed)->toBeFalse(); // Powinien zablokować, bo to premium, a user nie płaci
});
```

# Subscribe video

Brak zanieczyszczenia core systemu: Plik app/Models/User.php nie wie, że istnieje coś takiego jak subskrypcja. Jeśli usuniesz moduł Billing, aplikacja nadal działa (User nie ma niepotrzebnych relacji).

## DB

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // np. 'premium-tier'
            $table->timestamp('ends_at')->nullable(); // null oznacza bezterminowo, data w przyszłości = aktywna
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

## Model Subskrypcji (Moduł Billing)

app/Modules/Billing/Models/Subscription.php

```php
namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['type', 'ends_at'];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    /**
     * Sprawdza, czy subskrypcja jest wciąż ważna.
     */
    public function isValid(): bool
    {
        return is_null($this->ends_at) || $this->ends_at->isFuture();
    }
}
```

## Dynamiczna relacja i metoda w Providerze (Moduł Billing)

app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Modules\Billing\Models\Subscription;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Definiujemy dynamiczną relację Eloquent dla modelu User
        User::resolveRelationUsing('subscriptions', function (User $user) {
            return $user->hasMany(Subscription::class);
        });

        // 2. Dodajemy prawdziwą metodę sprawdzającą subskrypcję w bazie danych
        User::macro('subscribed', function (string $type): bool {
            // $this wewnątrz macro odnosi się do instancji modelu User
            return $this->subscriptions()
                ->where('type', $type)
                ->get()
                ->stringent() // odfiltrowanie w kolekcji (lub pętli) dla czystości kodu
                ->some(fn ($subscription) => $subscription->isValid());
        });
    }
}
```

## Prawdziwy Feature Test w Pest (Bez Mockowania)

tests/Feature/Modules/Billing/PremiumContentPolicyTest.php

```php
use App\Models\User;
use App\Modules\Blog\Models\Article;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Gate;

test('użytkownik z wygasłą subskrypcją w bazie danych zostaje zablokowany', function () {
    // 1. Tworzymy prawdziwego użytkownika
    $user = User::factory()->create();

    // 2. Tworzymy wpis w bazie danych: subskrypcja wygasła wczoraj
    Subscription::create([
        'user_id' => $user->id,
        'type' => 'premium-tier',
        'ends_at' => now()->subDay(),
    ]);

    $premiumArticle = Article::factory()->create(['is_premium' => true]);

    // 3. Sprawdzamy działanie bramki – powinna odmówić dostępu
    $response = Gate::forUser($user)->denies('view', $premiumArticle);

    expect($response)->toBeTrue();
});

test('użytkownik z aktywną subskrypcją w bazie danych otrzymuje dostęp', function () {
    $user = User::factory()->create();

    // Wpis w bazie: subskrypcja ważna jeszcze przez rok
    Subscription::create([
        'user_id' => $user->id,
        'type' => 'premium-tier',
        'ends_at' => now()->addYear(),
    ]);

    $premiumArticle = Article::factory()->create(['is_premium' => true]);

    // Bramka powinna pozwolić na dostęp
    $response = Gate::forUser($user)->allows('view', $premiumArticle);

    expect($response)->toBeTrue();
});
```

## Optymalizacja Makra w Providerze (Zapobieganie N+1)

app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Modules\Billing\Models\Subscription;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::resolveRelationUsing('subscriptions', function (User $user) {
            return $user->hasMany(Subscription::class);
        });

        // Optymalizacja makra pod kątem braku N+1
        User::macro('subscribed', function (string $type): bool {
            // Tworzymy unikalny klucz dla tego zapytania w pamięci RAM żądania (Runtime Cache)
            $cacheKey = "subscribed_cache_{$type}";

            // Jeśli wynik dla tego użytkownika był już sprawdzany w tym requeście, zwróć go z pamięci
            if (isset($this->$cacheKey)) {
                return $this->$cacheKey;
            }

            // Wydajne zapytanie bazodanowe za pomocą whereExists() / whereRaw
            $isSubscribed = $this->subscriptions()
                ->where('type', $type)
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                          ->orWhere('ends_at', '>', now());
                })
                ->exists(); // Zwraca tylko true/false, bez pobierania całych modeli z bazy

            // Zapisujemy w pamięci podręcznej obiektu User na czas trwania requestu
            return $this->$cacheKey = $isSubscribed;
        });
    }
}
```

## Efekt w Kontrolerze (Moduł Blog)

app/Modules/Blog/Controllers/ArticleController.php

```php
namespace App\Modules\Blog\Controllers;

use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController
{
    public function index()
    {
        // Standardowe pobranie artykułów (np. z paginacją)
        $articles = Article::paginate(15);

        // Opcjonalnie: Filtrowanie kolekcji w kontrolerze bez generowania N+1 zapytań!
        // Pierwsze wywołanie Gate::allows wykona 1 zapytanie EXISTS.
        // Kolejne 14 wywołań pobierze wynik natychmiast z pamięci podręcznej modelu User.
        $visibleArticles = $articles->filter(function ($article) {
            return Gate::allows('view', $article);
        });

        return view('blog::index', [
            'articles' => $articles,
            'visibleArticles' => $visibleArticles
        ]);
    }
}
```

# Przygotowanie modeli (Eager Loading autorów)

app/Modules/Blog/Controllers/ArticleController.php

```php
namespace App\Modules\Blog\Controllers;

use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController
{
    public function index()
    {
        // KLUCZOWE: Pobieramy artykuły od razu z autorami (Eager Loading)
        $articles = Article::with('author')->paginate(20);

        // Pobieramy ID wszystkich autorów, którzy pojawili się na tej konkretnej stronie
        $authorIds = $articles->pluck('author_id')->unique()->toArray();

        // Informujemy moduł Billing, aby załadował subskrypcje dla TYCH konkretnych autorów w tym requeście
        if (auth()->check()) {
            auth()->user()->preloadSubscriptionsForAuthors($authorIds);
        }

        return view('blog::index', compact('articles'));
    }
}
```

## Implementacja w module Billing (Keszowanie na poziomie żądania)

app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Modules\Billing\Models\Subscription;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Metoda do masowego ładowania subskrypcji przed uruchomieniem pętli w widoku
        User::macro('preloadSubscriptionsForAuthors', function (array $authorIds) {
            if (empty($authorIds)) return;

            // Jedno zapytanie do bazy: Pobierz subskrypcje zalogowanego użytkownika dla wskazanych autorów
            $activeSubscriptions = Subscription::where('user_id', $this->id)
                ->whereIn('author_id', $authorIds)
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->pluck('author_id') // Pobieramy tylko ID autorów, których subskrybujemy
                ->toArray();

            // Zapisujemy tablicę ID autorów w pamięci obiektu User na czas tego requestu
            $this->loaded_author_subscriptions = $activeSubscriptions;
        });

        // Dynamiczna metoda sprawdzająca subskrypcję konkretnego autora
        User::macro('isSubscribedToAuthor', function (int $authorId): bool {
            // Jeśli nie załadowaliśmy wcześniej masowo (np. w widoku pojedynczego postu), pobieramy pojedynczo
            if (! isset($this->loaded_author_subscriptions)) {
                return Subscription::where('user_id', $this->id)
                    ->where('author_id', $authorId)
                    ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                    ->exists();
            }

            // Brak zapytania do bazy! Sprawdzamy w przygotowanej wcześniej tablicy w pamięci RAM
            return in_array($authorId, $this->loaded_author_subscriptions);
        });
    }
}
```

## Wykorzystanie relacji w Polityce (Moduł Billing)

app/Modules/Billing/Policies/PremiumContentPolicy.php

```php
namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Shared\Contracts\PurchasableContent;

class PremiumContentPolicy
{
    public function view(User $user, PurchasableContent $content): bool
    {
        if (! $content->isPremium()) {
            return true;
        }

        // Pobieramy ID autora bezpośrednio z modelu (obsługujemy interfejs)
        // Zakładamy, że Twój kontrakt PurchasableContent zawiera metodę getAuthorId()
        $authorId = $content->getAuthorId();

        // Błyskawiczne sprawdzenie w pamięci RAM (0 dodatkowych zapytań do bazy w pętli)
        return $user->isSubscribedToAuthor($authorId);
    }
}
```

# Aktualizacja Kontraktu (Shared)

app/Modules/Shared/Contracts/PurchasableContent.php

```php
namespace App\Modules\Shared\Contracts;

interface PurchasableContent
{
    /**
     * Czy dana treść wymaga subskrypcji premium?
     */
    public function isPremium(): bool;

    /**
     * Pobiera ID autora/twórcy danej treści.
     */
    public function getAuthorId(): int;
}
```

## Implementacja w Modelu (Moduł Blog)

app/Modules/Blog/Models/Article.php

```php
namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Shared\Contracts\PurchasableContent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Article extends Model implements PurchasableContent
{
    protected $fillable = ['title', 'content', 'is_premium', 'user_id'];

    /**
     * Relacja do autora artykułu.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Realizacja kontraktu: Sprawdzenie statusu premium.
     */
    public function isPremium(): bool
    {
        return (bool) $this->is_premium;
    }

    /**
     * Realizacja kontraktu: Pobranie ID autora z zachowaniem pełnego typowania.
     */
    public function getAuthorId(): int
    {
        return (int) $this->user_id;
    }
}
```

## Zastosowanie w Polityce (Moduł Billing)

app/Modules/Billing/Policies/PremiumContentPolicy.php

```php
namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Shared\Contracts\PurchasableContent;

class PremiumContentPolicy
{
    public function view(User $user, PurchasableContent $content): bool
    {
        // 1. Jeśli treść nie jest premium, każdy ma dostęp
        if (! $content->isPremium()) {
            return true;
        }

        // 2. Pobranie ID autora – IDE wie, że to jest 'int'
        $authorId = $content->getAuthorId();

        // 3. Przekazanie int do zoptymalizowanego makra w modelu User
        return $user->isSubscribedToAuthor($authorId);
    }
}
```

## Aktualizacja Makra dla Pełnego Typowania (Moduł Billing)

app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Wymuszamy tablicę liczb całkowitych (array) dla masowego ładowania
        User::macro('preloadSubscriptionsForAuthors', function (array $authorIds): void {
            if (empty($authorIds)) return;

            // Rzutujemy tablicę na inty dla bezpieczeństwa przed zapytaniem
            $cleanIds = array_map('intval', $authorIds);

            $activeSubscriptions = \App\Modules\Billing\Models\Subscription::where('user_id', $this->id)
                ->whereIn('author_id', $cleanIds)
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->pluck('author_id')
                ->toArray();

            $this->loaded_author_subscriptions = array_map('intval', $activeSubscriptions);
        });

        // Wymuszamy 'int' jako argument oraz 'bool' jako typ zwracany
        User::macro('isSubscribedToAuthor', function (int $authorId): bool {
            if (! isset($this->loaded_author_subscriptions)) {
                return \App\Modules\Billing\Models\Subscription::where('user_id', $this->id)
                    ->where('author_id', $authorId)
                    ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                    ->exists();
            }

            return in_array($authorId, $this->loaded_author_subscriptions, true); // true włącza ścisłe porównywanie typów (strict mode)
        });
    }
}
```

# Zabezpieczenie makra przed brakiem danych (Moduł Billing)

app/Modules/Billing/Providers/BillingServiceProvider.php

```php
namespace App\Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use InvalidArgumentException;

class BillingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        User::macro('preloadSubscriptionsForAuthors', function (array $authorIds): void {
            // Zabezpieczenie 1: Brak ID użytkownika (np. niezapisany model)
            if (! $this->id) {
                $this->loaded_author_subscriptions = [];
                return;
            }

            if (empty($authorIds)) return;

            $cleanIds = array_map('intval', $authorIds);

            $activeSubscriptions = \App\Modules\Billing\Models\Subscription::where('user_id', $this->id)
                ->whereIn('author_id', $cleanIds)
                ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->pluck('author_id')
                ->toArray();

            $this->loaded_author_subscriptions = array_map('intval', $activeSubscriptions);
        });

        User::macro('isSubscribedToAuthor', function (int $authorId): bool {
            // Zabezpieczenie 2: Jeśli user nie ma ID, na pewno niczego nie subskrybuje
            if (! $this->id) {
                return false;
            }

            if (! isset($this->loaded_author_subscriptions)) {
                return \App\Modules\Billing\Models\Subscription::where('user_id', $this->id)
                    ->where('author_id', $authorId)
                    ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                    ->exists();
            }

            return in_array($authorId, $this->loaded_author_subscriptions, true);
        });
    }
}
```

## Obsługa gościa w Polityce (Moduł Billing)

app/Modules/Billing/Policies/PremiumContentPolicy.php

```php
namespace App\Modules\Billing\Policies;

use App\Models\User;
use App\Modules\Shared\Contracts\PurchasableContent;

class PremiumContentPolicy
{
    /**
     * Zwróć uwagę na '?User' – to pozwala na obsługę gości w Laravel 13.
     */
    public function view(?User $user, PurchasableContent $content): bool
    {
        // 1. Jeśli treść jest darmowa – gość i zalogowany mają dostęp
        if (! $content->isPremium()) {
            return true;
        }

        // 2. Jeśli treść JEST premium, a użytkownik NIE jest zalogowany – natychmiast blokuj
        if ($user === null) {
            return false;
        }

        // 3. Jeśli użytkownik jest zalogowany, bezpiecznie sprawdzamy subskrypcję w pamięci RAM
        return $user->isSubscribedToAuthor($content->getAuthorId());
    }
}
```

## Bezpieczne wywołanie w Kontrolerze (Moduł Blog)

app/Modules/Blog/Controllers/ArticleController.php

```php
namespace App\Modules\Blog\Controllers;

use App\Modules\Blog\Models\Article;
use Illuminate\Support\Facades\Gate;

class ArticleController
{
    public function index()
    {
        $articles = Article::with('author')->paginate(20);
        $authorIds = $articles->pluck('author_id')->unique()->toArray();

        // Użycie operatora ?-> chroni przed błędem "Call to a member function on null"
        // Jeśli użytkownik jest gościem, ta linijka po prostu się nie wykona.
        auth()->user()?->preloadSubscriptionsForAuthors($authorIds);

        return view('blog::index', compact('articles'));
    }
}
```
