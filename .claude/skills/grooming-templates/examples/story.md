# Grooming 

## 📔 Scope a solution
This is an audit and the old grooming holds: almost no code changes. Checked on `feature/8710-dashboard-redesign` and in a browser. The redesign kept every page ID (hash) except `imagify`, which moved to `media` and is aliased in `pageManager.js`. Every `#<id>` link in `inc/`, `views/` and `src/js` points to an existing page, and the repo sends no emails. Two small fixes remain: two texts that name the old menu path, and the Mixpanel alias for `#imagify`.

## 🪜 Development steps:

<details>
<summary>1. Record the audit result (no code)</summary>

- Hash inventory, all valid: `#rocket_insights` (`NoticeSubscriber.php:70`, `User.php:277`), `#page_cdn` (`DataManagerSubscriber.php:238`), `#tools` (`UpdaterSubscriber.php:545`), `#preload` (`Page.php:1295`), `#media` (`Page.php:1999`).
- Not an issue: `Cloudflare.php:278` (official Cloudflare plugin page), the admin bar (base URL, no hash).
- Post it as a comment on the issue.
</details>

<details>
<summary>2. Update the old menu path in two strings</summary>

In `inc/Engine/Preload/Abilities/CheckCacheHealth.php`
- Change "Settings > WP Rocket > Preload" to "Settings > WP Rocket > Cache > Preload" in the message at line 160

In `inc/Engine/Preload/Abilities/CheckCacheStatus.php`
- Same change in the message at line 175
</details>

<details>
<summary>3. Track the `#imagify` alias in Mixpanel</summary>

In `src/js/global/mixpanel.js`
- Add a `hashAliases = { imagify: 'media' }` property
- Apply it in `_cleanHash()` so the old hash is tracked as Media

In `src/js/global/pageManager.js`
- Add a comment next to `movedPages` saying it must stay in sync with `hashAliases`

In `assets/js/`
- Rebuild with `npm run build:js` (generated files, own commit)
</details>

## 🧪 How will this be validated?
- Open `options-general.php?page=wprocket` with each hash (`#dashboard`, `#preload`, `#media`, `#tools`, …) and with `#imagify`: the right page and nav group open, and `#imagify` becomes `#media`.
- With the analytics opt-in on, change the hash to `#imagify` on the settings page: a "Page Viewed" event for Media is sent.
- Call the two Abilities with preload tracking off and read the message.
- Setup: a licensed live site (or `WP_ROCKET_DEBUG`).

## ⏳ Effort estimation
S

## ℹ️ Additional information

### ✳️ Grooming confidence level
High for the audit and the strings (code search and a browser run of every hash). The Mixpanel change is small but not run yet. Unknown: links from outside this repo (other plugins, docs). A stale or unknown hash shows a blank page, as on develop; not changed here.

### 👥 Can be peer-coded
No

### 🥊 Is a refactor needed in that part of the codebase?
No.
