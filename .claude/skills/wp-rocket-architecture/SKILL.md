---
name: wp-rocket-architecture
description: Use this skill when changing service structure, Subscribers, ServiceProviders, Container wiring, bootstrapping, Context classes, or any code that may affect the core caching engine in WP Rocket.
---

# WP Rocket Architecture Integrity

Enforce architectural patterns and protect internal service structure.

## Core principles

- Every WordPress hook goes through a Subscriber — never `add_action` / `add_filter` directly.
- Every new filter uses `wpm_apply_filters_typed()` — never `apply_filters()`.
- Plugin settings are read via injected `Options_Data` — never `get_option()`.
- All instantiation goes through the League Container — never `new ClassName()` in business code.
- Do not modify `inc/Dependencies/` (Mozart-bundled vendored code).

## Subscriber pattern

```php
class MySubscriber implements Subscriber_Interface {
    public static function get_subscribed_events(): array {
        return [
            'init'             => 'on_init',                      // priority 10, 1 arg
            'save_post'        => [ 'on_save_post', 10, 2 ],     // priority 10, 2 args
            'admin_menu'       => [
                [ 'add_menu',    9 ],
                [ 'add_submenu', 10 ],
            ],
        ];
    }
}
```

**PHPStan enforces this**: every method name in `get_subscribed_events()` must exist in the class (`EnsureCallbackMethodsExistsInSubscribedEvents`). Always implement the method before or alongside declaring it.

## ServiceProvider pattern

```php
class ServiceProvider extends AbstractServiceProvider {
    protected $provides = [ 'my_subscriber', 'my_controller' ];

    public function register(): void {
        $this->getContainer()->addShared( 'my_controller', Controller::class )
            ->addArguments( [ 'options', 'logger' ] );         // string = container key

        $this->getContainer()->addShared( 'my_subscriber', MySubscriber::class )
            ->addArgument( 'my_controller' );
    }
}
```

- `addShared()` → singleton (same instance every `get()` call)
- `add()` → factory (new instance every call)
- String arguments are container keys; use `new StringArgument('...')` for literal strings, `new ArrayArgument([...])` for literal arrays.

## Context classes

Features that are conditionally active encapsulate that logic in a `Context` class rather than inlining checks in the Subscriber:

```
inc/Engine/MyFeature/
└── Context/
    └── Context.php   // should_run(): bool — inject into Subscriber
```

The Subscriber calls `$this->context->is_allowed()` (or similar) as a guard clause. This keeps the Subscriber clean and the condition unit-testable in isolation.

## `wpm_apply_filters_typed()` — mandatory for all new filters

```php
// ❌ Flagged by PHPStan (DiscourageApplyFilters)
$value = apply_filters( 'rocket_my_filter', $default );

// ✅ Required — with full docblock
/**
 * Filters the custom value.
 *
 * @param string $value The value.
 * @return string
 */
$value = wpm_apply_filters_typed( 'string', 'rocket_my_filter', $default );
```

Available types: `'string'`, `'integer'`, `'boolean'`, `'array'`, `'string[]'`.

## Option objects — mandatory for reading plugin settings

```php
// ❌ Flagged by PHPStan (DiscourageWPOptionUsage)
$setting = get_option( 'wp_rocket_settings' );

// ✅ Required — inject Options_Data via constructor (PHP 7.3+ compatible)
/** @var Options_Data */
private $options;

public function __construct( Options_Data $options ) {
    $this->options = $options;
}

$setting = $this->options->get( 'option_key', $default );
```

## PHPStan custom rules — all must pass

| Rule | Implication |
|---|---|
| `DiscourageApplyFilters` | Use `wpm_apply_filters_typed()` |
| `DiscourageWPOptionUsage` | Use `Options_Data` injection |
| `EnsureCallbackMethodsExistsInSubscribedEvents` | Implement every method you declare in `get_subscribed_events()` |
| `NoHooksInORM` | No `add_action`, `add_filter`, `apply_filters` inside `Database/Tables/` or `Database/Queries/` classes |

## Standard module directory structure

```
inc/Engine/MyFeature/
├── ServiceProvider.php          # all bindings + declares $provides
├── Context/
│   └── Context.php             # feature availability guard
├── Admin/
│   └── Subscriber.php          # admin-only hooks
├── Frontend/
│   ├── Controller.php          # business logic
│   └── Subscriber.php          # frontend hooks
└── Database/                   # only when custom tables are needed
    ├── Tables/MyFeature.php    # BerlinDB schema, version YYYYMMDD
    ├── Queries/MyFeature.php   # query builder
    ├── Rows/MyFeature.php      # row model
    └── Schemas/MyFeature.php   # column definitions
```

## BerlinDB table versioning

```php
protected $version = 20251006;  // YYYYMMDD
protected $upgrades = [
    20251006 => 'add_new_column',
];
protected function add_new_column(): void {
    // ALTER TABLE with $wpdb->prepare
}
```

## Structural guardrails

Avoid:
- Global state or new singletons outside the container
- Static helpers replacing injected services
- Business logic inside ServiceProvider `register()` — wiring only
- Hooks or filters inside Database/Query/Table classes (`NoHooksInORM`)

## Git Operations

Follow the policy defined in AGENTS.md §5.1. Outside the issue workflow, do not run `git commit` or `git push`.
