<?php
/**
 * Scans WP Rocket for `rocket_` hooks and generates filters.json — the catalog
 * of filters that can be exercised by a validated MCP Helpers callback.
 *
 * Usage: php inc/MCP/tools/scan-hooks.php <plugin-root> <output-json>
 * Example: php inc/MCP/tools/scan-hooks.php . inc/MCP/catalog/filters.json
 */

$root = rtrim($argv[1] ?? '', '/');
$out  = $argv[2] ?? '';
if ( ! is_dir($root) || '' === $out ) {
	fwrite(STDERR, "usage: php scan-hooks.php <plugin-root> <output-file>\n");
	exit(1);
}

$hookFns = [
	// 'hook_arg' = index of the hook-name argument. Core filters/actions put it
	// first; wp-media/apply-filters-typed's wpm_apply_filters_typed() puts the type
	// first and the hook name second.
	'apply_filters'             => ['type' => 'filter', 'deprecated' => false, 'hook_arg' => 0],
	'apply_filters_ref_array'   => ['type' => 'filter', 'deprecated' => false, 'hook_arg' => 0],
	'apply_filters_deprecated'  => ['type' => 'filter', 'deprecated' => true,  'hook_arg' => 0],
	'wpm_apply_filters_typed'   => ['type' => 'filter', 'deprecated' => false, 'hook_arg' => 1],
	'wpm_apply_filters_typesafe' => ['type' => 'filter', 'deprecated' => false, 'hook_arg' => 0],
	'do_action'                 => ['type' => 'action', 'deprecated' => false, 'hook_arg' => 0],
	'do_action_ref_array'       => ['type' => 'action', 'deprecated' => false, 'hook_arg' => 0],
	'do_action_deprecated'      => ['type' => 'action', 'deprecated' => true,  'hook_arg' => 0],
];

// Directories to scan (WP Rocket's own code) and to skip.
$scanDirs = [ $root . '/inc' ];
$skip = [
	$root . '/inc/vendors',
	$root . '/inc/Dependencies',
	$root . '/inc/MCP', // the integration's own files (demo probes reference hooks).
];

$files = [];
$rii = function ($dir) use (&$rii, &$files, $skip) {
	foreach (scandir($dir) as $e) {
		if ('.' === $e || '..' === $e) continue;
		$p = $dir . '/' . $e;
		foreach ($skip as $s) { if (strpos($p, $s) === 0) continue 2; }
		if (is_dir($p)) { $rii($p); }
		elseif (substr($p, -4) === '.php') { $files[] = $p; }
	}
};
foreach ($scanDirs as $d) { if (is_dir($d)) $rii($d); }
sort($files);

$catalog = []; // name => entry

$humanize = function (string $name): string {
	$s = preg_replace('/^rocket_/', '', $name);
	$s = str_replace('_', ' ', $s);
	return ucfirst(trim($s));
};
$categoryFor = function (string $file) use ($root): string {
	$rel = ltrim(str_replace($root, '', $file), '/'); // inc/Engine/Media/LazyLoad/..
	$parts = explode('/', $rel);
	// inc/Engine/<Cat>/...
	if (isset($parts[1]) && $parts[1] === 'Engine' && isset($parts[2])) return strtolower($parts[2]);
	if (isset($parts[1]) && $parts[1] === 'Addon' && isset($parts[2]))  return 'addon:' . strtolower($parts[2]);
	if (isset($parts[1])) return strtolower(str_replace('.php', '', $parts[1]));
	return '';
};

$parseDoc = function (?string $doc): array {
	if (!$doc) return ['summary' => '', 'since' => '', 'params' => []];
	$lines = preg_split('/\r?\n/', $doc);
	$clean = [];
	foreach ($lines as $l) {
		$l = preg_replace('#^\s*/\*\*?#', '', $l);
		$l = preg_replace('#\*/\s*$#', '', $l);
		$l = preg_replace('#^\s*\*\s?#', '', $l);
		$clean[] = rtrim($l);
	}
	$summary = '';
	$since = '';
	$params = [];
	foreach ($clean as $l) {
		$t = trim($l);
		if ($t === '') continue;
		if ($summary === '' && $t[0] !== '@') { $summary = $t; continue; }
		if (stripos($t, '@since') === 0) { $since = trim(substr($t, 6)); continue; }
		if (stripos($t, '@param') === 0) {
			// @param <type> $name Description
			if (preg_match('/@param\s+(\S+)\s+(\$\S+)?\s*(.*)$/', $t, $m)) {
				$params[] = [
					'type' => $m[1],
					'name' => isset($m[2]) ? ltrim($m[2], '$') : '',
					'description' => trim($m[3]),
				];
			}
		}
	}
	return ['summary' => $summary, 'since' => $since, 'params' => $params];
};

foreach ($files as $file) {
	$code = file_get_contents($file);
	if ($code === false || strpos($code, 'rocket_') === false) continue;
	$tokens = token_get_all($code);
	$n = count($tokens);

	// Track most recent doc comment + its end line.
	$lastDoc = null; $lastDocLine = -100;

	for ($i = 0; $i < $n; $i++) {
		$tok = $tokens[$i];
		if (is_array($tok) && $tok[0] === T_DOC_COMMENT) {
			$lastDoc = $tok[1];
			$lastDocLine = $tok[2] + substr_count($tok[1], "\n");
			continue;
		}
		if (!is_array($tok) || $tok[0] !== T_STRING || !isset($hookFns[$tok[1]])) continue;

		$fn = $tok[1];
		$callLine = $tok[2];

		// Next meaningful token must be '('.
		$j = $i + 1;
		while ($j < $n && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT], true)) $j++;
		if ($j >= $n || $tokens[$j] !== '(') continue;

		// Locate the hook-name argument. Its position depends on the function:
		// core filters/actions -> 0; wpm_apply_filters_typed -> 1 (type is first).
		$hookArgIndex = $hookFns[$fn]['hook_arg'];
		$depth = 0; $argIdx = -1; $expectStart = false; $hookTok = null;
		for ($m = $j; $m < $n; $m++) {
			$t = $tokens[$m];
			if ($t === '(') { $depth++; if ($depth === 1) { $argIdx = 0; $expectStart = true; } continue; }
			if ($t === ')') { $depth--; if ($depth === 0) break; continue; }
			if ($t === '[' || $t === '{') { $depth++; continue; }
			if ($t === ']' || $t === '}') { $depth--; continue; }
			if ($depth === 1 && $t === ',') { $argIdx++; $expectStart = true; continue; }
			if ($depth === 1 && $expectStart) {
				if (is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
				if ($argIdx === $hookArgIndex) { $hookTok = $t; }
				$expectStart = false;
			}
		}
		if (!is_array($hookTok) || $hookTok[0] !== T_CONSTANT_ENCAPSED_STRING) continue;

		$raw = $hookTok[1];
		$quote = $raw[0];
		$name = substr($raw, 1, -1);
		if ($quote === "'") { $name = str_replace(["\\'", "\\\\"], ["'", "\\"], $name); }
		if (strpos($name, 'rocket_') !== 0) continue;

		// Count top-level args from '(' to matching ')'.
		$depth = 0; $args = 0; $seenAny = false;
		for ($m = $j; $m < $n; $m++) {
			$t = $tokens[$m];
			if ($t === '(') { $depth++; if ($depth === 1) { $args = 1; } continue; }
			if ($t === ')') { $depth--; if ($depth === 0) break; continue; }
			if ($t === '[') { $depth++; continue; }
			if ($t === ']') { $depth--; continue; }
			if ($depth === 1 && $t === ',') { $args++; }
			if ($depth >= 1 && !(is_array($t) && in_array($t[0], [T_WHITESPACE, T_COMMENT], true))) $seenAny = true;
		}
		if (!$seenAny) $args = 0;
		$callbackArgs = max(0, $args - 1); // minus the hook name

		$meta = $hookFns[$fn];
		$doc = ($lastDoc !== null && ($callLine - $lastDocLine) <= 3) ? $lastDoc : null;
		$parsed = $parseDoc($doc);

		$existing = $catalog[$name] ?? null;
		$hasDoc = $parsed['summary'] !== '';

		if ($existing === null) {
			$catalog[$name] = [
				'name'        => $name,
				'type'        => $meta['type'],
				'deprecated'  => $meta['deprecated'],
				'category'    => $categoryFor($file),
				'summary'     => $parsed['summary'],
				'since'       => $parsed['since'],
				'params'      => $parsed['params'],
				'args'        => $callbackArgs,
				'occurrences' => 1,
			];
		} else {
			$catalog[$name]['occurrences']++;
			$catalog[$name]['args'] = max($existing['args'], $callbackArgs);
			if ($existing['summary'] === '' && $hasDoc) {
				$catalog[$name]['summary'] = $parsed['summary'];
				$catalog[$name]['since']   = $parsed['since'];
				$catalog[$name]['params']  = $parsed['params'];
			}
			// A non-deprecated occurrence wins over a deprecated-only record.
			if ($existing['deprecated'] && !$meta['deprecated']) {
				$catalog[$name]['deprecated'] = false;
			}
		}
	}
}

ksort($catalog);

// ---- Emit the PHP catalog file ----
$total = count($catalog);
$filters = count(array_filter($catalog, fn($e) => $e['type'] === 'filter'));
$actions = $total - $filters;
$withDoc = count(array_filter($catalog, fn($e) => $e['summary'] !== ''));
$cats = [];
foreach ($catalog as $e) { $cats[$e['category']] = ($cats[$e['category']] ?? 0) + 1; }
ksort($cats);

// Keep only filters that can be exercised by a validated callback (their filtered
// value is an array, bool, or int). Actions and string/other/undocumented filters
// are dropped: a return-value callback can't act on them meaningfully.
$entries = [];
$kept = ['array' => 0, 'assoc' => 0, 'bool' => 0, 'int' => 0, 'htaccess' => 0];

// Array-typed filters whose value is an associative MAP, not a plain list. These
// take rocket/set-array-key (setting a named key), not append/remove.
$associative = [
	'rocket_cache_ignored_parameters',
	'rocket_preload_url_request_args',
	'rocket_homepage_preload_url_request_args',
	'rocket_preload_after_purge_cache_request_args',
];

// htaccess rule-block filters return rule TEXT (string), so they are normally
// dropped — injecting a string into .htaccess is a server-config risk. But a
// boolean DISABLE (return false to blank the block) is a safe, real pattern used
// by the helpers, so these specific ones are exposed with core/return-false only.
$htaccess_toggle = [
	'rocket_htaccess_mod_rewrite',
];

foreach ($catalog as $e) {
	if ($e['type'] !== 'filter') { continue; }

	// Never catalog deprecated hooks (called via *_deprecated, or defined under
	// inc/deprecated/), so agents are only steered to current hooks.
	if ( ! empty($e['deprecated']) || 'deprecated' === $e['category'] ) { continue; }

	$ft = strtolower($e['params'][0]['type'] ?? '');
	if (in_array($e['name'], $associative, true)) {
		$compat = ['rocket/set-array-key', 'core/return-empty-array'];
		$kept['assoc']++;
	} elseif (in_array($e['name'], $htaccess_toggle, true)) {
		$compat = ['core/return-false'];
		$kept['htaccess']++;
	} elseif (strpos($ft, 'array') !== false || strpos($ft, '[]') !== false) {
		$compat = ['rocket/append-to-list', 'rocket/remove-from-list', 'core/return-empty-array'];
		$kept['array']++;
	} elseif (strpos($ft, 'bool') !== false) {
		$compat = ['core/return-true', 'core/return-false'];
		$kept['bool']++;
	} elseif (strpos($ft, 'int') !== false) {
		$compat = ['rocket/return-int', 'core/return-zero'];
		$kept['int']++;
	} else {
		continue; // not exercisable
	}

	$keywords = array_values(array_unique(array_filter(
		explode('_', preg_replace('/^rocket_/', '', $e['name'])),
		fn($w) => strlen($w) > 2
	)));
	$label = ucfirst(trim(str_replace('_', ' ', preg_replace('/^rocket_/', '', $e['name']))));
	$desc = $e['summary'];
	if ($e['deprecated']) { $desc = trim('[Deprecated] ' . $desc); }

	$params = [];
	foreach ($e['params'] as $p) {
		$params[] = [
			'name'        => $p['name'],
			'type'        => $p['type'],
			'description' => $p['description'],
		];
	}

	$entries[] = [
		'name'                 => $e['name'],
		'type'                 => 'filter',
		'label'                => $label,
		'description'          => $desc,
		'category'             => $e['category'],
		'keywords'             => $keywords,
		'params'               => $params,
		'since'                => $e['since'],
		'deprecated'           => (bool) $e['deprecated'],
		'compatible_callbacks' => $compat,
	];
}

file_put_contents(
	$out,
	json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);

// ---- Report ----
$keptTotal = count($entries);
$deprecated = count(array_filter($entries, fn($e) => $e['deprecated']));
fwrite(STDERR, "Scanned {$total} rocket_ hooks ({$filters} filters, {$actions} actions).\n");
fwrite(STDERR, "Kept {$keptTotal} exercisable filters:\n");
fwrite(STDERR, "  array-valued (append/remove/empty): {$kept['array']}\n");
	fwrite(STDERR, "  assoc-map    (set-array-key):       {$kept['assoc']}\n");
	fwrite(STDERR, "  htaccess     (return-false only):   {$kept['htaccess']}\n");
fwrite(STDERR, "  bool-valued  (return-true/false):   {$kept['bool']}\n");
fwrite(STDERR, "  int-valued   (return-int/zero):     {$kept['int']}\n");
fwrite(STDERR, "  (of which deprecated: {$deprecated})\n");
fwrite(STDERR, "Dropped " . ($total - $keptTotal) . " (actions + non-exercisable filters).\n");
fwrite(STDERR, "Wrote: {$out}\n");
