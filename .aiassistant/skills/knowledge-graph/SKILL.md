---
name: knowledge-graph
description: >
  Read and refresh wp-rocket's pre-built dependency graph at .aiassistant/graph/dependency-graph.json.
  Use to locate a class file, trace dependencies, find the ServiceProvider that wires a service,
  or enumerate Subscribers in a module — without re-scanning the codebase from scratch. This
  skill is primarily a reader: the graph is built (and incrementally refreshed) by
  `node bin/build-knowledge-graph.js`. Invoke this skill at session start (to refresh if stale)
  and before grep/glob searches for class relationships.
---

# Knowledge Graph

A pre-built dependency graph lives at `.aiassistant/graph/dependency-graph.json`. The
builder is `node bin/build-knowledge-graph.js` (incremental by default, `--full` to force
rebuild).

This skill has two responsibilities:
1. **Refresh** the graph at session start if it is stale (`base_commit` ≠ `git rev-parse HEAD`).
2. **Read** the graph to answer dependency, namespace, and structure questions instantly.

**Read it before** running grep/glob searches for class relationships, namespace
exploration, or dependency tracing. It eliminates redundant file scans and speeds up the
first useful response in any session.

---

## Graph shape

```json
{
  "generated_at": "<ISO timestamp>",
  "base_commit":  "<git SHA>",
  "node_count":   913,
  "nodes": {
    "inc/Engine/Cache/Subscriber.php": {
      "language":  "php",
      "namespace": "WP_Rocket\\Engine\\Cache",
      "symbols": [
        { "kind": "class", "name": "Subscriber", "extends": [], "implements": ["Subscriber_Interface"] }
      ],
      "imports": [
        "WP_Rocket\\Event_Management\\Subscriber_Interface",
        "WP_Rocket\\Engine\\Cache\\Purge"
      ]
    }
  },
  "symbol_index": {
    "WP_Rocket\\Engine\\Cache\\Subscriber": "inc/Engine/Cache/Subscriber.php"
  }
}
```

- **`nodes`** — keyed by relative file path. Each node has the language (`php` or `js`), declared symbols (PHP only), and all import/use statements.
- **`symbol_index`** — maps every fully-qualified PHP class / interface / trait / enum to its file path. Use this for instant "where is this class?" lookups.

---

## WP Rocket specific query patterns

### Find a class file (zero grep)
```
symbol_index["WP_Rocket\\Engine\\Cache\\Purge"]
→ "inc/Engine/Cache/Purge.php"
```

### Find the ServiceProvider that wires a class
The ServiceProvider that registers a class imports it. Search for files whose `imports` contain the target FQN:

```
filter nodes where "WP_Rocket\\Engine\\Cache\\Purge" ∈ node.imports
→ "inc/Engine/Cache/ServiceProvider.php"
```
Then read that ServiceProvider to see how the class is registered in `register()`.

### Find all Subscribers in a module
Filter nodes where:
- `namespace` starts with the module prefix (e.g. `WP_Rocket\Engine\Cache`)
- `symbols[*].implements` contains `Subscriber_Interface`

### Find all ServiceProviders in the codebase
Filter nodes where `symbols[*].extends` contains `AbstractServiceProvider`.

### Trace a class's full dependency chain
1. Start at `symbol_index["WP_Rocket\\...\\ClassName"]` → get file path
2. Read `nodes[file].imports` → these are its direct dependencies
3. For each dependency, repeat → you get the full constructor injection tree without reading any PHP

### Verify no unexpected cross-module dependencies
Check `nodes[file].imports` for any FQN that shouldn't be there.
For example, a Frontend Subscriber importing an Admin class is a red flag.

### Find classes that use `apply_filters` directly (not `wpm_apply_filters_typed`)
This requires reading the actual files, but the graph narrows the field: only check PHP nodes in `inc/Engine/` and `inc/Addon/` (the paths covered by PHPStan).

---

## Keeping the graph fresh

The graph records the git commit it was built from (`base_commit`). If that SHA differs from `HEAD`, run:

```bash
node bin/build-knowledge-graph.js
```

The script is incremental — it only re-parses files changed since `base_commit`. Use `--full` to force a complete rebuild.

**When to refresh:**
- At the start of every issue workflow session (step 9 checks this automatically).
- After merging a branch with structural changes (new classes, namespace moves).
- Before an architecture review session.

---

## Supported languages

| Language | What is extracted |
|---|---|
| PHP | `namespace`, `class`/`interface`/`trait`/`enum` declarations (with `extends`/`implements`), `use` imports (including grouped `\{A, B}` forms) |
| TypeScript / JavaScript | `import` (static + dynamic) and `require()` sources |

---

## Practical workflow (issue implementation)

Before writing a single line of code for an issue:

1. Check `base_commit` vs `HEAD` — refresh if stale.
2. Use `symbol_index` to locate all classes involved in the fix.
3. For each class, read `nodes[file].imports` — know the dependency chain before touching the constructor.
4. Find the ServiceProvider via the import search above — know where to add/modify the binding.
5. List all Subscribers in the module — know which ones may need new hook entries.
6. Only then open the actual PHP files (now you know exactly which ones to read).
