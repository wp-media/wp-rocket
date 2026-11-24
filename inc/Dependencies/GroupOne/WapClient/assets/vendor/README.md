# Vendored third-party assets

Both files here come from the Mixpanel Web SDK. They are **vendored, not fetched from
a CDN**: WordPress.org guidelines require plugins to serve their own scripts, and
loading from `cdn.mxpnl.com` would also break customer sites with a restrictive
`script-src` CSP.

WordPress.org asks for the source of any compressed file. This file is that record.

| File                 | Upstream path                                | Bytes   | sha256 |
| -------------------- | -------------------------------------------- | ------- | ------ |
| `mixpanel.min.js`    | `dist/mixpanel.min.js`                       | 103,897 | `7a4dbc27d1667b6cfa60cd11591e9a37b2daefebcb7dc28bcbf466db7c73b60b` |
| `mixpanel-loader.js` | `src/loaders/mixpanel-jslib-snippet.js`      | 6,571   | `c27fd9dbf2b24d5d3c2faacfff92424840b8c70193a9e64912be84746fc9f28f` |

- **Package:** [`mixpanel-browser`](https://www.npmjs.com/package/mixpanel-browser) **2.80.0**
- **Repository:** https://github.com/mixpanel/mixpanel-js
- **License:** Apache-2.0 (see the package's `LICENSE`)

`mixpanel.min.js` is byte-identical to the published tarball. `mixpanel-loader.js`
is the *unminified* snippet source with the local modifications below — its hash is
ours, not upstream's; the untouched upstream file hashes to
`d95311db91ac45f804575e05bac4c16de8323f89ef4980427a27727d548352e4`.

## Why two files

`mixpanel.min.js` is the snippet-companion build: loaded on its own it logs
`"mixpanel" object not initialized` and never defines `window.mixpanel`. It needs the
stub that the loader snippet creates. So the host enqueues **the loader**, and the
loader injects the library from `window.MIXPANEL_CUSTOM_LIB_URL` (Mixpanel's supported
self-hosting hook), pointed at the copy here. The stub queues `init` / `register` /
`track` calls until the library lands.

## Local modifications to `mixpanel-loader.js`

Three deltas from upstream, all of them in this file only. `mixpanel.min.js` is
untouched.

1. **`MIXPANEL_LIB_URL` default emptied.** Upstream defaults it to
   `//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js`; ours is `''`, so a
   misconfiguration can never silently reach a third-party host.
2. **The script insert is guarded on a non-empty `src`.** Consequence of (1): an
   empty `src` resolves to the *current document URL*, so upstream's unconditional
   insert would make the browser re-request the page (cookie-bearing) and try to
   parse the HTML as JS.
3. **The `==ClosureCompiler==` header block is dropped**, replaced by our own file
   header. It is a build directive for minifying the snippet, and we ship it
   unminified.

## SDK configuration

Defaults verified against `src/mixpanel-core.js` `DEFAULT_CONFIG` in 2.80.0. Nothing
auto-captures, and no cookies are set:

| Option                    | Upstream default              | As initialised here |
| ------------------------- | ----------------------------- | ------------------- |
| `autocapture`             | `false`                       | default             |
| `track_pageview`          | `false`                       | `false` (explicit)  |
| `record_sessions_percent` | `0`                           | default             |
| `persistence`             | `'cookie'`                    | **`'localStorage'`** |
| `api_host`                | `https://api-js.mixpanel.com` | **`https://api-eu.mixpanel.com`** |

The last two are deliberate overrides, both set in `wap-chat.js` `initMixpanel()`:
`localStorage` so the widget sets no cookies, and the EU ingest host so data does not
leave the EU. Note the upstream `api_host` default is a **US** endpoint, which is why
`wap-chat.js` carries its own `DEFAULT_MIXPANEL_API_HOST` fallback rather than letting
the SDK default apply.

`mixpanel.min.js` is ~101 KB, the largest asset the widget loads.
