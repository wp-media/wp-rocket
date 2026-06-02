---
name: orchestrator-team
description: >
  Team-execution variant of the orchestrator for the wp-rocket issue workflow. Drives the
  delivery pipeline as a real Agent Team — shared task list, direct teammate-to-teammate
  messaging, and real-time review — instead of sequentially spawned, isolated sub-agents.
  Use on Claude Code (desktop or CLI) when experimental Agent Teams are enabled. Requires
  CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS=1 and Claude Code v2.1.32+. Falls back to the
  `orchestrator` skill automatically when teams are unavailable (e.g. GitHub Copilot).
---

# Orchestrator — Team mode — wp-media/wp-rocket

You are the **team lead** of the wp-rocket agentic delivery pipeline. Unlike the base
`orchestrator` skill — which spawns isolated sub-agents that can only report back to you —
this skill runs the pipeline as a real Agent Team: teammates share a task list, **message
each other directly**, and refine each other's work in real time. You still own routing,
loop management, escalation, model selection, cleanup, and the HTML run log.

This skill exists because some phases of delivery are genuinely collaborative — grooming
and challenger sharpen a spec by arguing; a reviewer catches a defect faster by talking to
the implementer *while the code is being written* than by waiting for a PR. The base
orchestrator cannot express that. This one can.

---

## Relationship to the base `orchestrator` skill

**Everything about *what* to decide is shared. Only *how* agents are executed differs.**

The following are **identical** to `.aiassistant/skills/orchestrator/SKILL.md` — read that
file first, then return here. Do not duplicate or re-derive them:

- Inputs and `CURRENT_MODEL` handling
- `session_learnings` extraction from `AGENTS.md` section 13
- Escalation calibration (high-autonomy / standard / high-oversight)
- The **post-grooming routing table**, CHALLENGER trigger conditions, skip conditions
- All **loop counters** (`grooming_loop`, `review_loop`, `dod_loop`, `qa_loop`) and their limits
- DOD L2 / Lead Review / QA **routing tables** (PASS/WARN/FAIL actions, criticality tiers)
- The **adaptive model table** and complexity-signal assessment
- The **HTML run log** format (`html-log-format.md`) and the decisions strip

This skill documents only what is **different in team mode**: team setup, who is a teammate
vs a lead-spawned subagent, the coordination model, real-time review, phased membership for
cost control, and teardown.

---

## Phase 0 — Preflight (mandatory, before any team is created)

Teams are experimental and not universally available. Check before committing to this path.

```bash
claude --version            # need >= 2.1.32
echo "${CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS:-<unset>}"   # need "1"
```

Decision:
- Both satisfied → proceed with this skill.
- Either missing → **fall back to the base `orchestrator` skill** (sub-agent mode). Log a
  ROUTING DECISION event: "Team mode unavailable (reason: version | flag) — falling back to
  sub-agent orchestrator." Do not attempt to enable the flag mid-session; it is read at
  startup. Tell the user how to enable it for next time (`CLAUDE_CODE_EXPERIMENTAL_AGENT_TEAMS=1`
  in settings.json), then continue in sub-agent mode.

If the platform is GitHub Copilot or any environment without the Agent/team tools, fall back
unconditionally — teams do not exist there.

---

## Team topology — who is a teammate, who is not

Not every agent benefits from cross-talk. Reserve team membership (each teammate is a full
Claude instance — linear token cost) for agents whose work improves through peer discussion.
Keep mechanical, fire-and-forget agents as **lead-spawned plain sub-agents** (spawn via the
Agent tool **without** `team_name`).

| Agent | Role in team mode | Why |
|---|---|---|
| `grooming-agent` | **Teammate** (grooming cluster) | Argues the spec with the challenger in real time |
| `challenger` | **Teammate** (grooming cluster) | Adversarial peer to grooming |
| `backend-agent` | **Teammate** (impl cluster) | Reviewer challenges it mid-implementation |
| `frontend-agent` | **Teammate** (impl cluster) | Same; also reconciles API contract with backend by message |
| `lead-reviewer` | **Teammate** (impl cluster + gate) | **Real-time review** — flags defects while code is written, not after the PR |
| `qa-engineer` | **Teammate** (QA cluster) | Coordinates browser validation with e2e peer |
| `e2e-qa-tester` | **Teammate** (QA cluster) | Peer of qa-engineer; lead owns the spawn (no nested spawning) |
| `ticket-writer` | **Lead-spawned subagent** | Mechanical, no cross-talk, often non-blocking |
| `release-agent` | **Lead-spawned subagent** | Mechanical push/PR; nothing to discuss |

**Phased membership.** Do not run all seven teammates at once — it is expensive and the docs
recommend 3–5 active teammates. Spawn a cluster when its phase starts, shut it down when the
phase completes, then spawn the next. Active teammate count stays at 2–3 throughout.

---

## Coordination model

- **Shared task list** (`TaskCreate` / `TaskUpdate` / `TaskList`) is the source of truth for
  *what work exists and its status*. Set `addBlockedBy` to encode dependencies; the system
  unblocks dependents automatically on completion.
- **Mailbox** (`SendMessage`) is for *collaboration* — questions, critiques, contract
  handoffs, verdicts. Messages arrive automatically as new turns; never poll an inbox.
- **Durable artifacts** still go to `.TemporaryItems/Issues/wp-rocket/issue-<N>/`: the spec
  file, `contracts/backend-api.json`, and result JSONs. These survive teammate idle/shutdown
  and feed the HTML log. In team mode the API contract may *also* be exchanged by message for
  speed, but the file remains the authority if the two diverge.
- **Always refer to teammates by name** (`grooming`, `challenger`, `backend`, `frontend`,
  `reviewer`, `qa`, `e2e`). Assign predictable names at spawn time.

---

## Model selection in team mode (critical for cost)

**Teammates inherit the lead's model when their definition has no `model:` field.** If the
lead is on Opus, an un-pinned teammate spawns on Opus. This is the single biggest cost risk
in team mode.

Rules:
1. **Always pass `model` explicitly** on every teammate spawn (the Agent tool `model` param),
   resolved from the shared adaptive model table in the base orchestrator. Never rely on
   inheritance.
2. The agent frontmatter `model:` floors (sonnet for analysis/impl/QA, haiku for
   release/ticket) are a safety net only — the per-spawn value wins.
3. Apply the same Opus-escalation gate as the base orchestrator: only run implementation on
   Opus after explicit user confirmation when `complexity == HIGH`.

---

## Lifecycle

### Phase 1 — Intake + create the team

1. Resolve the issue (issue number / URL / raw input). For raw input, spawn `ticket-writer`
   as a **plain subagent** (no `team_name`) in `create` mode first.
2. Assess the complexity signal (base orchestrator's `assess_complexity`) for grooming's model.
3. Create the team:
   ```
   TeamCreate(team_name: "wp-rocket-issue-<N>", agent_type: "orchestrator",
              description: "Delivery of issue #<N>")
   ```
4. Create the initial HTML log. Log: "Team created — wp-rocket-issue-<N>. Calibration: <mode>."

### Phase 2 — Grooming cluster (grooming ⇄ challenger)

1. Create tasks: `groom-spec` (owner `grooming`) and `challenge-spec` (owner `challenger`,
   `addBlockedBy: [groom-spec]`).
2. Spawn `grooming` (subagent_type `grooming-agent`, `team_name`, resolved model, `complexity_signal`)
   and `challenger` (subagent_type `challenger`, `team_name`) as teammates.
3. Instruct grooming to write the spec, then message `challenger` directly. Challenger reviews
   and messages findings **back to grooming** (not just to you) so they can converge before
   escalating — exactly the refine loop validated in prototyping. Challenger reports its final
   verdict (APPROVED / NEEDS_REVISION / BLOCKED) to you.
4. Route on the verdict using the **base orchestrator's CHALLENGER routing table** and
   `grooming_loop` counter. In team mode the grooming↔challenger revision happens by message
   without re-spawning, which is cheaper — only escalate to the user at the loop limit.
5. When the spec is APPROVED (or routed), shut down the grooming cluster
   (`SendMessage` shutdown_request to `grooming` and `challenger`), then proceed.

Apply post-grooming routing (domains, branch prefix, scope, skip decisions) exactly as the
base orchestrator. Create the branch and `tasks.json` as usual.

### Phase 3 — Implementation + **real-time review**

This is the phase team mode most improves. The reviewer works *alongside* the implementers.

1. Create tasks: `impl-backend` (owner `backend`), `impl-frontend` (owner `frontend`),
   `live-review` (owner `reviewer`). Use worktrees for the implementers so disjoint scopes
   never collide:
   ```bash
   git worktree add .TemporaryItems/Issues/wp-rocket/issue-<N>/worktrees/backend <branch>
   git worktree add .TemporaryItems/Issues/wp-rocket/issue-<N>/worktrees/frontend <branch>
   ```
   Spawn each implementer with its `cwd` set to its worktree (single-domain issues spawn only
   the relevant one).
2. Spawn `backend` and `frontend` (teammates, resolved model, dispatch plan + file_scope) and
   `reviewer` (subagent_type `lead-reviewer`, teammate).
3. **Real-time review protocol:**
   - Implementers commit atomically in their worktree as usual (docs + e2e basic + DOD L1
     inline before commit — unchanged from the base agents).
   - As each implementer completes a meaningful unit, it messages `reviewer` with the changed
     files. The reviewer reads the diff and, if it finds a CRITICAL/HIGH/MEDIUM issue,
     **messages the implementer directly with the fix** — the implementer corrects before the
     PR exists. This replaces most post-PR review loop-backs.
   - The reviewer reports a running status to you; LOW findings become NTH follow-ups.
   - Backend writes `contracts/backend-api.json`; frontend reconciles against it (by message
     for speed, file as authority).
4. Proceed when `impl-backend` and `impl-frontend` are `completed` and the reviewer has no
   open CRITICAL/HIGH blockers. Keep the `review_loop` counter for any blocker the implementer
   and reviewer cannot resolve between themselves — escalate at the limit per the base table.
5. Shut down the implementation cluster **except keep `reviewer` alive** — it carries straight
   into the gate phase for the final pass (saves a re-spawn and preserves its context).

### Phase 4 — Release (lead-spawned subagent)

Spawn `release-agent` as a **plain subagent** (no `team_name`): verify trailers, push, open
the draft PR with the AI-generated notice. Unchanged from the base orchestrator. Update the
decisions strip with the PR URL.

### Phase 5 — Quality gates (QA cluster + DOD + final review)

1. **DOD L2** — invoke the `dod` skill with `layer: "2"` inline **in your own (lead) context**.
   It is a skill, not a teammate; it polls `gh pr checks` and returns blockers. Route on its
   result using the base DOD L2 table and `dod_loop`.
2. **QA cluster** — create tasks `qa-validate` (owner `qa`) and, when `domains` is `frontend`/
   `both` or `ui_visible: true`, `e2e-browser` (owner `e2e`, `addBlockedBy: [qa-validate]` or
   parallel as appropriate). Spawn `qa` (subagent_type `qa-engineer`, teammate) and `e2e`
   (subagent_type `e2e-qa-tester`, teammate). The lead owns the `e2e` spawn — `qa` cannot spawn
   it (no nested spawning, and qa-engineer has no Agent tool). `qa` and `e2e` coordinate
   directly by message: `qa` sends acceptance criteria + frontend files + PR number; `e2e`
   replies with per-criterion results and screenshot URLs.
3. **Final review** — the `reviewer` teammate (still alive from Phase 3) does the formal pass
   against the spec and posts the PR review comment. Because it already reviewed in real time,
   this pass is usually a confirmation rather than a fresh discovery.
4. Route QA and review results on the **base orchestrator's tables** and counters. A code FAIL
   loops back to the relevant implementer — re-spawn it as a teammate for the fix, or, if it
   is still alive, message it directly.

### Phase 6 — Finalize + teardown

1. Synthesize results into the HTML log and the PR "What was tested" section, as the base
   orchestrator does.
2. Mark the PR ready (`gh pr ready`) only after all gates pass and per calibration mode.
3. Dispatch any NTH follow-ups via `ticket-writer` (plain subagent).
4. **Teardown (mandatory):**
   - Shut down every remaining teammate: `SendMessage` with `{type: "shutdown_request"}` to
     each, wait for `shutdown_approved`.
   - `TeamDelete` — only after all teammates have exited (it fails if any are active).
   - Verify no orphaned worktrees: `git worktree prune` and remove the issue worktree dirs.
   Always tear down from the lead, never from a teammate.

---

## Membership & cost discipline

- Keep **2–3 teammates active** at a time via phased spawn/shutdown. Never hold all seven open.
- Pass `model` on every spawn; default un-pinned analysis/impl/QA work to Sonnet, never inherit
  Opus silently.
- Reserve Opus for implementation only after the explicit `complexity == HIGH` user gate.
- `ticket-writer` and `release-agent` are plain subagents — do not pay teammate cost for them.

---

## Limitations & known issues (experimental)

Carry these into your routing and surface them to the user when relevant:

- **No nested spawning.** Only you (the lead) may spawn teammates. This is why the qa→e2e
  delegation is flattened to the lead.
- **No `/resume` for in-process teammates.** A resumed session cannot restore teammates; if you
  resume, re-spawn rather than messaging ghosts.
- **Task status can lag.** Teammates sometimes fail to mark a task complete, blocking
  dependents. If a task looks stuck but the work is clearly done (check the artifact/message),
  set it complete yourself with `TaskUpdate` or nudge the teammate.
- **Shutdown can be slow.** Teammates finish their current tool call before exiting; wait for
  `shutdown_approved` before `TeamDelete`.
- **One team at a time; lead is fixed.** Finish and tear down before starting another issue.
- **`skills` / `mcpServers` frontmatter is not applied to teammates.** Our agents invoke skills
  by reading `.aiassistant/skills/.../SKILL.md` inline (not via the `skills:` field) and load
  MCP from project/user settings, so this does not affect us — but do not start relying on those
  frontmatter fields for teammate behavior.
- **`color:` frontmatter is ignored** by teams (colors are auto-assigned by join order).

---

## Cleanup checklist (run before declaring done)

- [ ] All teammates shut down (`shutdown_approved` received for each)
- [ ] `TeamDelete` succeeded
- [ ] Issue worktrees removed; `git worktree prune` clean
- [ ] No `.e2e-temp/` or `.e2e-screenshots/` left on the branch (e2e teammate's own teardown)
- [ ] HTML run log finalized; PR in correct state (draft → ready per calibration)
