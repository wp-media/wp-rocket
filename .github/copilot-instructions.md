# WP Rocket Copilot Instructions

## Context

You are an expert WordPress plugin developer specializing in performance optimization working on WP Rocket. Your task is to provide SOLID, well-tested, and maintainable code that follows WP Rocket's established architectural patterns.

## Core Principles

### Code Quality Standards
- **SOLID Principles**: Write clean, single-responsibility code that's easy to test and maintain
- **DRY (Don't Repeat Yourself)**: Extract reusable logic into dedicated methods or classes
- **KISS (Keep It Simple)**: Prefer simple, clear solutions over complex abstractions
- **Early Returns**: Use guard clauses and bail out early instead of nested conditionals
- **Type Safety**: Always use strict types (`declare(strict_types=1)`) in new PHP files

### Design Patterns
You MUST use the following architectural patterns consistently:

1. **Subscriber Pattern** for WordPress hooks
   - All WordPress hooks (`add_action`, `add_filter`) MUST be registered via Subscribers
   - Subscribers implement `Subscriber_Interface` and define `get_subscribed_events()`
   - Avoid using `add_action`/`add_filter` directly in code
   - **Why**: Centralized hook management makes testing easier, provides clear documentation of all hooks in one place, and enables hook priority management without scattered magic numbers

2. **Dependency Injection** via Container
   - All dependencies MUST be injected through constructor
   - Use the League Container for service registration
   - Services are registered in ServiceProvider classes
   - **Why**: Eliminates tight coupling, makes testing possible with mocks, and provides a single source of truth for object creation and configuration

3. **Service Provider Pattern** for module organization
   - Each feature module has a ServiceProvider extending `AbstractServiceProvider`
   - ServiceProviders register all module services in the container
   - Must implement `provides()` and `register()` methods
   - **Why**: Encapsulates feature initialization, enables lazy loading of services, and provides clear boundaries between modules for better maintainability

### Code Organization Best Practices

**Abstraction Hierarchy:**
- Use **Interfaces** for contracts and polymorphism
- Use **Abstract Classes** for shared implementation with required overrides
- Use **Traits** for reusable behavior across unrelated classes
- Prefer composition over inheritance

**Method Extraction:**
- Keep methods short (ideally < 20 lines)
- One method = one responsibility
- Extract complex logic into private methods with descriptive names
- Public methods should read like a table of contents

**Example of Good Method Extraction:**
```php
// ✅ GOOD: Clear, single-responsibility methods
public function optimize_css( string $html ): string {
    if ( ! $this->should_optimize() ) {
        return $html;
    }
    
    $css_files = $this->extract_css_files( $html );
    $optimized = $this->process_css_files( $css_files );
    
    return $this->inject_optimized_css( $html, $optimized );
}

// Each private method has a clear, single purpose
private function should_optimize(): bool { /* ... */ }
private function extract_css_files( string $html ): array { /* ... */ }
private function process_css_files( array $files ): array { /* ... */ }
private function inject_optimized_css( string $html, array $css ): string { /* ... */ }
```

**Conditional Logic:**
```php
// ✅ GOOD: Early return pattern (guard clauses)
public function process_data( $data ) {
    if ( empty( $data ) ) {
        return;
    }
    
    if ( ! $this->is_valid( $data ) ) {
        return;
    }
    
    // Main logic here - reduced nesting, easier to read
    $processed = $this->transform( $data );
    $this->save( $processed );
}

// ❌ BAD: Nested conditionals (arrow anti-pattern)
public function process_data( $data ) {
    if ( ! empty( $data ) ) {
        if ( $this->is_valid( $data ) ) {
            // Main logic buried in nesting
            $processed = $this->transform( $data );
            if ( $processed ) {
                $this->save( $processed );
            }
        }
    }
}
```

### Documentation Standards

**PHPDoc Blocks:**
- All public methods MUST have complete PHPDoc
- Include `@since`, `@param`, `@return`, and `@throws` when applicable
- Document complex private methods
- Keep inline comments minimal and meaningful

**Code Comments:**
- Avoid obvious comments that restate the code
- Use comments to explain **why**, not **what**
- Update comments when code changes
- Remove commented-out code before committing

### Project-Specific Practices

**Before Creating New Code:**
1. Search for existing implementations using semantic search
2. Check if similar functionality exists elsewhere in the project
3. Consider refactoring existing code instead of duplicating
4. Follow the directory structure of similar features

**Refactoring Legacy Code:**
- When touching old code, improve it incrementally
- Add tests before refactoring
- Maintain backward compatibility unless explicitly breaking
- Update related documentation

All new code MUST be tested and MUST NOT break existing tests.

## Decision Making Guide

### When to Use: Interface vs Abstract Class vs Trait

**Use an Interface when:**
- Defining a contract that multiple unrelated classes must implement
- You need polymorphism (different classes, same behavior contract)
- Example: `Subscriber_Interface` - any class can subscribe to events
- Real example: `inc/Engine/Common/PerformanceHints/WarmUp/APIClient.php` implements `APIClientInterface`

```php
// ✅ Interface: Contract for different implementations
interface CacheInterface {
    public function get( string $key );
    public function set( string $key, $value, int $expiration );
    public function delete( string $key ): bool;
}
```

**Use an Abstract Class when:**
- Sharing common implementation across related classes
- You need to enforce method implementation while providing base functionality
- Classes form an inheritance hierarchy (is-a relationship)
- Example: `AbstractTable` - all database tables share common behavior

```php
// ✅ Abstract: Shared implementation with required overrides
abstract class AbstractTable {
    // Shared implementation
    protected function maybe_upgrade() { /* ... */ }
    
    // Must be implemented by children
    abstract protected function set_schema();
}
```

**Use a Trait when:**
- Sharing behavior across unrelated classes (not an is-a relationship)
- Code reuse without inheritance
- Example: `LoggerAware` - adds logging capability to any class
- Real example: `inc/Logger/LoggerAware.php` used across multiple unrelated classes

```php
// ✅ Trait: Reusable behavior across unrelated classes
trait LoggerAware {
    protected $logger;
    
    public function set_logger( LoggerInterface $logger ) {
        $this->logger = $logger;
    }
}
```

**Decision Tree:**
```
Need to share code?
├─ Yes → Is it an "is-a" relationship?
│  ├─ Yes → Use Abstract Class
│  └─ No → Use Trait
└─ No → Need a contract?
   └─ Yes → Use Interface
```

### When to Create: New ServiceProvider vs Extend Existing

**Create a NEW ServiceProvider when:**
- Building a completely new feature/module (e.g., new optimization feature)
- The feature has its own namespace directory (e.g., `inc/Engine/MyNewFeature/`)
- The feature will have multiple services (Subscriber, Controller, Context, etc.)
- Example: `inc/Engine/Admin/PerformanceMonitoring/ServiceProvider.php`

**Extend an EXISTING ServiceProvider when:**
- Adding a small enhancement to an existing feature
- Adding a single new service to an existing module
- The code logically belongs to the existing feature
- Example: Adding a new subscriber to the Cache module

**Decision Criteria:**
```
Is this a new major feature?
├─ Yes → Create new ServiceProvider + directory structure
└─ No → Does it fit logically into existing module?
    ├─ Yes → Add to existing ServiceProvider
    └─ No → Create new ServiceProvider
```

### When to Use: Subscriber Pattern

**ALWAYS use Subscriber when:**
- You need to hook into WordPress actions/filters
- Building any feature that responds to WordPress events
- Even for a single hook - maintains consistency

**Example of when simple hook SEEMS easier but DON'T do it:**
```php
// ❌ NEVER do this, even for "just one hook"
add_action( 'init', [ $this, 'init_feature' ] );

// ✅ ALWAYS use Subscriber, even for one hook
class MySubscriber implements Subscriber_Interface {
    public static function get_subscribed_events(): array {
        return [ 'init' => 'init_feature' ];
    }
}
```

**Why this matters:**
- Testability: Subscribers can be unit tested without WordPress
- Discoverability: All hooks in one place via `get_subscribed_events()`
- Consistency: Same pattern everywhere makes code predictable
- Flexibility: Easy to add/remove/reorder hooks

### When to Use: Unit Test vs Integration Test

**Use Unit Tests when:**
- Testing business logic in isolation
- Class has injected dependencies that can be mocked
- No WordPress functions required
- Testing data transformations, calculations, validations
- Example: Testing a data processor that doesn't touch database or WordPress

**Use Integration Tests when:**
- Testing WordPress hook interactions
- Database operations (using custom tables)
- Testing with actual WordPress functions
- End-to-end feature testing
- Example: Testing that a Subscriber correctly registers hooks

**Quick Decision:**
```
Does it need WordPress or database?
├─ Yes → Integration Test
└─ No → Can dependencies be mocked?
    ├─ Yes → Unit Test
    └─ No → Integration Test
```


## Project Overview
WP Rocket is a WordPress performance optimization plugin using modern PHP architecture with dependency injection, event-driven design, and comprehensive testing. The codebase follows PSR-4 autoloading with a service provider pattern for modularity.

This project is the WordPress plugin for WP Rocket.

WP Rocket is a plugin that helps you to speed up your website by caching and compressing your assets but also by optimizing your page structure.


## Core Architecture

### Service Provider Pattern
- All features organized as **Service Providers** extending `AbstractServiceProvider`
- Each provider registers services in DI container via `register()` method
- Each provider must declare `$provides` array listing all service IDs
- Each provider must implement `provides(string $id): bool` method
- Services accessed through global container: `apply_filters('rocket_container', null)->get('service_name')`
- Example: `inc/Engine/Admin/PerformanceMonitoring/ServiceProvider.php` shows typical registration patterns

**Service Registration Examples:**
```php
// Shared services (singletons)
$this->getContainer()->addShared('service_name', ClassName::class)
    ->addArguments(['dependency1', 'dependency2']);

// Regular services (new instance each time)
$this->getContainer()->add('service_name', ClassName::class)
    ->addArgument('dependency');

// Using StringArgument for literal values
$this->getContainer()->add('service_name', ClassName::class)
    ->addArgument(new StringArgument('/path/to/template'));
```

### Subscriber Pattern for WordPress Hooks
All WordPress hooks MUST be managed through Subscribers, never directly:

```php
// Subscriber implementation
class MySubscriber implements Subscriber_Interface {
    public static function get_subscribed_events(): array {
        return [
            'init'                    => 'on_init',
            'wp_enqueue_scripts'      => [ 'enqueue_assets', 10 ],
            'save_post'               => [ 'save_meta', 10, 2 ],
            'admin_menu'              => [
                [ 'add_menu_page', 9 ],
                [ 'add_submenu_page', 10 ],
            ],
        ];
    }
}

// Registration in ServiceProvider
$this->getContainer()->addShared('my_subscriber', MySubscriber::class)
    ->addArguments(['dependency1', 'dependency2']);

// Activation in Plugin.php via Event_Manager
$this->event_manager->add_subscriber($this->container->get('my_subscriber'));
```

**Real-world examples:**
- `inc/Engine/Optimization/RUCSS/Frontend/Subscriber.php` - Frontend CSS optimization hooks
- `inc/Engine/Admin/Settings/Subscriber.php` - Admin settings page hooks
- `inc/Engine/Cache/Subscriber.php` - Cache management hooks

**Event Array Formats:**
- Simple: `'hook_name' => 'method_name'` (priority 10, 1 arg)
- With priority: `'hook_name' => ['method_name', 20]`
- With args: `'hook_name' => ['method_name', 10, 3]`
- Multiple callbacks: `'hook_name' => [['method1', 9], ['method2', 11]]`

### Key Architectural Components
- **Engine**: Core functionality modules (Cache, Optimization, Media, etc.)
- **Dependencies**: Third-party libraries managed by Mozart for namespace prefixing
- **Database Layer**: BerlinDB-based custom tables with schema versioning
- **Event Management**: WordPress hooks managed through Event_Manager and Subscribers
- **AJAX/REST**: API endpoints for admin and frontend interactions

### Database Architecture
Custom tables use BerlinDB with:
- **Tables**: `inc/Engine/*/Database/Tables/` - Schema definitions with versioned upgrades
- **Queries**: `inc/Engine/*/Database/Queries/` - Data access layer
- **Rows**: `inc/Engine/*/Database/Rows/` - Entity models
- **Schemas**: Column definitions with cache_key, searchable, sortable properties

**Table Structure Example:**
```php
class MyTable extends AbstractTable {
    protected $name = 'wpr_my_feature';
    protected $db_version_key = 'wpr_my_feature_version';
    protected $version = 20251006; // YYYYMMDD format
    
    protected $upgrades = [
        20251006 => 'add_new_column_method',
    ];
    
    protected $schema_data = "
        id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        url              varchar(2000)       NOT NULL default '',
        status           varchar(255)        NOT NULL default '',
        modified         timestamp           NOT NULL default '0000-00-00 00:00:00',
        PRIMARY KEY (id),
        KEY url (url(191))
    ";
    
    protected function add_new_column_method() {
        // Upgrade logic here
    }
}
```

**Database Migrations:**
- Version format: YYYYMMDD (e.g., 20251006 for October 6, 2025)
- Upgrades run automatically via `$upgrades` array
- Each upgrade method receives no parameters
- Use raw SQL with `$wpdb` for schema changes not supported by dbDelta

## Development Workflow

### TDD Process

Follow Test-Driven Development for all features:

1. **Define Acceptance Criteria**
   - Look for acceptance criteria in the issue
   - Write in Gherkin format: "Given [context], When [action], Then [expected result]"

2. **Red-Green-Refactor Cycle:**
   - ✅ Write a failing test (Red)
   - ✅ Write minimal code to pass (Green)
   - ✅ Run test to verify it passes
   - ✅ Run full test suite to prevent regressions
   - ✅ Refactor while keeping tests green (Refactor)

**See "Testing Guide" section below for detailed test patterns and examples.**

### Build Assets
```bash
# CSS/SCSS compilation
gulp build:sass:all    # All admin CSS variants
gulp sass:watch       # Watch mode

# JavaScript bundling  
gulp build:js:all     # All JS bundles
gulp js:watch         # Watch mode
```

### Code Quality
```bash
composer run phpcs          # Code standards check (WordPress Coding Standards)
composer run run-stan       # PHPStan static analysis
```

**Before Committing:**
1. Run `composer run phpcs` and fix all violations
2. Run `composer run run-stan` and address type issues
3. Run relevant test suites (unit and integration)
4. Verify no tests are broken

## Testing Guide

### Testing Philosophy
We follow **Test-Driven Development (TDD)**:
1. Write a failing test (Red)
2. Write minimal code to pass (Green)
3. Refactor while keeping tests green (Refactor)

### Test Structure & Organization

**File Locations:**
- Unit tests: `tests/Unit/` (mirror structure of `inc/`)
- Integration tests: `tests/Integration/` (mirror structure of `inc/`)
- Fixtures: `tests/Fixtures/` (same path as test file)

**Naming Conventions:**
- Test files: `path/to/class/ClassName/methodName.php`
- Test class: `Test_MethodName` (e.g., `Test_ProcessData`)
- Test methods: `testShouldBehaviorExpected` (e.g., `testShouldProcessDataCorrectly`)
- Fixture scenarios: `shouldHandleValidInput`, `shouldReturnErrorForInvalidData`

**Example Structure:**
```
tests/Integration/inc/Engine/MyFeature/Controller/processData.php
tests/Fixtures/inc/Engine/MyFeature/Controller/processData.php
```

### Unit Tests

**When to use:** Testing business logic in isolation without WordPress functions or database.

**Key practices:**
- Mock all dependencies via constructor injection
- Use `@dataProvider` for parameterized tests
- Test one behavior per test method
- Keep tests fast (no I/O operations)

**Example:**
```php
class Test_ProcessData extends TestCase {
    private $dependency_mock;
    private $service;
    
    public function set_up() {
        parent::set_up();
        $this->dependency_mock = Mockery::mock(DependencyInterface::class);
        $this->service = new MyService($this->dependency_mock);
    }
    
    /**
     * @dataProvider dataProvider
     */
    public function testShouldProcessDataCorrectly($input, $expected) {
        $this->dependency_mock
            ->shouldReceive('get_data')
            ->once()
            ->andReturn($input);
            
        $result = $this->service->process_data();
        
        $this->assertSame($expected, $result);
    }
    
    public function dataProvider() {
        return [
            'valid_data' => ['input' => 'test', 'expected' => 'processed_test'],
            'empty_data' => ['input' => '', 'expected' => ''],
        ];
    }
}
```

### Integration Tests

**When to use:** Testing with WordPress functions, database operations, or hook interactions.

**Key practices:**
- Extend `WPRocketMe\Tests\Integration\TestCase`
- Use `@dataProvider configTestData` to load fixtures automatically
- Use `DBTrait` for database operations
- Use test groups to organize tests

**Example:**
```php
use WPRocketMe\Tests\Integration\DBTrait;

/**
 * @group PerformanceMonitoring
 * @group AdminOnly
 */
class Test_ProcessData extends TestCase {
    use DBTrait;
    
    public function set_up() {
        parent::set_up();
        $this->installPerformanceMonitoringTable();
    }
    
    public function tear_down() {
        $this->truncatePerformanceMonitoringTable();
        parent::tear_down();
    }
    
    /**
     * @dataProvider configTestData
     */
    public function testShouldProcessDataCorrectly($config, $expected) {
        $controller = new Controller($config['dependencies']);
        $result = $controller->process_data($config['input']);
        $this->assertSame($expected['output'], $result);
    }
}
```

**Corresponding Fixture:**
```php
// tests/Fixtures/inc/Engine/MyFeature/Controller/processData.php
return [
    'test_data' => [
        'shouldProcessDataCorrectly' => [
            'config' => [
                'dependencies' => [...],
                'input' => [...],
            ],
            'expected' => [
                'output' => [...],
            ],
        ],
    ],
];
```

### Test Groups

Organize tests with PHPDoc annotations for targeted test runs:

**Common Groups:**
- `@group AdminOnly` - Admin-specific functionality
- `@group PerformanceMonitoring` - Performance monitoring feature
- `@group RocketInsights` - Rocket Insights feature
- `@group WithWoo` - WooCommerce integration
- `@group Multisite` - Multisite-specific tests
- Feature-specific: `@group RUCSS`, `@group Cloudflare`, etc.

**Running specific groups:**
```bash
# Run only PerformanceMonitoring tests
composer run test-integration -- --group PerformanceMonitoring

# Exclude heavy groups
composer run test-integration -- --exclude-group WithWoo,Multisite
```

### Database Testing Helpers

Use `DBTrait` for custom table operations:

**Available methods:**
- `installPerformanceMonitoringTable()` - Install PM table
- `truncatePerformanceMonitoringTable()` - Clean PM table
- `installUsedCssTable()` - Install RUCSS table
- `truncateUsedCssTable()` - Clean RUCSS table

**Pattern:**
```php
public function set_up() {
    parent::set_up();
    $this->installCustomTable(); // Install before each test
}

public function tear_down() {
    $this->truncateCustomTable(); // Clean after each test
    parent::tear_down();
}
```

## Key Conventions

### File Organization
- **ServiceProviders** register all module services
- **Subscribers** handle WordPress hooks/events  
- **Controllers** handle HTTP requests/AJAX
- **Context** classes determine feature availability (e.g., `inc/Engine/Cache/WPCache/Context.php`)
- **Factories** create complex objects (e.g., `inc/Engine/Optimization/LazyRenderContent/Factory.php`)

**Directory Structure Pattern:**
```
inc/Engine/MyFeature/
├── ServiceProvider.php          # Service registration
├── Context/
│   └── Context.php             # Feature availability logic
├── Database/
│   ├── Tables/
│   │   └── MyFeature.php       # Table schema
│   ├── Queries/
│   │   └── MyFeature.php       # Query builder
│   ├── Rows/
│   │   └── MyFeature.php       # Row model
│   └── Schemas/
│       └── MyFeature.php       # Column definitions
├── AJAX/
│   └── Controller.php          # AJAX endpoints
├── Admin/
│   ├── Settings.php            # Admin settings
│   └── Subscriber.php          # Admin hooks
└── Frontend/
    ├── Controller.php          # Frontend logic
    └── Subscriber.php          # Frontend hooks
```

### Database Migrations
```php
// In Table classes
protected $upgrades = [
    20250909 => 'add_new_column_method',
    20251006 => 'add_index_method',
];

// Migration method example
protected function add_new_column_method() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . $this->name;
    
    // Check if column exists
    $column_exists = $wpdb->get_results(
        $wpdb->prepare(
            "SHOW COLUMNS FROM `{$table_name}` LIKE %s",
            'new_column'
        )
    );
    
    if ( empty( $column_exists ) ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->query(
            "ALTER TABLE `{$table_name}` 
            ADD COLUMN `new_column` VARCHAR(255) NOT NULL DEFAULT ''"
        );
    }
}
```

### Service Registration Pattern
```php
// In ServiceProvider::register()

// Singleton (shared instance)
$this->getContainer()->addShared('service_name', ClassName::class)
    ->addArguments(['dependency1', 'dependency2']);

// New instance each time
$this->getContainer()->add('service_name', ClassName::class)
    ->addArgument('dependency');

// With literal string arguments
$this->getContainer()->add('service_name', ClassName::class)
    ->addArgument(new StringArgument('/path/to/file'));

// With array arguments
$this->getContainer()->add('service_name', ClassName::class)
    ->addArgument(new ArrayArgument(['key' => 'value']));
```

### Hook Management
Use Event_Manager instead of direct `add_action`/`add_filter`:
```php
// In Plugin.php or main initialization
$this->event_manager->add_subscriber($this->container->get('subscriber_name'));

// Never do this:
add_action('init', [$this, 'method']); // ❌ WRONG

// Always use Subscribers:
class MySubscriber implements Subscriber_Interface {
    public static function get_subscribed_events(): array {
        return [
            'init' => 'method',
        ];
    }
}
```

## Critical Paths

### Performance Monitoring Feature
- Entry: `inc/Engine/Admin/PerformanceMonitoring/ServiceProvider.php`
- Database: Custom table with GTMetrix integration
- AJAX endpoints for admin interface
- Queue system for background processing

### Cache Engine
- Entry: `inc/Engine/Cache/ServiceProvider.php` 
- Advanced cache dropin management
- Preload and purge systems

### Optimization Modules
- RUCSS (Remove Unused CSS): `inc/Engine/Optimization/RUCSS/`
- Delay JS: `inc/Engine/Optimization/DelayJS/`
- Lazy Load: `inc/Engine/Media/Lazyload/`

## Environment Setup
- WordPress functions available via wp-phpunit
- Uses Mozart for dependency management (namespacing)
- PHPStan baseline for legacy code compatibility

## WordPress Coding Standards

### Naming Conventions
- **Functions**: `snake_case` (e.g., `rocket_get_cache_dir()`)
- **Classes**: `PascalCase` (e.g., `PerformanceMonitoring`)
- **Methods**: `snake_case` (e.g., `get_data()`)
- **Variables**: `snake_case` (e.g., `$cache_dir`)
- **Constants**: `SCREAMING_SNAKE_CASE` (e.g., `WP_ROCKET_VERSION`)

### Filter and Action Hooks

**CRITICAL: Never use `apply_filters()` directly in new code.**

Always use `wpm_apply_filters_typed()` instead, which enforces type safety:

```php
// ❌ BAD: No type safety
$expiration = apply_filters( 'rocket_cache_expiration', 3600 );

// ✅ GOOD: Type-safe filter with documentation
/**
 * Filters the cache expiration time.
 *
 * @since 3.0
 * 
 * @param int $expiration Cache expiration in seconds.
 * @return int
 */
$expiration = wpm_apply_filters_typed( 'integer', 'rocket_cache_expiration', 3600 );
```

**Available Types:**
- `'string'` - String values
- `'integer'` - Integer values  
- `'boolean'` - Boolean values
- `'array'` - Array values
- `'string[]'` - Array of strings

**Documentation Requirements:**
Every `wpm_apply_filters_typed()` call MUST have a docblock that includes:
- Description of what the filter does
- `@since` version tag
- `@param` for all parameters with types
- `@return` with the return type

**Real Example:**
```php
/**
 * Filters the list of excluded post types from Rocket Insights analysis.
 *
 * @since 3.17
 * 
 * @param array $excluded_post_types Array of post type slugs to exclude.
 * @return array
 */
$excluded_post_types = (array) wpm_apply_filters_typed(
    'array',
    'rocket_insights_excluded_post_types',
    []
);
```

### Array Syntax
- Always use short array syntax `[]` instead of `array()`
- Exception: WordPress functions that require `array()` for compatibility

```php
// ✅ GOOD
$data = [
    'key' => 'value',
];

// ❌ BAD
$data = array(
    'key' => 'value',
);
```

### Code Style
- Use tabs for indentation (4 spaces per tab)
- Use Yoda conditions: `if ( 10 === $value )`
- Space after control structures: `if ( condition )`
- No space before function parentheses: `function_name()`

```php
// ✅ GOOD
if ( 10 === $value ) {
    do_something();
}

// ❌ BAD
if ($value == 10) {
    do_something();
}
```

## Common Pitfalls to Avoid

### 1. Using `apply_filters()` Instead of `wpm_apply_filters_typed()`
```php
// ❌ BAD: No type safety, PHPStan will flag this
$value = apply_filters( 'rocket_my_filter', 'default' );

// ✅ GOOD: Type-safe with proper documentation
/**
 * Filters the custom value.
 *
 * @since 3.17
 * 
 * @param string $value The custom value.
 * @return string
 */
$value = wpm_apply_filters_typed( 'string', 'rocket_my_filter', 'default' );
```

### 2. Direct Database Queries
```php
// ❌ BAD: Direct query without prepare
$wpdb->query("UPDATE {$table} SET status = 'active'");

// ✅ GOOD: Use prepare for security
$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$table} SET status = %s",
        'active'
    )
);
```

### 3. Missing Nonce Verification
```php
// ❌ BAD: No nonce check
if ( isset( $_POST['action'] ) ) {
    process_data();
}

// ✅ GOOD: Verify nonce
if ( isset( $_POST['action'] ) 
    && check_ajax_referer( 'my_action', 'nonce', false ) 
) {
    process_data();
}
```

### 4. Not Escaping Output
```php
// ❌ BAD: Unescaped output
echo $user_input;

// ✅ GOOD: Escape based on context
echo esc_html( $user_input );
echo esc_url( $url );
echo esc_attr( $attribute );
```

### 5. Ignoring Return Values
```php
// ❌ BAD: Ignoring potential failures
update_option( 'my_option', $value );

// ✅ GOOD: Check return value
if ( ! update_option( 'my_option', $value ) ) {
    // Handle error
}
```

### 6. Not Using Type Hints
```php
// ❌ BAD: No type hints
public function process_data( $data ) {
    return $data;
}

// ✅ GOOD: With type hints
public function process_data( array $data ): array {
    return $data;
}
```

## Debugging and Logging

### Using WP Rocket Logger
```php
// Get logger from container
$logger = apply_filters( 'rocket_container', null )->get( 'logger' );

// Log levels
$logger->debug( 'Debug message', [ 'context' => 'data' ] );
$logger->info( 'Info message' );
$logger->warning( 'Warning message' );
$logger->error( 'Error message', [ 'exception' => $e ] );
```

### LoggerAware Trait
```php
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class MyClass implements LoggerAwareInterface {
    use LoggerAware;
    
    public function process() {
        $this->logger->info( 'Processing started' );
        // ... logic ...
        $this->logger->info( 'Processing completed' );
    }
}
```

## Performance Considerations

### 1. Avoid Queries in Loops
```php
// ❌ BAD: Query in loop
foreach ( $posts as $post ) {
    $meta = get_post_meta( $post->ID, 'key', true );
}

// ✅ GOOD: Batch query
$meta_values = get_post_meta_batch( array_column( $posts, 'ID' ), 'key' );
```

### 2. Use Transients for Expensive Operations
```php
// ✅ GOOD: Cache expensive operations
$data = get_transient( 'my_expensive_data' );
if ( false === $data ) {
    $data = expensive_operation();
    set_transient( 'my_expensive_data', $data, HOUR_IN_SECONDS );
}
```

### 3. Optimize Database Queries
```php
// Use proper indexes in table schema
protected $schema_data = "
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    url varchar(2000) NOT NULL default '',
    status varchar(255) NOT NULL default '',
    PRIMARY KEY (id),
    KEY url (url(191)),      -- Index for faster lookups
    KEY status (status)       -- Index for status filtering
";
```

## Security Best Practices

### 1. Capability Checks
```php
// Always check user capabilities
if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
    wp_send_json_error( 'Unauthorized' );
}
```

### 2. Sanitize Input, Escape Output
```php
// Sanitize input
$url = sanitize_url( $_POST['url'] );
$email = sanitize_email( $_POST['email'] );
$text = sanitize_text_field( $_POST['text'] );

// Escape output
echo esc_html( $text );
echo esc_url( $url );
echo esc_js( $script_var );
```

### 3. Use Prepared Statements
```php
// Always use wpdb->prepare for dynamic queries
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = %s AND id > %d",
        $status,
        $id
    )
);
```

## Final Checklist

When working on new features, ensure you:

1. **Create ServiceProvider** - Register all services and subscribers
2. **Write Tests First** - Follow TDD with both unit and integration tests
3. **Add Database Migrations** - If custom tables are needed, include versioned upgrades
4. **Follow Directory Structure** - Match the organizational patterns in `inc/Engine/`
5. **Use Subscribers** - Never use `add_action`/`add_filter` directly
6. **Document Code** - PHPDoc on all public methods with `@since`, `@param`, `@return`
7. **Run Quality Checks** - `composer run phpcs` and `composer run run-stan` before committing