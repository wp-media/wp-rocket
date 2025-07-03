# GitHub Copilot Guidelines for WP Rocket

This document provides comprehensive guidelines for developing code with GitHub Copilot for the WP Rocket plugin. It outlines coding standards, architectural patterns, feature implementations, and best practices specific to WP Rocket's codebase.

---

## Table of Contents

1. [PHP Coding Standards](#1-php-coding-standards)
2. [Project Architecture](#2-project-architecture)
3. [Feature Implementation Guidelines](#3-feature-implementation-guidelines)
4. [Testing Framework](#4-testing-framework)
5. [Development Workflow](#5-development-workflow)
6. [Major WP Rocket Features](#6-major-wp-rocket-features)
7. [WordPress Integration](#7-wordpress-integration)
8. [Performance & Security](#8-performance--security)

---

## 1. PHP Coding Standards

### 1.1 Basic Requirements

- **PHP Version**: Use PHP 7.3 or higher with type declarations
- **Follow WordPress PHP coding standards** with PSR-12 style guidelines
- **Use spaces for indentation** (4 spaces, no tabs)
- **Always declare strict types** at the top of PHP files:
  ```php
  <?php
  declare(strict_types=1);
  ```

### 1.2 Type Safety & Documentation

- **Type all function parameters and return values** where possible (PHP 7+)
- **Use nullable types when appropriate**: `?string`, `?array`, `?int`
- **Document all public functions and classes** with comprehensive PHPDoc blocks
- **Include `@since` tags** in docblocks for version tracking

### 1.3 Filter and Action Hooks

- **CRITICAL: Avoid using `apply_filters` directly**
- **Always use `wpm_apply_filters_typed`** instead, which enforces output typing
- **Document all filters** with a docblock immediately before the call

The docblock must describe:
- What the filter does
- The output type 
- All parameters with their types and descriptions

**Correct Example:**
```php
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

**Available Types for `wpm_apply_filters_typed`:**
- `'string'` - String values
- `'integer'` - Integer values  
- `'boolean'` - Boolean values
- `'array'` - Array values
- `'string[]'` - Array of strings

### 1.4 Security & Sanitization

- **Escape all output** using appropriate functions:
  - `esc_html()` and `esc_html__()` for HTML content
  - `esc_url()` for URLs
  - `esc_attr()` for HTML attributes
  - `wp_kses_post()` for rich content
- **Sanitize all inputs**:
  - `sanitize_text_field()` for text inputs
  - `sanitize_url()` for URLs
  - `wp_unslash()` for form data
- **Use nonces** for all form submissions and AJAX actions
- **Validate user capabilities** before allowing actions

### 1.5 Error Handling

- **Use try-catch blocks** for operations that may fail
- **Log errors appropriately** using WP Rocket's logging system
- **Return meaningful error messages** for user-facing errors
- **Fail gracefully** without breaking the entire page

---

## 2. Project Architecture

### 2.1 Directory Structure

```
inc/Engine/
├── Cache/                    # Cache management
├── Optimization/            # Performance optimizations
│   ├── RUCSS/              # Remove Unused CSS
│   ├── LazyRenderContent/  # Lazy loading content
│   ├── DelayJS/            # JavaScript delay
│   └── Minify/             # CSS/JS minification
├── Media/                  # Media optimizations
│   ├── PreloadFonts/       # Font preloading
│   ├── Lazyload/           # Image lazy loading
│   └── AboveTheFold/       # Critical image optimization
├── Preload/                # Cache preloading
├── Admin/                  # Admin interface
└── Common/                 # Shared components
```

### 2.2 Dependency Injection

- **Use [league/container](https://container.thephpleague.com/)** for dependency injection
- **All container definitions and service providers** must be placed in `/inc/Engine`
- **Keep service registration logic** separate from business logic classes
- **Follow the ServiceProvider pattern** for registering dependencies

**ServiceProvider Example:**
```php
<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Feature\FeatureName;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {
    protected $provides = [
        'feature_controller',
        'feature_subscriber',
        'feature_context',
    ];

    public function register(): void {
        $this->getContainer()->add( 'feature_controller', Controller::class )
            ->addArguments([
                'options',
                'feature_context',
            ]);
    }
}
```

### 2.3 Namespace Conventions

- **Root namespace**: `WP_Rocket\Engine\`
- **Follow PSR-4 autoloading standards**
- **Use descriptive namespace names** that match directory structure
- **Avoid deeply nested namespaces** (max 4-5 levels)

### 2.4 Context Classes

Every major feature should have a Context class that:
- Determines if the feature should run
- Checks user permissions and settings
- Validates the current environment
- Provides consistent enable/disable logic

**Context Example:**
```php
class Context extends AbstractContext {
    public function is_allowed(): bool {
        return $this->run_common_checks([
            'do_not_optimize'    => false,
            'bypass'             => false,
            'option'             => 'feature_enabled',
            'post_excluded'      => 'feature_name',
        ]);
    }
}
```

---

## 3. Feature Implementation Guidelines

### 3.1 Service Provider Pattern

Each feature must implement:
1. **ServiceProvider** - Registers all dependencies
2. **Controller** - Contains main business logic  
3. **Subscriber** - Handles WordPress hooks
4. **Context** - Determines when feature should run
5. **Database components** (if needed) - Tables, Queries

### 3.2 Subscriber Pattern for WordPress Hooks

- **Use static `get_subscribed_events()` method** to define hooks
- **Return array mapping hooks to methods**
- **Include priority and parameter count** when needed

**Subscriber Example:**
```php
class Subscriber implements SubscriberInterface {
    public static function get_subscribed_events(): array {
        return [
            'wp_head'           => ['add_critical_css', 10],
            'rocket_buffer'     => ['optimize_html', 15],
            'wp_enqueue_scripts' => 'maybe_enqueue_assets',
        ];
    }

    public function add_critical_css(): void {
        if ( ! $this->context->is_allowed() ) {
            return;
        }
        // Implementation
    }
}
```

### 3.3 Database Integration

For features requiring data storage:

1. **Table Schema** - Extend `AbstractTable`
2. **Query Class** - Extend `AbstractQuery` 
3. **Use prepared statements** for all database operations
4. **Include proper indexing** for performance

**Table Example:**
```php
class FeatureTable extends AbstractTable {
    protected function get_schema(): array {
        return [
            'id' => [
                'type'          => 'bigint',
                'length'        => 20,
                'unsigned'      => true,
                'auto_increment' => true,
                'primary'       => true,
            ],
            'url' => [
                'type'   => 'varchar',
                'length' => 2000,
                'null'   => false,
            ],
            // ... more columns
        ];
    }
}
```

---

## 4. Testing Framework

### 4.1 Test Structure

- **Unit Tests**: `tests/Unit/` - Test individual classes in isolation
- **Integration Tests**: `tests/Integration/` - Test feature integration with WordPress
- **Fixtures**: `tests/Fixtures/` - Test data and scenarios

### 4.2 Test Requirements

- **Write tests for all new features and bug fixes**
- **Maintain or improve code coverage**
- **Use meaningful test method names** that describe the scenario
- **Follow the AAA pattern**: Arrange, Act, Assert

### 4.3 Test Fixtures

**Location**: `tests/Fixtures/inc/*` (mirror the actual code structure)

**Format**: Return an array with named test scenarios:
```php
<?php
return [
    'testShouldReturnExpectedWhenValidInput' => [
        'config'   => [
            'option_enabled' => true,
            'user_capability' => 'manage_options',
        ],
        'expected' => true,
    ],
    'testShouldReturnFalseWhenDisabled' => [
        'config'   => [
            'option_enabled' => false,
        ],
        'expected' => false,
    ],
];
```

### 4.4 Running Tests

```bash
# Run all tests
composer run-tests

# Run specific test suite
composer run-tests -- tests/Unit/
composer run-tests -- tests/Integration/

# Run with coverage
composer run-tests -- --coverage-html coverage/

# Static analysis
composer run-stan

# Code standards
composer phpcs
```

---

## 5. Development Workflow

### 5.1 Test-Driven Development (TDD)

1. **Read acceptance criteria** from the GitHub issue
2. **Write failing tests** for each acceptance criterion
3. **Implement minimal code** to make tests pass
4. **Refactor** while keeping tests green
5. **Run full test suite** to prevent regressions

### 5.2 Branching Strategy

```
feature/{issue-id}-{short-description}
fix/{issue-id}-{short-description}  
hotfix/{issue-id}-{short-description}
core/{issue-id}-{short-description}
```

### 5.3 Pull Request Guidelines

- **Use the provided PR template**
- **Include screenshots** for UI changes
- **Reference the related issue**
- **Ensure all CI checks pass**
- **Request review from relevant team members**

### 5.4 Code Quality Checks

Before submitting code:
```bash
# Lint PHP code
composer phpcs

# Static analysis  
composer run-stan

# Run tests
composer run-tests

# Build assets
npm run build
```

---

## 6. Major WP Rocket Features

### 6.1 Remove Unused CSS (RUCSS)

**Purpose**: Removes unused CSS to reduce file sizes and improve performance.

**Key Components**:
- `UsedCSS` controller processes CSS files
- `Queue` system manages CSS analysis jobs  
- `Context` determines when RUCSS should run
- Database stores processed CSS results

**Location**: `inc/Engine/Optimization/RUCSS/`

**Key Methods**:
```php
// Generate used CSS for a page
$used_css = $this->used_css->generate( $url, $is_mobile );

// Apply used CSS to HTML
$html = $this->used_css->add_used_css_to_html( $items );
```

**Important Notes**:
- Integrates with SaaS API for CSS processing
- Supports mobile-specific CSS generation
- Handles font preloading within used CSS
- Uses queue system for background processing

### 6.2 Lazy Render Content (LRC)

**Purpose**: Lazy loads below-the-fold content using CSS `content-visibility`.

**Key Components**:
- `Processor` adds hash identifiers to HTML elements
- `Controller` optimizes content based on collected data
- `AJAX` controller handles data collection from frontend
- Database stores below-the-fold element hashes

**Location**: `inc/Engine/Optimization/LazyRenderContent/`

**Key Concepts**:
```php
// Add hashes to HTML elements
$html = $this->controller->add_hashes_when_allowed( $html );

// Optimize content based on collected data  
$html = $this->controller->optimize( $html, $row );
```

**Configuration**:
- Depth control: `rocket_lrc_depth` filter
- Processed tags: `rocket_lrc_processed_tags` filter
- Exclusion patterns: `rocket_lrc_exclusions` filter

### 6.3 Preload Fonts

**Purpose**: Automatically preloads critical fonts to improve loading performance.

**Key Components**:
- `Controller` manages font preloading logic
- `AJAX` controller processes frontend font data
- Database stores detected fonts per page
- Integration with RUCSS for font detection

**Location**: `inc/Engine/Media/PreloadFonts/`

**Key Features**:
```php
// Add preload links to head
$items = $this->preload_fonts->add_preload_fonts_in_head( $items );

// Process collected font data
$result = $this->ajax_controller->add_data();
```

**Font Types Supported**:
- WOFF2, WOFF, TTF, OTF font formats
- Local and external font sources
- Integration with Google Fonts optimization

### 6.4 Image Lazy Loading

**Purpose**: Defers loading of images until they're needed, improving initial page load.

**Key Components**:
- `Image` class handles image processing
- `Assets` class manages lazy loading scripts
- Native lazy loading support for modern browsers
- Background image lazy loading via CSS

**Location**: `inc/Engine/Media/Lazyload/` and `inc/Dependencies/RocketLazyload/`

**Implementation**:
```php
// Apply lazy loading to images
$html = $this->image->lazyloadImages( $html, $buffer, $use_native );

// Apply to background images  
$html = $this->image->lazyloadBackgroundImages( $html, $buffer );
```

**Features**:
- Native `loading="lazy"` attribute support
- JavaScript fallback for older browsers
- Responsive image (`srcset`) support
- Placeholder generation for layout stability

### 6.5 Cache Preloading

**Purpose**: Automatically generates cached versions of pages to improve performance.

**Key Components**:
- `Queue` system manages preload jobs
- `Controller` handles URL processing
- Sitemap integration for URL discovery
- Homepage and common pages priority

**Location**: `inc/Engine/Preload/`

**Key Operations**:
```php
// Add URL to preload queue
$this->preload_queue->add_job( $url );

// Process preload queue
$this->preload_controller->process_queue();
```

**Features**:
- Automatic sitemap crawling
- Mobile and desktop cache variants
- Exclusion patterns support
- Background processing via WP Cron

### 6.6 Above The Fold Optimization

**Purpose**: Optimizes critical images and content that appears above the fold.

**Key Components**:
- `Controller` manages ATF image detection
- `AJAX` controller processes frontend ATF data
- Beacon script collects performance data
- Database stores ATF optimization data

**Location**: `inc/Engine/Media/AboveTheFold/` and `inc/Engine/Common/PerformanceHints/`

**Key Features**:
```php
// Apply ATF optimizations
$html = $this->controller->maybe_apply_optimizations( $html );

// Collect ATF data
$data = $this->ajax_controller->add_data();
```

**Optimizations Applied**:
- `fetchpriority="high"` for critical images
- Preload links for LCP images
- WebP format optimization
- Mobile-specific optimizations

### 6.7 JavaScript Delay (DelayJS)

**Purpose**: Delays non-critical JavaScript execution to improve initial page load speed.

**Key Components**:
- `HTML` processor modifies script tags
- `Subscriber` manages WordPress integration
- Exclusion system for critical scripts
- User interaction triggers for delayed scripts

**Location**: `inc/Engine/Optimization/DelayJS/`

**Implementation**:
```php
// Delay JavaScript execution
$html = $this->delay_js->delay_js( $html );

// Check if script should be delayed
$should_delay = $this->delay_js->is_delayed( $script_content );
```

**Features**:
- Script type modification to prevent execution
- User interaction detection (click, scroll, touch)
- Exclusion patterns for critical scripts
- Integration with popular plugins

### 6.8 CSS and JS Minification

**Purpose**: Reduces file sizes by removing unnecessary characters and optimizing code.

**Key Components**:
- `Minify` classes for CSS and JS processing
- `Combine` functionality for file concatenation
- Path rewriting for relative URLs
- Font display optimization

**Location**: `inc/Engine/Optimization/Minify/`

**Processing Flow**:
```php
// Minify CSS content
$minified = $this->css_minify->minify( $file_path, $minified_file, $content );

// Minify JavaScript content  
$minified = $this->js_minify->minify( $content );
```

**Features**:
- CSS `@import` processing
- Relative path resolution
- Font display swap injection
- Source map support for debugging

---

## 7. WordPress Integration

### 7.1 Hook Priorities

Use consistent hook priorities across the plugin:
- **Critical functionality**: Priority 1-5
- **Content processing**: Priority 10-15  
- **Asset optimization**: Priority 20-25
- **Output buffering**: Priority 30+

### 7.2 Option Management

- **Use `Options_Data` class** for all option operations
- **Validate option values** before saving
- **Provide default values** for all options
- **Use consistent option naming** with `rocket_` prefix

```php
// Get option with default
$value = $this->options->get( 'cache_mobile', 1 );

// Set option
$this->options->set( 'cache_mobile', 1 );
```

### 7.3 Capability Checks

Always verify user capabilities:
```php
if ( ! current_user_can( 'rocket_manage_options' ) ) {
    return;
}
```

### 7.4 Multisite Compatibility

- **Check for multisite environment**: `is_multisite()`
- **Handle network-wide settings** appropriately
- **Respect site-specific configurations**

---

## 8. Performance & Security

### 8.1 Performance Guidelines

- **Minimize database queries** in frontend requests
- **Use transient caching** for expensive operations
- **Implement lazy loading** for non-critical functionality
- **Optimize file I/O operations**
- **Use appropriate WordPress APIs** (WP_Filesystem, etc.)

### 8.2 Security Best Practices

- **Validate and sanitize all user input**
- **Use nonces for state-changing operations**
- **Implement proper capability checks**
- **Escape output in all contexts**
- **Use prepared statements** for database queries

### 8.3 Caching Considerations

- **Implement cache invalidation** properly
- **Consider cache variations** (mobile, logged-in users)
- **Use cache bypassing** for admin users when needed
- **Implement cache warming** strategies

### 8.4 Error Handling & Logging

```php
// Log errors appropriately
Logger::error( 'Failed to process file', [
    'optimization_feature',
    'file' => $file_path,
    'error' => $error_message,
]);

// Return graceful fallbacks
if ( ! $optimized_content ) {
    return $original_content;
}
```

---

## Development Commands

```bash
# Development setup
composer install
npm install

# Code quality
composer phpcs                    # Check coding standards
composer phpcs:fix               # Fix coding standards  
composer run-stan                # Static analysis

# Testing
composer run-tests               # Run all tests
composer run-tests -- --group Unit   # Run unit tests only
composer run-tests -- --coverage-html coverage/  # With coverage

# Asset building
npm run build                    # Build all assets
npm run build:css               # Build CSS only
npm run build:js                # Build JavaScript only
npm run watch                   # Watch for changes
```


