#!/usr/bin/env node
/**
 * WP Rocket Knowledge Graph Builder
 *
 * Scans PHP, TypeScript, and JavaScript source files and extracts
 * import/dependency relationships into a structured JSON graph stored at
 * .aiassistant/graph/dependency-graph.json.
 *
 * First run: full scan of all source files.
 * Subsequent runs: incremental — only files changed since the last recorded commit.
 *
 * Usage:
 *   node bin/build-knowledge-graph.js           # auto (incremental if possible)
 *   node bin/build-knowledge-graph.js --full    # force full rebuild
 *   node bin/build-knowledge-graph.js --dry-run # print stats without writing
 */

'use strict';

const fs   = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// ---------------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------------

const ROOT        = path.resolve(__dirname, '..');
const GRAPH_PATH  = path.join(ROOT, '.aiassistant', 'graph', 'dependency-graph.json');
const SCAN_DIRS   = ['inc', 'bin'];
const ASSET_DIRS  = ['src']; // src/ contains JS/SCSS sources; assets/ is compiled output
const EXTENSIONS  = { php: ['.php'], js: ['.js', '.ts', '.tsx', '.jsx'] };

const ARGS     = process.argv.slice(2);
const FORCE    = ARGS.includes('--full');
const DRY_RUN  = ARGS.includes('--dry-run');

// ---------------------------------------------------------------------------
// Git helpers
// ---------------------------------------------------------------------------

function git(cmd) {
	try {
		return execSync(`git -C "${ROOT}" ${cmd}`, { encoding: 'utf8' }).trim();
	} catch {
		return null;
	}
}

function headSha() {
	return git('rev-parse HEAD');
}

/** Returns list of relative file paths changed between two commits. */
function changedFiles(fromSha, toSha = 'HEAD') {
	const out = git(`diff --name-only ${fromSha}..${toSha}`);
	return out ? out.split('\n').filter(Boolean) : [];
}

/** Returns all tracked files (for full scan). */
function allTrackedFiles() {
	const out = git('ls-files');
	return out ? out.split('\n').filter(Boolean) : [];
}

// ---------------------------------------------------------------------------
// PHP parser
// ---------------------------------------------------------------------------

/**
 * Extracts dependency info from a single PHP file.
 * @param {string} content
 * @returns {{ namespace: string|null, symbols: Array, imports: string[] }}
 */
function parsePhp(content) {
	const imports   = [];
	const symbols   = [];
	let   namespace = null;

	// Namespace
	const nsMatch = content.match(/^\s*namespace\s+([\w\\]+)\s*;/m);
	if (nsMatch) namespace = nsMatch[1];

	// Use statements — handles simple, aliased, and grouped forms
	// e.g. use Foo\Bar; / use Foo\Bar as Baz; / use Foo\{A, B as C, D};
	const useRe = /^\s*use\s+([\w\\]+)(?:\\{([^}]+)})?\s*(?:as\s+\w+)?\s*;/gm;
	let m;
	while ((m = useRe.exec(content)) !== null) {
		const base    = m[1];
		const grouped = m[2];
		if (grouped) {
			for (const part of grouped.split(',')) {
				const trimmed = part.trim().replace(/\s+as\s+\w+$/, '');
				imports.push(`${base}\\${trimmed}`);
			}
		} else {
			imports.push(base.replace(/\s+as\s+\w+$/, '').trim());
		}
	}

	// Class / interface / trait / enum declarations
	const declRe = /^\s*(abstract\s+|final\s+|readonly\s+)*(class|interface|trait|enum)\s+(\w+)(?:\s+extends\s+([\w\\,\s]+?))?(?:\s+implements\s+([\w\\,\s]+?))?\s*(?:\{|$)/gm;
	while ((m = declRe.exec(content)) !== null) {
		const kind       = m[2];
		const name       = m[3];
		const rawExtends = m[4] ? m[4].split(',').map(s => s.trim()).filter(Boolean) : [];
		const rawImpls   = m[5] ? m[5].split(',').map(s => s.trim()).filter(Boolean) : [];
		symbols.push({ kind, name, extends: rawExtends, implements: rawImpls });
	}

	return { namespace, symbols, imports: [...new Set(imports)] };
}

// ---------------------------------------------------------------------------
// JS / TS parser
// ---------------------------------------------------------------------------

/**
 * Extracts import sources from JS/TS files.
 * @param {string} content
 * @returns {{ imports: string[] }}
 */
function parseJs(content) {
	const imports = [];

	// Static imports: import ... from 'source'
	const staticRe = /\bimport\s+(?:[\w*{][^'"]*from\s+)?['"]([^'"]+)['"]/g;
	let m;
	while ((m = staticRe.exec(content)) !== null) {
		imports.push(m[1]);
	}

	// Dynamic imports: import('source') / require('source')
	const dynRe = /(?:\bimport|\brequire)\s*\(\s*['"]([^'"]+)['"]\s*\)/g;
	while ((m = dynRe.exec(content)) !== null) {
		imports.push(m[1]);
	}

	return { imports: [...new Set(imports)] };
}

// ---------------------------------------------------------------------------
// File scanner
// ---------------------------------------------------------------------------

function isSourceFile(relPath) {
	const allDirs = [...SCAN_DIRS, ...ASSET_DIRS];
	return allDirs.some(d => relPath.startsWith(d + '/') || relPath.startsWith(d + '\\'));
}

function languageOf(relPath) {
	const ext = path.extname(relPath).toLowerCase();
	if (EXTENSIONS.php.includes(ext)) return 'php';
	if (EXTENSIONS.js.includes(ext))  return 'js';
	return null;
}

/**
 * Parse a single file and return its graph node.
 * Returns null if the file cannot be read or is not a recognised source file.
 */
function processFile(relPath) {
	const lang = languageOf(relPath);
	if (!lang) return null;
	if (!isSourceFile(relPath)) return null;

	const absPath = path.join(ROOT, relPath);
	if (!fs.existsSync(absPath)) return null;  // deleted file — caller removes it

	const content = fs.readFileSync(absPath, 'utf8');

	if (lang === 'php') {
		const { namespace, symbols, imports } = parsePhp(content);
		return { language: 'php', namespace, symbols, imports };
	}

	const { imports } = parseJs(content);
	return { language: 'js', imports };
}

// ---------------------------------------------------------------------------
// Symbol index builder
// ---------------------------------------------------------------------------

/**
 * Rebuilds the symbol_index from the nodes map.
 * symbol_index maps "Fully\\Qualified\\ClassName" → "relative/file/path.php"
 */
function buildSymbolIndex(nodes) {
	const index = {};
	for (const [filePath, node] of Object.entries(nodes)) {
		if (node.language !== 'php' || !node.symbols) continue;
		for (const sym of node.symbols) {
			const fqn = node.namespace ? `${node.namespace}\\${sym.name}` : sym.name;
			index[fqn] = filePath;
		}
	}
	return index;
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

function loadGraph() {
	if (fs.existsSync(GRAPH_PATH)) {
		try {
			return JSON.parse(fs.readFileSync(GRAPH_PATH, 'utf8'));
		} catch {
			// corrupted — start fresh
		}
	}
	return { generated_at: null, base_commit: null, nodes: {}, symbol_index: {} };
}

function saveGraph(graph) {
	fs.writeFileSync(GRAPH_PATH, JSON.stringify(graph, null, 2) + '\n', 'utf8');
}

function run() {
	const currentSha = headSha();
	const existing   = loadGraph();

	const isIncremental = !FORCE && existing.base_commit && currentSha &&
	                      existing.base_commit !== currentSha;
	const isUpToDate    = !FORCE && existing.base_commit === currentSha;

	if (isUpToDate) {
		console.log(`✓ Knowledge graph is already up to date (${currentSha?.slice(0, 8)}).`);
		return;
	}

	let filesToProcess;
	let mode;

	if (isIncremental) {
		filesToProcess = changedFiles(existing.base_commit, 'HEAD');
		mode = `incremental (${filesToProcess.length} changed files since ${existing.base_commit?.slice(0, 8)})`;
	} else {
		filesToProcess = allTrackedFiles();
		mode = `full scan (${filesToProcess.length} tracked files)`;
	}

	console.log(`⚙  Building knowledge graph — ${mode} …`);

	const nodes = isIncremental ? { ...existing.nodes } : {};
	let processed = 0, skipped = 0, removed = 0;

	for (const relPath of filesToProcess) {
		const lang = languageOf(relPath);
		if (!lang || !isSourceFile(relPath)) { skipped++; continue; }

		const absPath = path.join(ROOT, relPath);
		if (!fs.existsSync(absPath)) {
			// File was deleted
			if (nodes[relPath]) { delete nodes[relPath]; removed++; }
			continue;
		}

		const node = processFile(relPath);
		if (node) {
			nodes[relPath] = node;
			processed++;
		} else {
			skipped++;
		}
	}

	const symbol_index = buildSymbolIndex(nodes);

	const graph = {
		generated_at:  new Date().toISOString(),
		base_commit:   currentSha,
		node_count:    Object.keys(nodes).length,
		nodes,
		symbol_index,
	};

	console.log(`   Processed : ${processed}`);
	if (removed) console.log(`   Removed   : ${removed}`);
	console.log(`   Total nodes: ${graph.node_count}`);
	console.log(`   Symbols indexed: ${Object.keys(symbol_index).length}`);

	if (DRY_RUN) {
		console.log('ℹ  Dry-run mode — graph not written.');
		return;
	}

	saveGraph(graph);
	console.log(`✓  Graph saved → .aiassistant/graph/dependency-graph.json`);

	if (!existing.base_commit) {
		console.log('\n──────────────────────────────────────────────────────────────');
		console.log('Add the following to your AI instructions file');
		console.log('(AGENTS.md, .github/copilot-instructions.md, CLAUDE.md, etc.):');
		console.log('──────────────────────────────────────────────────────────────');
		console.log(`
## Knowledge Graph

A pre-built dependency graph is available at \`.aiassistant/graph/dependency-graph.json\`.

Before exploring the codebase structure, read this file first. It contains:
- \`nodes\`: per-file namespace, symbols declared, and imports.
- \`symbol_index\`: maps every fully-qualified PHP class/interface/trait to its file.

Run \`node bin/build-knowledge-graph.js\` to refresh the graph after significant changes.
`);
		console.log('──────────────────────────────────────────────────────────────');
	}
}

run();
