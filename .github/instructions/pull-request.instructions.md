---
# Pull Request Guidelines
Instructions for creating and reviewing pull requests in wp-rocket

## When Creating Pull Requests

### Template Reference
- MUST follow the complete PR template from `wp-media/.github/.github/PULL_REQUEST_TEMPLATE.md`
- Template location: https://github.com/wp-media/.github/blob/main/.github/PULL_REQUEST_TEMPLATE.md
- YOU MUST read and use the latest version of this template for every PR

### CRITICAL: Exact Section Headings Required
- DO NOT modify, rename, or abbreviate any section headings from the template
- The PR validation workflow (`pr-checklist-action`) performs strict string matching on section headings
- Examples of what NOT to do:
- ❌ "QA Scope" instead of "Affected Features & Quality Assurance Scope"
- ❌ "Docs" instead of "Documentation"
- ❌ "Testing" instead of "What was tested"
- ❌ Any variation in capitalization, punctuation, or wording
- ALWAYS copy section headings exactly as they appear in the template
- You may ONLY modify the content under each heading, never the heading itself

### Required Sections
The validation workflow checks for these EXACT headings:
- `# Description`
- `## Type of change`
- `## Detailed scenario`
- `### What was tested`
- `### How to test`
- `### Affected Features & Quality Assurance Scope`
- `## Technical description`
- `### Documentation`
- `### New dependencies`
- `### Risks`
- `# Mandatory Checklist`
- `## Code validation`
- `## Code style`
- `## Unticked items justification`
- `# Additional Checks`

### Content Requirements
- Fill out ALL sections from the template
- If a section is not applicable, explain why in "Unticked items justification"
- Never leave template placeholder text unchanged (e.g., "*Explain how this code impacts users.*")
- **Issue Reference**: MUST use `Fixes #(number)` format in Description
- **User Impact**: Replace "*Explain how this code impacts users.*" with actual explanation
- **Type of Change**: At least one checkbox MUST be selected
- **Testing Instructions**: Must be detailed enough for validators to test autonomously
- **Affected Features & Quality Assurance Scope**: MUST specify which existing features are impacted
- **Documentation**: Explain how the code works (diagrams welcome)
- **Mandatory Checklist**: Complete ALL items or justify in "Unticked items justification"

### Validation Rules
The PR will fail validation if:
1. Section headings don't match the template exactly
2. Template placeholder text is left unchanged
3. No "Type of change" is selected
4. Required sections are empty without justification
5. Mandatory checklist items are unchecked without justification in "Unticked items justification"

### Special Cases
- **Release PRs**: Only Description section is mandatory
- **Chore PRs**: Only Description section is mandatory
- If providing "Unticked items justification", mandatory checklist verification is skipped

## Code Quality Standards
- Write self-explanatory code
- Protect entry points against unexpected inputs
- Avoid unnecessary complexity
- Make error messages explicit and actionable
- Add error handling for functions that could throw errors (HTTP/API, filesystem, etc.)
