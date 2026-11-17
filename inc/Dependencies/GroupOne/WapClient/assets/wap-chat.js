/**
 * WAP Chat Widget — Vanilla JS SSE streaming chat client.
 *
 * No framework, no jQuery. The entire UI is composed from Gravity (group-one
 * brand) UI components — see wap-chat.css header for the component list.
 *
 * Platform-agnostic core: the widget talks to the WAP backend directly
 * (POST /api/v1/chat/stream for streaming, GET /api/v1/chat/{id}/history for
 * history) and delegates everything platform-specific to injectable hooks:
 *
 *   getSession(opts)   REQUIRED contract — async, resolves {token,
 *                      conversationId}. Called on load and again with
 *                      {forceNew: true} after a 401. The WordPress adapter
 *                      (wap-client PHP) provides a default implementation via
 *                      admin-ajax (the GRND and Application Password never
 *                      touch the browser). A SaaS host (e.g. partners.one)
 *                      supplies its own that calls its own backend, which
 *                      mints a GRND and returns it as the token — see
 *                      docs/integrating-a-saas-host.md.
 *   consent            OPTIONAL — {get, set} pair persisting the user's T&C
 *                      consent in host storage. get() resolves a boolean;
 *                      set(true) records acceptance. When a hook is available
 *                      the first chat is gated behind an in-widget consent
 *                      prompt. The WordPress adapter stores consent in user
 *                      meta via admin-ajax; SaaS hosts inject their own pair.
 *                      Hosts that provide neither get no gate (opt-in).
 *   eraseLocalData()   OPTIONAL — async host-side cleanup after GDPR erasure
 *                      (the WordPress adapter revokes App Passwords here).
 *   i18n               OPTIONAL — string overrides; English defaults built in.
 *   root               OPTIONAL — Element or CSS selector to mount into;
 *                      defaults to #wap-chat-root.
 *   layout             OPTIONAL — presentation framing; see resolveLayout() and
 *                      docs/integrating-a-saas-host.md. Defaults = boxed panel.
 *   readOnly           OPTIONAL — replay mode. Renders `transcript` and stops:
 *                      no getSession call, no consent gate, no analytics, and
 *                      sending is refused. Used by the WAP admin conversation
 *                      browser to review a past conversation; see
 *                      docs/admin-ui.md → Conversations. NOTE: init() merges
 *                      options over the previous window.WapClientConfig, so a
 *                      page that mounts a replay and then a live widget must
 *                      pass readOnly: false explicitly on the live mount.
 *   transcript         OPTIONAL — read with readOnly. Array of
 *                      {role: 'user'|'assistant', content} — or
 *                      {role: 'visual', visual} for a rendered figure — in the
 *                      shape GET /chat/{id}/history returns, so a replay and a
 *                      live reload of the same thread render identically.
 *   column             OPTIONAL — docked-column surface (see resolveColumn()).
 *                      column.startOnOpen: true defers the session, credential
 *                      and consent request until the column is first opened.
 *
 * Initialise with WapChat.init({...}) — options merge over window.WapClientConfig,
 * so WordPress keeps using wp_localize_script while SaaS hosts pass options
 * directly. Timestamps honour cfg.timeFormat (PHP date() tokens).
 *
 * @package GroupOne\WapClient
 * @version 2.2.0
 */

/* global WapClientConfig, fetch, AbortController, TextDecoder */

(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // Config (provided by wp_localize_script in class-chat-widget.php)
    // -------------------------------------------------------------------------

    let cfg = window.WapClientConfig || {};

    // English defaults so the core renders without any host-provided i18n
    // (WordPress overrides these with pre-translated strings; SaaS hosts pass
    // their own via WapChat.init({i18n: {...}})).
    const DEFAULT_I18N = {
        assistantName:   'Assistant',
        you:             'You',
        welcomeTitle:    'How can I help?',
        welcomeSubtitle: 'Ask me anything about your site, content, or settings.',
        agentSelector:   'Assistant',
        hint:            'Enter to send · Shift+Enter for a new line',
        placeholder:     'Ask the assistant…',
        send:            'Send',
        newChat:         'New chat',
        thinking:        'Thinking…',
        consulting:      'Consulting multiple specialists…',
        loadingHistory:  'Loading your conversation…',
        reconnecting:    'Connecting…',
        connected:       'Connected',
        disconnected:    'Disconnected',
        actionLabel:     'Action',
        showDetails:     'Show details',
        hideDetails:     'Hide details',
        termsLabel:      'Terms & Conditions',
        // Footer legal notice. {terms} and {privacy} are replaced with the
        // Terms of Use / Privacy Policy links (see buildLegalNotice()).
        aiLegalNotice:   'This tool uses AI to generate content. Accuracy and legal compliance are not guaranteed. By using this tool you agree to {terms} and acknowledge {privacy}.',
        termsOfUseLabel: 'Terms of Use',
        privacyLabel:    'Privacy Policy',
        deleteData:      'Delete my data',
        deleteTitle:     'Delete your data?',
        cancel:          'Cancel',
        deleteConfirm:   'This will permanently delete all your conversation history with the assistant. This action cannot be undone.',
        deleteSuccess:   'Your data has been deleted.',
        errorGeneric:    'An error occurred. Please try again.',
        errorRateLimit:  'Too many requests. Please wait before sending another message.',
        errorAuthFailed: 'Could not connect to the assistant. Please reload the page, or contact support if this continues.',
        errorRetry:      'Try again',
        errorQuota:      'You have reached your monthly message limit. It resets at the start of next month.',
        quotaLow:        'Only {remaining} of {total} messages left this month. Your quota renews on {date} at {time}.',
        quotaExhausted:  'Limit reached. Your quota will renew on {date} at {time}.',
        quotaBlocked:    'Message limit reached',
        errorReference:  'Reference:',
        consentTitle:    'Before you start',
        // {terms} is replaced with the Terms & Conditions link (see openConsentModal()).
        consentText:     'Please review and accept the {terms} for the assistant.',
        // Optional second consent sentence. {learnMore} adds a "Learn more" link to
        // cfg.siteAccessUrl on its own line; {link}…{/link} links the wrapped words inline.
        // Empty by default since how the assistant reaches the host is host-specific.
        consentAccessText: '',
        learnMore:       'Learn more',
        consentAgree:    'Agree and continue',
        consentRequired: 'You need to accept the Terms & Conditions to use the assistant.',
        consentReview:   'Review Terms & Conditions',
        // Permissions chrome only; row and option labels come from the host via cfg.permissions.
        permissions:     'Permissions',
        permissionsSaveError: 'Could not save that setting. Please try again.',
        columnLabel:     'Assistant',
        openAssistant:   'Open the assistant',
        closeAssistant:  'Hide the assistant',
        readOnly:        'Read-only',
    };

    function mergeI18n(overrides) {
        return Object.assign({}, DEFAULT_I18N, overrides || {});
    }

    let i18n = mergeI18n(cfg.i18n);

    // Opt-in per-agent overrides from GET /api/v1/agents/welcome. Outside `cfg`
    // because `cfg` is frozen in init(). Empty means "use the host's values".
    const agentWelcome = {
        welcomeTitle: '',
        welcomeMessage: '',
        promptSuggestions: [],
    };

    // Normalise cfg.layout (see docs/integrating-a-saas-host.md for the keys).
    // Defaults reproduce the boxed 900px panel; applied by applyLayout().
    function resolveLayout(raw) {
        const l = (raw && typeof raw === 'object') ? raw : {};

        const width = l.width === 'fluid' ? 'fluid' : 'boxed';

        // A fluid shell has nothing to expand to → toggle defaults 'off';
        // boxed defaults to the full-window overlay.
        let expandToggle = l.expandToggle;
        if (expandToggle !== 'off' && expandToggle !== 'container' && expandToggle !== 'window') {
            expandToggle = width === 'fluid' ? 'off' : 'window';
        }

        const align = (l.align === 'center' || l.align === 'right') ? l.align : 'left';

        return {
            width:          width,
            expandToggle:   expandToggle,
            innerWidth:     typeof l.innerWidth === 'string' ? l.innerWidth : '',
            align:          align,
            height:         typeof l.height === 'string' ? l.height : '',
            chrome:         l.chrome === 'flat' ? 'flat' : 'card',
            accent:         typeof l.accent === 'string' ? l.accent : '',
            keepScrollbar:      l.keepScrollbar !== false,
            showHeader:         l.showHeader !== false,
            showNewChat:        l.showNewChat !== false,
            showSettings:       l.showSettings !== false,
            showDeleteData:     l.showDeleteData !== false,
            showAgentSelector:  l.showAgentSelector !== false,
        };
    }

    const CSS_LENGTH = /^\d+(\.\d+)?(px|rem|em|ch|vw|vh|vmin|vmax|%)$/;

    function cssLength(value, fallback) {
        const str = (typeof value === 'string') ? value.trim() : '';
        return CSS_LENGTH.test(str) ? str : fallback;
    }

    // Normalise cfg.column. Null when not mounted as a docked column.
    function resolveColumn(raw) {
        if (!raw || typeof raw !== 'object') return null;

        const c = raw;

        return {
            id:           (typeof c.id === 'string' && c.id) ? c.id : 'default',
            side:         c.side === 'left' ? 'left' : 'right',
            width:        cssLength(c.width, '400px'),
            mode:         c.mode === 'overlay' ? 'overlay' : 'push',
            breakpoint:   cssLength(c.breakpoint, '960px'),
            defaultState: c.defaultState === 'expanded' ? 'expanded' : 'collapsed',
            showLauncher: c.showLauncher !== false,
            persist:      c.persist !== false,
            startOnOpen:  c.startOnOpen === true,
            label:        typeof c.label === 'string' && c.label ? c.label : '',
            stateNonce:   typeof c.stateNonce === 'string' ? c.stateNonce : '',
        };
    }

    // Same charset as PHP's sanitize_key(): ids become part of a user-meta key on WordPress.
    const PERMISSION_ID = /^[a-z0-9-]{1,64}$/;
    // The settings sheet is a small modal, not a settings page.
    const PERMISSION_ROWS_MAX = 10;

    // Normalise the caller-supplied rows; mirrors PHP's sanitize_permissions() for npm hosts. Bad rows are dropped.
    function resolvePermissions(raw) {
        if (!Array.isArray(raw)) return [];

        const rows = [];
        const seen = {};

        for (let i = 0; i < raw.length && rows.length < PERMISSION_ROWS_MAX; i++) {
            const r = raw[i];
            if (!r || typeof r !== 'object') continue;
            if (typeof r.id !== 'string' || !PERMISSION_ID.test(r.id)) continue;
            // Duplicate ids would share one stored value; keep the first.
            if (seen[r.id]) continue;
            if (typeof r.label !== 'string' || !r.label) continue;
            if (!Array.isArray(r.options) || !r.options.length) continue;

            const options = [];
            for (let j = 0; j < r.options.length; j++) {
                const o = r.options[j];
                if (!o || typeof o !== 'object') continue;
                if (typeof o.value !== 'string' || !o.value) continue;
                if (typeof o.label !== 'string' || !o.label) continue;
                options.push({ value: o.value, label: o.label });
            }
            if (!options.length) continue;

            // A default outside the row's options could never be restored, so drop the row.
            let hasDefault = false;
            for (let k = 0; k < options.length; k++) {
                if (options[k].value === r.defaultValue) { hasDefault = true; break; }
            }
            if (!hasDefault) continue;

            seen[r.id] = true;
            rows.push({
                id:           r.id,
                label:        r.label,
                options:      options,
                defaultValue: r.defaultValue,
                // A single option is already unchangeable.
                disabled:     r.disabled === true || options.length === 1,
                hint:         typeof r.hint === 'string' ? r.hint : '',
                // Short tag next to the label, e.g. "Coming soon".
                badge:        typeof r.badge === 'string' ? r.badge : '',
            });
        }

        return rows;
    }

    const GRAVITY_VERSION = 'v5.52.4';
    const GRAVITY_CDN_BASE = 'https://gravity.group-cdn.one/' + GRAVITY_VERSION + '/';
    // Gravity brand stylesheet; cfg.brand picks it (e.g. 'rankmath').
    function gravityStylesheetUrl() {
        const brand = typeof cfg.brand === 'string' && /^[a-z0-9-]{1,40}$/.test(cfg.brand) ? cfg.brand : 'group-one';
        return GRAVITY_CDN_BASE + 'css/brands/' + brand + '.min.css';
    }
    const GRAVITY_RUNTIME_URL = GRAVITY_CDN_BASE + 'index.umd.js';

    const ICON_BASE = GRAVITY_CDN_BASE + 'icons/';
    const LOADER_SPINNER_SRC = GRAVITY_CDN_BASE + 'loaders/spinner.svg';

    // Ceiling on the pre-init event queue. It only drains from initMixpanel(), and
    // trackingInit() can bail permanently (no loader URL) with mpRequested already
    // set — so without a cap the queue would grow for the life of the page. Past
    // the cap we drop the NEWEST events: the early ones, Page Viewed included,
    // are the funnel-critical ones worth keeping.
    const MIXPANEL_QUEUE_MAX = 100;

    // EU ingest, matching the wap_client_mixpanel_api_host PHP default. Hosts
    // that build WapClientConfig themselves (npm/SaaS) may omit mixpanelApiHost,
    // and a WP filter can return a value esc_url_raw() rejects, so this fallback
    // has to exist — reading it undeclared throws a ReferenceError that
    // initMixpanel()'s catch would swallow, silently queueing every event.
    const DEFAULT_MIXPANEL_API_HOST = 'https://api-eu.mixpanel.com';

    // Small, light-touch icons used as visual aids across the UI.
    const ICON = {
        send:      ICON_BASE + 'arrow_forward.svg',
        assistant: ICON_BASE + 'auto_awesome.svg',
        newChat:   ICON_BASE + 'add.svg',
        delete:    ICON_BASE + 'delete.svg',
        terms:     ICON_BASE + 'description.svg',
        error:     ICON_BASE + 'error.svg',
        success:   ICON_BASE + 'check_circle.svg',
        close:     ICON_BASE + 'close.svg',
        scrollDown: ICON_BASE + 'arrow_downward.svg',
        info:       ICON_BASE + 'info.svg',
        warning:    ICON_BASE + 'warning.svg',
        settings:   ICON_BASE + 'settings.svg',
        fullscreen: ICON_BASE + 'fullscreen.svg',
        fullscreenExit: ICON_BASE + 'fullscreen_exit.svg',
        back:       ICON_BASE + 'arrow_back.svg',
        edit:       ICON_BASE + 'edit.svg',
        expand:     ICON_BASE + 'expand_more.svg',
        // Gravity's fixed conversational-button icons: send + stop-generating.
        stop:       ICON_BASE + 'stop_fill.svg',
    };

    // -------------------------------------------------------------------------
    // State
    // -------------------------------------------------------------------------

    let sessionToken = cfg.sessionToken || '';
    // Session state, not config: cfg is frozen in init(), so this can't live on it.
    let conversationId = cfg.conversationId || '';
    let authPromise = null;
    let currentAbortController = null;
    let isStreaming = false;
    // Set while startNewChat() waits for WAP to delete the thread.
    let newChatPending = false;
    let flushStreamRender = null;
    let accCounter = 0;
    let hasMessages = false;
    // Set while an ask_user question card is on screen and unanswered; called to
    // dismiss that card when the user answers or types a normal message instead.
    let pendingQuestionCleanup = null;
    // Monotonic id source so each rendered question's radio/checkbox group gets a
    // unique `name` (multiple questions can coexist in one thread's history).
    let questionSeq = 0;
    let selectionGeneration = 0;
    let handoffTransitionGeneration = null;

    // T&C consent state machine: 'unknown' | 'pending' | 'granted' | 'refused'.
    let consentState = 'unknown';
    let consentCheckGeneration = 0;
    const consentListeners = [];
    let consentHook = null;

    function setConsentState(next) {
        consentState = next;
        consentListeners.forEach(function (fn) { try { fn(next); } catch (e) { /* ignore */ } });
    }

    function onConsentChange(fn) {
        consentListeners.push(fn);
    }

    function isConsentGranted() {
        return consentState === 'granted';
    }

    // Monthly quota snapshot from GET /chat/quota (WPIN-8805); null when unknown.
    let quota = null;
    // Warn once the remaining share drops to this fraction of the allowance.
    const QUOTA_LOW_RATIO = 0.1;

    // Mixpanel client-side analytics state. Inert unless cfg.mixpanelToken is
    // set; the SDK is loaded lazily on the first (consented) track call.
    let mpRequested = false;   // loader requested (guards re-entry)
    let mpInited = false;      // mixpanel.init() done for our named instance
    let mpIdentified = false;  // identify() called with the user id
    let mpQueue = [];          // events awaiting the loader stub
    let pageViewTracked = false;

    // -------------------------------------------------------------------------
    // DOM references — resolved after DOMContentLoaded.
    // -------------------------------------------------------------------------

    let rootEl, chatEl, chatListEl, inputEl, sendBtn, indicatorDotEl, statusTextEl, statusWrapperEl, deleteDataBtn, newChatBtn;
    let agentSelectEl, agentSelectWrapEl;
    // Empty means "let the backend decide" (page_context / product default).
    let selectedAgentRole = '';
    let settingsBtn, fullscreenBtn, footerEl, scrollBtnEl;
    let quotaNoticeEl, quotaNoticeIconEl, quotaNoticeTextEl;
    // Holds the open ask_user response selector, right above the composer.
    let replyDockEl;

    // Whether the widget is expanded via the header toggle; target ('window' vs
    // 'container') comes from the resolved layout — see resolveLayout().
    let isFullscreen = false;

    // Normalised presentation layout from cfg.layout (set in init()); defaults
    // reproduce the boxed 900px panel.
    let layout = {};

    // Set in init(); an empty `permissions` means no Permissions section.
    let permissions = [];
    let permissionHook = null;

    // Docked-column state; `column` is null for every non-column embed.
    let column = null;
    let columnEl, columnPanelEl, columnLauncherEl, columnScrimEl, columnCloseBtn, columnHeadingEl;
    let columnCollapsed = true;
    let columnStateHook = null;
    // Resolved mode — drops to overlay below the configured breakpoint.
    let columnMode = 'push';
    let columnMediaQuery = null;
    // Bumped on every build/teardown; an async state restore compares against it
    // before touching anything.
    let columnEpoch = 0;
    let columnUserToggled = false;
    let columnPendingRead = null;
    let columnReturnFocusEl = null;
    let columnWriteTimer = null;
    let columnWriteLatest = null;
    // Lazy start: boots once per mount on first open; armed only after init()
    // has decided its own boot.
    let sessionStarted = false;
    let lazyStartArmed = false;

    // Touch-primary devices — iPad/iPhone/Android — have no hover and never
    // fire :focus-visible on tap, so the tooltip reveal rules above don't
    // show tooltips for touch users. We force-show them on click instead.
    // Computed once at module load; doesn't change at runtime.
    const isTouchPrimary = typeof window.matchMedia === 'function'
        && window.matchMedia('(hover: none) and (pointer: coarse)').matches;

    // Monotonic id source for wiring tooltips to their trigger buttons.
    let tipCounter = 0;

    // Whether the view is "stuck" to the bottom (auto-follows new content). Set
    // true after the user sends; cleared when the user scrolls up.
    let stickToBottom = true;

    // Listeners bound outside rootEl, recorded so a re-init or destroy() can
    // remove them instead of stacking a second copy on every mount.
    let teardowns = [];

    function on(target, type, handler, options) {
        if (!target || typeof target.addEventListener !== 'function') return;
        target.addEventListener(type, handler, options);
        teardowns.push(function () {
            target.removeEventListener(type, handler, options);
        });
    }

    function runTeardowns() {
        const pending = teardowns;
        teardowns = [];
        pending.forEach(function (fn) {
            try { fn(); } catch (e) { /* ignore */ }
        });
    }

    // -------------------------------------------------------------------------
    // Initialisation
    // -------------------------------------------------------------------------

    // Auto-init for the script-tag embed, suppressed once init()/mount() has run
    // explicitly — an ESM host that mounts before parsing ends would get two.
    let explicitInit = false;

    document.addEventListener('DOMContentLoaded', function (e) {
        if (explicitInit) return;
        init(e);
    });

    window.WapChat = window.WapChat || {};
    window.WapChat.init = init;
    window.WapChat.mount = mount;
    window.WapChat.destroy = destroy;

    function init(options) {
        // WapChat.init(options) merges over the localized config so SaaS hosts
        // can configure the widget entirely from JS. Guard against the
        // DOMContentLoaded Event object arriving as the first argument.
        if (options && typeof options === 'object' && !(options instanceof Event)) {
            explicitInit = true;
            window.WapClientConfig = Object.assign({}, window.WapClientConfig || {}, options);
            // Freeze after the first merge so a later third-party script
            // (analytics, ad tags, another plugin) cannot silently override
            // security-relevant fields like wapBrowserUrl, mcpEndpoint,
            // authNonce, or the getSession function. Shallow freeze is
            // enough: leaves are primitives, Functions, or Promises and none
            // mutate the config object. Object.freeze is a no-op when the
            // object is already frozen.
            try { Object.freeze(window.WapClientConfig); } catch (e) { /* ignore */ }
        }

        // Nothing module-level is touched until the mount point is known good, so
        // a re-init with an unresolvable root leaves the previous mount intact.
        const nextCfg = window.WapClientConfig || {};
        const nextRoot = resolveRoot(nextCfg.root);
        if (!nextRoot) return;

        runTeardowns();

        cfg = nextCfg;
        i18n = mergeI18n(cfg.i18n);
        // Re-mount must not show a previous agent's welcome content.
        agentWelcome.welcomeTitle = '';
        agentWelcome.welcomeMessage = '';
        agentWelcome.promptSuggestions = [];
        layout = resolveLayout(cfg.layout);
        column = resolveColumn(cfg.column);
        permissions = resolvePermissions(cfg.permissions);
        permissionHook = resolvePermissionHook();

        // Rows without the gear are unreachable; warn instead of overriding the host's explicit showSettings.
        if (permissions.length && !layout.showSettings && window.console && window.console.warn) {
            window.console.warn(
                'WapChat: cfg.permissions has ' + permissions.length + ' row(s) but ' +
                'layout.showSettings is false, so the settings sheet — and with it the ' +
                'Permissions section — can never be opened. Set layout.showSettings to true.'
            );
        }

        rootEl = nextRoot;

        ensureGravityLoaded();

        if (currentAbortController) {
            try { currentAbortController.abort(); } catch (e) { /* ignore */ }
        }
        sessionToken = cfg.sessionToken || '';
        conversationId = cfg.conversationId || '';
        isStreaming = false;
        newChatPending = false;
        hasMessages = false;
        // A re-init (the admin tester's reconnect) must not carry the previous
        // mount's manual selection into this one — loadAgents() prefers
        // selectedAgentRole over the server's reported `current`, so a stale
        // value here pins the new mount to the old role.
        selectedAgentRole = '';
        // buildUI() rebuilds the DOM from scratch, so any prior expanded state
        // is gone — keep the flag in step so the toggle icon starts correct.
        isFullscreen = false;
        // One Page Viewed per mount. Reset here (not module scope) so a genuine
        // re-init — e.g. the admin tester's re-connect — counts as a new view,
        // while the mp* SDK flags persist so the library loads only once. cfg is
        // already the new config above, so the super-props pick up this mount's
        // page rather than the first mount's.
        pageViewTracked = false;
        sessionStarted = false;
        lazyStartArmed = false;
        trackingSyncSuperProps();

        buildUI();
        wireEvents();
        installViewportHandler();

        // Before anything renders, so a collapsed column never flashes open.
        if (column) buildColumn();

        showLoading();

        // Replay mode: render the supplied transcript and stop. No session auth,
        // no network calls, no consent gate — there is nothing to send. Used by
        // the WAP admin conversation browser to review a past conversation.
        if (cfg.readOnly) {
            renderReadOnly();
            return;
        }

        lazyStartArmed = !!(column && column.startOnOpen);

        // A lazy column stays idle while collapsed; applyColumnState() starts it on first open.
        if (!(column && column.startOnOpen && columnCollapsed)) {
            startSession();
        }
    }

    // Boots the session at most once per mount.
    function startSession() {
        if (sessionStarted) return;
        sessionStarted = true;
        bootSession();
    }

    /**
     * Resolve consent, then run exactly one of the two callbacks — the single
     * choke point for "check consent, then act".
     */
    function withConsent(onGranted, onRefused) {
        consentHook = resolveConsentHook();
        if (!consentHook) {
            setConsentState('granted');
            return onGranted();
        }

        const myGeneration = ++consentCheckGeneration;
        setConsentState('pending');
        setSendDisabled(true);

        function refuse() {
            if (myGeneration !== consentCheckGeneration) return;
            setConsentState('refused');
            showConsentGate();
            onRefused();
        }

        return Promise.resolve(consentHook.get())
            .then(function (granted) {
                if (myGeneration !== consentCheckGeneration) return;
                if (granted) {
                    setConsentState('granted');
                    return onGranted();
                }
                refuse();
            })
            // Fails closed: an unreachable consent store shows the gate rather than guessing.
            .catch(refuse);
    }

    /**
     * Authenticate, then rehydrate the thread from the server (the backend is
     * the source of truth — see GET /chat/{id}/history). Only fall back to the
     * welcome state once we know the thread is genuinely empty, so a reload
     * restores the conversation instead of flashing the welcome.
     *
     * Shared by init(), the Try again button on the terminal auth notice, and
     * grantConsent() (when the very first boot lost the consent race), so a
     * retry rebuilds exactly the same state a fresh page load would.
     */
    function bootSession() {
        return withConsent(authenticateAndLoad, function () {
            // Nothing else clears the initial-load spinner on this path.
            finishInitialLoad();
        });
    }

    // Actual session + history/agents/quota fetch, run once consent is granted.
    function authenticateAndLoad() {
        // Captured per boot: a manual agent switch (or a Stop) during the initial
        // load advances the generation, and the stale responses must not paint.
        const generation = selectionGeneration;
        return authenticate(false)
            .then(function () {
                return Promise.all([
                    loadHistory(undefined, generation),
                    loadAgentWelcome(undefined, generation),
                    loadAgents(),
                    loadQuota(),
                ]);
            })
            .catch(function () {})
            .then(function () {
                if (selectionGeneration === generation) finishInitialLoad();
            });
    }

    // Like bootSession(), but forces a fresh credential (erased session's token is dead)
    // and skips the history/agents/quota fetch — the caller already cleared the chat list.
    function reauthenticateAfterErasure() {
        withConsent(function () {
            authenticate(true).catch(function () {});
        }, function () {});
    }

    /**
     * Render cfg.transcript as a finished conversation and lock the composer.
     *
     * Entries are the {role, content} shape the history endpoint returns, so a
     * replay and a live reload of the same thread render identically.
     */
    function renderReadOnly() {
        // A replay has no consent gate to satisfy — there is nothing to send and
        // no analytics to emit (track() short-circuits on readOnly). Leaving
        // consentState anything but 'granted' would make finishInitialLoad()
        // drop a "you need to accept the T&Cs" reminder into someone else's
        // transcript. Sending is blocked by the disabled composer plus
        // handleSend()'s own readOnly guard.
        setConsentState('granted');
        setSendDisabled(true);
        inputEl.setAttribute('placeholder', i18n.readOnly || 'Read-only');
        inputEl.setAttribute('aria-label', i18n.readOnly || 'Read-only');

        clearLoading();
        const transcript = Array.isArray(cfg.transcript) ? cfg.transcript : [];
        transcript.forEach(function (msg) {
            if (!msg || typeof msg !== 'object') return;
            const when = parseTimestamp(msg.timestamp || msg.created_at || msg.createdAt || msg.time);
            if (msg.role === 'user') {
                appendUserMessage(msg.content || '', when);
            } else if (msg.role === 'assistant') {
                appendAssistantMessage(msg.content || '', when, msg.media);
            } else if (msg.role === 'visual' && msg.visual) {
                appendVisualMessage(msg.visual, when);
            }
        });

        setStatus('readOnly');
        finishInitialLoad();
        scrollToBottom(true);
    }

    function ensureGravityLoaded() {
        if (cfg.loadGravity === false || typeof document === 'undefined') return;
        var head = document.head || document.getElementsByTagName('head')[0];
        if (!head) return;

        var hasStyle = !!document.querySelector(
            'link[rel="stylesheet"][href*="gravity.group-cdn.one"], link[data-wap-gravity="style"]'
        );
        if (!hasStyle) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = gravityStylesheetUrl();
            link.setAttribute('data-wap-gravity', 'style');
            link.addEventListener('load', function () {
                if (inputEl) autoGrow();
                invalidateVisualTokens();
            });
            head.insertBefore(link, head.firstChild);
        }

        var runtimeRegistered = typeof window.customElements !== 'undefined'
            && typeof window.customElements.get === 'function'
            && !!window.customElements.get('gv-loader');
        var hasRuntime = runtimeRegistered || !!document.querySelector(
            'script[src*="gravity.group-cdn.one"], script[data-wap-gravity="runtime"]'
        );
        if (!hasRuntime) {
            var script = document.createElement('script');
            script.src = GRAVITY_RUNTIME_URL;
            script.defer = true;
            script.setAttribute('data-wap-gravity', 'runtime');
            head.appendChild(script);
        }
    }

    // -------------------------------------------------------------------------
    // Analytics — Mixpanel client-side events (governed: Page Viewed / Button
    // Clicked). Inert unless cfg.mixpanelToken is set; every call is gated on
    // consent. Mandatory governed props (application / brand / page) are
    // registered as super-properties. See the Mixpanel Taxonomy Governance
    // guide and WAP Product Core Principles → Data.
    // -------------------------------------------------------------------------

    // Stable logical page for the chat surface (query params stripped). Prefer
    // an explicit config value; otherwise derive from product + pageContext.
    function resolveTrackingPage() {
        if (cfg.mixpanelPage) return cfg.mixpanelPage;
        var product = cfg.product ? '/' + cfg.product : '';
        var ctx = cfg.pageContext ? '/' + cfg.pageContext : '';
        return '/wap' + product + ctx;
    }

    // conversationId is "{user_id}:{role}"; its user_id prefix is the same
    // distinct_id the server-side Agent Work events use, so both sides of the
    // funnel land on one Mixpanel profile.
    function currentDistinctId() {
        if (conversationId && conversationId.indexOf(':') > -1) {
            return conversationId.split(':')[0];
        }
        return '';
    }

    function mixpanelSuperProps() {
        var props = {
            application: cfg.mixpanelApplication || 'wap',
            page: resolveTrackingPage(),
        };
        // brand is a governed mandatory prop but has no WP-family mapping yet —
        // only send it when the host configured a value (see MR notes).
        if (cfg.mixpanelBrand) props.brand = cfg.mixpanelBrand;
        return props;
    }

    // Resolve our named instance on every call, never cache it: the loader's
    // queueing stub is REPLACED by the real instance once the library finishes
    // loading, so a cached reference would go stale and silently drop events.
    function mpInstance() {
        return (window.mixpanel && window.mixpanel.wap) || null;
    }

    function maybeIdentify() {
        if (mpIdentified) return;
        var inst = mpInstance();
        var id = currentDistinctId();
        if (!inst || !id) return;
        try { inst.identify(id); mpIdentified = true; } catch (e) { /* ignore */ }
    }

    function initMixpanel() {
        if (mpInited) return;
        try {
            if (!window.mixpanel || typeof window.mixpanel.init !== 'function') return;
            // Named instance ('wap') so we never clobber — or get clobbered by —
            // another plugin's default Mixpanel instance on the same WP page.
            window.mixpanel.init(cfg.mixpanelToken, {
                api_host: cfg.mixpanelApiHost || DEFAULT_MIXPANEL_API_HOST,
                persistence: 'localStorage',
                // We fire the governed Page Viewed ourselves.
                track_pageview: false,
            }, 'wap');
            var inst = mpInstance();
            if (!inst) return;
            mpInited = true;
            inst.register(mixpanelSuperProps());
            maybeIdentify();
            // Flush anything captured before the loader stub existed. Calls made
            // after this point go straight to the stub, which queues them itself
            // until the library lands.
            var queued = mpQueue;
            mpQueue = [];
            queued.forEach(function (item) { emit(item.event, item.props); });
        } catch (e) { /* analytics must never break the widget */ }
    }

    // Ensure the Mixpanel loader snippet is present. The WordPress plugin
    // enqueues it as a dependency, so window.mixpanel (the queueing stub) is
    // normally already defined; other hosts can point cfg.mixpanelLoaderUrl at
    // the vendored copy instead. We never load from a third-party CDN — both the
    // loader and the library live in assets/vendor/.
    //
    // Note we load the LOADER, never the library directly: the library is the
    // snippet-companion build and needs the stub the loader creates. The loader
    // fetches the library from MIXPANEL_CUSTOM_LIB_URL.
    function trackingInit() {
        if (mpRequested || !cfg.mixpanelToken || typeof document === 'undefined') return;
        mpRequested = true;
        if (window.mixpanel && typeof window.mixpanel.init === 'function') {
            // Stub (or full library) already present — init straight away.
            initMixpanel();
            return;
        }
        if (!cfg.mixpanelLoaderUrl) return; // no local loader available → stay disabled
        // The loader locates the library ONLY via MIXPANEL_CUSTOM_LIB_URL — its
        // upstream CDN default is emptied in our vendored copy. Injecting it
        // without a library URL would leave its inner script tag with an empty
        // src, which resolves to the current document URL: the browser would
        // re-request the page and try to parse the HTML as JS. A host that sets
        // mixpanelLoaderUrl but not mixpanelLibUrl stays disabled instead.
        if (!cfg.mixpanelLibUrl && typeof window.MIXPANEL_CUSTOM_LIB_URL === 'undefined') return;
        var head = document.head || document.getElementsByTagName('head')[0];
        if (!head) return;
        if (cfg.mixpanelLibUrl && typeof window.MIXPANEL_CUSTOM_LIB_URL === 'undefined') {
            // The loader reads this global to locate the library.
            window.MIXPANEL_CUSTOM_LIB_URL = cfg.mixpanelLibUrl;
        }
        var script = document.createElement('script');
        script.src = cfg.mixpanelLoaderUrl;
        script.async = true;
        script.setAttribute('data-wap-mixpanel', '1');
        // The loader defines the stub synchronously on execution, so init() is
        // safe as soon as it has run; queued calls flush when the library lands.
        script.addEventListener('load', initMixpanel);
        head.appendChild(script);
    }

    function emit(eventName, props) {
        var inst = mpInstance();
        if (!inst || typeof inst.track !== 'function') return;
        try { inst.track(eventName, props || {}); } catch (e) { /* ignore */ }
    }

    // Public tracking entry. No-ops without a token or consent; ensures the
    // loader is present on first use and queues events until it is.
    function track(eventName, props) {
        // A read-only replay is an admin reviewing someone else's conversation —
        // never the end user's own session, so it must not emit product events.
        if (cfg.readOnly) return;
        if (!cfg.mixpanelToken || !isConsentGranted()) return;
        if (!mpRequested) trackingInit();
        if (mpInited) {
            maybeIdentify();
            emit(eventName, props || {});
        } else if (mpQueue.length < MIXPANEL_QUEUE_MAX) {
            mpQueue.push({ event: eventName, props: props || {} });
        }
    }

    function trackPageView() {
        if (pageViewTracked || !isConsentGranted()) return;
        pageViewTracked = true;
        track('Page Viewed', {});
    }

    function trackButtonClick(buttonName, extra) {
        var props = { button_name: buttonName };
        if (extra) { for (var k in extra) { if (extra.hasOwnProperty(k)) props[k] = extra[k]; } }
        track('Button Clicked', props);
    }

    // Super-props are registered once, when the SDK comes up. A re-mount can
    // resolve a different product / pageContext / mixpanelPage, so re-register to
    // keep `page` in step with the fresh Page Viewed that init() arms — otherwise
    // the second mount's view reports the first mount's page. No-op until the SDK
    // is up: initMixpanel() registers the current values itself.
    function trackingSyncSuperProps() {
        if (!mpInited) return;
        var inst = mpInstance();
        if (!inst || typeof inst.register !== 'function') return;
        try { inst.register(mixpanelSuperProps()); } catch (e) { /* ignore */ }
    }

    // Drop the local analytics identity. Called from the GDPR erase path, so the
    // "Delete my data" button also takes the client-side identifier with it:
    // reset() clears the SDK's mp_<token>_wap persistence entry — distinct_id
    // (the WAP user_id), the registered super-props and any un-flushed batch —
    // and mints a fresh anonymous $device: id.
    //
    // persistence.clear() wipes the super-props too, so the governed mandatory
    // ones MUST be re-registered here; otherwise every later event on this page
    // ships without application/page/brand. Both reset and register are in the
    // loader stub's function list, so this is safe before the library lands.
    function trackingResetIdentity() {
        // Queued pre-init events were captured under the erased identity.
        mpQueue = [];
        mpIdentified = false;
        var inst = mpInstance();
        if (!inst) return;
        try {
            if (typeof inst.reset === 'function') inst.reset();
            if (typeof inst.register === 'function') inst.register(mixpanelSuperProps());
        } catch (e) { /* analytics must never break the erase flow */ }
    }

    // Resolve the mount container: an Element, a CSS selector, the default
    // #wap-chat-root, or the documented [data-wap-chat-column] mount point.
    function resolveRoot(root) {
        if (root && typeof root === 'object' && root.nodeType === 1) {
            return root;
        }
        if (typeof root === 'string' && root) {
            return document.querySelector(root);
        }

        const byId = document.getElementById('wap-chat-root');
        if (byId) return byId;

        const host = document.querySelector('[data-wap-chat-column]');
        if (!host) return null;

        const existing = host.querySelector('.wap-chat-root');
        if (existing) return existing;

        const created = el('div', 'wap-chat-root');
        // On the node, not in a module flag, so it survives a re-init's teardown.
        created.setAttribute('data-wap-root-owned', '');
        host.appendChild(created);
        return created;
    }

    // -------------------------------------------------------------------------
    // UI construction — all Gravity components
    // -------------------------------------------------------------------------

    function buildUI() {
        rootEl.innerHTML = '';
        // The widget's own CSS is scoped under .wap-chat-root and Gravity's
        // under .gv-activated. The WordPress adapter renders wap-chat-root in
        // its PHP template, but a custom `root` (npm/SaaS embeds) is an
        // arbitrary element — ensure both classes are present either way.
        rootEl.classList.add('wap-chat-root', 'gv-activated');
        applyLayout();

        // Null button refs before rebuilding so a now-hidden one leaves no
        // stale ref to a detached node.
        newChatBtn = settingsBtn = deleteDataBtn = fullscreenBtn = null;
        indicatorDotEl = statusTextEl = statusWrapperEl = null;

        // ---- Header: title + connection status + optional icon actions -------
        // Optional (layout.showHeader); each action is gated too.
        let header = null;
        if (layout.showHeader) {
            header = el('div', 'wap-chat__header');

            const titleWrap = el('div', 'wap-chat__title');
            const titleIcon = gvIcon(ICON.assistant, 'wap-chat__title-icon');
            titleIcon.setAttribute('aria-hidden', 'true');
            titleWrap.appendChild(titleIcon);
            titleWrap.appendChild(elText('h2', i18n.headerTitle || 'Assistant', 'wap-chat__title-text'));

            // Only worth showing when something is off — setStatus() hides it
            // once connected.
            statusWrapperEl = el('div', 'gv-text-indicator');
            indicatorDotEl = el('div', 'gv-indicator gv-state-busy');
            statusTextEl = el('span', '');
            statusTextEl.textContent = i18n.reconnecting || 'Connecting…';
            statusWrapperEl.appendChild(indicatorDotEl);
            statusWrapperEl.appendChild(statusTextEl);
            titleWrap.appendChild(statusWrapperEl);
            header.appendChild(titleWrap);

            // Right-aligned header actions — icon-only, each with a Gravity
            // tooltip revealed on hover/focus.
            const actions = el('div', 'wap-chat__actions gv-flex gv-items-center gv-gap-xs');

            if (layout.showNewChat) {
                newChatBtn = iconAction(ICON.newChat, i18n.newChat || 'New chat', i18n.newChat || 'New chat');
                actions.appendChild(newChatBtn.parentNode);
            }

            if (layout.showSettings) {
                settingsBtn = iconAction(ICON.settings, i18n.settings || 'Chat settings', i18n.settings || 'Chat settings');
                actions.appendChild(settingsBtn.parentNode);
            }

            // Delete lives in the settings page; it only stays up here when
            // there is no settings page to hold it.
            if (layout.showDeleteData && !layout.showSettings) {
                deleteDataBtn = iconAction(ICON.delete, i18n.deleteData || 'Delete my data', i18n.deleteTooltip || 'Delete data for this chat session');
                deleteDataBtn.classList.add('wap-icon-btn--danger');
                actions.appendChild(deleteDataBtn.parentNode);
            }

            if (layout.expandToggle !== 'off') {
                fullscreenBtn = iconAction(ICON.fullscreen, i18n.fullscreen || 'Full screen', i18n.fullscreen || 'Full screen');
                actions.appendChild(fullscreenBtn.parentNode);
            }

            // Whichever action ends up at the right edge gets a right-aligned
            // tooltip, so a centred one can't overflow the shell and get clipped.
            const lastTip = actions.lastElementChild;
            if (lastTip) alignTipEnd(lastTip);

            header.appendChild(actions);
        }

        // ---- gv-chat: list + footer -----------------------------------------
        chatEl = el('section', 'gv-chat');
        chatEl.setAttribute('role', 'log');
        chatEl.setAttribute('aria-live', 'polite');

        chatListEl = el('ul', 'gv-chat-list');
        chatListEl.setAttribute('aria-label', 'Chat messages');

        const footer = el('footer', '');
        footerEl = footer;
        const footerInner = el('div', 'gv-chat-footer');

        // gv-input-ai composer: toolbar (with send button) + textarea.
        const inputAi = el('div', 'gv-form-option gv-input-ai');
        const inputBox = el('div', 'gv-input gv-input-textarea');

        const toolbar = el('div', 'gv-input-toolbar gv-mode-condensed');
        toolbar.setAttribute('role', 'toolbar');

        const toolbarContent = el('div', 'gv-toolbar-content');
        const toolbarEnd = el('div', 'gv-toolbar-end');

        // Hidden until GET /chat/agents answers, so a slow or failed lookup
        // leaves the composer as it is rather than showing an empty box.
        //
        // Built even when layout.showAgentSelector is false — only the append is
        // skipped; renderAgentOptions() and the handoff path both need the element to exist.
        agentSelectWrapEl = el(
            'div',
            'gv-input gv-input-select gv-min-w-0 gv-children-min-w-0 wap-agent-select'
        );
        agentSelectEl = el('select', '');
        agentSelectWrapEl.hidden = true;
        agentSelectEl.setAttribute('aria-label', i18n.agentSelector || 'Assistant');
        agentSelectWrapEl.appendChild(agentSelectEl);
        agentSelectWrapEl.appendChild(gvIcon(ICON.expand));
        // Left out of the DOM rather than appended-and-hidden: a hidden <select> is still a tab stop in some engines.
        if (layout.showAgentSelector) {
            toolbarContent.appendChild(agentSelectWrapEl);
        }

        sendBtn = el('button', 'gv-button gv-button-primary gv-button-icon');
        sendBtn.type = 'button';
        sendBtn.setAttribute('aria-label', i18n.send || 'Send');
        sendBtn.title = i18n.send || 'Send';
        sendBtn.appendChild(gvIcon(ICON.send));
        toolbarEnd.appendChild(sendBtn);
        toolbar.appendChild(toolbarContent);
        toolbar.appendChild(toolbarEnd);

        inputEl = el('textarea', '');
        inputEl.setAttribute('rows', '1');
        inputEl.setAttribute('placeholder', i18n.placeholder || 'Ask the assistant…');
        inputEl.setAttribute('aria-label', i18n.placeholder || 'Ask the assistant…');

        // Textarea first so the text starts at the top of the box; the toolbar
        // (with the send button) sits at the bottom-right corner.
        inputBox.appendChild(inputEl);
        inputBox.appendChild(toolbar);

        // The textarea is only the top of the box; the toolbar row under it
        // spans the full width. A click on any empty part of the box focuses
        // the textarea, while the picker and Send keep their own clicks.
        inputBox.addEventListener('mousedown', function (e) {
            for (let n = e.target; n && n !== inputBox; n = n.parentNode) {
                if (/^(BUTTON|SELECT|OPTION|A|INPUT|TEXTAREA)$/.test(n.tagName)) return;
            }
            if (inputEl.disabled) return;
            e.preventDefault(); // keep focus from landing on the box and blinking
            inputEl.focus();
            const end = inputEl.value.length;
            try { inputEl.setSelectionRange(end, end); } catch (err) { /* ignore */ }
        });
        inputAi.appendChild(inputBox);
        inputAi.appendChild(buildLegalNotice());

        // Gravity's conversational "scroll to bottom" pattern: a secondary icon
        // button inside .gv-chat-scroll-bottom, placed in the footer before the
        // chat footer. Gravity's own CSS positions it above the footer and
        // reveals it when the `.gv-show-scroll` class is on the .gv-chat section.
        const scrollBottom = el('div', 'gv-chat-scroll-bottom');
        scrollBtnEl = el('button', 'gv-button gv-button-icon gv-button-secondary gv-mode-condensed');
        scrollBtnEl.type = 'button';
        scrollBtnEl.setAttribute('aria-label', i18n.scrollDown || 'Scroll to bottom');
        scrollBtnEl.appendChild(gvIcon(ICON.scrollDown));
        scrollBtnEl.addEventListener('click', onScrollBtnClick);
        scrollBottom.appendChild(scrollBtnEl);

        // wap-quota-notice mirrors Gravity's addon notice onto the top edge of
        // the composer (Gravity specifies it for the bottom edge).
        quotaNoticeEl = el('div', 'gv-notice gv-notice-addon gv-mode-condensed wap-quota-notice');
        quotaNoticeEl.setAttribute('role', 'status');
        quotaNoticeEl.hidden = true;
        quotaNoticeIconEl = gvIcon(ICON.warning, 'gv-notice-icon');
        quotaNoticeTextEl = el('p', 'gv-notice-content');
        quotaNoticeEl.appendChild(quotaNoticeIconEl);
        quotaNoticeEl.appendChild(quotaNoticeTextEl);

        replyDockEl = el('div', 'wap-reply-dock');

        const composerStack = el('div', 'wap-composer-stack');
        composerStack.appendChild(replyDockEl);
        composerStack.appendChild(quotaNoticeEl);
        composerStack.appendChild(inputAi);
        footerInner.appendChild(composerStack);
        footer.appendChild(scrollBottom);
        footer.appendChild(footerInner);

        // The scroll container may be the list or the section — listen on both;
        // only the one that actually scrolls will emit events.
        chatListEl.addEventListener('scroll', updateScrollState);
        chatEl.addEventListener('scroll', updateScrollState);

        chatEl.appendChild(chatListEl);
        chatEl.appendChild(footer);

        if (header) rootEl.appendChild(header);
        rootEl.appendChild(chatEl);

        // Relative reply times ("5 minutes ago") go stale; refresh them while mounted.
        const timeTimer = setInterval(refreshMessageTimes, 60000);
        teardowns.push(function () { clearInterval(timeTimer); });
    }

    // Translate the resolved layout into classes + CSS vars on the shell. The
    // stylesheet owns the actual rules; this only flips switches. Idempotent.
    function applyLayout() {
        // Clear runtime expand state from a prior build (init() resets isFullscreen).
        rootEl.classList.remove('wap-fullscreen', 'wap-expanded');

        rootEl.classList.toggle('wap-width-fluid', layout.width === 'fluid');
        rootEl.classList.toggle('wap-chrome-flat', layout.chrome === 'flat');
        rootEl.classList.toggle('wap-hide-scrollbar', !layout.keepScrollbar);

        rootEl.classList.remove('wap-align-left', 'wap-align-center', 'wap-align-right');
        rootEl.classList.add('wap-align-' + layout.align);

        // Brand-specific touches the Gravity stylesheet doesn't cover (see wap-chat.css).
        if (typeof cfg.brand === 'string' && cfg.brand) {
            rootEl.setAttribute('data-wap-brand', cfg.brand);
        } else {
            rootEl.removeAttribute('data-wap-brand');
        }

        // Inner-column cap; empty → 100% via the CSS var default.
        if (layout.innerWidth) {
            rootEl.style.setProperty('--wap-inner-width', layout.innerWidth);
        } else {
            rootEl.style.removeProperty('--wap-inner-width');
        }

        // 'fill' → class (100% of parent); a length → inline height wins over the
        // stylesheet's viewport math; '' → default.
        //
        // min-height must move with it. The stylesheet floors the shell at
        // min-height: 480px for the standalone desktop page, and used height is
        // clamp(min-height, height, max-height) — so without this an explicit
        // height below 480px silently rendered as 480px. That floor is only
        // lifted by the phone media queries or the 'fill' class, neither of
        // which applies to an embed sized by its host container.
        rootEl.classList.toggle('wap-height-fill', layout.height === 'fill');
        if (layout.height && layout.height !== 'fill') {
            rootEl.style.height = layout.height;
            rootEl.style.minHeight = layout.height;
        } else {
            rootEl.style.removeProperty('height');
            rootEl.style.removeProperty('min-height');
        }

        if (layout.accent) {
            // primary → button bg + icons; surface-highlight → bubbles/avatar
            // (tinted); on-accent → legible arrow on dark accents.
            rootEl.style.setProperty('--color-primary', layout.accent);
            rootEl.style.setProperty('--color-surface-highlight',
                'color-mix(in srgb, ' + layout.accent + ' 14%, #fff)');
            rootEl.style.setProperty('--wap-on-accent', contrastColor(layout.accent));
            rootEl.setAttribute('data-wap-accent', '');
        } else {
            rootEl.style.removeProperty('--color-primary');
            rootEl.style.removeProperty('--color-surface-highlight');
            rootEl.style.removeProperty('--wap-on-accent');
            rootEl.removeAttribute('data-wap-accent');
        }
    }

    // Black or white for content on top of `color`, by sRGB luminance. Lets the
    // browser normalise any colour form (hex/rgb/hsl/named) to rgb via a probe.
    function contrastColor(color) {
        let r = 0, g = 0, b = 0;
        try {
            const probe = document.createElement('span');
            probe.style.color = color;
            probe.style.display = 'none';
            document.body.appendChild(probe);
            const resolved = getComputedStyle(probe).color; // "rgb(r, g, b)"
            document.body.removeChild(probe);
            const parts = resolved.match(/[\d.]+/g);
            if (parts && parts.length >= 3) {
                r = +parts[0]; g = +parts[1]; b = +parts[2];
            }
        } catch (e) { /* fall back to the dark default below */ }
        const luminance = (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
        return luminance > 0.6 ? '#111111' : '#ffffff';
    }

    // A new-tab link when the host configured the URL; otherwise the label as
    // plain text so the sentence still reads correctly. Gravity styles bare
    // anchors as underlined links, so no class is needed for the affordance.
    function linkOrText(url, label) {
        if (!url) return document.createTextNode(label);
        const link = el('a', '');
        link.href = safeHref(url);
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = label;
        return link;
    }

    // Append an i18n template to parent, swapping each {name} slot for
    // slots[name] (a DOM node, or null to drop the slot). Keeps a sentence with
    // inline links translatable as one string with locale-specific word order.
    // A wrapping {name}…{/name} pair hands its text to slots[name], which must
    // then be a function returning the node, so translators own the link label.
    function appendTemplate(parent, template, slots) {
        const slotPattern = /\{(\w+)\}(?:([\s\S]*?)\{\/\1\})?/g;
        let last = 0;
        let match;
        function appendText(text) {
            if (text) parent.appendChild(document.createTextNode(text));
        }
        while ((match = slotPattern.exec(template)) !== null) {
            appendText(template.slice(last, match.index));
            last = slotPattern.lastIndex;
            if (!Object.prototype.hasOwnProperty.call(slots, match[1])) {
                appendText(match[0]);
                continue;
            }
            const slot = slots[match[1]];
            const node = typeof slot === 'function' ? slot(match[2] || '') : slot;
            if (node) parent.appendChild(node);
        }
        appendText(template.slice(last));
    }

    // AI + legal notice under the composer (Gravity's input disclaimer line),
    // with inline Terms of Use / Privacy Policy links. The notice is one i18n
    // template with {terms}/{privacy} slots so translations can reorder them.
    function buildLegalNotice() {
        const notice = el('span', 'gv-input-message gv-message-info wap-legal-notice');

        appendTemplate(notice, i18n.aiLegalNotice || DEFAULT_I18N.aiLegalNotice, {
            terms:   linkOrText(cfg.termsUrl, i18n.termsOfUseLabel || 'Terms of Use'),
            privacy: linkOrText(cfg.privacyUrl, i18n.privacyLabel || 'Privacy Policy'),
        });
        return notice;
    }

    // Build a borderless icon-only header action using Gravity's tooltip anatomy:
    // a gv-tooltip-button trigger paired with a gv-tooltip revealed on hover/focus
    // (positioning is pinned by our CSS so it works without Gravity's JS layer).
    // Returns the <button>; its parentNode is the gv-tooltip-container to append.
    // Pin an iconAction() tooltip to its trigger's right edge.
    function alignTipEnd(container) {
        container.classList.add('wap-tip--end');
        const tip = container.querySelector('.gv-tooltip');
        if (tip) {
            tip.classList.remove('gv-arrow-top-center');
            tip.classList.add('gv-arrow-top-right');
        }
    }

    function iconAction(iconSrc, label, tooltipText, align) {
        tipCounter += 1;
        const tipId = 'wap-tip-' + tipCounter;

        // align 'end' pins the tooltip to the trigger's right edge so a wide
        // tooltip near the header's right edge is not clipped by the shell.
        const end = align === 'end';
        const container = el('div', 'gv-tooltip-container wap-tip' + (end ? ' wap-tip--end' : ''));

        const btn = el('button', 'gv-tooltip-button wap-icon-btn');
        btn.type = 'button';
        btn.setAttribute('aria-label', label);
        btn.setAttribute('aria-describedby', tipId);
        btn.appendChild(gvIcon(iconSrc));

        const tip = el('div', 'gv-tooltip gv-mode-condensed ' + (end ? 'gv-arrow-top-right' : 'gv-arrow-top-center'));
        tip.id = tipId;
        tip.setAttribute('role', 'tooltip');
        tip.appendChild(elText('p', tooltipText));

        container.appendChild(btn);
        container.appendChild(tip);

        // Dismiss the tooltip once the button is activated — otherwise it lingers
        // (the button keeps focus/hover after a click). The suppression clears on
        // the next mouseleave or blur so hover/keyboard reveal works again.
        btn.addEventListener('click', function () {
            container.classList.add('wap-tip-hide');
            if (isTouchPrimary) {
                // On touch-primary devices, briefly force-show the tooltip so
                // the user can read the action label — hover/focus-visible
                // never fire on tap. Cleared after 1.5s; mouseleave/blur will
                // also clear it via the existing handlers.
                container.classList.add('wap-tip-force');
                setTimeout(function () { container.classList.remove('wap-tip-force'); }, 1500);
            }
        });
        btn.addEventListener('blur', function () { container.classList.remove('wap-tip-hide'); });
        container.addEventListener('mouseleave', function () {
            container.classList.remove('wap-tip-hide');
            container.classList.remove('wap-tip-force');
        });

        return btn;
    }

    function wireEvents() {
        sendBtn.addEventListener('click', onSendBtnClick);
        if (agentSelectEl) agentSelectEl.addEventListener('change', onAgentChange);

        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSend('keyboard');
            }
        });

        inputEl.addEventListener('input', autoGrow);
        // Header actions are optional — wire only what was built.
        if (deleteDataBtn) deleteDataBtn.addEventListener('click', function () { trackButtonClick('delete_data'); handleDeleteData(); });
        if (newChatBtn) newChatBtn.addEventListener('click', function () { trackButtonClick('new_chat'); handleNewChat(); });
        if (settingsBtn) settingsBtn.addEventListener('click', function () { trackButtonClick('settings'); openSettings(); });
        if (fullscreenBtn) fullscreenBtn.addEventListener('click', function () { trackButtonClick('fullscreen'); toggleFullscreen(); });

        // Escape exits full screen — but only when no dialog/panel is open, so it
        // doesn't fight the modal's own Escape-to-close handler.
        on(document, 'keydown', function (e) {
            if (e.key !== 'Escape' || !isFullscreen) return;
            if (rootEl.querySelector('.gv-modal, .wap-settings')) return;
            toggleFullscreen();
        });

        // Start collapsed to a single line; autoGrow expands as the user types.
        autoGrow();
    }

    // New chat is confirmed first (it clears the current conversation). On an
    // empty conversation there is nothing to lose, so we skip straight to it.
    //
    // Consent check here is a UX shortcut only — applyCredentialGate() is the real enforcement.
    function handleNewChat() {
        if (consentHook && !isConsentGranted()) {
            showConsentGate();
            return;
        }
        if (!hasMessages) {
            startNewChat();
            return;
        }
        showConfirm({
            title:        i18n.newChatTitle || 'Start a new chat?',
            message:      i18n.newChatConfirm || 'This clears the current conversation and its history. Anything I’ve remembered about you is kept.',
            confirmLabel: i18n.newChat || 'New chat',
            cancelLabel:  i18n.cancel || 'Cancel',
        }).then(function (confirmed) {
            if (confirmed) startNewChat();
        });
    }

    // Start a brand-new conversation: abort any stream, delete the current
    // thread on WAP, provision a fresh session (force_new) and reset to the
    // welcome state. The thread is keyed by user and agent, not by token, so
    // force_new alone would keep feeding the old history to the model.
    function startNewChat() {
        if (newChatPending) return;
        if (currentAbortController) {
            try { currentAbortController.abort(); } catch (e) { /* ignore */ }
        }
        isStreaming = false;
        // Locks the composer until the thread is gone: a message sent now would
        // land in the old thread and could recreate it after the delete.
        newChatPending = true;
        setStreamingUI(false); // reset Stop → Send if a stream was aborted.

        // This force_new is the user asking for a clean thread, not a rejected
        // credential — it must not eat into the 401 re-auth budget.
        reauthAttempts = 0;

        (sessionToken ? Promise.resolve() : authenticate(false))
            .then(deleteServerConversation)
            .then(function () {
                newChatPending = false;
                hasMessages = false;
                conversationId = '';
                // Not just discarded: a pending OAuth consent card still has a live
                // window 'message' listener, which a bare null would leak.
                if (pendingQuestionCleanup) pendingQuestionCleanup();
                chatListEl.innerHTML = '';
                applyCredentialGate();
                showLoading();

                return authenticate(true)
                    .then(function () { return Promise.all([loadAgents(), loadQuota()]); })
                    .catch(function () {})
                    .then(function () { finishInitialLoad(); });
            }, function (err) {
                // The conversation stays on screen: clearing it here would hide
                // history the model still sees on the next turn.
                newChatPending = false;
                applyCredentialGate();
                if (err && err.wapAuthExhausted) {
                    showAuthFailure();
                } else if (err && err.wapNewChatFailed) {
                    appendErrorMessage(i18n.newChatFailed || 'Couldn’t start a new chat. Please try again.');
                }
                // Anything else came from authenticate(), which already said so.
            });
    }

    // POST /chat/new deletes this user's thread for the agent in use (and any
    // question pending in it). Resolves once WAP confirmed the deletion.
    function deleteServerConversation() {
        const body = {};
        if (cfg.pageContext) body.page_context = cfg.pageContext;
        if (selectedAgentRole) body.agent_role = selectedAgentRole;

        const failed = function (message) {
            const err = new Error(message);
            err.wapNewChatFailed = true;
            return err;
        };

        return fetch(cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/chat/new', {
            method:  'POST',
            headers: {
                Authorization:  'Bearer ' + sessionToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(body),
        })
            .then(function (res) {
                const handoff = handleAuthFailure(res, deleteServerConversation);
                if (handoff) return handoff;
                if (!res.ok) throw failed('HTTP ' + res.status);
            }, function (err) {
                throw failed(err && err.message ? err.message : 'Network error');
            });
    }

    const INPUT_MAX_HEIGHT = 160;

    function autoGrow() {
        inputEl.style.height = 'auto';
        const needed = inputEl.scrollHeight;
        inputEl.style.height = Math.min(needed, INPUT_MAX_HEIGHT) + 'px';
        inputEl.style.overflowY = needed > INPUT_MAX_HEIGHT ? 'auto' : 'hidden';
    }

    // The shell height reads from a CSS variable (--wap-vh) that we update
    // from window.visualViewport. This handles the iOS on-screen keyboard,
    // which neither vh nor dvh shrinks for, and orientation changes without a
    // JS-resize race. rAF-throttled because resize fires continuously while
    // the keyboard animates open on iOS.
    function installViewportHandler() {
        if (!window.visualViewport) return;

        let raf = 0;
        function update() {
            raf = 0;
            rootEl.style.setProperty('--wap-vh', window.visualViewport.height + 'px');
            // The scroll-affordance button depends on chat height — recompute
            // so it appears/hides correctly after a keyboard open or rotation.
            if (typeof updateScrollBtn === 'function') {
                updateScrollBtn();
            }
        }
        function schedule() {
            if (raf) return;
            raf = window.requestAnimationFrame(update);
        }

        on(window.visualViewport, 'resize', schedule);
        on(window.visualViewport, 'scroll', schedule);
        teardowns.push(function () {
            if (raf) window.cancelAnimationFrame(raf);
        });
        update();
    }

    // -------------------------------------------------------------------------
    // Docked column — a shell around the widget, not a second widget.
    //
    // Deliberately not gv-sidedrawer: that component is modal (role="dialog" +
    // aria-modal + trap) and a pushing column is not. See the README's "Gravity
    // note" and accessibility contract.
    // -------------------------------------------------------------------------

    function buildColumn() {
        columnEl = columnHost();
        if (!columnEl) {
            column = null;
            return;
        }

        const epoch = ++columnEpoch;
        columnUserToggled = false;

        columnEl.classList.add('wap-chat-column', 'gv-activated');
        columnEl.setAttribute('data-wap-column-side', column.side);
        columnEl.style.setProperty('--wap-column-width', column.width);
        // Carries the push transition for the column's whole lifetime; see the
        // stylesheet's host-inset block.
        document.documentElement.classList.add('wap-column-host');

        const label = column.label || i18n.columnLabel || 'Assistant';
        const panelId = 'wap-chat-column-panel-' + (columnEl.id || ++tipCounter);

        columnPanelEl = el('div', 'wap-chat-column__panel');
        columnPanelEl.id = panelId;

        // Names the region, and gives aria-labelledby a target in dialog mode.
        columnHeadingEl = el('h2', 'gv-sr-only');
        columnHeadingEl.id = panelId + '-title';
        columnHeadingEl.textContent = label;

        // Same helper as New chat / Settings / Delete, so the dismiss control is
        // one of the header actions rather than a second kind of button.
        const closeLabel = i18n.closeAssistant || 'Hide the assistant';
        columnCloseBtn = iconAction(
            ICON.close,
            closeLabel,
            closeLabel,
            'end'
        );

        const anchor = rootEl.parentNode;
        columnPanelEl.appendChild(columnHeadingEl);
        if (anchor === columnEl) {
            columnEl.insertBefore(columnPanelEl, rootEl);
        } else {
            columnEl.appendChild(columnPanelEl);
        }
        columnPanelEl.appendChild(rootEl);

        // With layout.showHeader:false there is no row to join; the stylesheet
        // then floats it over the panel corner (.wap-chat-column--no-header).
        // parentNode is iconAction()'s .gv-tooltip-container.wap-tip wrapper, which
        // the --no-header rule below styles by name. Unwrap the helper and this
        // append becomes a silent no-op.
        const closeWrap = columnCloseBtn.parentNode;
        const actionsRow = rootEl.querySelector('.wap-chat__actions');
        if (actionsRow) {
            actionsRow.appendChild(closeWrap);
        } else {
            columnEl.classList.add('wap-chat-column--no-header');
            columnPanelEl.appendChild(closeWrap);
        }

        columnScrimEl = el('div', 'wap-chat-column__scrim');
        columnScrimEl.setAttribute('aria-hidden', 'true');
        columnEl.insertBefore(columnScrimEl, columnPanelEl);

        // ---- Launcher: the collapsed-state affordance -----------------------
        if (column.showLauncher) {
            // Gravity's circular icon-only button is the documented
            // floating-action affordance, and avoids gv-button's mobile width:100%.
            columnLauncherEl = el('button', 'gv-button gv-button-icon gv-button-primary wap-chat-column__launcher');
            columnLauncherEl.type = 'button';
            columnLauncherEl.setAttribute('aria-controls', panelId);
            columnLauncherEl.setAttribute('aria-expanded', 'false');
            columnLauncherEl.title = label;
            columnLauncherEl.appendChild(gvIcon(ICON.assistant));
            columnEl.appendChild(columnLauncherEl);
        }

        wireColumnEvents();
        syncColumnMode();

        resolveColumnStateHook();

        // persist:false on every restore path: the state came FROM the store, and
        // on a re-mount columnCollapsed still holds the previous mount's value.
        const seeded = columnEl.getAttribute('data-wap-column-state');
        let initial = (seeded === 'expanded' || seeded === 'collapsed') ? seeded : null;

        // A synchronous hook can be read before first paint, so the JS path gets
        // the same no-flash guarantee as the server-seeded PHP path. An async
        // hook's Promise is kept for readColumnState() rather than discarded —
        // dropping it costs a second round-trip and an escaping rejection.
        columnPendingRead = null;
        if (null === initial && columnStateHook) {
            let probe = null;
            try {
                probe = columnStateHook.get();
            } catch (e) {
                probe = null;
            }
            if (probe && typeof probe.then === 'function') {
                columnPendingRead = probe;
            } else if (probe === 'expanded' || probe === 'collapsed') {
                initial = probe;
            }
        }

        // Known baseline for the `changed` comparison in applyColumnState().
        columnCollapsed = true;
        applyColumnState(initial || column.defaultState, { animate: false, focus: false, persist: false });

        if (null === initial) {
            readColumnState().then(function (state) {
                // Torn down, or the user already chose — either way, stand down.
                if (epoch !== columnEpoch || !columnEl || columnUserToggled) return;
                if (state && state !== currentColumnState()) {
                    applyColumnState(state, { animate: false, focus: false, persist: false });
                }
            }).catch(function () { /* best effort — the default state stands */ });
        }

        // Last, so runTeardowns() collects this build before init() starts the
        // next one and a second mount cannot strand this panel on the page.
        teardowns.push(destroyColumn);
    }

    // The [data-wap-chat-column] mount point when the host provided one,
    // otherwise a wrapper of our own around rootEl.
    function columnHost() {
        const explicit = rootEl.closest ? rootEl.closest('[data-wap-chat-column]') : null;
        if (explicit) return explicit;

        const parent = rootEl.parentNode;
        if (!parent || parent.nodeType !== 1) return null;

        const wrapper = el('div', '');
        wrapper.setAttribute('data-wap-chat-column', column.id);
        wrapper.setAttribute('data-wap-column-owned', '');
        parent.insertBefore(wrapper, rootEl);
        wrapper.appendChild(rootEl);
        return wrapper;
    }

    // Every listener goes through on() — node removal alone leaves the
    // document-level handlers below bound.
    function wireColumnEvents() {
        if (columnLauncherEl) {
            on(columnLauncherEl, 'click', function () {
                // Read the state before toggling: the launcher is hidden while the
                // panel is open, so in practice this is always 'expand', but the
                // direction is recorded rather than assumed.
                trackButtonClick('column_launcher', {
                    button_context: columnCollapsed ? 'expand' : 'collapse',
                });
                toggleColumn(true);
            });
        }
        on(columnCloseBtn, 'click', function () {
            trackButtonClick('column_close');
            applyColumnState('collapsed', { focus: true });
        });
        on(columnScrimEl, 'click', function () {
            if ('overlay' === columnMode) applyColumnState('collapsed', { focus: true });
        });

        // Only where the panel owns the key: push mode is non-modal, so Escape
        // aimed at the host's own UI must reach it. Fullscreen, a gv-modal and the
        // settings sheet each own Escape ahead of the column.
        on(document, 'keydown', function (e) {
            if (e.key !== 'Escape' || columnCollapsed) return;
            if ('overlay' !== columnMode && !columnPanelEl.contains(document.activeElement)) return;
            if (isFullscreen) return;
            if (rootEl.querySelector('.gv-modal, .wap-settings')) return;
            e.preventDefault();
            // preventDefault() alone does not stop a host keydown handler.
            e.stopPropagation();
            applyColumnState('collapsed', { focus: true });
        });

        // Overlay mode only — a pushing panel is non-modal.
        on(document, 'keydown', function (e) {
            if (e.key !== 'Tab' || columnCollapsed || 'overlay' !== columnMode) return;
            trapColumnFocus(e);
        }, true);
    }

    // Below the configured breakpoint the column is always an overlay. Bound
    // fresh per build: destroyColumn() nulls columnMediaQuery, so a re-init
    // re-binds rather than short-circuiting on a stale, unlistened query.
    function syncColumnMode() {
        if (!columnMediaQuery && typeof window.matchMedia === 'function') {
            const query = window.matchMedia('(max-width: ' + column.breakpoint + ')');
            columnMediaQuery = query;
            const onChange = function () {
                resolveColumnMode();
                // Re-apply the same state so the mode switch takes effect.
                applyColumnState(currentColumnState(), { animate: false, focus: false, persist: false });
            };
            // Safari < 14 only has the deprecated listener API.
            if (typeof query.addEventListener === 'function') {
                on(query, 'change', onChange);
            } else if (typeof query.addListener === 'function') {
                query.addListener(onChange);
                teardowns.push(function () { query.removeListener(onChange); });
            }
        }
        resolveColumnMode();
    }

    function resolveColumnMode() {
        if (!columnEl) return;
        const narrow = !!(columnMediaQuery && columnMediaQuery.matches);
        columnMode = (narrow || 'overlay' === column.mode) ? 'overlay' : 'push';
        columnEl.setAttribute('data-wap-column-mode', columnMode);
        // The breakpoint is configurable, so the class carries the full-bleed
        // switch that a static media query cannot.
        columnEl.classList.toggle('wap-chat-column--narrow', narrow);
    }

    function currentColumnState() {
        return columnCollapsed ? 'collapsed' : 'expanded';
    }

    // Single writer for the column's visual + a11y state.
    //   animate:false  suppress the slide (initial restore, breakpoint flip)
    //   focus:true     move focus, as a user action would
    //   persist:false  don't write the preference
    function applyColumnState(state, opts) {
        // column:false, or buildColumn() bailed on a missing host.
        if (!columnEl || !column) return;

        const options = opts || {};
        const collapse = 'expanded' !== state;
        const changed = collapse !== columnCollapsed;

        // Captured before the panel takes focus, so collapsing can hand it back.
        if (changed && !collapse && options.focus) {
            const active = document.activeElement;
            columnReturnFocusEl = (active && active !== document.body) ? active : null;
        }

        columnCollapsed = collapse;

        columnEl.classList.toggle('wap-column-animate', false !== options.animate);
        columnEl.setAttribute('data-wap-column-state', currentColumnState());

        if ('overlay' === columnMode && !collapse) {
            columnPanelEl.setAttribute('role', 'dialog');
            columnPanelEl.setAttribute('aria-modal', 'true');
            columnPanelEl.setAttribute('aria-labelledby', columnHeadingEl.id);
        } else {
            columnPanelEl.setAttribute('role', 'complementary');
            columnPanelEl.removeAttribute('aria-modal');
            columnPanelEl.setAttribute('aria-labelledby', columnHeadingEl.id);
        }

        // The stylesheet also applies visibility:hidden, which is what keeps the
        // controls untabbable in browsers without `inert`.
        if (collapse) {
            columnPanelEl.setAttribute('aria-hidden', 'true');
            columnPanelEl.setAttribute('inert', '');
        } else {
            columnPanelEl.removeAttribute('aria-hidden');
            columnPanelEl.removeAttribute('inert');
        }

        if (columnLauncherEl) {
            columnLauncherEl.setAttribute('aria-expanded', collapse ? 'false' : 'true');
            columnLauncherEl.setAttribute(
                'aria-label',
                collapse
                    ? (i18n.openAssistant || 'Open the assistant')
                    : (i18n.closeAssistant || 'Hide the assistant')
            );
            columnLauncherEl.hidden = !collapse;
        }

        applyColumnPush();

        if (options.focus) {
            if (collapse) {
                focusColumnTrigger();
            } else {
                // Composer first; the dismiss button while it is still disabled.
                const target = (inputEl && !inputEl.disabled) ? inputEl : columnCloseBtn;
                try { target.focus(); } catch (e) { /* ignore */ }
            }
        }

        // A persisting change is user- or host-driven, so it also cancels any
        // restore still in flight.
        if (changed && false !== options.persist) {
            columnUserToggled = true;
            writeColumnState(currentColumnState());
        }

        // After the save, so a host hook throwing while the session starts can't lose the user's open.
        if (!collapse && lazyStartArmed) startSession();
    }

    // The element that opened the panel, else the launcher. Never columnCloseBtn:
    // the panel is inert and visibility:hidden by now, so focus() would no-op and
    // focus would land on <body>.
    function focusColumnTrigger() {
        const saved = columnReturnFocusEl;
        columnReturnFocusEl = null;

        const restorable = saved
            && (saved.isConnected !== false)
            && !(columnPanelEl && columnPanelEl.contains(saved));

        const target = restorable
            ? saved
            : ((columnLauncherEl && !columnLauncherEl.hidden) ? columnLauncherEl : null);

        if (target) {
            try { target.focus(); } catch (e) { /* ignore */ }
        }
    }

    function toggleColumn(fromUser) {
        applyColumnState(columnCollapsed ? 'expanded' : 'collapsed', { focus: !!fromUser });
    }

    // Publish the inset as a custom property on <html>; the stylesheet decides
    // which host containers absorb it.
    function applyColumnPush() {
        if (!column) return;

        const root = document.documentElement;
        const pushing = 'push' === columnMode && !columnCollapsed;

        if (pushing) {
            // Clamped as the panel's own width is, so an oversized `width` cannot
            // inset the host further than the panel actually covers.
            root.style.setProperty('--wap-column-push', 'min(' + column.width + ', 100vw)');
        } else {
            root.style.removeProperty('--wap-column-push');
        }
        root.classList.toggle('wap-column-pushing', pushing);
        root.setAttribute('data-wap-column-side', column.side);
        root.classList.toggle('wap-column-locked', 'overlay' === columnMode && !columnCollapsed);
    }

    // Tab-only — siblings are not `inert`, so a virtual cursor and find-in-page
    // still reach the page behind the scrim.
    function trapColumnFocus(e) {
        if (!columnPanelEl) return;

        const focusables = columnPanelEl.querySelectorAll([
            'a[href]:not([tabindex="-1"])',
            'area[href]',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([disabled]):not([type="hidden"])',
            'select:not([disabled])',
            'iframe',
            'audio[controls]',
            'video[controls]',
            'summary',
            '[contenteditable]:not([contenteditable="false"])',
            '[tabindex]:not([tabindex="-1"])',
        ].join(', '));
        const visible = Array.prototype.filter.call(focusables, function (node) {
            return node.offsetWidth > 0 || node.offsetHeight > 0 || node === document.activeElement;
        });
        if (!visible.length) return;

        const first = visible[0];
        const last = visible[visible.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        } else if (!columnPanelEl.contains(document.activeElement)) {
            e.preventDefault();
            first.focus();
        }
    }

    // ---- Persistence --------------------------------------------------------

    // Per browser profile, not per account — the WordPress path uses user meta.
    // From the resolved config, not the DOM: a TypeError here would be swallowed
    // by localColumnState's catch and read as a quota error.
    function columnStorageKey() {
        const id = (column && column.id) || 'default';
        return 'wap:column:' + (cfg.product || 'wap') + ':' + id;
    }

    const localColumnState = {
        get: function () {
            try { return window.localStorage.getItem(columnStorageKey()); } catch (e) { return null; }
        },
        set: function (state) {
            try { window.localStorage.setItem(columnStorageKey(), state); } catch (e) { /* quota/private mode */ }
        },
    };

    // WordPress adapter: per-user user meta through admin-ajax.
    function wordPressColumnState(nonce) {
        function request(body) {
            const params = new URLSearchParams();
            params.set('action', 'wap_client_column_state');
            params.set('_ajax_nonce', nonce);
            params.set('id', column.id);
            Object.keys(body).forEach(function (k) { params.set(k, body[k]); });

            return fetch(cfg.ajaxUrl, {
                method:      'POST',
                headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:        params.toString(),
                credentials: 'same-origin',
            })
                // An expired nonce must not read as "no preference": a falsy
                // `success` treated as null would silently drop every later set()
                // for the life of the page, and a wp-admin tab routinely outlives
                // a 12–24h nonce.
                .then(function (res) {
                    if (!res.ok) throw new Error('wap column state: HTTP ' + res.status);
                    return res.json();
                })
                .then(function (json) {
                    if (!json || !json.success) throw new Error('wap column state: request rejected');
                    return json.data ? json.data.state : null;
                })
                .catch(function (e) {
                    // Degrade, don't go silent: the preference stops following the
                    // account but still survives a reload.
                    if (columnStateHook === wpHook) columnStateHook = localColumnState;
                    if (window.console && window.console.warn) window.console.warn(e.message);
                    return null;
                });
        }

        const wpHook = {
            get: function () { return request({ op: 'get' }); },
            set: function (state) { return request({ op: 'set', state: state }); },
        };

        return wpHook;
    }

    function resolveColumnStateHook() {
        columnStateHook = null;
        if (!column || !column.persist) return;

        if (cfg.columnState && typeof cfg.columnState.get === 'function') {
            columnStateHook = cfg.columnState;
            return;
        }
        const nonce = wordPressStateNonce();
        columnStateHook = (nonce && cfg.ajaxUrl) ? wordPressColumnState(nonce) : localColumnState;
    }

    // cfg.column.stateNonce is the contract; the WapClientColumn_<id> global is a
    // fallback for a host rendering the mount point against an older adapter.
    function wordPressStateNonce() {
        if (column.stateNonce) return column.stateNonce;

        const global = window['WapClientColumn_' + column.id.replace(/-/g, '_')];
        return (global && typeof global.stateNonce === 'string') ? global.stateNonce : '';
    }

    function readColumnState() {
        if (!columnStateHook) return Promise.resolve(null);
        // Reuses the probe's Promise, so one mount is one round-trip.
        const pending = columnPendingRead;
        columnPendingRead = null;

        return Promise.resolve()
            .then(function () { return pending || columnStateHook.get(); })
            .then(function (state) {
                return (state === 'expanded' || state === 'collapsed') ? state : null;
            })
            .catch(function () { return null; });
    }

    // Trailing edge only: two POSTs from a double-click have no ordering
    // guarantee, so user meta could settle on the older value.
    function writeColumnState(state) {
        if (!columnStateHook || typeof columnStateHook.set !== 'function') return;

        columnWriteLatest = state;
        if (columnWriteTimer) return;

        columnWriteTimer = window.setTimeout(function () {
            columnWriteTimer = null;
            const value = columnWriteLatest;
            columnWriteLatest = null;
            if (!columnStateHook || typeof columnStateHook.set !== 'function') return;
            try {
                Promise.resolve(columnStateHook.set(value)).catch(function () { /* best effort */ });
            } catch (e) { /* a lost preference must never break the UI */ }
        }, 250);
    }

    // So a collapse immediately followed by an SPA route change is not lost.
    function flushColumnState() {
        if (!columnWriteTimer) return;
        window.clearTimeout(columnWriteTimer);
        columnWriteTimer = null;

        const value = columnWriteLatest;
        columnWriteLatest = null;
        if (!value || !columnStateHook || typeof columnStateHook.set !== 'function') return;
        try {
            Promise.resolve(columnStateHook.set(value)).catch(function () { /* best effort */ });
        } catch (e) { /* ignore */ }
    }

    // ---- Public mount surface ----------------------------------------------
    function mount(target, options) {
        const host = (target && typeof target === 'object' && target.nodeType === 1)
            ? target
            : (typeof target === 'string' ? document.querySelector(target) : null);

        if (!host) {
            if (window.console && window.console.warn) {
                window.console.warn('WapChat.mount: no element found for the given target.');
            }
            return null;
        }

        const opts = Object.assign({}, options || {});

        // column:false opts back out to a plain in-place embed.
        opts.column = (false === opts.column) ? null : Object.assign({}, opts.column || {});

        const columnId = (opts.column && opts.column.id) ? String(opts.column.id) : 'default';

        let container = host.querySelector('[data-wap-chat-column]');
        if (!container) {
            container = el('div', '');
            container.setAttribute('data-wap-chat-column', columnId);
            container.setAttribute('data-wap-column-owned', '');
            host.appendChild(container);
        }

        let root = container.querySelector('.wap-chat-root');
        if (!root) {
            root = el('div', 'wap-chat-root');
            root.setAttribute('data-wap-root-owned', '');
            container.appendChild(root);
        }

        opts.root = root;

        init(opts);

        // focus defaults to false — a host collapsing on a route change must not
        // steal it. Pass {focus: true} from a real click handler.
        return {
            expand:      function (o) { applyColumnState('expanded', focusOpt(o)); },
            collapse:    function (o) { applyColumnState('collapsed', focusOpt(o)); },
            toggle:      function (o) { toggleColumn(!!(o && o.focus)); },
            isCollapsed: function () { return columnCollapsed; },
            root:        root,
            destroy:     destroy,
        };
    }

    function focusOpt(o) {
        return { focus: !!(o && o.focus) };
    }

    function destroyColumn() {
        flushColumnState();

        columnEpoch += 1;
        columnUserToggled = false;
        columnPendingRead = null;

        const root = document.documentElement;
        root.style.removeProperty('--wap-column-push');
        root.classList.remove('wap-column-pushing', 'wap-column-locked', 'wap-column-host');
        root.removeAttribute('data-wap-column-side');

        if (columnEl) {
            const owned = columnEl.hasAttribute('data-wap-column-owned');
            if (rootEl && columnPanelEl && columnPanelEl.contains(rootEl)) {
                try {
                    if (owned && columnEl.parentNode) {
                        columnEl.parentNode.insertBefore(rootEl, columnEl);
                    } else if (!owned) {
                        columnEl.appendChild(rootEl);
                    }
                } catch (e) { /* ignore */ }
            }

            if (columnScrimEl) columnScrimEl.remove();
            if (columnLauncherEl) columnLauncherEl.remove();
            if (columnPanelEl) columnPanelEl.remove();

            if (owned) {
                columnEl.remove();
            } else {
                // A host-provided mount point is theirs — hand it back clean.
                columnEl.classList.remove(
                    'wap-chat-column',
                    'gv-activated',
                    'wap-column-animate',
                    'wap-chat-column--narrow',
                    'wap-chat-column--no-header'
                );
                columnEl.removeAttribute('data-wap-column-state');
                columnEl.removeAttribute('data-wap-column-mode');
                columnEl.removeAttribute('data-wap-column-side');
                columnEl.style.removeProperty('--wap-column-width');
            }
        }

        column = null;
        columnEl = columnPanelEl = columnLauncherEl = columnScrimEl = columnCloseBtn = columnHeadingEl = null;
        columnMediaQuery = null;
        columnStateHook = null;
        columnReturnFocusEl = null;
        columnCollapsed = true;
        columnMode = 'push';
    }

    function destroy() {
        if (currentAbortController) {
            try { currentAbortController.abort(); } catch (e) { /* ignore */ }
        }
        isStreaming = false;
        isFullscreen = false;

        runTeardowns();
        destroyColumn();

        if (rootEl) {
            rootEl.innerHTML = '';
            if (rootEl.hasAttribute('data-wap-root-owned')) rootEl.remove();
        }
        rootEl = null;
    }

    // -------------------------------------------------------------------------
    // Welcome / empty state — greeting message + gv-chip suggestions
    // -------------------------------------------------------------------------

    function showWelcome() {
        if (hasMessages || chatListEl.querySelector('.wap-welcome')) return;

        // Centred empty state: sparkle mark, greeting title, muted subtitle and
        // (optionally) suggestion chips — mirrors the Gravity conversational
        // empty state rather than a left-aligned assistant bubble.
        const wrap = el('li', 'wap-welcome wap-empty');

        const mark = el('span', 'wap-empty__mark');
        mark.setAttribute('aria-hidden', 'true');
        mark.appendChild(gvIcon(ICON.assistant, 'wap-empty__icon'));
        wrap.appendChild(mark);

        // Precedence: agent override → host config → built-in default.
        const title = el('h2', 'wap-empty__title');
        title.textContent = agentWelcome.welcomeTitle || i18n.welcomeTitle || DEFAULT_I18N.welcomeTitle;
        wrap.appendChild(title);

        const sub = el('p', 'wap-empty__subtitle');
        sub.textContent = agentWelcome.welcomeMessage || i18n.welcomeSubtitle || DEFAULT_I18N.welcomeSubtitle;
        wrap.appendChild(sub);

        const suggestions = agentWelcome.promptSuggestions.length
            ? agentWelcome.promptSuggestions
            : (Array.isArray(cfg.suggestions) ? cfg.suggestions : []);
        if (suggestions.length) {
            const row = el('div', 'wap-chat__suggestions');
            suggestions.slice(0, 4).forEach(function (text) {
                const chip = el('button', 'gv-chip');
                chip.type = 'button';
                chip.setAttribute('aria-pressed', 'false');
                chip.textContent = text;
                chip.addEventListener('click', function () {
                    trackButtonClick('suggestion_chip', { button_context: text });
                    // Sends right away through the composer. If handleSend bails
                    // (no consent yet, quota spent, a run streaming) the prompt
                    // stays in the composer for the user to send later.
                    inputEl.value = text;
                    autoGrow();
                    handleSend('suggestion');
                    if (inputEl.value) inputEl.focus();
                });
                row.appendChild(chip);
            });
            wrap.appendChild(row);
        }

        chatListEl.appendChild(wrap);
    }

    function clearWelcome() {
        chatListEl.querySelectorAll('.wap-welcome').forEach(function (n) { n.remove(); });
    }

    // Initial-load spinner (gv-stream-loader) shown while we authenticate and
    // fetch history, so a reload doesn't flash the empty/welcome state.
    function showLoading() {
        if (chatListEl.querySelector('.wap-loading')) return;
        const row = el('li', 'gv-chat-message gv-chat-incoming wap-loading');
        const body = el('div', 'gv-chat-message-body');
        const loader = el('div', 'gv-stream-loader gv-stack-space-sm');
        loader.appendChild(makeStep(i18n.loadingHistory || 'Loading your conversation…', false));
        body.appendChild(loader);
        row.appendChild(body);
        chatListEl.appendChild(row);
    }

    function clearLoading() {
        chatListEl.querySelectorAll('.wap-loading').forEach(function (n) { n.remove(); });
    }

    // Called once after auth + history settle: drop the spinner and, only if no
    // history rendered and no error is showing, present the welcome state.
    function finishInitialLoad(opts) {
        clearLoading();
        if (!opts || !opts.skipWelcome) {
            if (!hasMessages && !chatListEl.querySelector('.gv-notice')) {
                showWelcome();
            }
        }
        // Re-assert the consent reminder if the list was rebuilt (e.g. New
        // chat) while consent is still pending — clearing the list removed
        // it. The modal itself lives outside the list and is unaffected.
        if (!isConsentGranted() && !rootEl.querySelector('.wap-consent-modal')) {
            showConsentReminder();
        }
        // The conversational UI has settled — record the view. No-ops if consent
        // is still pending; grantConsent() fires it once consent is given.
        trackPageView();
    }

    // -------------------------------------------------------------------------
    // Authentication
    // -------------------------------------------------------------------------

    let pendingForceNew = false;

    /**
     * Budget for 401-driven re-authentication.
     *
     * A WAP 401 means "this GRND was rejected", and the cure is one forced
     * re-mint — but that round is expensive: the host revokes and re-mints an
     * Application Password, re-fetches WAP's wrap key, re-seals the credential
     * and re-runs the brand issuer exchange. When the rejection is
     * deterministic (issuer signing key absent from WAP's JWKS, JWKS fetch
     * blocked to WAP's egress, wrong issuer environment, clock skew, wrap key
     * rotated out) minting another token cannot help, so an unbounded retry
     * hammers the site and the issuer forever while the user only ever sees the
     * status pill flip between Connecting… and Connected.
     *
     * The budget is CONSECUTIVE, reset by any non-401 response: GRNDs are
     * short-lived (an hour by default) and the widget holds one in memory, so a
     * long-lived tab legitimately re-auths on expiry over and over — a lifetime
     * cap would break normal use.
     */
    const MAX_REAUTH_ATTEMPTS = 2;
    let reauthAttempts = 0;

    /**
     * The error raised once the re-auth budget is spent. The flag lets callers
     * tell a dead credential apart from a transient failure, so only this case
     * shows the terminal notice.
     */
    function authExhaustedError() {
        const err = new Error(
            i18n.errorAuthFailed
                || 'Could not connect to the assistant. Please reload the page, or contact support if this continues.'
        );
        err.wapAuthExhausted = true;
        return err;
    }

    /**
     * Single 401 policy for every WAP call.
     *
     * Returns a promise when this response is ours to handle — either
     * "re-authenticated, here is your retry" or a rejection because the budget
     * is spent — and null when the response belongs to the caller. The retry
     * thunk runs only after a successful re-auth, so callers can put cleanup
     * that must not happen on a terminal failure inside it.
     *
     * @param {Response} res   The WAP response.
     * @param {Function} retry Re-issues the same call.
     * @returns {Promise|null}
     */
    function handleAuthFailure(res, retry) {
        if (res.status !== 401) {
            // WAP accepted this GRND, so whatever went wrong before is behind
            // us — restore the full budget for the next credential.
            reauthAttempts = 0;
            return null;
        }
        if (reauthAttempts >= MAX_REAUTH_ATTEMPTS) {
            // The credential is dead as of now, so own the status here — each
            // caller then only decides how to render the message (in-chat
            // notice, inside the assistant bubble, or not at all).
            setStatus('error');
            return Promise.reject(authExhaustedError());
        }
        reauthAttempts++;
        return authenticate(true).then(retry);
    }

    function authenticate(forceNew) {
        if (forceNew) pendingForceNew = true;
        if (authPromise) return authPromise;

        var useForceNew = pendingForceNew;
        pendingForceNew = false;

        setStatus('connecting');

        // getSession is the platform contract: hosts resolve {token,
        // conversationId} however their platform requires. WordPress pages get
        // the built-in admin-ajax implementation as the default.
        const getSession = typeof cfg.getSession === 'function'
            ? cfg.getSession
            : wordPressGetSession;

        authPromise = Promise.resolve(getSession({ forceNew: useForceNew }))
            .then(function (data) {
                if (!data || !data.token) {
                    throw new Error(i18n.errorGeneric || 'Auth failed');
                }
                sessionToken = data.token;
                if (data.conversationId) conversationId = data.conversationId;
                setStatus('connected');
                // A successful session means consent is (still) granted, even for
                // calls that skipped withConsent() (New Chat, send-message, 401 retry).
                setConsentState('granted');
                return sessionToken;
            })
            .catch(function (err) {
                sessionToken = '';
                if (err && err.wapConsentRequired) {
                    // Consent was revoked out-of-band since applyCredentialGate() last checked
                    // (admin revoked the App Password, or another tab ran a GDPR erasure).
                    setConsentState('refused');
                    showConsentGate();
                } else {
                    setStatus('error');
                    appendErrorMessage(err.message || (i18n.errorGeneric || 'Could not connect.'));
                }
                throw err;
            })
            .finally(function () { authPromise = null; });

        return authPromise;
    }

    function throwAuthError(json) {
        const err = new Error(
            json.data && json.data.message
                ? json.data.message
                : (i18n.errorGeneric || 'Auth failed')
        );
        // Actionable state, not a failure to report — lets callers show the consent gate.
        if (json.data && json.data.code === 'wap_client_consent_required') {
            err.wapConsentRequired = true;
        }
        throw err;
    }

    /**
     * Default getSession for WordPress pages: server-side auth via admin-ajax.
     * PHP obtains the GRND (TokenManager + provider) and the Application
     * Password, exchanges them with WAP, and returns only the session token —
     * no credential ever reaches the browser.
     *
     * SaaS example (partners.one) — pass your own instead:
     *   WapChat.init({
     *     wapBrowserUrl: 'https://wap.group.one',
     *     getSession: function (opts) {
     *       // Your backend mints a GRND for the logged-in user (see
     *       // docs/integrating-a-saas-host.md) and returns it as the token —
     *       // WAP verifies it per call; there is no session exchange.
     *       return fetch('/api/assistant/session', { method: 'POST' })
     *         .then(function (r) { return r.json(); });
     *     },
     *   });
     */
    function wordPressGetSession(opts) {
        const data = new URLSearchParams({
            action:      'wap_client_auth',
            _ajax_nonce: cfg.authNonce || '',
            product:     cfg.product || '',
            menu_slug:   cfg.menuSlug || '',
            force_new:   opts && opts.forceNew ? '1' : '0',
        });

        return fetch(cfg.ajaxUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    data.toString(),
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                return json.success === false ? throwAuthError(json) : (json.data || json);
            });
    }

    // -------------------------------------------------------------------------
    // T&C consent gate
    // -------------------------------------------------------------------------

    /**
     * Resolve the consent hook: hosts inject {get, set} like getSession; on
     * WordPress pages the built-in admin-ajax pair is the default. Returns
     * null when no hook is available — the gate is then skipped entirely.
     */
    function resolveConsentHook() {
        if (cfg.consent && typeof cfg.consent.get === 'function' && typeof cfg.consent.set === 'function') {
            return cfg.consent;
        }
        if (cfg.ajaxUrl && cfg.consentNonce) {
            return wordPressConsent;
        }
        return null;
    }

    // Default consent storage for WordPress pages: user meta via admin-ajax
    // (action wap_client_consent), so acceptance is per user and per product.
    const wordPressConsent = {
        get: function () {
            return wpConsentRequest('get').then(function (data) {
                return !!(data && data.granted);
            });
        },
        set: function () {
            return wpConsentRequest('grant');
        },
    };

    function wpConsentRequest(op) {
        const data = new URLSearchParams({
            action:      'wap_client_consent',
            _ajax_nonce: cfg.consentNonce || '',
            product:     cfg.product || '',
            op:          op,
        });

        return fetch(cfg.ajaxUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    data.toString(),
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (json.success === false) {
                    throw new Error(json.data && json.data.message ? json.data.message : (i18n.errorGeneric || 'Consent check failed'));
                }
                return json.data || json;
            });
    }

    // Called once consent is confirmed granted, by bootSession() or the modal.
    function grantConsent() {
        setConsentState('granted');
        rootEl.querySelectorAll('.wap-consent-gate, .wap-consent-modal').forEach(function (n) { n.remove(); });
        inputEl.focus();
        // Consent may resolve after finishInitialLoad(), which then skipped the
        // view — fire it now that tracking is allowed (dedup-guarded).
        trackPageView();

        // First boot showed the gate without ever calling authenticateAndLoad();
        // run it now so the widget doesn't sit stuck on "Connecting…".
        if (!sessionToken) {
            authenticateAndLoad();
        }
    }

    // Entry point for the gate: auto-opens the consent modal over the widget.
    function showConsentGate() {
        if (!consentHook || isConsentGranted()) return;
        openConsentModal();
    }

    /**
     * Blocking T&C consent dialog (Gravity gv-modal, same anatomy as the
     * delete confirmation). "Agree and continue" records consent via the
     * hook and unlocks the composer. Dismissing (X / overlay / Escape /
     * Cancel) leaves the composer locked and drops a reminder notice in the
     * chat with a button to reopen the dialog.
     */
    function openConsentModal() {
        if (rootEl.querySelector('.wap-consent-modal')) return;

        const overlay = el('div', 'gv-modal wap-consent-modal');

        const content = el('div', 'gv-modal-content');
        content.setAttribute('role', 'dialog');
        content.setAttribute('aria-modal', 'true');

        const closeBtn = el('button', 'gv-modal-close');
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', i18n.cancel || 'Close');
        closeBtn.appendChild(gvIcon(ICON.close));

        const body = el('div', 'gv-modal-body');
        const title = el('h2', 'gv-modal-title');
        title.id = 'wap-consent-title';
        title.textContent = i18n.consentTitle || 'Before you start';
        const msg = el('p', '');
        const termsLink = linkOrText(cfg.termsUrl, i18n.termsLabel || 'Terms & Conditions');
        const consentText = i18n.consentText || DEFAULT_I18N.consentText;
        if (consentText.indexOf('{terms}') !== -1) {
            appendTemplate(msg, consentText, { terms: termsLink });
        } else {
            // Host override written before the {terms} slot existed: keep the
            // link trailing the sentence as it used to.
            msg.appendChild(document.createTextNode(consentText + ' '));
            if (cfg.termsUrl) msg.appendChild(termsLink);
        }
        if (i18n.consentAccessText) {
            // The access note and its "Learn more" link each start on their own
            // line, so the prompt doesn't run on from the T&C sentence.
            msg.appendChild(document.createElement('br'));
            let learnMore = null;
            if (cfg.siteAccessUrl) {
                learnMore = document.createDocumentFragment();
                learnMore.appendChild(document.createElement('br'));
                learnMore.appendChild(linkOrText(cfg.siteAccessUrl, i18n.learnMore || 'Learn more'));
            }
            appendTemplate(msg, i18n.consentAccessText, {
                learnMore: learnMore,
                // Words wrapped in {link}…{/link} link inline; plain text when the URL is unset.
                link: function (label) { return label ? linkOrText(cfg.siteAccessUrl, label) : null; },
            });
        }
        body.appendChild(title);
        body.appendChild(msg);
        content.setAttribute('aria-labelledby', title.id);

        // Inline error slot for a failed consent write (kept inside the
        // dialog so the user can simply retry).
        const errorSlot = el('div', '');
        body.appendChild(errorSlot);

        const actions = el('div', 'gv-button-group');
        const cancelBtn = el('button', 'gv-button gv-button-cancel');
        cancelBtn.type = 'button';
        cancelBtn.textContent = i18n.cancel || 'Cancel';
        const agreeBtn = el('button', 'gv-button gv-button-primary');
        agreeBtn.type = 'button';
        agreeBtn.textContent = i18n.consentAgree || 'Agree and continue';
        actions.appendChild(cancelBtn);
        actions.appendChild(agreeBtn);

        content.appendChild(closeBtn);
        content.appendChild(body);
        content.appendChild(actions);
        overlay.appendChild(content);
        rootEl.appendChild(overlay);

        const focusables = [agreeBtn, cancelBtn, closeBtn];
        const prevFocus = document.activeElement;

        function cleanup() {
            document.removeEventListener('keydown', onKey, true);
            overlay.remove();
            if (prevFocus && prevFocus.focus) {
                try { prevFocus.focus(); } catch (e) { /* ignore */ }
            }
        }

        function dismiss() {
            cleanup();
            showConsentReminder();
        }

        function onKey(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                dismiss();
            } else if (e.key === 'Tab') {
                const first = focusables[0];
                const last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault(); last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault(); first.focus();
                }
            }
        }

        function showError(message) {
            errorSlot.innerHTML = '';
            const notice = el('div', 'gv-notice gv-notice-alert gv-mode-condensed');
            const p = el('p', 'gv-notice-content');
            p.textContent = message;
            notice.appendChild(gvIcon(ICON.error, 'gv-notice-icon'));
            notice.appendChild(p);
            errorSlot.appendChild(notice);
        }

        agreeBtn.addEventListener('click', function () {
            agreeBtn.disabled = true;
            cancelBtn.disabled = true;
            Promise.resolve(consentHook.set(true))
                .then(function () {
                    cleanup();
                    grantConsent();
                })
                .catch(function (err) {
                    agreeBtn.disabled = false;
                    cancelBtn.disabled = false;
                    showError(err.message || (i18n.errorGeneric || 'Could not record consent.'));
                });
        });
        cancelBtn.addEventListener('click', dismiss);
        closeBtn.addEventListener('click', dismiss);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) dismiss();
        });
        document.addEventListener('keydown', onKey, true);

        agreeBtn.focus();
    }

    // Shown after the modal is dismissed without consent: an info notice in
    // the chat explaining why the composer is locked, with a reopen button.
    // Gravity "notice with button" anatomy: variant modifier on the root,
    // type-matching icon, gv-button-neutral placed after the content.
    function showConsentReminder() {
        if (isConsentGranted() || chatListEl.querySelector('.wap-consent-gate')) return;

        const row = el('li', 'gv-chat-message gv-chat-incoming wap-consent-gate');
        const notice = el('div', 'gv-notice gv-notice-info gv-mode-condensed');
        const content = el('p', 'gv-notice-content');
        content.textContent = i18n.consentRequired || 'You need to accept the Terms & Conditions to use the assistant.';
        notice.appendChild(gvIcon(ICON.info, 'gv-notice-icon'));
        notice.appendChild(content);

        const reviewBtn = el('button', 'gv-button gv-button-neutral');
        reviewBtn.type = 'button';
        reviewBtn.textContent = i18n.consentReview || 'Review Terms & Conditions';
        reviewBtn.addEventListener('click', function () {
            row.remove();
            openConsentModal();
        });
        notice.appendChild(reviewBtn);

        row.appendChild(notice);
        chatListEl.appendChild(row);
        scrollToBottom(true);
    }

    // -------------------------------------------------------------------------
    // Permissions
    // -------------------------------------------------------------------------

    /**
     * Resolve the permission hook: the host's {get, set}, else WordPress admin-ajax, else null.
     * No localStorage fallback — a security preference must not look saved when it never
     * reached the server. With no hook the section renders read-only at each default.
     */
    function resolvePermissionHook() {
        const hook = cfg.permissionState;
        if (hook && typeof hook.get === 'function' && typeof hook.set === 'function') {
            return hook;
        }
        if (cfg.ajaxUrl && cfg.permissionNonce) {
            return wordPressPermissions;
        }
        return null;
    }

    // WordPress default: per-user, per-product user meta via admin-ajax (wap_client_permission_state).
    const wordPressPermissions = {
        get: function (id) {
            return wpPermissionRequest('get', id, '').then(function (data) {
                return (data && typeof data.value === 'string') ? data.value : null;
            });
        },
        set: function (id, value) {
            return wpPermissionRequest('set', id, value);
        },
    };

    function wpPermissionRequest(op, id, value) {
        const data = new URLSearchParams({
            action:      'wap_client_permission_state',
            _ajax_nonce: cfg.permissionNonce || '',
            product:     cfg.product || '',
            op:          op,
            id:          id || '',
            value:       value || '',
        });

        return fetch(cfg.ajaxUrl, {
            method:      'POST',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:        data.toString(),
            credentials: 'same-origin',
        })
            .then(function (res) {
                return res.text().then(function (body) {
                    let json = null;
                    try { json = JSON.parse(body); } catch (e) { /* e.g. "-1": expired nonce */ }
                    // Only an explicit success counts: admin-ajax answers "-1" (expired nonce) or "0" (logged out).
                    if (res.ok && json && json.success === true) return json.data || {};
                    throw new Error(
                        json && json.data && json.data.message
                            ? json.data.message
                            : (i18n.permissionsSaveError || 'Could not save that setting.')
                    );
                });
            });
    }

    /**
     * Resolve every row's stored value to an id -> value map, defaulting anything unknown.
     * Never rejects, so an unreachable store cannot break openSettings().
     */
    function readPermissionValues() {
        const fallback = {};
        permissions.forEach(function (row) { fallback[row.id] = row.defaultValue; });

        if (!permissionHook) return Promise.resolve(fallback);

        return Promise.all(permissions.map(function (row) {
            return Promise.resolve()
                .then(function () { return permissionHook.get(row.id); })
                .then(function (value) {
                    // A stored value no longer on the row's menu reads as the default.
                    for (let i = 0; i < row.options.length; i++) {
                        if (row.options[i].value === value) return value;
                    }
                    return row.defaultValue;
                })
                .catch(function (err) {
                    if (window.console && window.console.warn) {
                        window.console.warn(
                            'WapChat: could not read permission "' + row.id + '"; using its default.',
                            err
                        );
                    }
                    return row.defaultValue;
                });
        })).then(function (values) {
            const out = {};
            permissions.forEach(function (row, idx) { out[row.id] = values[idx]; });
            return out;
        });
    }

    /**
     * Persist a change, then re-mint the session so the new claim is in force at once.
     * authenticate(false), not true: forceNew is throttled, and a changed claim only needs a new token.
     * `set` drops the cached GRND server-side; custom hosts must do the same in their own set().
     */
    function savePermission(row, value) {
        return Promise.resolve()
            .then(function () { return permissionHook.set(row.id, value); })
            .then(function () {
                trackButtonClick('permission_change', {
                    permission_id: row.id,
                    permission_value: value,
                });
                return authenticate(false).catch(function () {
                    // Stored regardless; a failed refresh only delays it, and authenticate() already reported it.
                });
            });
    }

    // `values` is the id -> value map from readPermissionValues().
    function buildPermissionsSection(values) {
        const rows = el('div', 'wap-settings__rows');
        rows.setAttribute('role', 'group');
        rows.setAttribute('aria-label', i18n.permissions || 'Permissions');

        // One shared error line: a failed save is a store problem, not per-row state.
        const errorNotice = el('div', 'gv-notice gv-notice-alert gv-mode-condensed wap-settings__error');
        const errorText = el('p', 'gv-notice-content');
        errorNotice.appendChild(gvIcon(ICON.error, 'gv-notice-icon'));
        errorNotice.appendChild(errorText);
        errorNotice.hidden = true;

        permissions.forEach(function (row) {
            rows.appendChild(buildPermissionRow(row, values[row.id], errorNotice, errorText));
        });

        const section = el('div', 'wap-settings__section');
        section.appendChild(rows);
        section.appendChild(errorNotice);
        return section;
    }

    // A row whose only values are allow/deny is an on/off switch; anything else
    // (e.g. a third "ask" value) needs the select.
    function isToggleRow(row) {
        return row.options.every(function (opt) { return opt.value === 'allow' || opt.value === 'deny'; });
    }

    function buildPermissionRow(row, value, errorNotice, errorText) {
        // Read-only when disabled, single-choice, or unpersistable.
        const readOnly = row.disabled || !permissionHook;
        const toggle = isToggleRow(row);

        const rowEl = el('div', 'wap-settings__row wap-settings__row--permission' + (readOnly ? ' wap-settings__row--disabled' : ''));
        const text = el('div', 'wap-settings__text');
        const labelLine = el('div', 'wap-settings__label-line');
        const labelId = 'wap-perm-' + row.id;
        const label = elText('span', row.label, 'wap-settings__label');
        label.id = labelId;
        labelLine.appendChild(label);
        if (row.badge) labelLine.appendChild(elText('span', row.badge, 'wap-settings__badge'));
        text.appendChild(labelLine);
        if (row.hint) text.appendChild(elText('p', row.hint, 'wap-settings__hint'));
        rowEl.appendChild(text);

        let control, read, write;
        if (toggle) {
            const wrap = el('label', 'gv-toggle wap-settings__toggle');
            const input = el('input', '');
            input.type = 'checkbox';
            input.setAttribute('aria-labelledby', labelId);
            wrap.appendChild(input);
            wrap.appendChild(el('div', 'gv-toggle-slider'));
            rowEl.appendChild(wrap);
            control = input;
            read = function () { return input.checked ? 'allow' : 'deny'; };
            write = function (v) { input.checked = v === 'allow'; };
        } else {
            const selectWrap = el(
                'div',
                'gv-input gv-input-select gv-mode-condensed' + (readOnly ? ' gv-read-only' : '')
            );
            const select = el('select');
            select.setAttribute('aria-labelledby', labelId);
            row.options.forEach(function (opt) {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                select.appendChild(option);
            });
            selectWrap.appendChild(select);
            selectWrap.appendChild(gvIcon(ICON.expand));
            rowEl.appendChild(selectWrap);
            control = select;
            read = function () { return select.value; };
            write = function (v) { select.value = v; };
        }
        control.disabled = readOnly;
        write(value);

        if (!readOnly) {
            // Last stored value, so a rejected save reverts instead of showing an unsaved choice.
            let stored = value;
            control.addEventListener('change', function () {
                const next = read();
                control.disabled = true;
                errorNotice.hidden = true;

                savePermission(row, next)
                    .then(function () {
                        stored = next;
                    })
                    .catch(function (err) {
                        write(stored);
                        errorText.textContent = (err && err.message)
                            ? err.message
                            : (i18n.permissionsSaveError || 'Could not save that setting.');
                        errorNotice.hidden = false;
                    })
                    .then(function () {
                        control.disabled = false;
                    });
            });
        }

        return rowEl;
    }

    // -------------------------------------------------------------------------
    // Terminal auth failure
    // -------------------------------------------------------------------------

    /**
     * Shown once the re-auth budget (MAX_REAUTH_ATTEMPTS) is spent: WAP keeps
     * rejecting freshly minted GRNDs, which is a backend/configuration problem
     * no further minting will fix. Same Gravity "notice with button" anatomy as
     * showConsentReminder() — variant modifier on the root, type-matching icon,
     * gv-button-neutral after the content — with the button granting a fresh
     * budget so the user can retry without reloading the page.
     *
     * Idempotent: history, welcome content and the agent list can all exhaust
     * in the same round and must not stack three identical notices.
     */
    function showAuthFailure() {
        setStatus('error');
        if (chatListEl.querySelector('.wap-auth-failure')) return;

        clearLoading();

        const row = el('li', 'gv-chat-message gv-chat-incoming wap-auth-failure');
        row.setAttribute('role', 'alert');

        const notice = el('div', 'gv-notice gv-notice-alert gv-mode-condensed');
        const content = el('p', 'gv-notice-content');
        content.textContent = i18n.errorAuthFailed
            || 'Could not connect to the assistant. Please reload the page, or contact support if this continues.';
        notice.appendChild(gvIcon(ICON.error, 'gv-notice-icon'));
        notice.appendChild(content);

        const retryBtn = el('button', 'gv-button gv-button-neutral');
        retryBtn.type = 'button';
        retryBtn.textContent = i18n.errorRetry || 'Try again';
        retryBtn.addEventListener('click', function () {
            row.remove();
            // An explicit user gesture earns a fresh budget — the underlying
            // misconfiguration may well have been fixed since.
            reauthAttempts = 0;
            showLoading();
            bootSession();
        });
        notice.appendChild(retryBtn);

        row.appendChild(notice);
        chatListEl.appendChild(row);
        scrollToBottom(true);
    }

    // -------------------------------------------------------------------------
    // Agent selector (WPIN-8684)
    // -------------------------------------------------------------------------

    // Populate the composer's dropdown from GET /chat/agents, which scopes the
    // list to the connected agent's family. Failure is non-fatal.
    function loadAgents() {
        if (!sessionToken || !agentSelectEl) return Promise.resolve(null);

        let url = cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/chat/agents';
        if (cfg.pageContext) {
            url += '?page_context=' + encodeURIComponent(cfg.pageContext);
        }

        return fetch(url, { headers: { Authorization: 'Bearer ' + sessionToken } })
            .then(function (res) {
                // GRNDs are short-lived, so a reload often races an expired one.
                // Same bounded 401 recovery as every other WAP call.
                const handoff = handleAuthFailure(res, loadAgents);
                if (handoff) return handoff;
                return res.ok ? res.json() : null;
            })
            .then(function (data) {
                if (!data || !Array.isArray(data.agents)) return null;
                renderAgentOptions(data.agents, data.current);
                return data;
            })
            .catch(function () { return null; });
    }

    function renderAgentOptions(agents, current) {
        if (!agentSelectEl) return;

        // An explicit earlier choice wins, so re-rendering after a re-auth
        // doesn't reset it; `current` covers first load.
        const active = selectedAgentRole || current || '';

        agentSelectEl.innerHTML = '';
        agents.forEach(function (agent) {
            const opt = el('option', '');
            opt.value = agent.role;
            // Bare name: the family prefix is identical on every option and
            // would eat the width that distinguishes them.
            opt.textContent = agent.name || agent.displayName || agent.role;
            opt.title = agent.displayName || agent.name || agent.role;
            // On the option, not via select.value — that is a silent no-op when
            // the role isn't in the list, leaving the wrong agent showing.
            if (agent.role === active) opt.selected = true;
            agentSelectEl.appendChild(opt);
        });

        selectedAgentRole = agentSelectEl.value || '';
        const chosen = agents.filter(function (a) { return a.role === selectedAgentRole; })[0];
        agentSelectEl.title = chosen ? chosen.displayName || chosen.name || '' : '';

        agentSelectEl.disabled = false;
        agentSelectWrapEl.classList.remove('gv-disabled');
        agentSelectWrapEl.hidden = agents.length === 0 || !layout.showAgentSelector;
    }

    // Each agent has its own server-side thread ("{user_id}:{role}"), so
    // switching drops what's on screen and rehydrates from the new one.
    function switchAgent(role) {
        if (!role) return Promise.resolve(false);
        const generation = selectionGeneration;
        const isHandoffTransition = handoffTransitionGeneration === generation;
        const keepStop = isHandoffTransition;
        if (currentAbortController) {
            try { currentAbortController.abort(); } catch (e) { /* ignore */ }
            currentAbortController = null;
        }
        isStreaming = keepStop;
        setStreamingUI(keepStop);

        const option = Array.prototype.find.call(agentSelectEl.options, function (candidate) {
            return candidate.value === role;
        });
        if (!option) return Promise.resolve(false);
        agentSelectEl.value = role;
        selectedAgentRole = role;
        const displayName = option.title || option.text || option.textContent;
        agentSelectEl.title = displayName;
        i18n.assistantName = displayName;
        // Fires on every effective switch — manual dropdown *and* accepted
        // handoffs both funnel through here. Lets an embedding page (e.g. the
        // admin chat tester's Connection panel) keep its own role indicator in
        // sync without polling the widget's internal state.
        window.dispatchEvent(new CustomEvent('wap:agentchange', { detail: { role: role, displayName: displayName } }));
        hasMessages = false;
        conversationId = '';
        // Also discards any pending ask_user question — its choice card belongs
        // to the previous agent's paused run. Called, not just nulled: it may
        // hold a live OAuth completion listener that would otherwise leak.
        if (pendingQuestionCleanup) pendingQuestionCleanup();
        chatListEl.innerHTML = '';
        // Clear the old overrides so a failed refetch falls back to the i18n
        // defaults rather than mislabelling the new agent.
        agentWelcome.welcomeTitle = '';
        agentWelcome.welcomeMessage = '';
        agentWelcome.promptSuggestions = [];
        showLoading();

        return Promise.all([loadHistory(role, generation), loadAgentWelcome(role, generation), loadQuota()])
            .catch(function () {})
            .then(function () {
                if (selectionGeneration !== generation) return false;
                finishInitialLoad({ skipWelcome: isHandoffTransition });
                return true;
            });
    }

    function onAgentChange() {
        const role = agentSelectEl.value;
        if (!role || role === selectedAgentRole) return;
        selectionGeneration++;
        switchAgent(role);
    }

    // -------------------------------------------------------------------------
    // Monthly quota (WPIN-8805)
    // -------------------------------------------------------------------------

    function loadQuota() {
        if (cfg.readOnly || !sessionToken) return Promise.resolve();

        const params = [];
        if (cfg.pageContext) params.push('page_context=' + encodeURIComponent(cfg.pageContext));
        if (selectedAgentRole) params.push('agent_role=' + encodeURIComponent(selectedAgentRole));

        let url = cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/chat/quota';
        if (params.length) url += '?' + params.join('&');

        return fetch(url, { headers: { Authorization: 'Bearer ' + sessionToken } })
            .then(function (res) {
                // The retry re-enters loadQuota() and applies its own answer;
                // falling through would clear the snapshot it just stored.
                const handoff = handleAuthFailure(res, loadQuota);
                if (handoff) return handoff;
                if (!res.ok) return applyQuota(null);
                return res.json().then(applyQuota);
            })
            .catch(function () { applyQuota(null); });
    }

    // null is the fail-open state: no indicator, nothing locked.
    function applyQuota(data) {
        quota = null;
        if (data && data.enabled === true) {
            const remaining = Number(data.remaining);
            const total = Number(data.total);
            if (isFinite(remaining) && isFinite(total) && total > 0) {
                quota = {
                    remaining: Math.max(0, Math.min(remaining, total)),
                    total: total,
                    resetAt: parseTimestamp(data.resetAt),
                };
            }
        }
        renderQuota();
    }

    function isQuotaExhausted() {
        return !!quota && quota.remaining <= 0;
    }

    function isQuotaLow() {
        return !!quota && quota.remaining > 0 &&
            quota.remaining <= Math.max(1, Math.ceil(quota.total * QUOTA_LOW_RATIO));
    }

    function renderQuota() {
        if (!quotaNoticeEl) return;

        const at = quota && quota.resetAt;
        const vars = {
            remaining: quota ? String(quota.remaining) : '',
            total: quota ? String(quota.total) : '',
            date: at ? formatDate(at) : '',
            time: at ? formatTime(at) : '',
        };
        const spent = isQuotaExhausted();

        // The notice interrupts only when there is something to act on.
        quotaNoticeEl.hidden = !spent && !isQuotaLow();
        if (!quotaNoticeEl.hidden) {
            quotaNoticeEl.classList.toggle('gv-notice-alert', spent);
            quotaNoticeEl.classList.toggle('gv-notice-warning', !spent);
            quotaNoticeEl.setAttribute('aria-live', spent ? 'assertive' : 'polite');
            quotaNoticeIconEl.setAttribute('src', spent ? ICON.error : ICON.warning);
            quotaNoticeTextEl.textContent = interpolate(
                spent ? (vars.date ? i18n.quotaExhausted : i18n.errorQuota) : i18n.quotaLow,
                vars
            );
        }

        applyCredentialGate();
    }

    // Reconciles every credential-adjacent control against consent + quota + streaming.
    function applyCredentialGate() {
        if (cfg.readOnly || !inputEl || isStreaming) return;

        const spent = isQuotaExhausted();
        const locked = newChatPending || !isConsentGranted() || spent;
        setSendDisabled(locked);
        if (newChatBtn) newChatBtn.disabled = locked;
        if (deleteDataBtn) deleteDataBtn.disabled = locked;

        const placeholder = spent ? i18n.quotaBlocked : i18n.placeholder;
        inputEl.setAttribute('placeholder', placeholder);
        inputEl.setAttribute('aria-label', placeholder);
    }
    onConsentChange(applyCredentialGate);

    // -------------------------------------------------------------------------
    // History
    // -------------------------------------------------------------------------

    function loadHistory(role, generation, threadId) {
        if (!sessionToken) return Promise.resolve();
        const requestedRole = role === undefined ? selectedAgentRole : role;
        const requestedThreadId = threadId === undefined ? conversationId : threadId;
        const isCurrent = function () {
            return generation === undefined || selectionGeneration === generation;
        };

        const base = cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/chat/';
        let url;
        if (requestedThreadId) {
            url = base + encodeURIComponent(requestedThreadId) + '/history';
        } else {
            url = base + 'history';
            const params = [];
            if (cfg.pageContext) {
                params.push('page_context=' + encodeURIComponent(cfg.pageContext));
            }
            if (requestedRole) {
                params.push('agent_role=' + encodeURIComponent(requestedRole));
            }
            if (params.length) url += '?' + params.join('&');
        }

        return fetch(url, {
            headers: { Authorization: 'Bearer ' + sessionToken },
        })
            .then(function (res) {
                // Retry through the shared bounded-budget helper, but re-issue the
                // request for the SAME role/thread: a retry that fell back to the
                // default role would silently load the wrong conversation.
                const handoff = handleAuthFailure(res, function () {
                    if (!isCurrent()) return null;
                    return loadHistory(requestedRole, generation, requestedThreadId);
                });
                if (handoff) return handoff;
                return res.json();
            })
            .then(function (data) {
                if (!data || !isCurrent()) return;
                let messages = Array.isArray(data.messages) ? data.messages : [];
                if (!messages.length && !data.pendingQuestion) return;

                if (data.pendingQuestion && messages.length) {
                    const last = messages[messages.length - 1];
                    if (last && last.role === 'assistant' &&
                        (last.content || '').trim() === String(data.pendingQuestion.question || '').trim()) {
                        messages = messages.slice(0, -1);
                    }
                }

                clearWelcome();
                clearLoading();
                messages.forEach(function (msg) {
                    const when = parseTimestamp(msg.timestamp || msg.created_at || msg.createdAt || msg.time);
                    if (msg.role === 'user') {
                        appendUserMessage(msg.content || '', when);
                    } else if (msg.role === 'assistant') {
                        appendAssistantMessage(msg.content || '', when, msg.media);
                    } else if (msg.role === 'handoff') {
                        appendHandoffMessage(msg.sourceAgent || '', msg.content || '', when);
                    } else if (msg.role === 'visual' && msg.visual) {
                        appendVisualMessage(msg.visual, when);
                    } else if (msg.role === 'document' && msg.document) {
                        appendDocumentMessage(msg.document, when);
                    }
                });

                if (data.pendingQuestion) {
                    hasMessages = true;
                    // null: restored from history, so no reliable time to show.
                    const assistantEl = makeMessage('assistant', null);
                    chatListEl.appendChild(assistantEl);
                    // focus:false — restoring history must not pull the caret
                    // (and the page scroll) into the chat on load.
                    appendQuestion(assistantEl, data.pendingQuestion, { focus: false });
                }

                setStatus('connected');
                scrollToBottom(true);
            })
            .catch(function (err) {
                // A spent re-auth budget is terminal — say so instead of
                // painting "Connected" over a credential WAP keeps rejecting.
                if (err && err.wapAuthExhausted) {
                    showAuthFailure();
                    return;
                }
                if (isCurrent()) setStatus('connected');
            });
    }

    // -------------------------------------------------------------------------
    // Per-agent welcome content — failure is non-fatal (host defaults render).
    // -------------------------------------------------------------------------

    function loadAgentWelcome(role, generation) {
        if (!sessionToken) return Promise.resolve();
        if (!cfg.wapBrowserUrl) return Promise.resolve();
        const requestedRole = role === undefined ? selectedAgentRole : role;
        const isCurrent = function () {
            return generation === undefined || selectionGeneration === generation;
        };

        // page_context AND agent_role must match loadHistory()/send(), or the
        // welcome screen describes a different agent than the one answering.
        let url = cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/agents/welcome';
        const wparams = [];
        if (cfg.pageContext) {
            wparams.push('page_context=' + encodeURIComponent(cfg.pageContext));
        }
        if (requestedRole) {
            wparams.push('agent_role=' + encodeURIComponent(requestedRole));
        }
        if (wparams.length) url += '?' + wparams.join('&');
        return fetch(url, {
            headers: { Authorization: 'Bearer ' + sessionToken },
        })
            .then(function (res) {
                // Same as loadHistory: the retry must re-request this role, not
                // whatever the selector defaults to.
                const handoff = handleAuthFailure(res, function () {
                    if (!isCurrent()) return null;
                    return loadAgentWelcome(requestedRole, generation);
                });
                if (handoff) return handoff;
                if (!res.ok) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data || typeof data !== 'object' || !isCurrent()) return;
                if (typeof data.welcomeTitle === 'string') {
                    agentWelcome.welcomeTitle = data.welcomeTitle.trim();
                }
                if (typeof data.welcomeMessage === 'string') {
                    agentWelcome.welcomeMessage = data.welcomeMessage.trim();
                }
                if (Array.isArray(data.promptSuggestions)) {
                    agentWelcome.promptSuggestions = data.promptSuggestions
                        .filter(function (s) { return typeof s === 'string' && s.trim().length > 0; })
                        .slice(0, 4)
                        .map(function (s) { return s.trim(); });
                }
            })
            .catch(function () {});
    }

    // -------------------------------------------------------------------------
    // Sending messages
    // -------------------------------------------------------------------------

    // `source` is how the send was triggered ('keyboard' | 'button') — tracked as
    // button_context so the two paths stay distinguishable.
    function handleSend(source) {
        // readOnly is checked independently of the disabled composer: a replay
        // must not send even if something re-enables the input.
        if (cfg.readOnly) return;

        const message = inputEl.value.trim();
        if (!message || isStreaming || newChatPending || !isConsentGranted() || isQuotaExhausted()) return;

        // Fired here, past the guards, rather than on the button's click: Enter is
        // the primary send path and bypasses the button entirely (undercount),
        // while the button stays enabled on an empty composer, where handleSend
        // bails above (overcount). This point is exactly "a message was sent".
        trackButtonClick('send', { button_context: source || 'button' });

        inputEl.value = '';
        autoGrow();

        clearWelcome();
        // Drop any transient notice (e.g. the delete confirmation) on first send.
        chatListEl.querySelectorAll('.wap-transient').forEach(function (n) { n.remove(); });
        // Typing instead of answering an open ask_user question dismisses its card.
        if (pendingQuestionCleanup) pendingQuestionCleanup();
        appendUserMessage(message);

        if (!sessionToken) {
            authenticate(false).then(function () { sendMessage(message); });
        } else {
            sendMessage(message);
        }
    }

    // Shared SSE request driver for both the initial /chat/stream turn and the
    // /chat/resume continuation. `path` is the API path; `requestBody` the JSON
    // payload. A 401 refresh retries the same captured payload and role once.
    function streamRequest(path, requestBody, options) {
        options = options || {};
        const url = cfg.wapBrowserUrl.replace(/\/$/, '') + path;

        const payload = Object.assign({}, requestBody);
        if (cfg.pageContext) {
            payload.page_context = cfg.pageContext;
        }
        // Validated server-side against this session's family.
        const agentRole = Object.prototype.hasOwnProperty.call(options, 'agentRole')
            ? options.agentRole
            : selectedAgentRole;
        if (agentRole) {
            payload.agent_role = agentRole;
        }
        // Per-site MCP endpoint — authoritatively known by the browser via
        // home_url. The backend uses this (after a cross-tenant guard) to route
        // tool calls to the right site on OneHop multi-tenant clusters where the
        // GRND's signed sub is the cluster domain, not the per-site prefix.
        if (cfg.mcpEndpoint) {
            payload.mcp_endpoint = cfg.mcpEndpoint;
        }

        const body = JSON.stringify(payload);

        function attempt(canRetry) {
            isStreaming = true;
            setStreamingUI(true);
            const controller = new AbortController();
            currentAbortController = controller;
            const assistantEl = appendAssistantPlaceholder();

            return fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    Authorization:   'Bearer ' + sessionToken,
                },
                body:    body,
                signal:  controller.signal,
            })
            .then(function (res) {
                if (res.status === 401 && canRetry) {
                    assistantEl.remove();
                    return authenticate(true).then(function () {
                        if (controller.signal.aborted) {
                            return {
                                ok: false,
                                phase: 'aborted',
                                code: 'aborted',
                                message: '',
                                sawMessageStart: false,
                            };
                        }
                        return attempt(false);
                    });
                }
                if (!res.ok) {
                    // Non-2xx bodies become a STRUCTURED failure result rather than a
                    // throw: the handoff continuation inspects {code, sawDone, sawError}
                    // to decide whether to restore the source agent, and a thrown error
                    // would bypass that decision entirely.
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        const detail = data && data.detail ? data.detail : data || {};
                        const code = detail.error || 'stream_failed';
                        // 429 covers two different limits: waiting helps with the
                        // per-minute one but not with a spent monthly quota, so read
                        // the error code rather than guessing from the status.
                        let message = detail.message;
                        if (!message && res.status === 429) {
                            message = code === 'monthly_quota_exceeded'
                                ? (i18n.errorQuota || 'You have reached your monthly message limit. It resets at the start of next month.')
                                : (i18n.errorRateLimit || 'Too many requests. Please wait before sending another message.');
                        }
                        const failure = {
                            ok: false,
                            phase: 'http',
                            status: res.status,
                            code: code,
                            message: message || i18n.errorGeneric,
                            sawMessageStart: false,
                        };
                        appendErrorToAssistant(assistantEl, failure.message);
                        return failure;
                    });
                }
                if (!res.body) {
                    throw new Error('ReadableStream not supported');
                }
                return readSSEStream(res.body, assistantEl).then(function (result) {
                    if (!result.sawDone && !result.sawError) {
                        appendErrorToAssistant(assistantEl, i18n.errorGeneric);
                    }
                    return Object.assign({
                        ok: result.sawDone && !result.sawError,
                        phase: 'sse',
                    }, result);
                });
            })
            .catch(function (err) {
                if (flushStreamRender) flushStreamRender();
                if (err.name === 'AbortError') {
                    // Stop pressed: freeze spinners/loaders (else the placeholder
                    // spins forever); keep partial text, drop the bubble if empty.
                    const bodyEl = getBody(assistantEl);
                    sealStepLoader(assistantEl);
                    removeLoader(assistantEl); // clear any sibling routing loader
                    if (!bodyEl.textContent.trim()) assistantEl.remove();
                    return {
                        ok: false,
                        phase: 'aborted',
                        code: 'aborted',
                        message: '',
                        sawMessageStart: false,
                    };
                }
                const message = err.message || (i18n.errorGeneric || 'Stream error');
                appendErrorToAssistant(assistantEl, message);
                return {
                    ok: false,
                    phase: 'network',
                    code: 'stream_failed',
                    message: message,
                    sawMessageStart: false,
                };
            })
            .finally(function () {
                flushStreamRender = null;
                stampMessage(assistantEl);
                if (currentAbortController === controller) {
                    currentAbortController = null;
                    if (handoffTransitionGeneration !== selectionGeneration) {
                        isStreaming = false;
                        setStreamingUI(false);
                    }
                    scrollToBottom();
                }
                updateScrollBtn();
                loadQuota();
            });
        }

        return attempt(true);
    }

    function sendMessage(message) {
        return streamRequest('/api/v1/chat/stream', { message: message }, {});
    }

    // Resume a run paused at an ask_user question, sending the user's answer.
    function resumeWithAnswer(answer) {
        return streamRequest('/api/v1/chat/resume', { answer: answer }, {})
            .then(maybeContinueHandoff);
    }

    // Exchange an OAuth callback's code under this session; resolves true only on success.
    function redeemOauthCode(state, code) {
        if (!sessionToken || !cfg.wapBrowserUrl) return Promise.resolve(false);
        return fetch(cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/mcp/oauth/redeem', {
            method: 'POST',
            headers: { Authorization: 'Bearer ' + sessionToken, 'Content-Type': 'application/json' },
            body: JSON.stringify({ state: state, code: code }),
        })
            .then(function (res) { return res.ok; })
            .catch(function () { return false; });
    }

    function maybeContinueHandoff(result) {
        if (!result || !result.handoff || result.sawDone !== true || result.sawError !== false) {
            return result;
        }

        const handoff = result.handoff;
        const sourceRole = handoff.sourceRole || selectedAgentRole;
        const sourceOption = Array.prototype.find.call(agentSelectEl.options, function (option) {
            return option.value === sourceRole;
        });
        const sourceName = handoff.sourceAgent
            || (sourceOption && (sourceOption.title || sourceOption.text))
            || i18n.assistantName;
        const sourceAgentId = handoff.sourceAgentId;
        const generation = ++selectionGeneration;
        let token = handoff.handoffToken;
        handoff.handoffToken = '';
        handoffTransitionGeneration = generation;
        isStreaming = true;
        setStreamingUI(true);

        return loadAgents().then(function (data) {
            if (selectionGeneration !== generation) return result;
            const exactTarget = data && data.agents.find(function (agent) {
                return agent.role === handoff.targetRole && agent.agentId === handoff.targetAgentId;
            });
            if (!exactTarget) {
                appendErrorMessage(i18n.errorGeneric);
                return result;
            }
            return switchAgent(handoff.targetRole).then(function (switched) {
                if (!switched || selectionGeneration !== generation) return result;
                appendHandoffMessage(sourceName, handoff.brief || '');
                return streamRequest(
                    '/api/v1/chat/stream',
                    { handoff_token: token },
                    { agentRole: handoff.targetRole }
                ).then(function (targetResult) {
                    if (targetResult && targetResult.ok === false
                        && targetResult.sawMessageStart !== true) {
                        return restoreAfterHandoffFailure(
                            targetResult, sourceRole, sourceAgentId, generation
                        );
                    }
                    return targetResult;
                });
            });
        }).finally(function () {
            token = null;
            if (handoffTransitionGeneration === generation) {
                handoffTransitionGeneration = null;
                if (selectionGeneration === generation) {
                    isStreaming = false;
                    setStreamingUI(false);
                }
            }
        });
    }

    function restoreAfterHandoffFailure(result, sourceRole, sourceAgentId, generation) {
        if (selectionGeneration !== generation) return Promise.resolve(result);
        return loadAgents().then(function (data) {
            if (selectionGeneration !== generation) return result;
            const available = data && Array.isArray(data.agents) ? data.agents : [];
            const exactSource = available.some(function (agent) {
                return agent.role === sourceRole && agent.agentId === sourceAgentId;
            });
            const currentIsValid = data && available.some(function (agent) {
                return agent.role === data.current;
            });
            const restoreRole = exactSource ? sourceRole : (currentIsValid ? data.current : '');
            const showFailure = function () {
                if (selectionGeneration !== generation) return result;
                appendErrorMessage(result.message || i18n.errorGeneric);
                return result;
            };
            return restoreRole ? switchAgent(restoreRole).then(showFailure) : showFailure();
        });
    }

    // -------------------------------------------------------------------------
    // SSE stream reader
    // -------------------------------------------------------------------------

    function readSSEStream(body, assistantEl) {
        const reader   = body.getReader();
        const decoder  = new TextDecoder();
        let   buffer   = '';
        let   textNode = null;
        let   rawText  = '';
        // Every text block of this reply with its markdown, so a `media` event
        // can re-render them once the backend has approved their images.
        const segments = [];
        const result = {
            sawDone: false,
            sawError: false,
            sawMessageStart: false,
            handoff: null,
        };

        const bodyEl = getBody(assistantEl);

        let rafId = 0;
        let dirty = false;

        function getOrCreateTextNode() {
            if (!textNode) {
                textNode = el('div', 'wap-chat-message-text');
                textNode.setAttribute('translate', 'no');
                bodyEl.appendChild(textNode);
                segments.push({ node: textNode, raw: '' });
            }
            return textNode;
        }

        function renderNow() {
            rafId = 0;
            if (!dirty) return;
            dirty = false;
            const node = getOrCreateTextNode();
            segments[segments.length - 1].raw = rawText;
            reconcileMarkdown(node, renderMarkdown(rawText));
            updateScrollBtn();
        }

        function scheduleRender() {
            dirty = true;
            if (!rafId) rafId = window.requestAnimationFrame(renderNow);
        }

        function flushRender() {
            if (rafId) {
                window.cancelAnimationFrame(rafId);
                rafId = 0;
            }
            renderNow();
        }

        flushStreamRender = flushRender;

        function processEvent(event) {
            switch (event.type) {
                case 'message_start':
                    result.sawMessageStart = true;
                    if (event.conversationId) {
                        conversationId = event.conversationId;
                        // The user id is now known (conversationId prefix) — tie
                        // client events to the same profile as the server side.
                        maybeIdentify();
                    }
                    if (event.agentName) {
                        // Persist the real agent name so subsequent bubbles and
                        // the "About this chat" row pick it up via makeMessage,
                        // then update the current bubble's already-rendered label.
                        i18n.assistantName = event.agentName;
                        const label = assistantEl.querySelector('.wap-meta-author');
                        if (label) label.textContent = event.agentName;
                    }
                    break;

                case 'routing':
                    if (event.routing === 'multi') {
                        flushRender();
                        textNode = null;
                        rawText = '';
                        removeLoader(bodyEl);
                        const routingEl = el('div', 'gv-stream-loader gv-stack-space-sm');
                        routingEl.appendChild(makeStep(i18n.consulting || 'Consulting multiple specialists…', false));
                        assistantEl.insertBefore(routingEl, assistantEl.querySelector('.wap-msg-time'));
                    }
                    break;

                case 'status':
                    // Backend sends a coarse, tool-agnostic key (never the tool
                    // name); we localize it and add it as a new checklist row.
                    // The previously-active row flips to "done" and stays
                    // visible.
                    appendStepRow(assistantEl, statusText(event.key));
                    break;

                case 'text_delta':
                    if (event.delta) {
                        removeLoader(bodyEl);
                        rawText += event.delta;
                        scheduleRender();
                    }
                    break;

                case 'visual':
                    flushRender();
                    removeLoader(bodyEl);
                    if (event.visual) appendVisual(assistantEl, event.visual);
                    textNode = null;
                    rawText = '';
                    break;

                case 'document':
                    flushRender();
                    removeLoader(bodyEl);
                    if (event.document) appendDocument(assistantEl, event.document);
                    textNode = null;
                    rawText = '';
                    break;

                case 'tool_use':
                    flushRender();
                    textNode = null;
                    rawText = '';
                    appendToolUse(assistantEl, event.tool || '', event.input || {});
                    break;

                case 'tool_result':
                    flushRender();
                    appendToolResult(assistantEl, event.tool || '', event.output || '');
                    textNode = null;
                    rawText = '';
                    break;

                case 'question': {
                    flushRender();
                    // The question may repeat the text block a tool_use just closed, so check the last block.
                    const lastText = segments[segments.length - 1];
                    if (lastText && event.question &&
                        lastText.raw.trim() === String(event.question).trim()) {
                        lastText.node.remove();
                    }
                    textNode = null;
                    rawText = '';
                    removeLoader(bodyEl);
                    appendQuestion(assistantEl, event);
                    break;
                }

                case 'media':
                    // Sent after the reply's last token, once the backend has
                    // checked which of its images and videos may display.
                    flushRender();
                    segments.forEach(function (segment) {
                        if (!segment.node.parentNode) return;
                        reconcileMarkdown(segment.node, renderMarkdown(segment.raw, event.media));
                        bindMediaFallbacks(segment.node);
                    });
                    updateScrollBtn();
                    break;

                case 'message_end':
                    sealStepLoader(assistantEl);
                    flushRender();
                    break;

                case 'agent_handoff':
                    result.handoff = event;
                    break;

                case 'error':
                    result.sawError = true;
                    result.code = event.code || 'stream_failed';
                    result.message = event.message || (i18n.errorGeneric || 'Error');
                    flushRender();
                    appendErrorToAssistant(assistantEl, result.message, event.traceId, event.timestamp);
                    break;

                default:
                    break;
            }
        }

        function parseChunk(chunk) {
            buffer += chunk;
            const events = buffer.split('\n\n');
            buffer = events.pop() || '';

            for (let ei = 0; ei < events.length; ei++) {
                const lines = events[ei].split('\n');
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i].trim();
                    if (line === 'data: [DONE]') {
                        result.sawDone = true;
                        if (result.handoff && !result.sawError) {
                            removeLoader(bodyEl);
                            if (!bodyEl.textContent.trim()) assistantEl.remove();
                        } else {
                            sealStepLoader(assistantEl);
                        }
                        return true;
                    }
                    if (line.startsWith('data: ')) {
                        try {
                            processEvent(JSON.parse(line.slice(6)));
                        } catch (e) { /* malformed — skip */ }
                    }
                }
            }
            return false;
        }

        function pump() {
            return reader.read().then(function (read) {
                if (read.done) { flushRender(); return result; }
                var done = parseChunk(decoder.decode(read.value, { stream: true }));
                if (done) { flushRender(); reader.cancel(); return result; }
                return pump();
            });
        }

        return pump().catch(function (err) {
            if (err.name === 'AbortError') throw err;
            result.sawError = true;
            appendErrorToAssistant(assistantEl, i18n.errorGeneric);
            return result;
        });
    }

    // -------------------------------------------------------------------------
    // Message DOM builders — Gravity gv-chat-message structure
    // -------------------------------------------------------------------------

    function getBody(msgEl) {
        return msgEl.querySelector('.gv-chat-message-body');
    }

    // Build a gv-chat-message with a gv-meta row (author + timestamp), matching
    // Gravity's conversational chat pattern. Incoming (assistant) turns get a
    // small AI icon before the author name as a visual aid.
    function makeMessage(direction, date) {
        const incoming = direction !== 'user';
        const msg = el('li', 'gv-chat-message ' + (incoming ? 'gv-chat-incoming' : 'gv-chat-outgoing'));

        // Announced, not shown: the bubble side tells the speakers apart, and a
        // handoff notice marks where another agent takes over.
        msg.appendChild(elText('span',
            incoming ? (i18n.assistantName || 'Assistant') : (i18n.you || 'You'),
            'wap-meta-author gv-sr-only'
        ));

        const body = el('div', 'gv-chat-message-body');
        msg.appendChild(body);

        // History carries no per-message time (date === null), and a guessed
        // "now" would be wrong, so it gets none. A live reply passes null too and
        // is stamped by stampMessage() once its stream ends.
        if (incoming && date !== null) msg.appendChild(messageTime(date instanceof Date ? date : new Date()));
        return msg;
    }

    // "5 minutes ago" under a reply; the exact date and time are in the tooltip.
    function messageTime(date) {
        const time = el('time', 'wap-msg-time');
        time.setAttribute('datetime', date.toISOString());
        time.setAttribute('title', (formatDate(date) + ' ' + formatTime(date)).trim());
        time.textContent = relativeTime(date);
        return time;
    }

    // Stamp a live reply when its turn is over, not while it is still working.
    function stampMessage(msg) {
        if (msg.parentNode && !msg.querySelector('.wap-msg-time')) msg.appendChild(messageTime(new Date()));
    }

    function refreshMessageTimes() {
        if (!chatListEl) return;
        chatListEl.querySelectorAll('.wap-msg-time').forEach(function (node) {
            const date = new Date(node.getAttribute('datetime'));
            if (!isNaN(date.getTime())) node.textContent = relativeTime(date);
        });
    }

    function relativeTime(date) {
        const seconds = Math.round((date.getTime() - Date.now()) / 1000);
        const abs = Math.abs(seconds);
        if (abs >= 7 * 86400 || typeof Intl === 'undefined' || !Intl.RelativeTimeFormat) {
            return formatDate(date);
        }
        const locale = (cfg.locale || '').replace('_', '-') || undefined;
        let rtf;
        try {
            rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
        } catch (e) {
            rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
        }
        if (abs < 60) return rtf.format(0, 'second');
        if (abs < 3600) return rtf.format(Math.round(seconds / 60), 'minute');
        if (abs < 86400) return rtf.format(Math.round(seconds / 3600), 'hour');
        return rtf.format(Math.round(seconds / 86400), 'day');
    }

    function appendUserMessage(text, date) {
        hasMessages = true;
        const msg = makeMessage('user', date);
        getBody(msg).textContent = text;
        chatListEl.appendChild(msg);
        scrollToBottom(true);
        return msg;
    }

    function appendAssistantPlaceholder() {
        hasMessages = true;
        const msg = makeMessage('assistant', null);
        const loader = el('div', 'gv-stream-loader gv-stack-space-sm');
        const step = makeStep(i18n.thinking || 'Thinking…', false);
        step.setAttribute(STEP_PLACEHOLDER_ATTR, '');
        step.classList.add('wap-step--latest');
        loader.appendChild(step);
        getBody(msg).appendChild(loader);
        chatListEl.appendChild(msg);
        scrollToBottom(true);
        return msg;
    }

    function appendAssistantMessage(text, date, media) {
        hasMessages = true;
        const msg = makeMessage('assistant', date);
        getBody(msg).innerHTML = renderMarkdown(text, media);
        bindMediaFallbacks(getBody(msg));
        chatListEl.appendChild(msg);
        return msg;
    }

    function appendHandoffMessage(sourceAgent, content, date) {
        hasMessages = true;
        const row = el('li', 'gv-chat-message gv-chat-incoming');
        const notice = el('div', 'gv-notice gv-notice-info gv-mode-condensed');
        const text = el('p', 'gv-notice-content');
        const heading = el('strong', '');
        heading.textContent = 'Handoff from ' + sourceAgent;
        text.appendChild(heading);
        text.appendChild(document.createElement('br'));
        text.appendChild(document.createTextNode(content));
        notice.appendChild(gvIcon(ICON.info, 'gv-notice-icon'));
        notice.appendChild(text);
        row.appendChild(notice);
        if (date) row.setAttribute('data-timestamp', date.toISOString());
        chatListEl.appendChild(row);
        scrollToBottom(true);
        return row;
    }

    function removeLoader(container) {
        const loader = container.querySelector('.gv-stream-loader');
        if (loader) loader.remove();
    }

    // Resolve a coarse status key from the backend to a localized label.
    function statusText(key) {
        return (key && i18n[key]) || i18n.working || i18n.thinking || 'Working…';
    }

    // Marks the opening "Thinking…" step. The first real step repurposes it in
    // place, and a turn that never runs one drops the whole loader — so the
    // checklist never shows a sealed, meaningless "Thinking… ✓".
    const STEP_PLACEHOLDER_ATTR = 'data-wap-placeholder';

    // One Gravity stream-loader step. Gravity draws the state indicator from the
    // class via a ::before, so there is no icon element to build; the label is a
    // leading text node plus a screen-reader-only status suffix, per the
    // component anatomy. data-label mirrors the label for cheap dedupe/relabel.
    function makeStep(text, done) {
        const span = el('span', done ? 'gv-step-done' : 'gv-step-working');
        span.setAttribute('data-label', text);
        span.appendChild(document.createTextNode(text));
        span.appendChild(elText('span',
            done ? (i18n.stepDone || ', done') : (i18n.stepWorking || ', in progress'),
            'gv-sr-only'));
        return span;
    }

    function relabelStep(span, text) {
        span.setAttribute('data-label', text);
        span.firstChild.textContent = text; // leading text node (label)
    }

    function setStepDone(span) {
        span.classList.remove('gv-step-working');
        span.classList.add('gv-step-done');
        const sr = span.querySelector('.gv-sr-only');
        if (sr) sr.textContent = i18n.stepDone || ', done';
    }

    function setStepWorking(span) {
        span.classList.remove('gv-step-done');
        span.classList.add('gv-step-working');
        const sr = span.querySelector('.gv-sr-only');
        if (sr) sr.textContent = i18n.stepWorking || ', in progress';
    }

    // Collapsed, the checklist shows only the most recent step on one line; with
    // more than one step a caret toggles the full list.
    function markLatestStep(loader, step) {
        const previous = loader.querySelector('.wap-step--latest');
        if (previous) previous.classList.remove('wap-step--latest');
        step.classList.add('wap-step--latest');
        if (loader.querySelector('.wap-steps__toggle') ||
            loader.querySelectorAll('.gv-step-done, .gv-step-working').length < 2) return;

        const toggle = el('button', 'wap-steps__toggle');
        toggle.type = 'button';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', i18n.showDetails || 'Show details');
        toggle.appendChild(gvIcon(ICON.expand));
        toggle.addEventListener('click', function () {
            const open = !loader.classList.contains('wap-steps--open');
            loader.classList.toggle('wap-steps--open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? (i18n.hideDetails || 'Hide details') : (i18n.showDetails || 'Show details'));
        });
        loader.classList.add('wap-steps--many');
        loader.insertBefore(toggle, loader.firstChild);
    }

    function findStepByLabel(loader, text) {
        const steps = loader.children;
        for (let i = 0; i < steps.length; i++) {
            if (steps[i].getAttribute('data-label') === text) return steps[i];
        }
        return null;
    }

    // Append (or re-activate) a step in the assistant message's checklist. Each
    // distinct label appears at most once per turn: a repeated or interleaved
    // action re-activates its existing step instead of stacking a duplicate, so
    // the list stays short when the agent runs many tools repeatedly (WPIN-8731).
    function appendStepRow(assistantEl, text) {
        const bodyEl = getBody(assistantEl);
        let loader = bodyEl.querySelector('.gv-stream-loader');
        if (!loader) {
            loader = el('div', 'gv-stream-loader gv-stack-space-sm');
            bodyEl.appendChild(loader);
        }

        const working = loader.querySelector('.gv-step-working');
        if (working) {
            if (working.hasAttribute(STEP_PLACEHOLDER_ATTR)) {
                working.removeAttribute(STEP_PLACEHOLDER_ATTR);
                relabelStep(working, text);
                markLatestStep(loader, working);
                scrollToBottom();
                return;
            }
            if (working.getAttribute('data-label') === text) return; // already the live step
            setStepDone(working);
        }

        let step = findStepByLabel(loader, text);
        if (step) {
            setStepWorking(step); // re-activate in place
        } else {
            step = loader.appendChild(makeStep(text, false));
        }
        markLatestStep(loader, step);
        scrollToBottom();
    }

    // Called when the turn ends; content events drop the checklist instead. A
    // turn without content keeps it sealed; a placeholder-only loader goes.
    function sealStepLoader(assistantEl) {
        const bodyEl = getBody(assistantEl);
        const loader = bodyEl.querySelector('.gv-stream-loader');
        if (!loader) return;
        const working = loader.querySelector('.gv-step-working');
        if (working && working.hasAttribute(STEP_PLACEHOLDER_ATTR)) {
            loader.remove();
            return;
        }
        if (working) setStepDone(working);
    }

    // traceId/timestamp (from the backend's generic catch-all error event only,
    // see chat.py) let the user reference this specific failure when reporting
    // it — it's the same id attached to the corresponding Sentry event.
    function appendErrorToAssistant(assistantEl, errorMessage, traceId, timestamp) {
        const bodyEl = getBody(assistantEl);
        sealStepLoader(assistantEl); // don't leave a step spinning forever if a tool call errors
        // Also clear any sibling routing loader.
        removeLoader(assistantEl);

        const notice = el('div', 'gv-notice gv-notice-alert gv-mode-condensed');
        const content = el('p', 'gv-notice-content');
        content.textContent = errorMessage;
        notice.appendChild(gvIcon(ICON_BASE + 'error.svg', 'gv-notice-icon'));
        notice.appendChild(content);

        if (traceId) {
            const date = parseTimestamp(timestamp);
            const timeStr = date ? formatPreciseTime(date) : '';
            const refLine = el('p', 'gv-meta-time gv-notice-content');
            refLine.textContent = (i18n.errorReference || 'Reference:') + ' ' + traceId + (timeStr ? ' · ' + timeStr : '');
            notice.appendChild(refLine);
        }

        bodyEl.appendChild(notice);
    }

    function appendErrorMessage(errorMessage) {
        clearWelcome();
        const msg = el('li', 'gv-chat-message gv-chat-incoming');
        msg.setAttribute('role', 'alert');

        const notice = el('div', 'gv-notice gv-notice-alert gv-mode-condensed');
        const content = el('p', 'gv-notice-content');
        content.textContent = errorMessage;
        notice.appendChild(gvIcon(ICON_BASE + 'error.svg', 'gv-notice-icon'));
        notice.appendChild(content);
        msg.appendChild(notice);

        chatListEl.appendChild(msg);
        scrollToBottom();
    }

    // -------------------------------------------------------------------------
    // Tool-use cards — gv-accordion
    // -------------------------------------------------------------------------

    // Tool activity is intentionally NOT surfaced in the chat — we never reveal
    // which MCP ability the assistant is calling. No-op: every client tool call
    // is immediately followed by a coarse `status` event (see appendStepRow()),
    // so that event alone drives the checklist. Adding a generic "Working…" row
    // here too produced a duplicate "Working… / <specific>" pair per tool call
    // that stacked up on parallel/repeated calls (WPIN-8731).
    function appendToolUse() {}

    // No-op: results are never displayed. The next text_delta (the model's
    // answer) clears the working indicator and renders the prose.
    function appendToolResult() {}

    // -------------------------------------------------------------------------
    // Interactive questions — ask_user (Gravity Micro-interface)
    // -------------------------------------------------------------------------

    // Blank and duplicate options are not real choices — ["Yes", "Yes", ""] is
    // one option, not three. Mirrors normalise_choices() in
    // app/lib/agent_runtime/tools.py so both ends count the same way.
    //
    // Choices arrive either as bare strings or as {label, value} objects (a
    // handoff confirmation's display text vs. its routing token), so each entry
    // goes through normalizeChoice() first and de-duplication runs on the value
    // the run would resume with — two rows that merely read alike but resume
    // different agents are two real choices.
    function normaliseChoices(raw) {
        const seen = Object.create(null);
        const cleaned = [];
        (Array.isArray(raw) ? raw : []).forEach(function (choice) {
            const normalized = normalizeChoice(choice);
            const label = normalized.label.trim();
            const value = normalized.value.trim();
            if (!label || !value) return;
            const key = value.toLowerCase();
            if (seen[key]) return;
            seen[key] = true;
            cleaned.push({ label: label, value: value });
        });
        return cleaned;
    }

    // A permission card's question, built from nodes so ability names never render as markup.
    function permissionQuestion(actions) {
        const q = el('div', 'wap-chat__question-text');
        const names = actions.map(function (a) { return String(a); });
        if (names.length === 1) {
            const template = i18n.permissionAskOne || 'The assistant wants to run {action}. Allow it?';
            const parts = template.split('{action}');
            const p = el('p');
            p.appendChild(elText('span', parts[0]));
            p.appendChild(elText('code', names[0]));
            p.appendChild(elText('span', parts.slice(1).join('{action}')));
            q.appendChild(p);
            return q;
        }
        q.appendChild(elText('p', i18n.permissionAskMany || 'The assistant wants to run these actions:'));
        const list = el('ul');
        names.forEach(function (name) {
            const item = el('li');
            item.appendChild(elText('code', name));
            list.appendChild(item);
        });
        q.appendChild(list);
        q.appendChild(elText('p', i18n.permissionAskManyConfirm || 'Allow them?'));
        return q;
    }

    // Render an ask_user question as the response selector: a card docked above
    // the composer with the question, numbered quick replies and a "Type
    // something else…" field. Answering resumes the paused run via /chat/resume;
    // once the card goes, the question moves into the assistant bubble so the
    // transcript still reads question → answer.
    // event: { question, choices: string[], multi_select: bool, allow_free_text: bool, threadId }
    // opts: { focus: bool } — false when restoring a question from history, so a
    // page load never yanks focus (and scroll) into the chat.
    function appendQuestion(assistantEl, event, opts) {
        const bodyEl = getBody(assistantEl);
        const presentation = event.presentation;
        // Worded from our own translations; the server's English question and labels are the fallback.
        const permissionRequest = presentation && presentation.kind === 'permission_request' &&
            Array.isArray(presentation.actions) && presentation.actions.length ? presentation : null;

        let questionEl = null;
        if (permissionRequest) {
            questionEl = permissionQuestion(permissionRequest.actions);
        } else if (event.question) {
            questionEl = el('div', 'wap-chat__question-text');
            questionEl.innerHTML = renderMarkdown(event.question);
        }
        // Where the question lands in the bubble once settled: after any text
        // streamed before it, ahead of the OAuth link below.
        const questionSlot = el('div', 'wap-chat__question-slot');
        bodyEl.appendChild(questionSlot);
        // The bubble has nothing to show yet; its time appears with the question.
        assistantEl.classList.add('wap-msg--asking');

        // Auto-open is a convenience; the rendered link is the affordance that always works (popup blockers kill window.open()).
        let pendingOauthFlow = null;
        if (presentation && presentation.kind === 'mcp_oauth_consent' && presentation.url) {
            const serverName = presentation.serverLabel || (i18n.oauthThisService || 'this service');
            let opened = null;
            // Never auto-open for a rehydrated card (history reload).
            if (!(opts && opts.focus === false)) {
                try {
                    // No features string: 'noreferrer' implies 'noopener' and severs window.opener.
                    opened = window.open(presentation.url, '_blank');
                } catch (e) {
                    opened = null;
                }
            }
            const linkWrap = el('div', 'wap-chat__oauth-link');
            const link = el('a');
            link.href = presentation.url;
            link.target = '_blank';
            // No rel: noopener/noreferrer would sever the window.opener the callback needs to report back.
            link.rel = 'opener';
            link.textContent = (i18n.oauthConnectLink || 'Authorize {server}').replace('{server}', serverName);
            linkWrap.appendChild(link);
            if (!opened) {
                const hint = el('div', 'wap-chat__oauth-hint');
                hint.textContent = i18n.oauthPopupBlocked || 'Your browser blocked the pop-up — use the link above.';
                linkWrap.appendChild(hint);
            }
            bodyEl.appendChild(linkWrap);
            // Rehydrated cards listen too: a popup opened before a reload still reports to this window.
            pendingOauthFlow = {
                correlation: presentation.correlation || '',
                origin: presentation.callbackOrigin || '',
                statusEl: linkWrap,
            };
        }

        const multi = !!event.multi_select;
        const allowFreeText = event.allow_free_text !== false;
        // The free-text row already is the "something else" answer, so a
        // model-written "Other" option would show it twice (RKM-2088).
        const choices = normaliseChoices(event.choices).filter(function (choice) {
            return !(allowFreeText && isCatchAllChoice(choice.label));
        });
        if (permissionRequest) {
            choices.forEach(function (choice) {
                if (choice.value === permissionRequest.approveValue) choice.label = i18n.permissionAllow || 'Allow';
                if (choice.value === permissionRequest.rejectValue) choice.label = i18n.permissionDeny || "Don't allow";
            });
        }
        const autoFocus = !(opts && opts.focus === false);
        // Unique id prefix so questions coexisting in thread history don't collide.
        const groupName = 'wap-q-' + (++questionSeq);

        let card = null;
        // Settle the card once answered/dismissed so a late click can't answer
        // twice, and leave the question in the bubble as history.
        let answered = false;
        function neutralize() {
            answered = true;
            if (card) {
                card.querySelectorAll('input, button, textarea').forEach(function (n) { n.disabled = true; });
                card.remove();
            }
            if (questionEl) questionSlot.appendChild(questionEl);
            assistantEl.classList.remove('wap-msg--asking');
            pendingQuestionCleanup = null;
        }
        // label is what the user sees echoed back; value is what the run resumes
        // with. They differ for a handoff confirmation, whose value is a routing
        // token — see Choice.to_wire() in app/lib/agent_runtime/interrupt_payloads.py.
        function answer(label, value) {
            if (answered || !value) return;
            neutralize();
            removeOauthListener();
            appendUserMessage(label);
            resumeWithAnswer(value);
        }

        // The callback page hands us its code; redeeming it under our own session is what binds the grant to us.
        let oauthListener = null;
        function removeOauthListener() {
            if (oauthListener) {
                window.removeEventListener('message', oauthListener);
                oauthListener = null;
            }
        }
        function listenForOauthCompletion(flow) {
            if (!flow || !flow.correlation || !flow.origin) return;
            let redeeming = false;
            oauthListener = function (ev) {
                if (!ev || ev.origin !== flow.origin) return;
                const data = ev.data;
                if (!data || data.type !== 'wap:mcp-oauth-code' || data.state !== flow.correlation) return;
                if (redeeming || answered || typeof data.code !== 'string' || !data.code) return;
                redeeming = true;
                redeemOauthCode(data.state, data.code).then(function (ok) {
                    redeeming = false;
                    if (ok) {
                        answer(i18n.oauthConnectedChoice || "I've connected", 'connected');
                        return;
                    }
                    const hint = el('div', 'wap-chat__oauth-hint');
                    hint.textContent = i18n.oauthRedeemFailed ||
                        'The authorization could not be completed. Cancel and try again.';
                    flow.statusEl.appendChild(hint);
                });
            };
            window.addEventListener('message', oauthListener);
        }
        listenForOauthCompletion(pendingOauthFlow);
        // Gravity keeps the input live: typing a message instead drops this card.
        // The backend routes that message into the paused ask_user tool as its
        // answer, so the turn continues in one normal streamed run.
        pendingQuestionCleanup = function () {
            removeOauthListener();
            if (!answered) neutralize();
        };

        card = buildReplyCard({
            id:            groupName,
            questionEl:    questionEl,
            questionText:  event.question || '',
            // A one-row picker has nothing to choose between (WPIN-8940).
            choices:       choices.length < 2 ? [] : choices,
            multi:         multi && choices.length >= 2,
            // With fewer than 2 choices there is nothing to click, so free text
            // is the only way to answer whatever allow_free_text says (WPIN-8940).
            allowFreeText: allowFreeText || choices.length < 2,
            answer:        answer,
            dismiss:       function () {
                removeOauthListener();
                neutralize();
                if (inputEl) inputEl.focus();
            },
        });
        replyDockEl.appendChild(card);
        // The card itself takes focus, so the keys work at once without a
        // focus ring on the first option.
        if (autoFocus) setTimeout(function () { card.focus(); }, 0);
        scrollToBottom();
    }

    // "Other", "Something else", "None of these"… — an option that only means
    // "let me type my own answer". Exact phrases only, so "Other plugins" stays.
    const CATCH_ALL_RE = /^(?:other|others|something else|anything else|none of (?:these|the above)|else|custom)(?:\s*\(.*\))?\s*[.…:!?]*$/i;
    function isCatchAllChoice(label) {
        return CATCH_ALL_RE.test(String(label || '').trim()) ||
            /^(?:other|something else)\b.*\b(?:specify|in mind|type|write|own)\b/i.test(String(label || '').trim());
    }

    // Sent as the answer when the user presses Skip, so the model can move on.
    const SKIP_ANSWER = 'The user skipped this question.';

    // The response selector card (RKM-2088).
    // opts: { id, questionEl, questionText, choices, multi, allowFreeText, answer(label, value), dismiss() }
    function buildReplyCard(opts) {
        const card = el('div', 'wap-reply');
        card.setAttribute('role', 'group');
        card.setAttribute('tabindex', '-1');

        const head = el('div', 'wap-reply__head');
        if (opts.questionEl) {
            const q = opts.questionEl;
            q.id = opts.id + '-question';
            card.setAttribute('aria-labelledby', q.id);
            head.appendChild(q);
        } else {
            card.setAttribute('aria-label', i18n.selectOne || 'Select one');
        }
        const closeBtn = el('button', 'wap-icon-btn wap-reply__close');
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', i18n.closeQuestion || 'Close');
        closeBtn.title = i18n.closeQuestion || 'Close';
        closeBtn.appendChild(gvIcon(ICON.close));
        closeBtn.addEventListener('click', opts.dismiss);
        head.appendChild(closeBtn);
        card.appendChild(head);

        const list = el('div', 'wap-reply__options');
        card.appendChild(list);

        const selected = [];
        let info = null;
        const optionBtns = [];
        let elseRow = null;
        let elseInput = null;
        opts.choices.forEach(function (choice, i) {
            const btn = el('button', 'wap-reply__option');
            btn.type = 'button';
            const key = elText('span', i < 9 ? String(i + 1) : '', 'wap-reply__key');
            key.setAttribute('aria-hidden', 'true');
            btn.appendChild(key);
            btn.appendChild(elText('span', choice.label, 'wap-reply__label'));
            if (opts.multi) {
                btn.setAttribute('aria-pressed', 'false');
                btn.addEventListener('click', function () {
                    const at = selected.indexOf(choice);
                    if (at === -1) selected.push(choice);
                    else selected.splice(at, 1);
                    btn.setAttribute('aria-pressed', at === -1 ? 'true' : 'false');
                    card.classList.remove('wap-reply--error');
                    info.textContent = selected.length ? countLabel(selected.length) : '';
                });
            } else {
                btn.addEventListener('click', function () { opts.answer(choice.label, choice.value); });
            }
            optionBtns.push(btn);
            list.appendChild(btn);
        });

        // The "else" path: type an answer instead of picking. It bypasses any
        // selection, as typing in the main composer does.
        if (opts.allowFreeText) {
            const row = elseRow = el('div', 'wap-reply__else');
            const icon = gvIcon(ICON.edit, 'wap-reply__else-icon');
            icon.setAttribute('aria-hidden', 'true');
            row.appendChild(icon);
            const input = elseInput = el('input', '');
            input.type = 'text';
            input.setAttribute('placeholder', i18n.otherPlaceholder || 'Type something else…');
            input.setAttribute('aria-label', i18n.otherPlaceholder || 'Type something else…');
            function submitText() {
                const text = input.value.trim();
                if (text) opts.answer(text, text);
            }
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    submitText();
                }
            });
            // While there is text the typed answer is the one that counts: the
            // choices dim and Skip gives way to a send arrow.
            input.addEventListener('input', function () {
                card.classList.toggle('wap-reply--typing', input.value.trim() !== '');
            });
            row.appendChild(input);
            const skip = elText('button', i18n.skipQuestion || 'Skip', 'wap-reply__skip');
            skip.type = 'button';
            skip.addEventListener('click', function () {
                opts.answer(i18n.skipQuestion || 'Skip', SKIP_ANSWER);
            });
            row.appendChild(skip);
            const send = el('button', 'wap-icon-btn wap-reply__send');
            send.type = 'button';
            send.setAttribute('aria-label', i18n.send || 'Send');
            send.appendChild(gvIcon(ICON.send));
            send.addEventListener('click', submitText);
            row.appendChild(send);
            list.appendChild(row);
        }

        if (opts.multi) {
            const bottom = el('div', 'wap-reply__bottom');
            info = el('span', 'wap-reply__info');
            info.setAttribute('role', 'status');
            const confirmBtn = el('button', 'gv-button gv-button-primary gv-mode-condensed');
            confirmBtn.type = 'button';
            confirmBtn.appendChild(elText('span', i18n.confirmAnswer || 'Confirm'));
            confirmBtn.addEventListener('click', function () {
                if (!selected.length) {
                    card.classList.add('wap-reply--error');
                    info.textContent = i18n.selectAtLeastOne || 'Select at least 1 option.';
                    return;
                }
                const picked = opts.choices.filter(function (choice) { return selected.indexOf(choice) !== -1; });
                opts.answer(
                    picked.map(function (choice) { return choice.label; }).join(', '),
                    picked.map(function (choice) { return choice.value; }).join(', ')
                );
            });
            bottom.appendChild(info);
            bottom.appendChild(confirmBtn);
            card.appendChild(bottom);
        }

        // Up/Down step through the options and the free-text field, wrapping
        // at the ends. From the card itself (or Confirm), Down starts at the top
        // and Up at the bottom.
        const navItems = elseInput ? optionBtns.concat([elseInput]) : optionBtns;
        function stepFocus(from, delta) {
            if (!navItems.length) return;
            let at = navItems.indexOf(from);
            if (at === -1 && elseRow && from && from.parentNode === elseRow) at = navItems.length - 1;
            const next = at === -1
                ? (delta > 0 ? 0 : navItems.length - 1)
                : (at + delta + navItems.length) % navItems.length;
            navItems[next].focus();
        }

        // 1–9 pick a row and Escape closes, unless the user is typing.
        card.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                stepFocus(e.target, e.key === 'ArrowDown' ? 1 : -1);
                return;
            }
            if (e.target && e.target.tagName === 'INPUT') {
                if (e.key === 'Escape') { e.preventDefault(); opts.dismiss(); }
                return;
            }
            if (e.key === 'Escape') {
                e.preventDefault();
                opts.dismiss();
                return;
            }
            const n = /^[1-9]$/.test(e.key) ? parseInt(e.key, 10) - 1 : -1;
            if (n >= 0 && optionBtns[n]) {
                e.preventDefault();
                optionBtns[n].click();
            }
        });

        return card;
    }

    // "N selected.", honouring an optional {n} placeholder in the i18n string.
    function countLabel(n) {
        const tpl = i18n.nSelected || '{n} selected.';
        return tpl.indexOf('{n}') !== -1 ? tpl.replace('{n}', n) : (n + ' ' + tpl);
    }

    function normalizeChoice(choice) {
        if (choice && typeof choice === 'object') {
            return {
                label: String(choice.label || ''),
                value: String(choice.value || ''),
            };
        }
        return { label: String(choice || ''), value: String(choice || '') };
    }

    // -------------------------------------------------------------------------
    // GDPR
    // -------------------------------------------------------------------------

    function handleDeleteData(onConfirmed) {
        showConfirm({
            title:        i18n.deleteTitle || 'Delete your data?',
            message:      i18n.deleteConfirm || 'This will permanently delete all your conversation history with the assistant. This cannot be undone.',
            confirmLabel: i18n.deleteData || 'Delete my data',
            cancelLabel:  i18n.cancel || 'Cancel',
            destructive:  true,
        }).then(function (confirmed) {
            if (!confirmed) return;
            if (onConfirmed) onConfirmed();
            performDeleteData();
        });
    }

    function performDeleteData() {
        if (!sessionToken) {
            appendErrorMessage(i18n.errorGeneric || 'Not connected. Please refresh and try again.');
            return;
        }

        const backendUrl = cfg.wapBrowserUrl.replace(/\/$/, '') + '/api/v1/me/data/erase';
        // Sentinel: a 401 hands off to a re-authenticated retry — every later
        // stage of this chain must bail out so local cleanup and the success
        // notice only ever run after WAP confirmed the erasure.
        const RETRIED = { retried: true };

        fetch(backendUrl, {
            method:  'POST',
            headers: {
                Authorization:  'Bearer ' + sessionToken,
                'Content-Type': 'application/json',
            },
        })
            .then(function (res) {
                // Expired/revoked credential. Erasure MUST NOT silently no-op
                // (the success notice would lie about a GDPR deletion) — hand
                // off to a re-authenticated retry, or fail loudly once the
                // re-auth budget is spent.
                const handoff = handleAuthFailure(res, function () {
                    performDeleteData();
                    return RETRIED;
                });
                if (handoff) return handoff;
                if (!res.ok) throw new Error('HTTP ' + res.status);
            })
            .then(function (marker) {
                if (marker === RETRIED) return RETRIED;
                // Host-side cleanup after WAP erased the backend data. A SaaS
                // host injects eraseLocalData (or omits it — nothing local to
                // clean); WordPress pages fall back to the admin-ajax handler
                // that revokes App Passwords and cached tokens.
                if (typeof cfg.eraseLocalData === 'function') {
                    return Promise.resolve(cfg.eraseLocalData());
                }
                if (!cfg.ajaxUrl) return null;

                const data = new URLSearchParams({
                    action:      'wap_client_delete_data',
                    _ajax_nonce: cfg.deleteDataNonce || '',
                });
                return fetch(cfg.ajaxUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    data.toString(),
                }).then(function (res) { return res.json(); });
            })
            .then(function (json) {
                if (json === RETRIED) return;
                if (json && json.success === false) {
                    throw new Error(json.data && json.data.message ? json.data.message : (i18n.errorGeneric || 'Delete failed'));
                }

                // Erased: drop the local session and thread so the next message
                // provisions a fresh session/conversation automatically. No reload
                // needed — the composer stays usable.
                sessionToken = '';
                hasMessages = false;
                conversationId = '';
                // Called, not just nulled: it may hold a live OAuth completion
                // listener that would otherwise leak.
                if (pendingQuestionCleanup) pendingQuestionCleanup();
                // The erase covers the client-side analytics identity too — the
                // distinct_id here IS the erased user_id.
                trackingResetIdentity();

                chatListEl.innerHTML = '';

                const notice  = el('div', 'gv-notice gv-notice-success wap-transient');
                notice.setAttribute('role', 'status');
                const content = el('p', 'gv-notice-content');
                content.textContent = i18n.deleteSuccess || 'Your data has been deleted.';
                notice.appendChild(gvIcon(ICON.success, 'gv-notice-icon'));
                notice.appendChild(content);
                chatListEl.appendChild(notice);

                // Auto-dismiss the confirmation so it doesn't linger; it's also
                // cleared as soon as the user starts a new message (see handleSend).
                setTimeout(function () { notice.remove(); }, 5000);

                inputEl.focus();

                // Erasure may have purged the stored consent (the WordPress
                // adapter deletes it with the rest of the local state), so
                // re-check it before re-provisioning — same sequenced check
                // as bootSession() (see init()), and for the same reason:
                // checking consent and calling authenticate() in parallel let
                // whichever response landed last reopen/close the gate
                // independently of the other, which could reopen a gate the
                // other had just closed with nothing left to dismiss it.
                reauthAttempts = 0;
                reauthenticateAfterErasure();
            })
            .catch(function (err) {
                appendErrorMessage(err.message || (i18n.errorGeneric || 'Error deleting data.'));
            });
    }

    // -------------------------------------------------------------------------
    // Visual answers — charts, KPI rows, and the model's own HTML
    //
    // The backend sends typed data (see app/lib/agent_runtime/visuals.py), never
    // markup, for every kind but `html`. This registry maps a kind to the
    // function that draws it, so the model picks WHAT to show and this file
    // decides how it looks. Adding a kind is one entry here plus one branch in
    // the backend's VisualSpec — nothing in between needs to know.
    //
    // Everything a renderer draws is a single <svg> so the download is exact:
    // serialise it, rasterise it, hand the user a PNG. The `html` kind is the
    // exception on both counts — it renders in a sandboxed frame and falls back
    // to a self-contained .html download when it cannot be rasterised.
    // -------------------------------------------------------------------------

    const SVG_NS = 'http://www.w3.org/2000/svg';
    const XHTML_NS = 'http://www.w3.org/1999/xhtml';

    // Gravity design tokens the charts paint with, each with the v5 group-one
    // value as a fallback. Resolved through getComputedStyle at render time, so
    // a chart picks up the host's brand and its current light/dark values — and
    // so the serialised SVG carries concrete colours, which is what makes the
    // downloaded PNG match the screen (a var() reference resolves to nothing
    // once the SVG is detached from the document).
    const VISUAL_TOKENS = {
        '--color-chart-100': '#0066aa',
        '--color-chart-200': '#fec600',
        '--color-chart-300': '#63528d',
        '--color-chart-400': '#029697',
        '--color-chart-500': '#965592',
        '--color-chart-600': '#7ebdf0',
        '--color-chart-700': '#c55986',
        '--color-chart-800': '#89a8c2',
        '--color-chart-neutral': '#cecece',
        '--color-body-on-default': '#141414',
        '--color-body-on-alternative': '#69686b',
        '--color-border-alt': '#e2e2e2',
        '--color-surface-bright': '#ffffff',
        '--color-surface-dim': '#f7f7f7',
        '--color-signal-success': '#467412',
        '--color-signal-warning': '#a35700',
        '--color-signal-alert': '#b30016',
    };

    const CHART_SERIES_TOKENS = [
        '--color-chart-100', '--color-chart-200', '--color-chart-300', '--color-chart-400',
        '--color-chart-500', '--color-chart-600', '--color-chart-700', '--color-chart-800',
    ];

    // A font stack of locally-installed faces only. Gravity's own heading face is
    // a webfont, which the rasteriser cannot load from a detached SVG — naming it
    // here would make every downloaded PNG silently reflow into a fallback.
    const VISUAL_FONT = "system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";

    const STAT_STATE_TOKENS = {
        good: '--color-signal-success',
        warn: '--color-signal-warning',
        bad: '--color-signal-alert',
        neutral: '--color-body-on-alternative',
    };

    let tokenCache = null;

    // Every read is guarded independently: the widget embeds in host pages it
    // does not control, and a chart must still draw in its fallback colours if
    // the CSSOM is unavailable or behaves unexpectedly — never fail to render
    // over a colour lookup.
    function resolveVisualTokens() {
        let computed = null;
        try {
            if (document.documentElement && typeof window.getComputedStyle === 'function') {
                computed = window.getComputedStyle(document.documentElement);
            }
        } catch (e) { computed = null; }

        const resolved = {};
        Object.keys(VISUAL_TOKENS).forEach(function (key) {
            let value = '';
            try {
                if (computed && typeof computed.getPropertyValue === 'function') {
                    value = String(computed.getPropertyValue(key) || '').trim();
                }
            } catch (e) { value = ''; }
            resolved[key] = value || VISUAL_TOKENS[key];
        });
        return resolved;
    }

    function visualToken(name) {
        if (!tokenCache) tokenCache = resolveVisualTokens();
        return tokenCache[name] || VISUAL_TOKENS[name] || '#000000';
    }

    // Gravity's stylesheet loads after first paint, and the host page can switch
    // theme; either way the cached values are stale, so drop them and let the
    // next chart re-resolve. Already-drawn charts keep their colours — redrawing
    // them would fight the streaming reconciler for no visible gain.
    function invalidateVisualTokens() {
        tokenCache = null;
    }

    function visualLabel(visual) {
        if (!visual) return '';
        const title = String(visual.title || '');
        const caption = visual.caption ? ' — ' + String(visual.caption) : '';
        return title + caption;
    }

    function seriesColor(index) {
        return visualToken(CHART_SERIES_TOKENS[index % CHART_SERIES_TOKENS.length]);
    }

    // A colour the user asked for (validated #RRGGBB server-side, re-checked
    // here) overrides the palette; anything else keeps the palette colour.
    function seriesColorFor(entry, index) {
        const color = entry && entry.color;
        return typeof color === 'string' && /^#[0-9a-f]{6}$/i.test(color) ? color : seriesColor(index);
    }

    function svgEl(tag, attrs) {
        const node = document.createElementNS(SVG_NS, tag);
        if (attrs) {
            Object.keys(attrs).forEach(function (key) {
                if (attrs[key] !== null && attrs[key] !== undefined) {
                    node.setAttribute(key, String(attrs[key]));
                }
            });
        }
        return node;
    }

    function svgText(x, y, text, attrs) {
        const node = svgEl('text', attrs || {});
        node.setAttribute('x', String(x));
        node.setAttribute('y', String(y));
        node.setAttribute('font-family', VISUAL_FONT);
        node.textContent = text;
        return node;
    }

    // Chart canvas. A fixed viewBox with width:100% scales to any bubble width
    // without re-measuring on resize, and gives the PNG a deterministic size.
    const CHART_W = 640;
    const CHART_H = 280;
    const PAD = { top: 16, right: 18, bottom: 40, left: 52 };

    function chartRoot(height, visual) {
        const h = height || CHART_H;
        const svg = svgEl('svg', {
            viewBox: '0 0 ' + CHART_W + ' ' + h,
            width: '100%',
            height: 'auto',
            role: 'img',
            // Without a name, role="img" announces the chart as an unlabelled
            // image — the marks carry the whole answer and none of it reaches a
            // screen reader. The title and caption are that name.
            'aria-label': visualLabel(visual),
            preserveAspectRatio: 'xMidYMid meet',
        });
        // An opaque backdrop: a transparent PNG dropped into an email or a
        // light-on-light report is unreadable.
        svg.appendChild(svgEl('rect', {
            x: 0, y: 0, width: CHART_W, height: h, fill: visualToken('--color-surface-bright'),
        }));
        return svg;
    }

    // Axis bounds on a 1/2/5 × 10ⁿ step, so gridline labels read as round
    // numbers instead of the data's own extremes.
    function niceScale(min, max) {
        if (!isFinite(min) || !isFinite(max)) return { min: 0, max: 1, step: 1 };
        if (min === max) {
            if (min === 0) return { min: 0, max: 1, step: 0.5 };
            const pad = Math.abs(min) * 0.2;
            min -= pad;
            max += pad;
        }
        // Anchor at zero whenever the data is entirely one side of it: a bar
        // chart with a truncated baseline exaggerates every difference on it.
        if (min > 0) min = 0;
        if (max < 0) max = 0;
        const rawStep = (max - min) / 4;
        const magnitude = Math.pow(10, Math.floor(Math.log(rawStep) / Math.LN10));
        const normalized = rawStep / magnitude;
        const step = (normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10) * magnitude;
        return { min: Math.floor(min / step) * step, max: Math.ceil(max / step) * step, step: step };
    }

    function formatTick(value, unit) {
        const rounded = Math.round(value * 1000) / 1000;
        const abs = Math.abs(rounded);
        let text;
        if (abs >= 1000000) text = (rounded / 1000000).toFixed(abs >= 10000000 ? 0 : 1) + 'M';
        else if (abs >= 1000) text = (rounded / 1000).toFixed(abs >= 10000 ? 0 : 1) + 'k';
        else text = String(rounded);
        return text + (unit || '');
    }

    function numericPoints(series) {
        const values = [];
        (series || []).forEach(function (entry) {
            (entry.points || []).forEach(function (point) {
                if (typeof point === 'number' && isFinite(point)) values.push(point);
            });
        });
        return values;
    }

    // Shared frame for the two series charts: gridlines, y labels, x labels and
    // a legend. Returns the geometry the caller plots its marks into.
    function drawAxes(svg, visual, scale, height) {
        const h = height || CHART_H;
        const categories = visual.categories || [];
        const plot = {
            x: PAD.left,
            y: PAD.top,
            w: CHART_W - PAD.left - PAD.right,
            h: h - PAD.top - PAD.bottom,
        };
        const muted = visualToken('--color-body-on-alternative');
        const grid = visualToken('--color-border-alt');

        const toY = function (value) {
            const span = scale.max - scale.min || 1;
            return plot.y + plot.h - ((value - scale.min) / span) * plot.h;
        };

        for (let value = scale.min; value <= scale.max + scale.step / 2; value += scale.step) {
            const y = toY(value);
            svg.appendChild(svgEl('line', {
                x1: plot.x, y1: y, x2: plot.x + plot.w, y2: y,
                stroke: grid, 'stroke-width': 1,
            }));
            svg.appendChild(svgText(plot.x - 8, y + 4, formatTick(value, visual.unit), {
                fill: muted, 'font-size': 11, 'text-anchor': 'end',
            }));
        }

        // Thin the x labels rather than overlapping them: at most ~10 fit.
        const every = Math.ceil(categories.length / 10) || 1;
        const band = plot.w / (categories.length || 1);
        categories.forEach(function (label, i) {
            if (i % every !== 0 && i !== categories.length - 1) return;
            svg.appendChild(svgText(plot.x + band * (i + 0.5), plot.y + plot.h + 18, label, {
                fill: muted, 'font-size': 11, 'text-anchor': 'middle',
            }));
        });

        const series = visual.series || [];
        if (series.length > 1) {
            let offset = plot.x;
            series.forEach(function (entry, i) {
                svg.appendChild(svgEl('rect', {
                    x: offset, y: h - 12, width: 9, height: 9, rx: 2, fill: seriesColorFor(entry, i),
                }));
                const label = svgText(offset + 14, h - 4, entry.name, {
                    fill: visualToken('--color-body-on-default'), 'font-size': 11,
                });
                svg.appendChild(label);
                offset += 24 + String(entry.name).length * 6;
            });
        }

        return { plot: plot, toY: toY, band: band };
    }

    function renderLineChart(visual) {
        const svg = chartRoot(null, visual);
        const values = numericPoints(visual.series);
        const scale = niceScale(Math.min.apply(null, values.concat([0])), Math.max.apply(null, values.concat([0])));
        const axes = drawAxes(svg, visual, scale);
        const categories = visual.categories || [];

        (visual.series || []).forEach(function (entry, index) {
            const color = seriesColorFor(entry, index);
            // A null point is a gap in the measurements, not a zero, so the line
            // breaks there instead of diving to the axis and back.
            let run = [];
            const flush = function () {
                if (run.length > 1) {
                    svg.appendChild(svgEl('polyline', {
                        points: run.map(function (p) { return p.x + ',' + p.y; }).join(' '),
                        fill: 'none', stroke: color, 'stroke-width': 2,
                        'stroke-linecap': 'round', 'stroke-linejoin': 'round',
                    }));
                }
                run.forEach(function (p) {
                    svg.appendChild(svgEl('circle', { cx: p.x, cy: p.y, r: 3, fill: color }));
                });
                run = [];
            };
            categories.forEach(function (unusedLabel, i) {
                const value = (entry.points || [])[i];
                if (typeof value !== 'number' || !isFinite(value)) { flush(); return; }
                run.push({ x: axes.plot.x + axes.band * (i + 0.5), y: axes.toY(value) });
            });
            flush();
        });

        return svg;
    }

    function renderBarChart(visual) {
        const svg = chartRoot(null, visual);
        const values = numericPoints(visual.series);
        const scale = niceScale(Math.min.apply(null, values.concat([0])), Math.max.apply(null, values.concat([0])));
        const axes = drawAxes(svg, visual, scale);
        const series = visual.series || [];
        const zeroY = axes.toY(Math.max(scale.min, 0));

        // Bars share their category's band with a gutter either side.
        const groupWidth = axes.band * 0.72;
        const barWidth = Math.max(2, groupWidth / (series.length || 1));

        (visual.categories || []).forEach(function (unusedLabel, i) {
            const groupX = axes.plot.x + axes.band * (i + 0.5) - groupWidth / 2;
            series.forEach(function (entry, s) {
                const value = (entry.points || [])[i];
                if (typeof value !== 'number' || !isFinite(value)) return;
                const y = axes.toY(value);
                svg.appendChild(svgEl('rect', {
                    x: groupX + barWidth * s,
                    y: Math.min(y, zeroY),
                    width: Math.max(1, barWidth - 1.5),
                    height: Math.max(1, Math.abs(zeroY - y)),
                    rx: 2,
                    fill: seriesColorFor(entry, s),
                }));
            });
        });

        return svg;
    }

    const GAUGE_H = 190;

    function renderScoreGauge(visual) {
        const svg = chartRoot(GAUGE_H, visual);
        const cx = CHART_W / 2;
        const cy = GAUGE_H / 2 + 6;
        const radius = 62;
        const circumference = 2 * Math.PI * radius;
        const max = typeof visual.maxValue === 'number' && visual.maxValue > 0 ? visual.maxValue : 100;
        const value = typeof visual.value === 'number' ? visual.value : 0;
        const fraction = Math.max(0, Math.min(1, value / max));

        svg.appendChild(svgEl('circle', {
            cx: cx, cy: cy, r: radius, fill: 'none',
            stroke: visualToken('--color-chart-neutral'), 'stroke-width': 14,
        }));
        // Deliberately the neutral brand colour, not a red/amber/green band: the
        // widget does not know whether a high number is good news here. A visual
        // only carries a judgement when the model states one — which is what
        // stat_row's `state` field is for.
        svg.appendChild(svgEl('circle', {
            cx: cx, cy: cy, r: radius, fill: 'none',
            stroke: visualToken('--color-chart-100'), 'stroke-width': 14, 'stroke-linecap': 'round',
            'stroke-dasharray': (circumference * fraction) + ' ' + circumference,
            transform: 'rotate(-90 ' + cx + ' ' + cy + ')',
        }));

        svg.appendChild(svgText(cx, cy + 6, formatTick(value, visual.unit), {
            fill: visualToken('--color-body-on-default'), 'font-size': 38,
            'font-weight': 700, 'text-anchor': 'middle',
        }));
        svg.appendChild(svgText(cx, cy + 28, 'of ' + formatTick(max, visual.unit), {
            fill: visualToken('--color-body-on-alternative'), 'font-size': 12, 'text-anchor': 'middle',
        }));
        return svg;
    }

    function renderStatRow(visual) {
        const stats = visual.stats || [];
        const columns = Math.min(stats.length, 4) || 1;
        const rows = Math.ceil(stats.length / columns);
        const tileW = (CHART_W - 16 - (columns - 1) * 12) / columns;
        const tileH = 92;
        const height = 16 + rows * tileH + (rows - 1) * 12;
        const svg = chartRoot(height, visual);

        stats.forEach(function (stat, i) {
            const col = i % columns;
            const row = Math.floor(i / columns);
            const x = 8 + col * (tileW + 12);
            const y = 8 + row * (tileH + 12);

            svg.appendChild(svgEl('rect', {
                x: x, y: y, width: tileW, height: tileH, rx: 6,
                fill: visualToken('--color-surface-dim'),
                stroke: visualToken('--color-border-alt'), 'stroke-width': 1,
            }));
            svg.appendChild(svgText(x + 14, y + 26, String(stat.label || ''), {
                fill: visualToken('--color-body-on-alternative'), 'font-size': 12,
            }));
            svg.appendChild(svgText(x + 14, y + 58, String(stat.value || ''), {
                fill: visualToken('--color-body-on-default'), 'font-size': 26, 'font-weight': 700,
            }));
            if (stat.delta) {
                svg.appendChild(svgText(x + 14, y + 79, String(stat.delta), {
                    fill: visualToken(STAT_STATE_TOKENS[stat.state] || '--color-body-on-alternative'),
                    'font-size': 12, 'font-weight': 600,
                }));
            }
        });

        return svg;
    }

    // -------------------------------------------------------------------------
    // The `html` kind
    //
    // This renders markup a language model wrote, inside wp-admin, where an
    // escape is a site takeover. The backend refuses anything outside a
    // presentation-only allow-list before it gets here (visuals.py); this is the
    // second and third layers, and neither trusts the first:
    //
    //   isolation  <iframe sandbox="allow-scripts"> with no allow-same-origin.
    //              The frame gets a unique opaque origin: no parent DOM, no
    //              wp-admin cookies or nonces, no storage, no top-level
    //              navigation. allow-scripts is granted for one reason only —
    //              the height reporter below — and the CSP nonce is what keeps
    //              it from being useful to anything the model wrote.
    //   policy     default-src 'none' inside the frame, so admitted markup
    //              still cannot reach the network: no fetch, no beacon, no
    //              stylesheet, no font, no image. Styles are inlined and
    //              script-src names a per-frame nonce, so the only code that
    //              runs is the reporter this file wrote.
    //
    // Design tokens are inlined rather than linked so the frame needs no network
    // at all, and so a visual matches the host's brand and theme exactly.
    // -------------------------------------------------------------------------

    const VISUAL_FRAME_MIN_H = 60;

    // A srcdoc frame inherits the host page's CSP, so on a nonce-CSP host
    // (partners.one) the height reporter only runs under the host's nonce. Read
    // once at load, while currentScript is this file; '' if there is none.
    const HOST_SCRIPT_NONCE = (function () {
        try {
            const script = document.currentScript;
            const nonce = script && script.nonce;
            return typeof nonce === 'string' && /^[A-Za-z0-9+/_=-]+$/.test(nonce) ? nonce : '';
        } catch (e) {
            return '';
        }
    }());
    const VISUAL_FRAME_MAX_H = 1200;

    function frameNonce() {
        const bytes = new Uint8Array(16);
        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(bytes);
        } else {
            for (let i = 0; i < bytes.length; i++) bytes[i] = Math.floor(Math.random() * 256);
        }
        return Array.prototype.map.call(bytes, function (b) {
            return ('0' + b.toString(16)).slice(-2);
        }).join('');
    }

    function tokenDeclarations() {
        return Object.keys(VISUAL_TOKENS).map(function (key) {
            return key + ':' + visualToken(key) + ';';
        }).join('');
    }

    // The frame's own base stylesheet. Kept deliberately plain — the model's
    // markup does the styling; this only makes the defaults sane and stops a
    // visual from painting itself onto an unreadable transparent background.
    function frameBaseCss() {
        return ':root{' + tokenDeclarations() + '}'
            + 'html,body{margin:0;padding:0;background:var(--color-surface-bright);'
            + 'color:var(--color-body-on-default);font-family:' + VISUAL_FONT + ';'
            + 'font-size:14px;line-height:1.5;overflow-x:hidden}'
            + 'body{padding:12px}'
            + '*{box-sizing:border-box;max-width:100%}'
            + 'table{border-collapse:collapse;width:100%}'
            + 'th,td{padding:6px 8px;text-align:left;border-bottom:1px solid var(--color-border-alt)}'
            + 'h1,h2,h3,h4{margin:0 0 8px;line-height:1.3}';
    }

    function visualFrameDocument(html, nonce, frameId) {
        return '<!doctype html><html><head><meta charset="utf-8">'
            + '<meta http-equiv="Content-Security-Policy" content="'
            + "default-src 'none'; style-src 'unsafe-inline'; img-src data:; "
            + "script-src 'nonce-" + nonce + "'; form-action 'none'; base-uri 'none'"
            + '">'
            + '<style>' + frameBaseCss() + '</style></head><body>'
            + html
            + '<script nonce="' + nonce + '">'
            + '(function(){function r(){try{parent.postMessage({wapVisualNonce:"' + frameId + '",'
            + 'height:document.documentElement.scrollHeight},"*");}catch(e){}}'
            + 'r();window.addEventListener("load",r);'
            + 'if(window.ResizeObserver){new ResizeObserver(r).observe(document.documentElement);}}());'
            + '<\/script></body></html>';
    }

    // One listener for every frame on the page: each message is matched to its
    // frame by nonce AND by source window, so neither the host page nor another
    // visual can resize someone else's.
    const visualFrames = {};
    let frameListenerBound = false;

    function bindFrameListener() {
        if (frameListenerBound) return;
        frameListenerBound = true;
        const onMessage = function (event) {
            const data = event.data;
            if (!data || typeof data !== 'object') return;
            const entry = visualFrames[data.wapVisualNonce];
            if (!entry || entry.frame.contentWindow !== event.source) return;
            const height = Number(data.height);
            if (!isFinite(height)) return;
            const maxHeight = entry.maxHeight || VISUAL_FRAME_MAX_H;
            entry.frame.style.height =
                Math.max(VISUAL_FRAME_MIN_H, Math.min(maxHeight, Math.ceil(height))) + 'px';
            updateScrollBtn();
        };
        window.addEventListener('message', onMessage);
        teardowns.push(function () {
            window.removeEventListener('message', onMessage);
            Object.keys(visualFrames).forEach(function (key) { delete visualFrames[key]; });
            frameListenerBound = false;
        });
    }

    // The one sealed frame for markup that is not this page's own: a free-HTML
    // visual, or a generated document's preview. frameId routes height
    // messages; the CSP nonce is the host's when it has one, else the same
    // random value (the behaviour without a host CSP). options: className,
    // height (the placeholder until the frame reports), maxHeight.
    function sandboxFrame(html, title, options) {
        const frameId = frameNonce();
        const frame = el('iframe', 'wap-visual-frame' + (options.className ? ' ' + options.className : ''));
        frame.setAttribute('sandbox', 'allow-scripts');
        frame.setAttribute('referrerpolicy', 'no-referrer');
        frame.setAttribute('loading', 'lazy');
        frame.setAttribute('title', title);
        frame.style.height = options.height + 'px';
        visualFrames[frameId] = { frame: frame, maxHeight: options.maxHeight };
        bindFrameListener();
        frame.setAttribute('srcdoc', visualFrameDocument(html || '', HOST_SCRIPT_NONCE || frameId, frameId));
        return frame;
    }

    function renderFreeHtml(visual) {
        return sandboxFrame(visual.html, visual.title || 'Visual', { height: 220 });
    }

    const VISUAL_RENDERERS = {
        line_chart: renderLineChart,
        bar_chart: renderBarChart,
        score_gauge: renderScoreGauge,
        stat_row: renderStatRow,
        html: renderFreeHtml,
    };

    // -------------------------------------------------------------------------
    // Download
    // -------------------------------------------------------------------------

    function visualFilename(visual, extension) {
        const base = String(visual.title || 'visual')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 60) || 'visual';
        return base + '.' + extension;
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = el('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        // Revoke on a later tick: Safari aborts the download if the URL dies
        // while the click is still being handled.
        window.setTimeout(function () {
            link.remove();
            URL.revokeObjectURL(url);
        }, 4000);
    }

    // Rasterise a standalone SVG string at 2× for a crisp figure in a report.
    // A data: URL (not blob:) keeps the canvas untainted, so toBlob is allowed.
    function rasterize(svgMarkup, width, height, filename, onFail) {
        const image = new Image();
        image.onload = function () {
            try {
                const scale = 2;
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(width * scale);
                canvas.height = Math.round(height * scale);
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = visualToken('--color-surface-bright');
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(function (blob) {
                    if (blob) downloadBlob(blob, filename);
                    else onFail();
                }, 'image/png');
            } catch (e) {
                onFail();
            }
        };
        image.onerror = function () { onFail(); };
        image.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgMarkup);
    }

    function serializeSvg(svg, width, height) {
        const clone = svg.cloneNode(true);
        clone.setAttribute('xmlns', SVG_NS);
        clone.setAttribute('width', String(width));
        clone.setAttribute('height', String(height));
        return new XMLSerializer().serializeToString(clone);
    }

    function svgViewportSize(svg) {
        const box = (svg.getAttribute('viewBox') || '').split(/\s+/);
        return { width: Number(box[2]) || CHART_W, height: Number(box[3]) || CHART_H };
    }

    // Re-parse the model's markup in an inert document before serialising it.
    // DOMParser neither executes script nor fetches subresources, which is why
    // the markup is never assigned into this page's DOM — not even a detached
    // node, where an image's error handler would still fire.
    function htmlToXhtml(html) {
        const doc = new DOMParser().parseFromString(
            '<div xmlns="' + XHTML_NS + '" id="wap-visual-root">' + html + '</div>', 'text/html'
        );
        const root = doc.getElementById('wap-visual-root');
        if (!root) return null;
        root.setAttribute('xmlns', XHTML_NS);
        return new XMLSerializer().serializeToString(root);
    }

    // A free-HTML visual becomes a PNG by wrapping it in <foreignObject>. Fonts
    // and layout are the browser's, so this is close but not guaranteed — hence
    // the .html fallback, which is always exact.
    function htmlVisualSvg(visual, width, height) {
        const body = htmlToXhtml(visual.html || '');
        if (body === null) return null;
        return '<svg xmlns="' + SVG_NS + '" width="' + width + '" height="' + height + '" '
            + 'viewBox="0 0 ' + width + ' ' + height + '">'
            + '<rect width="' + width + '" height="' + height + '" fill="'
            + visualToken('--color-surface-bright') + '"/>'
            + '<foreignObject x="0" y="0" width="' + width + '" height="' + height + '">'
            + '<style xmlns="' + XHTML_NS + '">' + frameBaseCss() + '</style>'
            + body
            + '</foreignObject></svg>';
    }

    function standaloneHtmlDocument(visual) {
        // No script and no nonce: a downloaded file is opened outside the
        // sandbox, so it gets markup and inlined styles only.
        return '<!doctype html>\n<html lang="en">\n<head>\n<meta charset="utf-8">\n'
            + '<meta name="viewport" content="width=device-width, initial-scale=1">\n'
            + '<title>' + escapeHtml(String(visual.title || 'Visual')) + '</title>\n'
            + '<style>' + frameBaseCss() + '</style>\n</head>\n<body>\n'
            + '<h1 style="font-size:18px">' + escapeHtml(String(visual.title || '')) + '</h1>\n'
            + (visual.caption
                ? '<p style="color:var(--color-body-on-alternative)">'
                    + escapeHtml(String(visual.caption)) + '</p>\n'
                : '')
            + (visual.html || '') + '\n</body>\n</html>\n';
    }

    function downloadVisual(visual, node, button) {
        const fail = function () {
            // PNG is best-effort for free HTML; the .html file always works.
            if (visual.kind === 'html') {
                downloadBlob(
                    new Blob([standaloneHtmlDocument(visual)], { type: 'text/html;charset=utf-8' }),
                    visualFilename(visual, 'html')
                );
            }
            if (button) button.disabled = false;
        };

        if (button) button.disabled = true;
        const done = function () { if (button) button.disabled = false; };

        if (visual.kind === 'html') {
            const width = Math.max(320, Math.round(node.getBoundingClientRect().width) || 640);
            const height = Math.max(
                VISUAL_FRAME_MIN_H,
                Math.round(parseFloat(node.style.height) || node.getBoundingClientRect().height || 220)
            );
            const markup = htmlVisualSvg(visual, width, height);
            if (!markup) { fail(); return; }
            rasterize(markup, width, height, visualFilename(visual, 'png'), fail);
            window.setTimeout(done, 1500);
            return;
        }

        const size = svgViewportSize(node);
        rasterize(
            serializeSvg(node, size.width, size.height),
            size.width, size.height,
            visualFilename(visual, 'png'),
            fail
        );
        window.setTimeout(done, 1500);
    }

    // -------------------------------------------------------------------------
    // Card assembly
    // -------------------------------------------------------------------------

    function visualActionButton(label, onClick) {
        const button = el('button', 'gv-button gv-button-secondary gv-mode-condensed wap-visual-action');
        button.type = 'button';
        button.appendChild(elText('span', label));
        button.addEventListener('click', onClick);
        return button;
    }

    /**
     * One visual as a titled card with its download controls.
     *
     * An unknown kind renders as a notice rather than nothing: a widget older
     * than the backend that sent it should say a figure is missing, not leave a
     * silent hole where the model's answer referred to one.
     */
    function buildVisualCard(visual) {
        const card = el('figure', 'wap-visual');
        if (!visual || typeof visual !== 'object') return card;

        const renderer = VISUAL_RENDERERS[visual.kind];
        const head = el('div', 'wap-visual-head');
        head.appendChild(elText('figcaption', String(visual.title || ''), 'wap-visual-title'));
        card.appendChild(head);

        if (!renderer) {
            const notice = el('div', 'gv-notice gv-notice-warning gv-mode-condensed');
            notice.appendChild(elText('p', i18n.visualUnsupported
                || 'This figure needs a newer version of the chat to display.', 'gv-notice-content'));
            card.appendChild(notice);
            return card;
        }

        let node;
        try {
            node = renderer(visual);
        } catch (e) {
            const notice = el('div', 'gv-notice gv-notice-warning gv-mode-condensed');
            notice.appendChild(elText('p', i18n.visualFailed
                || 'This figure could not be drawn.', 'gv-notice-content'));
            card.appendChild(notice);
            return card;
        }

        const body = el('div', 'wap-visual-body');
        body.appendChild(node);
        card.appendChild(body);

        if (visual.caption) {
            card.appendChild(elText('p', String(visual.caption), 'wap-visual-caption'));
        }

        const foot = el('div', 'wap-visual-foot');
        foot.appendChild(visualActionButton(i18n.visualDownload || 'Download image', function (event) {
            downloadVisual(visual, node, event.currentTarget);
        }));
        if (visual.kind === 'html') {
            foot.appendChild(visualActionButton(i18n.visualDownloadHtml || 'Download HTML', function () {
                downloadBlob(
                    new Blob([standaloneHtmlDocument(visual)], { type: 'text/html;charset=utf-8' }),
                    visualFilename(visual, 'html')
                );
            }));
        }
        // Says out loud that a figure came from the assistant. A chart reads as
        // authoritative in a way prose does not, and these get pasted into
        // reports and emails.
        foot.appendChild(elText('span', i18n.visualGenerated || 'Generated by AI', 'wap-visual-note'));
        card.appendChild(foot);
        return card;
    }

    // Live stream: the visual joins the assistant turn already being written, so
    // the model's own words sit above and below it in one bubble.
    function appendVisual(assistantEl, visual) {
        const body = getBody(assistantEl);
        body.classList.add('wap-visual-host');
        body.appendChild(buildVisualCard(visual));
        updateScrollBtn();
    }

    // History and replay: a visual can be the whole of an assistant turn (a
    // chart with no prose), so it needs a bubble of its own.
    function appendVisualMessage(visual, date) {
        hasMessages = true;
        const msg = makeMessage('assistant', date);
        const body = getBody(msg);
        body.classList.add('wap-visual-host');
        body.appendChild(buildVisualCard(visual));
        chatListEl.appendChild(msg);
        return msg;
    }

    // -------------------------------------------------------------------------
    // Documents — an invoice or report the assistant generated (render_document)
    //
    // The backend sends the document's HTML, rendered from a fixed, autoescaped
    // template (no model-authored markup), a one-line summary, and pdfPath —
    // where its PDF downloads from, stamped server-side for the user's own
    // thread (GET /api/v1/chat/{thread}/documents/{id}/pdf). The preview uses the
    // free-HTML visual's sandboxed frame, so it gets the same containment. The
    // PDF is never held client-side: the backend re-prints it from the persisted
    // call, under the same bearer auth as /history — no token goes into a URL.
    // -------------------------------------------------------------------------

    // A document is a page, not a figure: show a scrollable window onto it
    // rather than growing the bubble to the height of a two-page invoice.
    const DOCUMENT_FRAME_MAX_H = 520;

    function documentFilename(doc) {
        const name = String(doc.filename || '');
        return /^[A-Za-z0-9._-]{1,120}\.pdf$/.test(name) ? name : 'document.pdf';
    }

    // A same-origin path, or '' — the widget only fetches what the server stamped.
    function documentPdfPath(doc) {
        const path = typeof doc.pdfPath === 'string' ? doc.pdfPath : '';
        return path.charAt(0) === '/' && path.charAt(1) !== '/' ? path : '';
    }

    function fetchDocumentPdf(doc) {
        const url = cfg.wapBrowserUrl.replace(/\/$/, '') + documentPdfPath(doc);
        const get = function () {
            return fetch(url, { headers: { Authorization: 'Bearer ' + sessionToken } });
        };
        // A GRND that expired since the card was drawn re-authenticates through
        // the shared bounded budget, then retries once.
        return get().then(function (res) {
            return handleAuthFailure(res, get) || res;
        });
    }

    function downloadDocument(doc, button, errorEl) {
        const label = button.querySelector('span');
        const idle = label ? label.textContent : '';
        button.disabled = true;
        errorEl.hidden = true;
        if (label) label.textContent = i18n.documentDownloading || 'Preparing PDF…';
        fetchDocumentPdf(doc)
            .then(function (res) {
                if (!res || !res.ok) throw new Error('document download failed: ' + (res && res.status));
                return res.blob();
            })
            .then(function (blob) {
                downloadBlob(blob, documentFilename(doc));
            })
            .catch(function () {
                errorEl.hidden = false;
            })
            .then(function () {
                button.disabled = false;
                if (label) label.textContent = idle;
            });
    }

    /**
     * One document as a card: title, summary line, sandboxed preview, and a
     * Download PDF action. A payload without a download path still previews,
     * so the user is never left without what the model made.
     */
    function buildDocumentCard(doc) {
        const card = el('figure', 'wap-visual wap-document');
        if (!doc || typeof doc !== 'object') return card;

        const head = el('div', 'wap-visual-head');
        head.appendChild(elText('figcaption', String(doc.title || ''), 'wap-visual-title'));
        card.appendChild(head);
        if (doc.summary) {
            card.appendChild(elText('p', String(doc.summary), 'wap-visual-caption wap-document-summary'));
        }

        const body = el('div', 'wap-visual-body');
        body.appendChild(sandboxFrame(doc.html, doc.title || 'Document', {
            className: 'wap-document-frame',
            height: 320,
            maxHeight: DOCUMENT_FRAME_MAX_H,
        }));
        card.appendChild(body);

        const foot = el('div', 'wap-visual-foot');
        const errorEl = elText('p', i18n.documentDownloadFailed
            || 'The PDF could not be downloaded. Please try again.', 'wap-document-error');
        errorEl.hidden = true;
        if (documentPdfPath(doc) && cfg.wapBrowserUrl && !cfg.readOnly) {
            const button = visualActionButton(i18n.documentDownload || 'Download PDF', function () {
                downloadDocument(doc, button, errorEl);
            });
            foot.appendChild(button);
        }
        foot.appendChild(elText('span', i18n.visualGenerated || 'Generated by AI', 'wap-visual-note'));
        card.appendChild(foot);
        card.appendChild(errorEl);
        return card;
    }


    function appendDocument(assistantEl, doc) {
        const body = getBody(assistantEl);
        body.classList.add('wap-visual-host');
        body.appendChild(buildDocumentCard(doc));
        updateScrollBtn();
    }

    function appendDocumentMessage(doc, date) {
        hasMessages = true;
        const msg = makeMessage('assistant', date);
        const body = getBody(msg);
        body.classList.add('wap-visual-host');
        body.appendChild(buildDocumentCard(doc));
        chatListEl.appendChild(msg);
        return msg;
    }

    // -------------------------------------------------------------------------
    // Minimal, safe Markdown renderer
    // -------------------------------------------------------------------------

    function escapeHtml(s) {
        return s
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function safeHref(url) {
        return /^(https?:|mailto:)/i.test(url) ? url : '#';
    }

    // Private-use sentinels bracketing a code-span's index while we run the
    // other inline rules. They can't occur in real (already-escaped) content,
    // so emphasis/link regexes never touch code contents and we restore the
    // spans verbatim at the end.
    const CODE_OPEN = '';
    const CODE_CLOSE = '';
    const CODE_SENTINEL = /(\d+)/g;

    // -------------------------------------------------------------------------
    // Inline media: images and YouTube videos inside a reply
    // -------------------------------------------------------------------------

    // The media a reply may display, as approved by the backend for that reply
    // (app/lib/agent_runtime/media.py), set only for the duration of one
    // renderMarkdown call. An image or YouTube link that is not in it renders
    // as a plain link: the reply's own markdown never decides what loads.
    let renderMedia = null;

    // Same trick as the code-span sentinels: a rendered card is parked here so
    // the emphasis rules never touch its attributes (alt text with *stars*).
    const MEDIA_OPEN = '';
    const MEDIA_CLOSE = '';
    const MEDIA_SENTINEL = /(\d+)/g;

    const YOUTUBE_ID = /^[A-Za-z0-9_-]{11}$/;
    const YOUTUBE_HOSTS = {
        'youtube.com': 1, 'www.youtube.com': 1, 'm.youtube.com': 1, 'music.youtube.com': 1,
        'youtu.be': 1, 'youtube-nocookie.com': 1, 'www.youtube-nocookie.com': 1,
    };
    const YOUTUBE_ID_PATHS = { embed: 1, shorts: 1, live: 1, v: 1 };

    // Mirrors youtube_video_id in media.py. `url` is HTML-escaped markdown text.
    function youtubeVideoId(url) {
        let parsed;
        try {
            parsed = new URL(url.replace(/&amp;/g, '&'));
        } catch (e) {
            return null;
        }
        if (!/^https?:$/.test(parsed.protocol) || !YOUTUBE_HOSTS[parsed.hostname.toLowerCase()]) return null;
        const segments = parsed.pathname.split('/').filter(Boolean);
        let candidate = '';
        if (parsed.hostname.toLowerCase() === 'youtu.be') candidate = segments[0] || '';
        else if (segments[0] === 'watch') candidate = parsed.searchParams.get('v') || '';
        else if (segments.length >= 2 && YOUTUBE_ID_PATHS[segments[0]]) candidate = segments[1];
        return YOUTUBE_ID.test(candidate) ? candidate : null;
    }

    // Keyed by the escaped URL, because that is how it appears in the escaped
    // markdown being rendered.
    function mediaApproval(media) {
        if (!media || typeof media !== 'object') return null;
        const images = {};
        const videos = {};
        (Array.isArray(media.images) ? media.images : []).forEach(function (url) {
            url = String(url);
            if (/^https:\/\//i.test(url)) images[escapeHtml(url)] = true;
        });
        (Array.isArray(media.videos) ? media.videos : []).forEach(function (id) {
            id = String(id);
            if (YOUTUBE_ID.test(id)) videos[id] = true;
        });
        return { images: images, videos: videos };
    }

    // no-referrer: an image host should not learn which wp-admin page showed it.
    function inlineImageHtml(url, alt) {
        return '<a class="wap-inline-media wap-media-image" href="' + url + '" target="_blank" rel="noopener noreferrer">'
            + '<img src="' + url + '" alt="' + alt + '" loading="lazy" decoding="async" referrerpolicy="no-referrer">'
            + '</a>';
    }

    const PLAY_ICON = '<svg class="wap-media-play" viewBox="0 0 68 48" aria-hidden="true">'
        + '<rect width="68" height="48" rx="12" fill="rgba(0,0,0,0.72)"/>'
        + '<path d="M27 15 L46 24 L27 33 Z" fill="#fff"/></svg>';

    // The card's label as plain text. A linked thumbnail ([![thumb](img)](video))
    // arrives with the image an earlier rule already rendered, as a link or a
    // parked card: its tags and placeholder are dropped, keeping any alt text.
    function videoLabel(label) {
        const text = String(label || '').replace(/<[^>]*>/g, '').replace(MEDIA_SENTINEL, '').trim();
        return text || escapeHtml(i18n.mediaWatch || 'Watch on YouTube');
    }

    // A thumbnail that opens the video on YouTube. Nothing is embedded, and both
    // URLs are built here from the id rather than taken from the reply. The
    // label is text content only, never an attribute value: it comes from the
    // reply, and inside an attribute any quote left in it would end that
    // attribute and start new ones on the card.
    function inlineVideoHtml(id, label) {
        return '<a class="wap-inline-media wap-media-video" href="https://www.youtube.com/watch?v=' + id + '"'
            + ' target="_blank" rel="noopener noreferrer">'
            + '<span class="wap-media-thumb"><img src="https://i.ytimg.com/vi/' + id + '/hqdefault.jpg" alt=""'
            + ' loading="lazy" decoding="async" referrerpolicy="no-referrer">' + PLAY_ICON + '</span>'
            + '<span class="wap-media-label">' + videoLabel(label) + '</span></a>';
    }

    // A moved or deleted image still leaves its link, and says so instead of
    // showing the browser's broken-image glyph. Bound after the markup lands,
    // since rendered HTML cannot carry handlers.
    function bindMediaFallbacks(root) {
        root.querySelectorAll('img').forEach(function (img) {
            const link = img.parentNode;
            if (!link || !link.classList || !link.classList.contains('wap-inline-media')) return;
            if (img.getAttribute('data-wap-fallback')) return;
            img.setAttribute('data-wap-fallback', '1');
            img.addEventListener('error', function () {
                link.classList.add('wap-media-broken');
                link.textContent = i18n.mediaUnavailable || 'This image could not be loaded.';
            });
        });
    }

    function inlineMd(text) {
        // Pull code spans out first and replace with sentinels. Their contents
        // are literal: underscores (`wp_enqueue_scripts`), asterisks and
        // brackets inside code must NOT become emphasis or links.
        const codes = [];
        text = text.replace(/`([^`]+)`/g, function (m, c) {
            codes.push(c);
            return CODE_OPEN + (codes.length - 1) + CODE_CLOSE;
        });
        const media = [];
        function stashMedia(html) {
            media.push(html);
            return MEDIA_OPEN + (media.length - 1) + MEDIA_CLOSE;
        }
        // Images before links: ![alt](url) also matches the link rule. One the
        // backend did not approve is a link, not a stray "!" before one.
        text = text.replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, function (m, alt, url) {
            if (renderMedia && renderMedia.images[url]) return stashMedia(inlineImageHtml(url, alt));
            return '<a href="' + escapeHtml(safeHref(url)) + '" target="_blank" rel="noopener noreferrer">' + (alt || url) + '</a>';
        });
        text = text.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, function (m, label, url) {
            const videoId = renderMedia && youtubeVideoId(url);
            if (videoId && renderMedia.videos[videoId]) return stashMedia(inlineVideoHtml(videoId, label));
            return '<a href="' + escapeHtml(safeHref(url)) + '" target="_blank" rel="noopener noreferrer">' + label + '</a>';
        });
        // A bare YouTube URL on its own; one inside an <a> above is preceded by
        // a quote or a ">", so it is never matched twice.
        if (renderMedia) {
            text = text.replace(/(^|[\s(])(https?:\/\/[^\s<)]+)/g, function (m, lead, url) {
                const videoId = youtubeVideoId(url);
                return videoId && renderMedia.videos[videoId] ? lead + stashMedia(inlineVideoHtml(videoId, '')) : m;
            });
        }
        text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/(^|[^*])\*([^*]+)\*/g, '$1<em>$2</em>');
        // Underscore emphasis only at word boundaries — otherwise snake_case
        // identifiers (foo_bar_baz) would be mangled into italics mid-word.
        text = text.replace(/(^|[^\w])_([^_]+)_(?!\w)/g, '$1<em>$2</em>');
        // Restore the code spans verbatim.
        text = text.replace(CODE_SENTINEL, function (m, i) {
            return '<code>' + codes[+i] + '</code>';
        });
        text = text.replace(MEDIA_SENTINEL, function (m, i) {
            return media[+i];
        });
        return text;
    }

    // A GFM table separator row: each cell is dashes with optional colons.
    function isTableSeparator(line) {
        const cells = line.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|');
        return cells.length > 0 && cells.every(function (c) {
            return /^\s*:?-{1,}:?\s*$/.test(c);
        });
    }

    function splitTableRow(line) {
        return line.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|').map(function (c) {
            return c.trim();
        });
    }

    // Strip tool-call scaffolding that some models emit inline as text
    // (`<function_calls>…</function_calls>`, `<invoke>`, `<parameter>`,
    // `<function_results>`/`<function_response>` blocks, with or without the
    // `antml:` prefix). These are protocol tokens, not content — the widget
    // surfaces real tool activity via the accordion cards, so here we want only
    // the model's human-readable prose ("direct output").
    function stripToolScaffolding(s) {
        // Only remove COMPLETE, properly-closed scaffolding blocks and any stray
        // standalone tags. We deliberately do NOT strip from an unclosed tag to
        // the end of the message — doing so can eat the real answer that streams
        // right after a tool block. Any transient unclosed tag simply renders
        // for a moment mid-stream and is cleaned up once its close arrives.
        return s
            // Complete call / result blocks (any attributes).
            .replace(/<(antml:)?function_calls\b[\s\S]*?<\/(antml:)?function_calls>/gi, '')
            .replace(/<(antml:)?function_(results|response)\b[\s\S]*?<\/(antml:)?function_(results|response)>/gi, '')
            // Any stray scaffolding tags left over (opening or closing).
            .replace(/<\/?(antml:)?(invoke|parameter|function_calls|function_results|function_response)\b[^>]*>/gi, '')
            // Leftover SSE terminator if it ever leaks into text.
            .replace(/\bdata:\s*\[DONE\]\s*/gi, '');
    }

    let renderScratch = null;

    // The markup each streamed element was inserted with. Other scripts rewrite
    // the reply after we insert it (wp-admin's wp-emoji swaps emoji for
    // <img class="emoji">, translation and grammar extensions do similar), so
    // comparing the live outerHTML would never match again: everything from the
    // first rewritten node on would be torn down and faded back in every frame.
    const renderedHtml = new WeakMap();

    function reconcileMarkdown(node, html) {
        if (!renderScratch) renderScratch = document.createElement('div');
        renderScratch.innerHTML = html;
        wrapWords(renderScratch);
        reconcileChildren(node, renderScratch);
        renderScratch.innerHTML = '';
    }

    const NO_WRAP_TAGS = { PRE: 1, CODE: 1 };

    function wrapWords(node) {
        let child = node.firstChild;
        while (child) {
            const after = child.nextSibling;
            if (child.nodeType === 3) {
                wrapTextNode(node, child);
            } else if (child.nodeType === 1 && !NO_WRAP_TAGS[child.tagName]) {
                wrapWords(child);
            }
            child = after;
        }
    }

    function wrapTextNode(parent, textNode) {
        const parts = textNode.nodeValue.split(/(\s+)/);
        const frag = document.createDocumentFragment();
        let wrapped = false;
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            if (!part) continue;
            if (/^\s+$/.test(part)) {
                frag.appendChild(document.createTextNode(part));
            } else {
                const span = document.createElement('span');
                span.textContent = part;
                frag.appendChild(span);
                wrapped = true;
            }
        }
        if (wrapped) parent.replaceChild(frag, textNode);
    }

    function reconcileChildren(target, source) {
        const next = Array.prototype.slice.call(source.childNodes);
        const cur = Array.prototype.slice.call(target.childNodes);
        const shared = Math.min(cur.length, next.length);

        let i = 0;
        while (i < shared && isSameRendered(cur[i], next[i])) i++;

        if (i < shared && isSameElement(cur[i], next[i])) {
            // Read before recursing: the recursion moves next[i]'s children out.
            const html = next[i].outerHTML;
            reconcileChildren(cur[i], next[i]);
            renderedHtml.set(cur[i], html);
            i++;
        }

        for (let j = cur.length - 1; j >= i; j--) target.removeChild(cur[j]);
        for (; i < next.length; i++) {
            const fresh = next[i];
            rememberRendered(fresh);
            target.appendChild(fresh);
            animateIn(fresh);
        }
    }

    function rememberRendered(node) {
        if (node.nodeType !== 1) return;
        renderedHtml.set(node, node.outerHTML);
        for (let i = 0; i < node.children.length; i++) rememberRendered(node.children[i]);
    }

    const FLAT_FADE_TAGS = { SPAN: 1, PRE: 1, CODE: 1, TABLE: 1, THEAD: 1, TBODY: 1, TR: 1, TD: 1, TH: 1 };
    const FADE_MS = 280;
    const FADE_EASING = 'cubic-bezier(0.2, 0, 0, 1)';

    function animateIn(node) {
        if (node.nodeType !== 1 || typeof node.animate !== 'function') return;
        if (prefersReducedMotion()) return;
        node.animate(
            FLAT_FADE_TAGS[node.tagName]
                ? [{ opacity: 0 }, { opacity: 1 }]
                : [
                    { opacity: 0, transform: 'translateY(3px)', filter: 'blur(2px)' },
                    { opacity: 1, transform: 'translateY(0)', filter: 'blur(0px)' },
                ],
            { duration: FADE_MS, easing: FADE_EASING }
        );
    }

    let reducedMotionQuery = null;

    function prefersReducedMotion() {
        if (!window.matchMedia) return false;
        if (!reducedMotionQuery) {
            reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        }
        return reducedMotionQuery.matches;
    }

    function isSameRendered(a, b) {
        if (a.nodeType !== b.nodeType) return false;
        if (a.nodeType !== 1) return a.nodeValue === b.nodeValue;
        const rendered = renderedHtml.get(a);
        return (rendered !== undefined ? rendered : a.outerHTML) === b.outerHTML;
    }

    function isSameElement(a, b) {
        if (a.nodeType !== 1 || b.nodeType !== 1) return false;
        if (a.tagName !== b.tagName) return false;
        if (a.attributes.length !== b.attributes.length) return false;
        for (let i = 0; i < a.attributes.length; i++) {
            const attr = a.attributes[i];
            if (b.getAttribute(attr.name) !== attr.value) return false;
        }
        return true;
    }

    const LIST_ITEM_RE = /^\s*(?:[-*]|\d+\.)\s+/;

    function indentWidth(line) {
        return line.match(/^\s*/)[0].replace(/\t/g, '    ').length;
    }

    // A line that opens a non-paragraph block and so ends a list item's text.
    function isBlockStart(line) {
        const t = line.trim();
        return /^```/.test(t) ||
            /^#{1,6}\s+/.test(t) ||
            /^(-{3,}|\*{3,}|_{3,})$/.test(t) ||
            /^&gt;\s?/.test(t) ||
            t.charAt(0) === '|';
    }

    // `media` is the backend's approval for this reply ({images, videos}); without
    // it every image and YouTube link renders as a plain link.
    function renderMarkdown(src, media) {
        renderMedia = mediaApproval(media);
        try {
            return renderMarkdownBlocks(src);
        } finally {
            renderMedia = null;
        }
    }

    function renderMarkdownBlocks(src) {
        if (!src) return '';
        src = stripToolScaffolding(src);
        if (!src.trim()) return '';
        const escaped = escapeHtml(src); // '>' becomes '&gt;' — handled below.
        const lines = escaped.split('\n');
        let html = '';
        let i = 0;
        let listType = null;

        function closeList() {
            if (listType) { html += '</' + listType + '>'; listType = null; }
        }

        while (i < lines.length) {
            const line = lines[i];
            const trimmed = line.trim();

            // Fenced code block
            if (/^```/.test(trimmed)) {
                closeList();
                i++;
                let code = '';
                while (i < lines.length && !/^```/.test(lines[i].trim())) {
                    code += lines[i] + '\n';
                    i++;
                }
                i++;
                html += '<pre><code>' + code.replace(/\n$/, '') + '</code></pre>';
                continue;
            }

            // Horizontal rule
            if (/^(-{3,}|\*{3,}|_{3,})$/.test(trimmed)) {
                closeList();
                html += '<hr>';
                i++;
                continue;
            }

            // ATX heading (# .. ######)
            const hMatch = trimmed.match(/^(#{1,6})\s+(.*)$/);
            if (hMatch) {
                closeList();
                const level = hMatch[1].length;
                html += '<h' + level + '>' + inlineMd(hMatch[2]) + '</h' + level + '>';
                i++;
                continue;
            }

            // GFM table: header row with pipes, followed by a separator row.
            if (
                line.indexOf('|') !== -1 &&
                i + 1 < lines.length &&
                lines[i + 1].indexOf('|') !== -1 &&
                isTableSeparator(lines[i + 1])
            ) {
                closeList();
                const headers = splitTableRow(line);
                i += 2; // skip header + separator
                let table = '<table><thead><tr>';
                headers.forEach(function (h) { table += '<th>' + inlineMd(h) + '</th>'; });
                table += '</tr></thead><tbody>';
                while (i < lines.length && lines[i].trim() !== '' && lines[i].indexOf('|') !== -1) {
                    const cells = splitTableRow(lines[i]);
                    table += '<tr>';
                    cells.forEach(function (c) { table += '<td>' + inlineMd(c) + '</td>'; });
                    table += '</tr>';
                    i++;
                }
                table += '</tbody></table>';
                // Wrap in a scroll container so a wide table scrolls inside its
                // own box instead of forcing display:block on the <table> (which
                // drops row/column semantics for screen readers). The wrapper
                // owns the horizontal overflow; the table keeps real table
                // semantics. See the .wap-table-wrap rule in wap-chat.css.
                html += '<div class="wap-table-wrap">' + table + '</div>';
                continue;
            }

            // Blockquote ('>' was escaped to '&gt;')
            if (/^\s*&gt;\s?/.test(line)) {
                closeList();
                let quote = '';
                while (i < lines.length && /^\s*&gt;\s?/.test(lines[i])) {
                    quote += lines[i].replace(/^\s*&gt;\s?/, '') + '\n';
                    i++;
                }
                html += '<blockquote>' + inlineMd(quote.replace(/\n$/, '')).replace(/\n/g, '<br>') + '</blockquote>';
                continue;
            }

            // List block (unordered or ordered), possibly nested. A hand-rolled
            // line parser has to track nesting explicitly: we keep a stack of
            // open lists keyed by leading-indent width, opening a new <ul>/<ol>
            // when indentation increases and closing lists back down when it
            // decreases. This is what makes multi-level answers render as real
            // nested lists (and activates the li>ul/li>ol CSS) instead of one
            // flat list. Tabs count as 4 columns.
            if (LIST_ITEM_RE.test(line)) {
                const stack = []; // each entry: { indent, type }
                let listHtml = '';
                while (i < lines.length) {
                    const um = lines[i].match(/^(\s*)[-*]\s+(.*)$/);
                    const om = lines[i].match(/^(\s*)(\d+)\.\s+(.*)$/);
                    if (!um && !om) {
                        // Not an item: a blank gap, a continuation paragraph of the
                        // open item, or the end of the list.
                        let j = i;
                        while (j < lines.length && lines[j].trim() === '') j++;
                        if (j >= lines.length || isBlockStart(lines[j])) break;
                        const gap = j > i;
                        const indent = indentWidth(lines[j]);
                        if (LIST_ITEM_RE.test(lines[j])) {
                            // Loose list: a blank line between two items.
                            i = j;
                            continue;
                        }
                        // After a blank line only indented text stays in the
                        // list; right after an item even unindented text is a
                        // lazy continuation (CommonMark does the same).
                        if (gap && indent <= stack[0].indent) break;
                        // Text at or left of a nested list's marker belongs to
                        // the item that owns that list.
                        while (stack.length > 1 && indent <= stack[stack.length - 1].indent) {
                            listHtml += '</li></' + stack.pop().type + '>';
                        }
                        i = j;
                        let para = lines[i].trim();
                        i++;
                        while (
                            i < lines.length &&
                            lines[i].trim() !== '' &&
                            !LIST_ITEM_RE.test(lines[i]) &&
                            !isBlockStart(lines[i])
                        ) {
                            para += '\n' + lines[i].trim();
                            i++;
                        }
                        listHtml += '<p>' + inlineMd(para).replace(/\n/g, '<br>') + '</p>';
                        continue;
                    }
                    const indent = (um || om)[1].replace(/\t/g, '    ').length;
                    const type = um ? 'ul' : 'ol';
                    const text = um ? um[2] : om[3];

                    // Close any lists deeper than — or at the same indent but a
                    // different type from — the current item.
                    while (
                        stack.length &&
                        (indent < stack[stack.length - 1].indent ||
                         (indent === stack[stack.length - 1].indent &&
                          type !== stack[stack.length - 1].type))
                    ) {
                        listHtml += '</li></' + stack.pop().type + '>';
                    }

                    if (!stack.length || indent > stack[stack.length - 1].indent) {
                        // Deeper than the current level (or the first item):
                        // open a nested list inside the still-open parent <li>.
                        // An ordered list keeps its first number, so one split
                        // by unindented text continues at 2 rather than 1.
                        const start = om ? parseInt(om[2], 10) : 1;
                        listHtml += '<' + type + (start !== 1 ? ' start="' + start + '"' : '') + '>';
                        stack.push({ indent: indent, type: type });
                    } else {
                        // Same level: close the previous sibling item first.
                        listHtml += '</li>';
                    }
                    listHtml += '<li>' + inlineMd(text);
                    i++;
                }
                while (stack.length) {
                    listHtml += '</li></' + stack.pop().type + '>';
                }
                html += listHtml;
                continue;
            }

            closeList();

            if (line.trim() === '') { i++; continue; }

            let para = line;
            i++;
            while (
                i < lines.length &&
                lines[i].trim() !== '' &&
                !/^```/.test(lines[i].trim()) &&
                !/^(-{3,}|\*{3,}|_{3,})$/.test(lines[i].trim()) &&
                !/^#{1,6}\s+/.test(lines[i].trim()) &&
                !/^\s*&gt;\s?/.test(lines[i]) &&
                lines[i].indexOf('|') === -1 &&
                !/^\s*[-*]\s+/.test(lines[i]) &&
                !/^\s*\d+\.\s+/.test(lines[i])
            ) {
                para += '\n' + lines[i];
                i++;
            }
            html += '<p>' + inlineMd(para).replace(/\n/g, '<br>') + '</p>';
        }

        closeList();
        return html;
    }

    // -------------------------------------------------------------------------
    // UI utilities
    // -------------------------------------------------------------------------

    function el(tag, cls) {
        const node = document.createElement(tag);
        if (cls) node.className = cls;
        return node;
    }

    // Element with text content in one step.
    function elText(tag, text, cls) {
        const node = el(tag, cls);
        node.textContent = text;
        return node;
    }

    // Parse a backend timestamp (ISO string, epoch seconds or milliseconds) into
    // a Date, or return null when absent/unparseable.
    function parseTimestamp(value) {
        if (value === undefined || value === null || value === '') return null;
        if (typeof value === 'number') {
            // Heuristic: values below ~1e12 are epoch seconds, not milliseconds.
            const d = new Date(value < 1e12 ? value * 1000 : value);
            return isNaN(d.getTime()) ? null : d;
        }
        const d = new Date(value);
        return isNaN(d.getTime()) ? null : d;
    }

    // Format a Date's time part using the site's WordPress time_format setting
    // (PHP date() tokens), so timestamps honour the admin's configured format.
    function formatTime(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) return '';
        return phpDateFormat(cfg.timeFormat || 'H:i', date);
    }

    // Locale-aware date part; formatTime() covers the time using WP's time_format.
    function formatDate(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) return '';
        const opts = { year: 'numeric', month: 'short', day: 'numeric' };
        const locale = (cfg.locale || '').replace('_', '-');
        try {
            return new Intl.DateTimeFormat(locale || undefined, opts).format(date);
        } catch (e) {
            return new Intl.DateTimeFormat(undefined, opts).format(date);
        }
    }

    function interpolate(template, vars) {
        return String(template).replace(/\{(\w+)\}/g, function (match, key) {
            return Object.prototype.hasOwnProperty.call(vars, key) ? vars[key] : match;
        });
    }

    // Precise HH:MM:SS.mmm, ignoring the site's time_format — used for the error
    // reference line, where seconds/ms are what actually distinguish two events
    // close together when matching against Sentry (unlike the minute-granularity
    // "am I looking at the right message" formatTime() above).
    function formatPreciseTime(date) {
        if (!(date instanceof Date) || isNaN(date.getTime())) return '';
        const pad = function (n, len) { return String(n).padStart(len || 2, '0'); };
        return pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) +
            '.' + pad(date.getMilliseconds(), 3);
    }

    // Minimal PHP date()-token formatter covering the time tokens WordPress uses
    // in its time_format option (a A g G h H i s), with backslash escaping.
    function phpDateFormat(fmt, d) {
        const H = d.getHours();
        const g = (H % 12) || 12;
        const min = d.getMinutes();
        const sec = d.getSeconds();
        const pad = function (n) { return n < 10 ? '0' + n : String(n); };

        let out = '';
        for (let k = 0; k < fmt.length; k++) {
            const ch = fmt.charAt(k);
            if (ch === '\\') { out += fmt.charAt(++k) || ''; continue; }
            switch (ch) {
                case 'a': out += H < 12 ? 'am' : 'pm'; break;
                case 'A': out += H < 12 ? 'AM' : 'PM'; break;
                case 'g': out += String(g); break;
                case 'G': out += String(H); break;
                case 'h': out += pad(g); break;
                case 'H': out += pad(H); break;
                case 'i': out += pad(min); break;
                case 's': out += pad(sec); break;
                default:  out += ch;
            }
        }
        return out;
    }

    function gvIcon(src, cls) {
        const icon = document.createElement('gv-icon');
        icon.setAttribute('src', src);
        icon.setAttribute('aria-hidden', 'true');
        if (cls) icon.className = cls;
        return icon;
    }

    function setStatus(state) {
        if (!indicatorDotEl || !statusTextEl) return;
        const stateMap = {
            connected:  { cls: 'gv-state-positive', label: i18n.connected || 'Connected' },
            connecting: { cls: 'gv-state-busy',     label: i18n.reconnecting || 'Connecting…' },
            error:      { cls: 'gv-state-critical', label: i18n.disconnected || 'Disconnected' },
            readOnly:   { cls: 'gv-state-blank',    label: i18n.readOnly || 'Read-only' },
        };
        const s = stateMap[state] || stateMap.connecting;
        indicatorDotEl.className = 'gv-indicator ' + s.cls;
        statusTextEl.textContent = s.label;
        if (statusWrapperEl) statusWrapperEl.hidden = state === 'connected';
    }

    function setSendDisabled(disabled) {
        sendBtn.disabled = disabled;
        inputEl.disabled = disabled;
    }

    // The conversational button doubles as Send and Stop (Gravity spec: fixed
    // icons arrow_forward / stop_fill). While a response streams it becomes a
    // Stop button; clicking it aborts the stream.
    function onSendBtnClick() {
        if (isStreaming) {
            trackButtonClick('stop');
            stopStream();
        } else {
            // Not tracked here — handleSend() fires 'send' once it has a message,
            // so an empty-composer click doesn't count.
            handleSend('button');
        }
    }

    // Toggle the conversational button between Send and Stop states.
    function setStreamingUI(streaming) {
        if (!sendBtn) return;
        // Stays clickable while streaming so the user can Stop. A pending
        // ask_user question deliberately does NOT lock it — Gravity keeps the
        // input live so the user can type an answer instead of picking a choice.
        const locked = !streaming && (newChatPending || !isConsentGranted() || isQuotaExhausted());
        sendBtn.disabled = locked;
        inputEl.disabled = locked;

        const iconEl = sendBtn.querySelector('gv-icon');
        if (iconEl) {
            iconEl.setAttribute('src', streaming ? ICON.stop : ICON.send);
        }
        const label = streaming ? (i18n.stop || 'Stop') : (i18n.send || 'Send');
        sendBtn.setAttribute('aria-label', label);
        sendBtn.title = label;
        sendBtn.classList.toggle('wap-stop', streaming);
    }

    // Abort the in-flight stream and immediately restore the Send state. No
    // further SSE events are processed once the fetch signal is aborted.
    function stopStream() {
        selectionGeneration++;
        handoffTransitionGeneration = null;
        if (currentAbortController) {
            try { currentAbortController.abort(); } catch (e) { /* ignore */ }
            currentAbortController = null;
        }
        isStreaming = false;
        setStreamingUI(false);
    }

    // Distance (px) from the bottom within which we consider the view "at bottom".
    const SCROLL_EPSILON = 48;

    // The element that actually scrolls depends on Gravity's chat CSS: it may be
    // the <ul> (.gv-chat-list) or the <section> (.gv-chat). Resolve whichever is
    // genuinely overflowing so the button and sticky logic act on the right one.
    function getScroller() {
        if (chatListEl && chatListEl.scrollHeight - chatListEl.clientHeight > 4) {
            return chatListEl;
        }
        if (chatEl && chatEl.scrollHeight - chatEl.clientHeight > 4) {
            return chatEl;
        }
        return chatListEl;
    }

    function isNearBottom() {
        const s = getScroller();
        if (!s) return true;
        return s.scrollHeight - s.scrollTop - s.clientHeight <= SCROLL_EPSILON;
    }

    // Sticky auto-scroll: only follow new content while the user is at the
    // bottom. `force` re-sticks (used when the user themselves sends a message).
    function scrollToBottom(force) {
        const s = getScroller();
        if (!s) return;
        if (force) stickToBottom = true;
        if (stickToBottom) {
            s.scrollTop = s.scrollHeight;
        }
        updateScrollBtn();
    }

    // Called on user scroll: update stickiness + the helper button.
    function updateScrollState() {
        stickToBottom = isNearBottom();
        updateScrollBtn();
    }

    // Reveal Gravity's scroll-to-bottom button only when the conversation is
    // scrollable and the user is NOT already at the bottom. Gravity's CSS shows
    // .gv-chat-scroll-bottom whenever .gv-show-scroll is present on .gv-chat.
    function updateScrollBtn() {
        if (!chatEl) return;
        const s = getScroller();
        const scrollable = s && (s.scrollHeight - s.clientHeight > SCROLL_EPSILON);
        // Never show the arrow on an empty conversation (welcome state) — only
        // once there are real messages and the user has scrolled up.
        chatEl.classList.toggle('gv-show-scroll', hasMessages && !!scrollable && !isNearBottom());
    }

    // Down-only: animate to the bottom and hide the button (per Gravity guidance).
    // Gravity's chat list has no CSS scroll-behavior; its demo scrolls smoothly
    // via JS on the button press, so we do the same here (an instant jump feels
    // off-brand).
    function onScrollBtnClick() {
        const s = getScroller();
        if (!s) return;
        stickToBottom = true;
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (typeof s.scrollTo === 'function' && !reduceMotion) {
            s.scrollTo({ top: s.scrollHeight, behavior: 'smooth' });
        } else {
            s.scrollTop = s.scrollHeight;
        }
        chatEl.classList.remove('gv-show-scroll');
    }

    // -------------------------------------------------------------------------
    // Expand toggle
    // -------------------------------------------------------------------------

    // Expand/collapse the widget per layout.expandToggle: 'window' = full-viewport
    // overlay, 'container' = fill the parent width in-flow. Icon/tooltip flip.
    function toggleFullscreen() {
        if (!fullscreenBtn) return;
        isFullscreen = !isFullscreen;
        // 'window' keeps the historical overlay; 'container' expands in-flow.
        const expandClass = layout.expandToggle === 'container' ? 'wap-expanded' : 'wap-fullscreen';
        rootEl.classList.toggle(expandClass, isFullscreen);

        const iconEl = fullscreenBtn.querySelector('gv-icon');
        if (iconEl) {
            iconEl.setAttribute('src', isFullscreen ? ICON.fullscreenExit : ICON.fullscreen);
        }
        const label = isFullscreen
            ? (i18n.exitFullscreen || 'Exit full screen')
            : (i18n.fullscreen || 'Full screen');
        fullscreenBtn.setAttribute('aria-label', label);
        const tip = document.getElementById(fullscreenBtn.getAttribute('aria-describedby'));
        const tipText = tip && tip.querySelector('p');
        if (tipText) tipText.textContent = label;

        // Layout changed — re-evaluate the scroll affordance.
        updateScrollBtn();
    }

    // -------------------------------------------------------------------------
    // Chat settings panel
    // -------------------------------------------------------------------------

    // In-widget settings page (opened from the header gear): it covers the chat
    // until the back arrow (or Escape) returns to it. Holds the Permissions
    // rows and the "Delete my data" action.
    function openSettings() {
        if (rootEl.querySelector('.wap-settings')) return;

        const overlay = el('div', 'wap-settings');

        const card = el('div', 'wap-settings__card');
        card.setAttribute('role', 'dialog');
        card.setAttribute('aria-modal', 'true');
        card.setAttribute('aria-label', i18n.settingsTitle || 'Settings');

        // Header row: back arrow + title, in the chat header's place.
        const head = el('div', 'wap-settings__head');
        const closeBtn = el('button', 'gv-tooltip-button wap-icon-btn wap-settings__close');
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', i18n.back || 'Back to chat');
        closeBtn.appendChild(gvIcon(ICON.back));
        head.appendChild(closeBtn);
        head.appendChild(elText('h2', i18n.settingsTitle || 'Settings', 'wap-settings__title'));
        overlay.appendChild(head);

        const body = el('div', 'wap-settings__body');

        // Inserted once values resolve, so a security control never flashes its default first.
        if (permissions.length) {
            readPermissionValues().then(function (values) {
                // Closed while loading? cleanup() detaches the overlay.
                if (!overlay.parentNode) return;
                body.insertBefore(buildPermissionsSection(values), body.firstChild);
            });
        }

        if (layout.showDeleteData) {
            body.appendChild(buildDeleteDataRow(function () { cleanup(); }));
        }

        if (cfg.version) {
            body.appendChild(elText('p', (i18n.versionLabel || 'Version') + ' ' + cfg.version, 'wap-settings__note'));
        }

        card.appendChild(body);
        overlay.appendChild(card);
        rootEl.appendChild(overlay);

        const prevFocus = document.activeElement;

        function cleanup() {
            document.removeEventListener('keydown', onKey, true);
            overlay.remove();
            if (prevFocus && prevFocus.focus) {
                try { prevFocus.focus(); } catch (e) { /* ignore */ }
            }
        }

        function onKey(e) {
            // The delete confirmation owns Escape while it is open.
            if (e.key === 'Escape' && !rootEl.querySelector('.gv-modal')) {
                e.preventDefault();
                e.stopPropagation();
                cleanup();
            }
        }

        closeBtn.addEventListener('click', cleanup);
        document.addEventListener('keydown', onKey, true);

        closeBtn.focus();
    }

    // "Delete my data" as a settings row; onConfirmed closes the page so the
    // outcome notice shows in the chat.
    function buildDeleteDataRow(onConfirmed) {
        const row = el('div', 'wap-settings__row wap-settings__row--action');
        const text = el('div', 'wap-settings__text');
        text.appendChild(elText('span', i18n.deleteData || 'Delete my data', 'wap-settings__label'));
        text.appendChild(elText(
            'p',
            i18n.deleteDataHint || 'Permanently delete your conversation history with the assistant.',
            'wap-settings__hint'
        ));
        row.appendChild(text);

        const btn = el('button', 'gv-button gv-button-secondary gv-mode-condensed wap-settings__delete');
        btn.type = 'button';
        btn.appendChild(elText('span', i18n.deleteDataAction || 'Delete'));
        btn.addEventListener('click', function () {
            trackButtonClick('delete_data');
            handleDeleteData(onConfirmed);
        });
        row.appendChild(btn);
        return row;
    }


    /**
     * Gravity modal confirmation dialog. Resolves true (confirmed) / false
     * (cancelled / dismissed). Replaces the native window.confirm().
     *
     * Built from gv-modal / gv-modal-content / gv-modal-title / gv-modal-body /
     * gv-modal-progress / gv-modal-close. The overlay is appended INSIDE the
     * gv-activated root so Gravity's `.gv-activated .gv-modal …` rules apply.
     *
     * @param {{title:string, message:string, confirmLabel:string, cancelLabel:string, destructive?:boolean}} opts
     * @returns {Promise<boolean>}
     */
    function showConfirm(opts) {
        return new Promise(function (resolve) {
            const overlay = el('div', 'gv-modal wap-confirm');

            const content = el('div', 'gv-modal-content');
            content.setAttribute('role', 'dialog');
            content.setAttribute('aria-modal', 'true');

            const closeBtn = el('button', 'gv-modal-close');
            closeBtn.type = 'button';
            closeBtn.setAttribute('aria-label', opts.cancelLabel || 'Close');
            closeBtn.appendChild(gvIcon(ICON_BASE + 'close.svg'));

            const body = el('div', 'gv-modal-body');
            const title = el('h2', 'gv-modal-title');
            title.id = 'wap-confirm-title';
            title.textContent = opts.title || 'Are you sure?';
            const msg = el('p', '');
            msg.id = 'wap-confirm-msg';
            msg.textContent = opts.message || '';
            body.appendChild(title);
            body.appendChild(msg);
            content.setAttribute('aria-labelledby', title.id);
            content.setAttribute('aria-describedby', msg.id);

            const actions = el('div', 'gv-button-group');
            const cancelBtn = el('button', 'gv-button gv-button-cancel');
            cancelBtn.type = 'button';
            cancelBtn.textContent = opts.cancelLabel || 'Cancel';
            const confirmBtn = el('button', 'gv-button ' + (opts.destructive ? 'gv-button-destructive' : 'gv-button-primary'));
            confirmBtn.type = 'button';
            confirmBtn.textContent = opts.confirmLabel || 'Confirm';
            actions.appendChild(cancelBtn);
            actions.appendChild(confirmBtn);

            content.appendChild(closeBtn);
            content.appendChild(body);
            content.appendChild(actions);
            overlay.appendChild(content);
            (rootEl || document.body).appendChild(overlay);

            const focusables = [confirmBtn, cancelBtn, closeBtn];
            const prevFocus = document.activeElement;

            function cleanup(result) {
                document.removeEventListener('keydown', onKey, true);
                overlay.remove();
                if (prevFocus && prevFocus.focus) {
                    try { prevFocus.focus(); } catch (e) { /* ignore */ }
                }
                resolve(result);
            }

            function onKey(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    cleanup(false);
                } else if (e.key === 'Tab') {
                    const first = focusables[0];
                    const last = focusables[focusables.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault(); last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault(); first.focus();
                    }
                }
            }

            cancelBtn.addEventListener('click', function () { cleanup(false); });
            closeBtn.addEventListener('click', function () { cleanup(false); });
            confirmBtn.addEventListener('click', function () { cleanup(true); });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) cleanup(false);
            });
            document.addEventListener('keydown', onKey, true);

            confirmBtn.focus();
        });
    }

}());
