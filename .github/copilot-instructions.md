# Wave SaaS Application - AI Agent Instructions

## Project Overview

**Wave** is a Laravel-based SaaS starter kit providing subscription billing, user management, themes, and plugins. Built on Laravel 12, Filament v4, Livewire v3, and Tailwind v4.

### Core Architecture

- **Wave Framework Layer** (`wave/`): Core SaaS functionality, models, routes, views, migrations
  - `WaveServiceProvider`: Registers middleware, Livewire components, Blade directives, theme colors
  - `Wave\User` base model: Handles subscriptions, roles, profile key-values
  - Custom helpers in `wave/src/Helpers/`: `setting()`, `blade()`, `wave_version()`
- **Application Layer** (`app/`): User's custom code extending Wave
  - `App\Models\User` extends `Wave\User`
  - Filament resources in `app/Filament/Resources/`
- **Themes** (`resources/themes/`): Frontend templates (currently using `anchor` theme)
  - Active theme configured in `theme.json`
  - Vite builds theme-specific assets from `resources/themes/{active}/assets/`
- **Plugins** (`resources/plugins/`): Modular feature extensions auto-loaded by `PluginServiceProvider`

### Key Integration Points

**Billing & Subscriptions:**
- Provider: Stripe or Paddle (configured via `BILLING_PROVIDER` env var, `config/wave.php`)
- Controllers: `Wave\Http\Controllers\SubscriptionController`, `Billing\Webhooks\{Stripe,Paddle}Webhook`
- Middleware: `Subscribed` checks subscription status
- Blade directives: `@subscriber`, `@notsubscriber`, `@subscribed('plan-name')`

**Authentication & Authorization:**
- Spatie Laravel Permission for roles
- Middleware: `InstallMiddleware` (redirects if no DB connection), `ThemeDemoMiddleware` (demo mode)
- Blade directives: `@admin`, `@subscriber`
- User impersonation via lab404/laravel-impersonate

**Themes & Frontend:**
- Dynamic theme loading based on `theme.json` or `theme` cookie (demo mode)
- Vite configuration reads active theme from `theme.json`
- Tailwind v4 with CSS-first config (`@import "tailwindcss"` not `@tailwind`)
- Filament components aliased: `<x-dropdown>`, `<x-dropdown.list>`, `<x-dropdown.list.item>`

**Routing:**
- `Wave::routes()` in `routes/web.php` registers all Wave routes from `wave/routes/web.php`
- Laravel Folio for page-based routing (check `wave/resources/views/pages/` and `resources/views/pages/`)
- Livewire URLs middleware for clean URLs

## Essential Development Workflows

### Development Environment
```bash
composer run dev  # Starts server, queue, logs (Pail), and Vite concurrently
php artisan serve # Dev server only
npm run dev       # Vite hot reload
npm run build     # Production build
```

### Testing
```bash
php artisan test                              # All tests (Pest)
php artisan test tests/Feature/ExampleTest.php # Specific file
php artisan test --filter=testName            # Filter by name
```

### Wave-Specific Commands
```bash
php artisan wave:cancel-expired-subscriptions # Subscription management
php artisan wave:create-plugin                # Plugin scaffolding
php artisan folio:list                        # List Folio routes
```

### Caching Strategy
Wave extensively uses caching for performance:
- User subscription/admin status: 5-10 minutes
- Plans: 30 minutes (`Plan::getActivePlans()`, `Plan::getByName()`)
- Categories: 1 hour (`Category::getAllCached()`)
- Settings: Permanent (`setting('key')` helper)
- Theme colors: 1 hour
- Plugin lists: 1 hour

**Always clear caches** when updating:
- `$user->clearUserCache()` after role changes
- `Plan::clearCache()` after plan updates
- `Category::clearCache()` after category changes

### Theme Development
1. Active theme set in `theme.json` (e.g., `{"name": "anchor"}`)
2. Vite reads this file and builds from `resources/themes/{name}/assets/`
3. Demo mode allows theme switching via `theme` cookie
4. Theme-specific colors configured in `WaveServiceProvider::setDefaultThemeColors()`

## Project-Specific Conventions

### Models & Database
- **Always** use proper Eloquent relationships with return type hints
- User model extends `Wave\User` which includes subscription methods: `subscriber()`, `subscribedToPlan($plan)`, `clearUserCache()`
- Use `Wave\Traits\HasProfileKeyValues` for dynamic user profile fields
- Prefer cached model methods: `Plan::getActivePlans()` over raw queries

### Filament Admin
- Resources in `app/Filament/Resources/` follow Filament v4 structure
- Test Filament with `livewire(ResourceClass::class)->assertCanSeeTableRecords($records)`
- All actions extend `Filament\Actions\Action` (no `Filament\Tables\Actions`)
- Schema components in `Filament\Schemas\Components` (Grid, Section, Fieldset moved in v4)

### Livewire & Volt
- Wave uses Livewire v3 with Volt for single-file components
- Check existing Volt components to determine if functional or class-based
- Livewire components registered in `WaveServiceProvider::loadLivewireComponents()`
- Use `wire:model.live` for real-time updates (deferred by default in v3)

### Blade & Views
- Custom Wave directives: `@admin`, `@subscriber`, `@subscribed('plan')`, `@home`
- Themes use `theme::view-name` notation
- Wave components in `wave/resources/views/components/` (anonymous)
- Filament components aliased for easier use

### Configuration
- **Never** use `env()` outside config files
- Use `setting('key', 'default')` helper for dynamic settings from database
- Billing provider: `config('wave.billing_provider')` (stripe or paddle)
- Primary color: `config('wave.primary_color')` (dynamic in demo mode)

### Testing Patterns
- All tests use Pest (`php artisan make:test --pest`)
- Use factories, check for custom states before manual setup
- Specific assertions: `assertForbidden()`, `assertSuccessful()` not `assertStatus()`
- Datasets for validation tests to reduce duplication

## Critical "Why" Architecture Decisions

**Why Wave extends Laravel instead of wrapping it:**
- Allows developers to use standard Laravel patterns
- Wave models/controllers can be overridden in `app/` layer
- Upgrades easier (composer update wave dependencies)

**Why themes are in `resources/` not `public/`:**
- Allows Blade templating and server-side logic
- Vite compiles theme assets to `public/build/`
- Demo mode can switch themes dynamically via middleware

**Why extensive caching with fallbacks:**
- Performance critical for subscription checks (every request)
- Fallbacks handle package discovery, CI/CD, cache service unavailability
- Prevents errors during `composer install` or migrations

**Why both Folio and traditional routes:**
- Wave core uses traditional routes (`wave/routes/web.php`)
- Application layer can use Folio for simpler page routing
- Provides flexibility for different routing needs

## Common Gotchas

1. **Vite manifest error**: Run `npm run build` or ask user to run `npm run dev`/`composer run dev`
2. **Cache issues during development**: Clear specific caches (`$user->clearUserCache()`) not just `php artisan cache:clear`
3. **Demo mode theming**: Check for `WAVE_DEMO=true` and `theme` cookie handling
4. **Subscription middleware**: Route must be behind `auth` before using `subscribed` middleware
5. **Filament v4 changes**: Use `Filament\Actions\Action` not `Filament\Tables\Actions`, schema components moved
6. **Tailwind v4**: Use `@import "tailwindcss"` not `@tailwind` directives

## Plugin Development

### Plugin Structure
Plugins live in `resources/plugins/` and auto-load via `PluginServiceProvider`. Each plugin:
- Extends `Wave\Plugins\Plugin` (which extends `ServiceProvider`)
- Must implement `getPluginInfo(): array` method
- Uses StudlyCase naming: `MyPlugin/MyPluginPlugin.php`
- Can optionally implement `postActivation()` hook

### Creating Plugins
```bash
php artisan wave:create-plugin
# Creates: resources/plugins/YourPlugin/
#   - YourPluginPlugin.php (main class)
#   - routes.php (plugin routes)
#   - Component.php (Livewire component)
```

### Plugin Registration
Plugins are auto-discovered and loaded from `resources/plugins/installed.json`:
```php
// PluginManager auto-loads from installed.json
$pluginClass = "Wave\\Plugins\\{$studlyPluginName}\\{$studlyPluginName}Plugin";
```

### Example Plugin Class
```php
namespace Wave\Plugins\MyFeature;

use Wave\Plugins\Plugin;

class MyFeaturePlugin extends Plugin
{
    protected $name = 'My Feature';

    public function register(): void
    {
        // Register services, bindings
    }

    public function boot(): void
    {
        // Load routes, views, migrations
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/views', 'myfeature');
    }

    public function getPluginInfo(): array
    {
        return [
            'name' => $this->name,
            'version' => '1.0.0',
            'description' => 'My custom feature',
        ];
    }
}
```

## Wave-Specific Models & Patterns

### Core Wave Models
- **`Wave\Plan`**: Subscription plans with role associations
  - `Plan::getActivePlans()` - Returns cached active plans (30min cache)
  - `Plan::getByName($name)` - Returns cached plan by name
  - `Plan::clearCache()` - Clears plan-related caches
  - `role()` relationship to Spatie `Role` model

- **`Wave\Category`**: Blog/content categories
  - `Category::getAllCached()` - Returns all categories (1hr cache)
  - `Category::clearCache()` - Clears category cache
  - `posts()` relationship to `Wave\Post`

- **`Wave\User`** (extended by `App\Models\User`):
  - Implements `JWTSubject` for API authentication
  - Implements `FilamentUser`, `HasAvatar` for admin panel
  - Uses `HasRoles`, `Impersonate`, `Notifiable` traits
  - `subscriptions()` - HasMany relationship to `Wave\Subscription`
  - `subscriber()` - Check if user has active subscription
  - `subscribedToPlan($plan)` - Check specific plan subscription
  - `onTrial()` - Check if user is on trial period
  - `clearUserCache()` - Clear user-specific caches

- **`Wave\Subscription`**: Billing subscription records
  - Supports both Stripe and Paddle
  - `plan()` relationship
  - Status tracking: active, cancelled, expired

### Model Cache Patterns
Wave models implement cache-first patterns with fallbacks:
```php
// Always check if cache is available
if (app()->bound('cache')) {
    try {
        return Cache::remember($key, $ttl, function() {
            return Model::query()->get();
        });
    } catch (Exception $e) {
        // Fallback to direct query
    }
}
return Model::query()->get();
```

**Why**: Ensures compatibility with CI/CD, package discovery, and cache service unavailability.

### Profile Key-Values
Use `Wave\Traits\HasProfileKeyValues` for dynamic user fields:
```php
// Configured in config/wave.php
'profile_fields' => [
    'about' => [
        'label' => 'About',
        'field' => 'textarea',
        'validation' => 'required',
    ],
]

// Access via trait methods
$user->keyValue('about'); // Get value
$user->keyValues(); // Get all
```

## API Integration & JWT Authentication

### API Authentication
Wave uses `tymon/jwt-auth` for API authentication with dual token support:

**1. JWT Tokens** (long-lived, from login):
```bash
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
# Returns: { "access_token": "...", "token_type": "bearer", "expires_in": 3600 }
```

**2. API Keys** (permanent, from admin panel):
```bash
POST /api/token
{
  "key": "your-api-key-from-database"
}
# Returns JWT token generated from API key
```

### API Routes
Located in `wave/routes/api.php`:
- `POST /api/login` - Email/password authentication
- `POST /api/register` - User registration
- `POST /api/logout` - Invalidate token
- `POST /api/refresh` - Refresh expired token
- `POST /api/token` - Exchange API key for JWT

### Middleware
- `token_api` middleware validates both JWT and API keys
- API keys converted to JWT tokens via `JWTAuth::fromUser($apiKey->user)`
- Registered in `WaveServiceProvider`: `$this->app->router->aliasMiddleware('token_api', TokenMiddleware::class)`

### Using API in Your App
```php
// Protect API routes with auth:api middleware
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Or use token_api for dual support
Route::middleware('token_api')->group(function () {
    // Accepts both JWT and API keys
});
```

### API Key Model
`Wave\ApiKey` stores permanent API keys:
- Belongs to user
- Tracks `last_used_at`
- Configurable expiration in `config/wave.php`: `'key_token_expires' => 1`

### JWT Configuration
Located in `config/jwt.php` (standard tymon/jwt-auth config):
- Token TTL configurable
- Refresh token support
- Multiple guard support

## Testing Wave Applications

### Test Structure
- All tests use Pest (`tests/Pest.php` configures `Tests\TestCase`)
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- Test database uses RefreshDatabase (commented out in Pest.php by default)

### Wave-Specific Test Patterns

**Testing Subscription Features:**
```php
use App\Models\User;
use Wave\Plan;

it('allows subscribed users to access premium content', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['name' => 'premium']);
    
    // Create subscription for user
    $user->subscriptions()->create([
        'plan_id' => $plan->id,
        'status' => 'active',
        'billable_type' => 'user',
        'billable_id' => $user->id,
    ]);
    
    $this->actingAs($user)
        ->get('/premium-content')
        ->assertSuccessful();
});

it('redirects non-subscribers from premium content', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get('/premium-content')
        ->assertRedirect(); // Or assertForbidden()
});
```

**Testing with Blade Directives:**
```php
it('shows subscriber-only content to subscribers', function () {
    $user = User::factory()->create();
    // Set up subscription...
    
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSee('Premium Feature')
        ->assertDontSee('Upgrade Now');
});
```

**Testing Filament Resources:**
```php
use App\Filament\Resources\PlanResource;
use Livewire\Livewire;

it('can list plans in admin', function () {
    $admin = User::factory()->admin()->create();
    $plans = Plan::factory()->count(3)->create();
    
    $this->actingAs($admin);
    
    Livewire::test(PlanResource\Pages\ListPlans::class)
        ->assertCanSeeTableRecords($plans);
});

it('can create a plan', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::create(['name' => 'premium']);
    
    $this->actingAs($admin);
    
    Livewire::test(PlanResource\Pages\CreatePlan::class)
        ->fillForm([
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 29.99,
            'role_id' => $role->id,
        ])
        ->call('create')
        ->assertHasNoErrors();
    
    $this->assertDatabaseHas('plans', ['name' => 'Pro']);
});
```

**Testing API Endpoints:**
```php
it('authenticates via API with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    $response->assertSuccessful()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
});

it('protects API routes with token middleware', function () {
    $this->getJson('/api/user')
        ->assertUnauthorized();
    
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user')
        ->assertSuccessful()
        ->assertJson(['email' => $user->email]);
});
```

**Testing Cache Behavior:**
```php
use Illuminate\Support\Facades\Cache;

it('caches active plans', function () {
    Plan::factory()->count(3)->create(['active' => 1]);
    
    Cache::shouldReceive('remember')
        ->once()
        ->with('wave_active_plans', 1800, Closure::class)
        ->andReturn(Plan::where('active', 1)->get());
    
    Plan::getActivePlans();
});

it('clears plan cache when updating', function () {
    $plan = Plan::factory()->create();
    
    Plan::clearCache();
    
    expect(Cache::has('wave_active_plans'))->toBeFalse();
});
```

### Test Factories
Create Wave model factories in `database/factories/`:
```php
// PlanFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wave\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;
    
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 9, 99),
            'active' => 1,
        ];
    }
    
    public function inactive(): static
    {
        return $this->state(['active' => 0]);
    }
}
```

### Running Tests
```bash
php artisan test                              # All tests
php artisan test --filter=subscription        # Filter by name
php artisan test tests/Feature/BillingTest.php # Specific file
php artisan test --parallel                   # Parallel execution
```

## Migration Patterns

### Wave Migration Structure
Wave migrations in `wave/database/migrations/` are loaded automatically by `WaveServiceProvider`:
```php
$this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations'));
```

### App-Level Migrations
Standard Laravel migrations in `database/migrations/`:
- Use anonymous class syntax (Laravel 11+)
- Always use `return new class extends Migration`

### Common Wave Migration Patterns

**Adding Columns to Wave Tables:**
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->boolean('email_verified')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email_verified']);
        });
    }
};
```

**Creating Plugin Tables:**
```php
// In plugin migration
Schema::create('my_plugin_data', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('setting_key');
    $table->text('setting_value')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'setting_key']);
});
```

**Modifying Existing Columns:**
```php
// IMPORTANT: Include ALL previous column attributes
Schema::table('plans', function (Blueprint $table) {
    // Wrong - will lose other attributes:
    // $table->decimal('price', 10, 2)->change();
    
    // Correct - preserve all attributes:
    $table->decimal('price', 10, 2)
        ->nullable()
        ->default(0)
        ->after('slug')
        ->change();
});
```

**Wave-Specific Foreign Keys:**
```php
// Reference Wave models using morph map
Schema::create('reactions', function (Blueprint $table) {
    $table->id();
    $table->morphs('reactable'); // Creates reactable_id, reactable_type
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type'); // like, love, etc.
    $table->timestamps();
});

// Morph map configured in WaveServiceProvider:
Relation::morphMap([
    'users' => config('wave.user_model'),
    'post' => Wave\Post::class,
]);
```

**Subscription-Related Tables:**
```php
// Example: Adding metadata to subscriptions
Schema::table('subscriptions', function (Blueprint $table) {
    $table->json('metadata')->nullable()->after('status');
    $table->timestamp('cancelled_at')->nullable();
    $table->string('cancellation_reason')->nullable();
});
```

### Migration Best Practices
1. **Always provide `down()` method** for rollback capability
2. **Use `after()` for column positioning** to match Wave's schema
3. **Add indexes** for foreign keys and frequently queried columns
4. **Use morphs** when relating to multiple model types
5. **Test migrations** with `php artisan migrate:fresh` before committing
6. **Check Wave migrations** in `wave/database/migrations/` before duplicating

### Seeding Wave Data
```php
// database/seeders/DatabaseSeeder.php
use Wave\Plan;
use Spatie\Permission\Models\Role;

public function run(): void
{
    // Create roles first (Wave uses Spatie)
    $role = Role::create(['name' => 'premium']);
    
    // Create plans
    Plan::create([
        'name' => 'Pro',
        'slug' => 'pro',
        'description' => 'Professional plan',
        'price' => 29.99,
        'role_id' => $role->id,
        'active' => 1,
    ]);
    
    // Clear caches after seeding
    Plan::clearCache();
}
```

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.1
- filament/filament (FILAMENT) - v4
- laravel/folio (FOLIO) - v1
- laravel/framework (LARAVEL) - v12
- laravel/mcp (MCP) - v0
- laravel/prompts (PROMPTS) - v0
- laravel/socialite (SOCIALITE) - v5
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/dusk (DUSK) - v8
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== filament/core rules ===

## Filament
- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan
- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features
- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships
- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>


## Testing
- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>


=== filament/v4 rules ===

## Filament 4

### Important Version 4 Changes
- File visibility is now `private` by default.
- The `deferFilters` method from Filament v3 is now the default behavior in Filament v4, so users must click a button before the filters are applied to the table. To disable this behavior, you can use the `deferFilters(false)` method.
- The `Grid`, `Section`, and `Fieldset` layout components no longer span all columns by default.
- The `all` pagination page method is not available for tables by default.
- All action classes extend `Filament\Actions\Action`. No action classes exist in `Filament\Tables\Actions`.
- The `Form` & `Infolist` layout components have been moved to `Filament\Schemas\Components`, for example `Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.
- A new `Repeater` component for Forms has been added.
- Icons now use the `Filament\Support\Icons\Heroicon` Enum by default. Other options are available and documented.

### Organize Component Classes Structure
- Schema components: `Schemas/Components/`
- Table columns: `Tables/Columns/`
- Table filters: `Tables/Filters/`
- Actions: `Actions/`


=== folio/core rules ===

## Laravel Folio

- Laravel Folio is a file based router. With Laravel Folio, a new route is created for every Blade file within the configured Folio directory. For example, pages are usually in in `resources/views/pages/` and the file structure determines routes:
    - `pages/index.blade.php` → `/`
    - `pages/profile/index.blade.php` → `/profile`
    - `pages/auth/login.blade.php` → `/auth/login`
- You may list available Folio routes using `php artisan folio:list` or using Boost's `list-routes` tool.

### New Pages & Routes
- Always create new `folio` pages and routes using `artisan folio:page [name]` following existing naming conventions.


<code-snippet name="Example folio:page Commands for Automatic Routing" lang="shell">
    // Creates: resources/views/pages/products.blade.php → /products
    php artisan folio:page 'products'

    // Creates: resources/views/pages/products/[id].blade.php → /products/{id}
    php artisan folio:page 'products/[id]'
</code-snippet>


- Add a 'name' to each new Folio page at the very top of the file so it has a named route available for other parts of the codebase to use.


<code-snippet name="Adding named route to Folio page" lang="php">
use function Laravel\Folio\name;

name('products.index');
</code-snippet>


### Support & Documentation
- Folio supports: middleware, serving pages from multiple paths, subdomain routing, named routes, nested routes, index routes, route parameters, and route model binding.
- If available, use Boost's `search-docs` tool to use Folio to its full potential and help the user effectively.


<code-snippet name="Folio Middleware Example" lang="php">
use function Laravel\Folio\{name, middleware};

name('admin.products');
middleware(['auth', 'verified', 'can:manage-products']);
?>
</code-snippet>


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== mcp/core rules ===

## Laravel MCP

- MCP (Model Context Protocol) is very new. You must use the `search-docs` tool to get documentation for how to write and test Laravel MCP servers, tools, resources, and prompts effectively.
- MCP servers need to be registered with a route or handle in `routes/ai.php`. Typically, they will be registered using `Mcp::web()` to register a HTTP streaming MCP server.
- Servers are very testable - use the `search-docs` tool to find testing instructions.
- Do not run `mcp:start`. This command hangs waiting for JSON RPC MCP requests.
- Some MCP clients use Node, which has its own certificate store. If a user tries to connect to their web MCP server locally using https://, it could fail due to this reason. They will need to switch to http:// during local development.


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt. There is documentation available for it.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`
- Volt is a **class-based** and **functional** API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to co-exist in the same file
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@volt` directive.
- You must check existing Volt components to determine if they're functional or class based. If you can't detect that, ask the user which they prefer before writing a Volt component.

### Volt Functional Component Example

<code-snippet name="Volt Functional Component Example" lang="php">
@volt
<?php
use function Livewire\Volt\{state, computed};

state(['count' => 0]);

$increment = fn () => $this->count++;
$decrement = fn () => $this->count--;

$double = computed(fn () => $this->count * 2);
?>

<div>
    <h1>Count: {{ $count }}</h1>
    <h2>Double: {{ $this->double }}</h2>
    <button wire:click="increment">+</button>
    <button wire:click="decrement">-</button>
</div>
@endvolt
</code-snippet>


### Volt Class Based Component Example
To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:


<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>


### Testing Volt & Volt Components
- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
</code-snippet>


<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
    $user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});
</code-snippet>


### Common Patterns


<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
    fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>

<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>

<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>
