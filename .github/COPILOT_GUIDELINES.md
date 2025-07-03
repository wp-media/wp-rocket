# Copilot Guidelines for WP Rocket

This document outlines best practices and conventions for contributing code to the WP Rocket plugin.

---

## 1. PHP Coding Standards

- **PHP Version**: Use PHP 7.3 or higher.
- **Follow WordPress PHP coding standards** (PSR-12 style) and use spaces for indentation.
- **Escape all output** using appropriate functions (`esc_html__`, `esc_url`, `esc_attr`, etc.).
- **Sanitize all inputs** (`sanitize_text_field()`, `wp_kses_post()`, etc.).
- **Use nonces** for form submissions and AJAX actions.
- **Type all function parameters and return values** where possible (PHP 7+).
- **Avoid using `apply_filters` directly.**  
  Use `wpm_apply_filters` instead, which enforces output typing.
- **Document all filters** with a docblock immediately before the call.  
  The docblock must describe:
  - What the filter does
  - The output type
  - All parameters, with types and descriptions

  Example:
  ```php
  /**
   * Filters the cache expiration time.
   *
   * @param int $expiration Cache expiration in seconds.
   * @return int
   */
  $expiration = wpm_apply_filters( 'int', 'rocket_cache_expiration', 3600 );
  ```

---

## 2. Project Architecture

- **Organize core logic in namespaced classes under `inc/Engine/`.**
- **Use dependency injection** for testability and decoupling.
- **Use [league/container](https://container.thephpleague.com/)** for dependency injection and service management.
  - All container definitions and service providers must be placed in `/inc/Engine`.
  - Keep service registration and resolution logic out of business logic classes.

---

## 3. JavaScript & CSS

- **Write vanilla JavaScript or jQuery** as needed for the admin interface.
- **Author styles in SCSS** under `assets/sass/`.
- **Compile styles with:**
  ```bash
  npm run build:css
  ```
- **Compile JavaScript with:**
  ```bash
  npm run build:js
  ```

---

## 4. Testing

- **Use PHPUnit** for unit and integration tests in the `tests/` directory.
- **Keep test files as simple as possible.**
  - Use fixtures for test data and setup, located in `tests/Fixtures`.
  - Avoid duplicating setup logic; prefer reusable fixtures and traits.
- **Write tests for all new features and bug fixes.**
- **Run all tests with:**
  ```bash
  composer run-tests
  ```
- **Run PhpStan with:**
  ```bash
  composer run-stan
  ```
- **Run PHPCS with:**
  ```bash
  composer phpcs
  ```

### 4.1 Writing Test Fixtures

When writing integration tests, you need to create fixtures for test data. Key requirements for fixtures:

1. **Location**: Fixtures must be placed in `tests/Fixtures/inc/*` directory, the path has to follow where the tested feature is located.
2. **Fixture format**: Must return an array with named test scenarios containing:
   - `config` - Test setup with required parameters to simulate the environment the test has to run with.
   - `expected` - Expected result after running the test

Without these fixtures, the integration tests cannot run properly as they depend on predefined test data scenarios.

---

## 5. Development Workflow

### 5.1 Branching & Pull Requests

- Branches must follow a pattern like:
  - `feature/{github-issue-id}-{short-description}`
  - `fix/{github-issue-id}-{short-description}`
  - `hotfix/{github-issue-id}-{short-description}`
  - `core/{github-issue-id}-{short-description}`
- Use the following template to write pull requests:  
  https://github.com/wp-media/.github/blob/main/.github/PULL_REQUEST_TEMPLATE.md

### 5.2 Commit Messages

- **Write clear, descriptive commit messages.**
- Reference related issues in PRs and commits.

### 5.3 Development Process (TDD)

We use a TDD process to develop:
- Seek the acceptance criteria inside the issue.
- For each acceptance criteria:
  - Create a test or a scenario implementing an acceptance criteria.
  - Make sure the test fails.
  - Implement the easiest logic to make the test pass.
  - Run the test again and assert it passes.
  - Run all tests from the project and assert they pass to prevent regressions.
  - Refactor the code if necessary and make sure all tests still pass.

---

## 6. General Guidelines

- **Document all public functions and classes with PHPDoc.**
- **Keep pull requests focused and small.**

---