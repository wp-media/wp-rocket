# Secret scan

Every pull request runs a [Gitleaks](https://github.com/gitleaks/gitleaks) scan (`Secret scan` workflow, job `gitleaks`). A PR that adds a credential fails the check, and the log never shows the value (`--redact`).

Credentials belong in the environment or in GitHub secrets, never in the code. Dummy values for tests must look like placeholders (see `tests/TestCredentials.php`).

## What is scanned

Only the commits of the PR (`origin/<base>..HEAD`). Secrets already in the base branch or its history never fail the check, so legacy findings are handled in their own issues, not here.

The scan reads the lines each commit adds:
- a PR that only deletes a secret passes;
- a secret added then removed within the same PR still fails, because every commit is scanned, merge commits included (what their conflict resolution adds). Rewrite the commit that added it (rebase or amend) so it never appears in the PR. Then rotate it anyway: it was pushed.

The check is meant to be a required status check on `develop`.

## Run it locally

With Docker, from the repo root:

```bash
# Commits of your branch not yet in develop
docker run --rm -v "$PWD:/repo" ghcr.io/gitleaks/gitleaks:v8.30.1 \
  git /repo --config /repo/.gitleaks.toml --redact -v --log-opts="--diff-merges=remerge origin/develop..HEAD"

# Staged changes (run `git add` first: untracked files are not scanned)
docker run --rm -v "$PWD:/repo" ghcr.io/gitleaks/gitleaks:v8.30.1 \
  git /repo --config /repo/.gitleaks.toml --redact -v --pre-commit --staged
```

Or with the binary (`brew install gitleaks`): `gitleaks git . --config .gitleaks.toml --redact -v --log-opts="--diff-merges=remerge origin/develop..HEAD"`.

## False positives

First make sure it really is one. If it is, pick the narrowest fix:

1. **One line in our code**: add `// gitleaks:allow` (or `# gitleaks:allow`) at the end of the line, with a short reason.
2. **A pattern of values or a file**: add an entry to `.gitleaks.toml`, scoped to a rule (`targetRules`) and to a value pattern (`regexes`) or a file (`paths`). If an entry uses both `regexes` and `paths`, set `condition = "AND"`: Gitleaks ORs them by default, so either one alone would allowlist a finding. Write `regexes` against the secret itself (`regexTarget = "secret"`), which is the captured token, not the whole line. Test the entry in `git` mode: in `dir` mode paths start with `/repo/` and anchored `paths` never match.

Never allowlist a whole directory for every rule: that turns the check off for everything below it.

To check a test value is caught, use one that looks real (the right prefix, length and characters): Gitleaks ignores obvious placeholders. Do not commit it.
