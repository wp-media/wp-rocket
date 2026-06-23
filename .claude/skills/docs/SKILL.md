---
name: docs
description: >
  Update developer-facing documentation to reflect code changes on the current branch.
  Runs as an inline skill inside backend-agent and frontend-agent (step 2.5 of the
  internal sequence) after implementation and before DOD. Receives the explicit list of
  changed files from the implementation agent. No-op if no public API changes occurred.
---

# DOCS SKILL

You are a technical writer updating internal developer documentation for WP Rocket. This
skill runs inline inside the implementation agent and receives the list of changed files
explicitly — do not infer scope from git.

---

## When to run / when to skip

**Run this skill if the implementation touched any of the following:**

- New or modified WordPress hooks (`apply_filters`, `do_action`, `wpm_apply_filters_typed`)
- New or modified AJAX actions, REST routes, or WP-CLI commands
- New or modified configuration keys, plugin options, or capabilities
- New or modified BerlinDB tables, columns, or schemas
- New or modified ServiceProvider bindings exposing a new public service
- New or modified Subscriber `get_subscribed_events()` returns (new hooks subscribed)
- New or modified plugin metadata (header constants, dependencies)
- New or modified events / callbacks consumed by external integrations

**Skip this skill (return no-op) if:**

- Implementation was internal refactoring only (private methods, no public surface change)
- Only tests, build artifacts, or `inc/Dependencies/` (vendored code) changed
- Spec explicitly flags "no public API change"

When skipping, return:
```json
{ "status": "SKIP", "reason": "No public API changes in this implementation" }
```

---

## Process

### Step 1 — Read the changed files

Use the Read tool on the explicit list provided by the implementation agent. Do not run
`git diff` to discover scope — the agent already knows what changed.

Identify:
- New or modified public API endpoints, hooks, AJAX actions, REST routes
- Removed endpoints, hooks, or option keys (document as deprecated or removed)
- New or modified capabilities (from the `wordpress-compliance` skill's list)

### Step 2 — Review existing documentation

```bash
find docs/ -name '*.md' -o -name '*.mdx' 2>/dev/null | head -50
ls -la README.md
```

Read relevant existing doc files. Understand what is already covered and what needs
updating.

### Step 3 — Identify gaps

For each significant public-facing change:
- Is it documented? Is the existing doc current?
- Which doc file should it go in? (existing or new)
- For new hooks, follow the existing hook documentation convention (filter name,
  parameter types, return type, example).

### Step 4 — Update documentation

For each gap:
1. Find the correct existing file, or create a new one under `docs/`
2. Write or update the content
3. Stage the changes alongside the implementation commit (the implementation agent
   handles the actual `git add` / `git commit`).

**Style guidelines:**

- Purpose: help engineers get a high-level understanding and find the relevant code fast
- Tone: neutral, technical, not promotional
- Structure: prose for explanations; bullets for parallel items; numbered steps for flows
- Length: concise — a few hundred lines per file max; split by topic if large
- Avoid embedding large code blocks; prefer referencing class/function names with their full namespace
- Document the **current state**, not the change history (the changelog handles history)
- For new filters, always include the `wpm_apply_filters_typed()` type as part of the signature

### Step 5 — Return

```json
{
  "status": "DONE|SKIP",
  "files_updated": ["docs/api/reports.md", "docs/configuration.md"],
  "files_created": ["docs/api/notifications.md"],
  "reason": "Populated if SKIP"
}
```

---

## wp-rocket-specific notes

- The docs root is `docs/`. The README at the repo root is for users; developer docs live in `docs/`.
- For new filters, the docstring above `wpm_apply_filters_typed()` is the authoritative source. The doc file should reference (not duplicate) the docstring location: `inc/Engine/Foo/Bar.php:42`.
- For new capabilities, add them to the list in `.claude/skills/wordpress-compliance/SKILL.md` (so PHPCS does not flag future uses).
- For BerlinDB changes, document the migration version (YYYYMMDD format) and the upgrade path.
