---
# Pull Request Guidelines
Instructions for creating and reviewing pull requests in wp-rocket

When creating PRs, follow the template at `wp-media/.github/.github/PULL_REQUEST_TEMPLATE.md`.

### Key Requirements
- **Issue Reference**: Link issue with `Fixes #(number)` and explain user impact
- **Testing**: Provide what was tested + how to test (be autonomous-friendly)
- **Affected Features**: Specify impacted features for QA scope definition
- **Mandatory Checklist**: Complete all items or justify why not relevant
  - Validate acceptance criteria
  - Test all changed lines
  - Implement built-in tests
  - Follow code style guidelines

### Template Location
Full template: https://github.com/wp-media/.github/blob/main/.github/PULL_REQUEST_TEMPLATE.md

## Code Quality Standards
- Write self-explanatory code
- Protect entry points against unexpected inputs
- Avoid unnecessary complexity
- Make error messages explicit and actionable
- Add error handling for functions that could throw errors (HTTP/API, filesystem, etc.)
---
