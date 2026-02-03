---
# Pull Request Guidelines
Instructions for creating and reviewing pull requests in wp-rocket

## When Creating Pull Requests

### Template Reference
- MUST follow the complete PR template from `wp-media/.github/.github/PULL_REQUEST_TEMPLATE.md`
- Template location: https://github.com/wp-media/.github/blob/main/.github/PULL_REQUEST_TEMPLATE.md
- YOU MUST read and use the latest version of this template for every PR

### Critical Instructions
- Fill out ALL sections from the template, do not skip any section
- If a section is not applicable, explain why in "Unticked items justification"
- Never leave template sections empty without justification

### Required Sections (from template)
The template includes these main sections - YOU MUST complete all:
1. **Description** - Issue reference and user impact
2. **Type of change** - Select all applicable checkboxes
3. **Detailed scenario** - What was tested, how to test, affected features
4. **Technical description** - Documentation, dependencies, risks
5. **Mandatory Checklist** - All code validation and style items
6. **Additional Checks** - Optional but recommended items

### Key Requirements
- **Issue Reference**: MUST use `Fixes #(number)` format
- **User Impact**: Explain in plain language how this affects end users
- **Testing Instructions**: Must be detailed enough for validators to test autonomously (environment, dependencies, steps, API requests, etc.)
- **Affected Features & QA Scope**: MUST specify which existing features are impacted - critical for QA team
- **Mandatory Checklist**: Complete ALL items or justify in "Unticked items justification" section

### Template Updates
- Always fetch and use the latest template from the repository
- Do not rely on cached or memorized template structure
- If template structure has changed, adapt accordingly

## Code Quality Standards
- Write self-explanatory code
- Protect entry points against unexpected inputs
- Avoid unnecessary complexity
- Make error messages explicit and actionable
- Add error handling for functions that could throw errors (HTTP/API, filesystem, etc.)
