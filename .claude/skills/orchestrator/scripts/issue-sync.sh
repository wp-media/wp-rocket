#!/usr/bin/env bash
# Sync a GitHub issue into a local Markdown snapshot for review.
# Usage: issue-sync.sh <issue-number>
# Optional: set WPROCKET_SYNC_RELATED=0 to skip syncing parent/sub-issues.
set -euo pipefail

die() {
  echo "issue-sync: $*" >&2
  exit 1
}

require_command() {
  local cmd="$1"
  if ! command -v "$cmd" >/dev/null 2>&1; then
    die "Missing required command: ${cmd}."
  fi
}

# Required argument: issue number.
ISSUE_NUMBER="${1:?issue number required}"

# Canonical repo for WP Rocket.
REPO="wp-media/wp-rocket"
OWNER="${REPO%%/*}"
REPO_NAME="${REPO#*/}"

# Resolve repository root (works regardless of the current working directory).
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR=""
if command -v git >/dev/null 2>&1; then
  ROOT_DIR="$(git -C "$SCRIPT_DIR" rev-parse --show-toplevel 2>/dev/null || true)"
fi
if [ -z "$ROOT_DIR" ]; then
  ROOT_DIR="$(cd "$SCRIPT_DIR/../../../../" && pwd)"
fi
if [ ! -d "$ROOT_DIR" ]; then
  die "Unable to resolve repository root from ${SCRIPT_DIR}."
fi

# Output location for the issue snapshot.
OUT_DIR="${ROOT_DIR}/.TemporaryItems/Issues/wp-rocket/issues"
OUT_FILE="${OUT_DIR}/${ISSUE_NUMBER}.md"

# Related issue sync controls.
SYNC_RELATED="${WPROCKET_SYNC_RELATED:-1}"
SEEN_ISSUES="${WPROCKET_SYNC_SEEN:-}"

is_number() {
  [[ "${1:-}" =~ ^[0-9]+$ ]]
}

has_seen_issue() {
  local num="$1"
  [ -z "$SEEN_ISSUES" ] && return 1
  case ",${SEEN_ISSUES}," in
    *,"${num}",*) return 0 ;;
  esac
  return 1
}

mark_seen_issue() {
  local num="$1"
  if has_seen_issue "$num"; then
    return 0
  fi
  if [ -z "$SEEN_ISSUES" ]; then
    SEEN_ISSUES="$num"
  else
    SEEN_ISSUES="${SEEN_ISSUES},${num}"
  fi
  export WPROCKET_SYNC_SEEN="$SEEN_ISSUES"
}

mark_seen_issue "$ISSUE_NUMBER"

require_command gh
require_command jq
if ! gh auth status -h github.com >/dev/null 2>&1; then
  die "GitHub CLI is not authenticated. Run \"gh auth login\"."
fi

# Extract issue numbers from task list items in a Markdown body.
extract_task_issue_numbers() {
  local body="$1"
  local repo="$2"
  local owner="${repo%%/*}"
  local name="${repo#*/}"

  awk -v owner="$owner" -v name="$name" -v repo="$repo" '
    BEGIN { IGNORECASE = 1 }
    function emit(num) {
      if (num ~ /^[0-9]+$/ && !seen[num]++) {
        print num
      }
    }
    /^[[:space:]]*[-*][[:space:]]+\[[ xX]\][[:space:]]+/ {
      line = $0
      sub(/^[[:space:]]*[-*][[:space:]]+\[[ xX]\][[:space:]]+/, "", line)
      while (match(line, /(https?:\/\/github\.com\/[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+\/issues\/[0-9]+)|([A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+#[0-9]+)|(#[0-9]+)/)) {
        ref = substr(line, RSTART, RLENGTH)
        if (ref ~ /^#/) {
          emit(substr(ref, 2))
        } else if (ref ~ /github\.com/) {
          n = split(ref, parts, "/")
          o = parts[4]
          r = parts[5]
          num = parts[7]
          if (tolower(o) == tolower(owner) && tolower(r) == tolower(name)) {
            emit(num)
          }
        } else if (ref ~ /#/) {
          split(ref, pr, "#")
          if (tolower(pr[1]) == tolower(repo)) {
            emit(pr[2])
          }
        }
        line = substr(line, RSTART + RLENGTH)
      }
    }
  ' <<< "$body"
}

# Ensure the output directory exists.
mkdir -p "$OUT_DIR"

# Fetch issue data and render a structured Markdown file.
if ! ISSUE_JSON="$(gh issue view "$ISSUE_NUMBER" \
  --repo "$REPO" \
  --json number,title,body,comments,state,labels,assignees,url 2> >(cat >&2))"; then
  die "Failed to fetch issue #${ISSUE_NUMBER} from ${REPO}."
fi

echo "$ISSUE_JSON" | jq -r --arg repo "$REPO" '
"# Issue #\(.number): \(.title)

Repo: \($repo)
State: \(.state)
URL: \(.url)

## Labels
\(
  if (.labels | length) > 0
  then (.labels | map(.name) | join(", "))
  else "None"
  end
)

## Assignees
\(
  if (.assignees | length) > 0
  then (.assignees | map(.login) | join(", "))
  else "None"
  end
)

## Description

\(.body // "")

## Comments

\(
  if (.comments | length) > 0
  then (
    .comments
    | map("### \(.author.login) — \(.createdAt)\n\n\(.body // "")")
    | join("\n\n")
  )
  else "No comments."
  end
)

## AI Notes

-
"' > "$OUT_FILE"

ISSUE_BODY="$(echo "$ISSUE_JSON" | jq -r '.body // ""')"
EPIC_LABEL_PRESENT="$(echo "$ISSUE_JSON" | jq -r '
  [.labels[].name? | ascii_downcase | startswith("epics")] | any | if . then "yes" else "no" end
')"

SUB_ISSUE_NUMBERS=()
while IFS= read -r sub_issue; do
  [ -n "$sub_issue" ] && SUB_ISSUE_NUMBERS+=("$sub_issue")
done < <(extract_task_issue_numbers "$ISSUE_BODY" "$REPO")

PARENT_SEARCH_QUERY="\"#${ISSUE_NUMBER}\" OR \"${REPO}#${ISSUE_NUMBER}\" OR \"issues/${ISSUE_NUMBER}\""
PARENT_SEARCH_JSON="[]"
if ! PARENT_SEARCH_JSON="$(gh issue list \
  --repo "$REPO" \
  --search "$PARENT_SEARCH_QUERY" \
  --limit 100 \
  --json number,title,body,url)"; then
  PARENT_SEARCH_JSON="[]"
fi

PARENT_EPIC_LINES=()
PARENT_EPIC_NUMBERS=()
while IFS= read -r issue; do
  number="$(echo "$issue" | jq -r '.number')"
  [ "$number" -eq "$ISSUE_NUMBER" ] && continue
  body="$(echo "$issue" | jq -r '.body // ""')"
  if extract_task_issue_numbers "$body" "$REPO" | grep -qx "$ISSUE_NUMBER"; then
    title="$(echo "$issue" | jq -r '.title')"
    url="$(echo "$issue" | jq -r '.url')"
    PARENT_EPIC_LINES+=("* #${number}: ${title} (${url})")
    PARENT_EPIC_NUMBERS+=("$number")
  fi
done < <(echo "$PARENT_SEARCH_JSON" | jq -c '.[]')

ISSUE_TYPE_NAME="unknown"
ISSUE_TYPE_EPIC="unknown"
SUB_ISSUES_GH_COUNT=0
SUB_ISSUES_GH_LINES=()
SUB_ISSUE_NUMBERS_GH=()
PARENT_EPIC_GH_LINE=""
PARENT_EPIC_GH_NUMBER=""
RELATIONSHIP_QUERY_STATUS="unknown"
RELATIONSHIP_JSON=""
if RELATIONSHIP_JSON="$(gh api graphql -f query='
  query ($owner: String!, $repo: String!, $number: Int!) {
    repository(owner: $owner, name: $repo) {
      issue(number: $number) {
        issueType { name }
        parent { __typename ... on Issue { number title url } }
        subIssues(first: 50) { totalCount nodes { number title url } }
      }
    }
  }
' -F owner="$OWNER" -F repo="$REPO_NAME" -F number="$ISSUE_NUMBER" 2>/dev/null)"; then
  RELATIONSHIP_QUERY_STATUS="ok"
  ISSUE_TYPE_NAME="$(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.issueType.name // "unknown"')"
  if [ "$ISSUE_TYPE_NAME" != "unknown" ] && [ -n "$ISSUE_TYPE_NAME" ]; then
    if echo "$ISSUE_TYPE_NAME" | tr '[:upper:]' '[:lower:]' | grep -qx "epic"; then
      ISSUE_TYPE_EPIC="yes"
    else
      ISSUE_TYPE_EPIC="no"
    fi
  fi
  SUB_ISSUES_GH_COUNT="$(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.subIssues.totalCount // 0')"
  while IFS= read -r line; do
    [ -n "$line" ] && SUB_ISSUES_GH_LINES+=("$line")
  done < <(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.subIssues.nodes[]? | "* #\(.number): \(.title) (\(.url))"')
  while IFS= read -r sub_issue; do
    [ -n "$sub_issue" ] && SUB_ISSUE_NUMBERS_GH+=("$sub_issue")
  done < <(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.subIssues.nodes[]?.number // empty')
  PARENT_EPIC_GH_LINE="$(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.parent | select(. != null and .__typename == "Issue") | "* #\(.number): \(.title) (\(.url))"')"
  PARENT_EPIC_GH_NUMBER="$(echo "$RELATIONSHIP_JSON" | jq -r '.data.repository.issue.parent | select(. != null and .__typename == "Issue") | .number // empty')"
else
  RELATIONSHIP_QUERY_STATUS="failed"
fi

PROJECT_TYPE_EPIC="unknown"
PROJECT_TYPE_LINES=()
PROJECT_ITEM_COUNT=0
PROJECT_QUERY_STATUS="unknown"
PROJECT_ITEMS_JSON=""
if PROJECT_ITEMS_JSON="$(gh api graphql -f query='
  query ($owner: String!, $repo: String!, $number: Int!) {
    repository(owner: $owner, name: $repo) {
      issue(number: $number) {
        projectItems(first: 20) {
          nodes {
            project { number title }
            fieldValues(first: 50) {
              nodes {
                __typename
                ... on ProjectV2ItemFieldSingleSelectValue {
                  field { ... on ProjectV2SingleSelectField { name } }
                  name
                }
                ... on ProjectV2ItemFieldTextValue {
                  field { ... on ProjectV2FieldCommon { name } }
                  text
                }
              }
            }
          }
        }
      }
    }
  }
' -F owner="$OWNER" -F repo="$REPO_NAME" -F number="$ISSUE_NUMBER" 2>/dev/null)"; then
  PROJECT_QUERY_STATUS="ok"
  PROJECT_ITEM_COUNT="$(echo "$PROJECT_ITEMS_JSON" | jq -r '.data.repository.issue.projectItems.nodes | length')"
  while IFS= read -r line; do
    [ -n "$line" ] && PROJECT_TYPE_LINES+=("$line")
  done < <(echo "$PROJECT_ITEMS_JSON" | jq -r '
    def type_value:
      (.fieldValues.nodes
        | map(select((.__typename=="ProjectV2ItemFieldSingleSelectValue" or .__typename=="ProjectV2ItemFieldTextValue") and (.field.name=="Type")))
        | .[0]
        | if .==null then "" else (if .__typename=="ProjectV2ItemFieldSingleSelectValue" then .name else .text end) end
      );
    .data.repository.issue.projectItems.nodes[]
    | {title: .project.title, number: .project.number, type: type_value}
    | select(.type != "")
    | "* Project \"" + .title + "\" (#" + (.number|tostring) + "): Type=" + .type
  ')
  if [ "$PROJECT_ITEM_COUNT" -gt 0 ]; then
    if echo "$PROJECT_ITEMS_JSON" | jq -e '
      def type_value:
        (.fieldValues.nodes
          | map(select((.__typename=="ProjectV2ItemFieldSingleSelectValue" or .__typename=="ProjectV2ItemFieldTextValue") and (.field.name=="Type")))
          | .[0]
          | if .==null then "" else (if .__typename=="ProjectV2ItemFieldSingleSelectValue" then .name else .text end) end
        );
      .data.repository.issue.projectItems.nodes
      | map(type_value | ascii_downcase)
      | any(. == "epic")
    ' >/dev/null; then
      PROJECT_TYPE_EPIC="yes"
    else
      TYPE_VALUE_COUNT="$(echo "$PROJECT_ITEMS_JSON" | jq -r '
        def type_value:
          (.fieldValues.nodes
            | map(select((.__typename=="ProjectV2ItemFieldSingleSelectValue" or .__typename=="ProjectV2ItemFieldTextValue") and (.field.name=="Type")))
            | .[0]
            | if .==null then "" else (if .__typename=="ProjectV2ItemFieldSingleSelectValue" then .name else .text end) end
          );
        .data.repository.issue.projectItems.nodes
        | map(type_value)
        | map(select(. != ""))
        | length
      ')"
      if [ "$TYPE_VALUE_COUNT" -gt 0 ]; then
        PROJECT_TYPE_EPIC="no"
      fi
    fi
  fi
else
  PROJECT_QUERY_STATUS="failed"
fi

{
  echo ""
  echo "## Epic Signals"
  echo "Label \"epics\": ${EPIC_LABEL_PRESENT}"
  echo "Issue Type: ${ISSUE_TYPE_NAME}"
  echo "Issue Type \"EPIC\": ${ISSUE_TYPE_EPIC}"
  echo "Type \"EPIC\" (Project field \"Type\"): ${PROJECT_TYPE_EPIC}"
  echo "Project field query: ${PROJECT_QUERY_STATUS}"
  echo "Project items: ${PROJECT_ITEM_COUNT}"
  echo "Sub-issues detected (GitHub): ${SUB_ISSUES_GH_COUNT}"
  echo "Sub-issues detected (Task List): ${#SUB_ISSUE_NUMBERS[@]}"
  if [ -n "$PARENT_EPIC_GH_LINE" ]; then
    echo "Parent epic detected (GitHub): yes"
  else
    echo "Parent epic detected (GitHub): no"
  fi
  echo "Parent epics detected (Task List): ${#PARENT_EPIC_LINES[@]}"
  echo ""
  echo "## Project Type (Field \"Type\")"
  if [ "${#PROJECT_TYPE_LINES[@]}" -gt 0 ]; then
    printf '%s\n' "${PROJECT_TYPE_LINES[@]}"
  else
    echo "None detected."
  fi
  echo ""
  echo "## Sub-issues (GitHub)"
  if [ "${#SUB_ISSUES_GH_LINES[@]}" -gt 0 ]; then
    printf '%s\n' "${SUB_ISSUES_GH_LINES[@]}"
  else
    echo "None detected."
  fi
  echo ""
  echo "## Parent Epic (GitHub)"
  if [ -n "$PARENT_EPIC_GH_LINE" ]; then
    echo "$PARENT_EPIC_GH_LINE"
  else
    echo "None detected."
  fi
  echo ""
  echo "## Sub-issues (Task List)"
  if [ "${#SUB_ISSUE_NUMBERS[@]}" -gt 0 ]; then
    for sub_issue in "${SUB_ISSUE_NUMBERS[@]}"; do
      if issue_line="$(gh issue view "$sub_issue" \
        --repo "$REPO" \
        --json number,title,url \
        --jq '"* #\(.number): \(.title) (\(.url))"')"; then
        echo "$issue_line"
      else
        echo "* #${sub_issue}"
      fi
    done
  else
    echo "None detected."
  fi

  echo ""
  echo "## Parent Epics (Task List)"
  if [ "${#PARENT_EPIC_LINES[@]}" -gt 0 ]; then
    printf '%s\n' "${PARENT_EPIC_LINES[@]}"
  else
    echo "None detected."
  fi
} >> "$OUT_FILE"

RELATED_ISSUE_NUMBERS=()
add_related_issue() {
  local num="$1"
  if ! is_number "$num"; then
    return 0
  fi
  if [ "$num" = "$ISSUE_NUMBER" ]; then
    return 0
  fi
  for existing in "${RELATED_ISSUE_NUMBERS[@]:-}"; do
    if [ "$existing" = "$num" ]; then
      return 0
    fi
  done
  RELATED_ISSUE_NUMBERS+=("$num")
}

for num in "${SUB_ISSUE_NUMBERS[@]:-}"; do
  add_related_issue "$num"
done
for num in "${SUB_ISSUE_NUMBERS_GH[@]:-}"; do
  add_related_issue "$num"
done
for num in "${PARENT_EPIC_NUMBERS[@]:-}"; do
  add_related_issue "$num"
done
if [ -n "$PARENT_EPIC_GH_NUMBER" ]; then
  add_related_issue "$PARENT_EPIC_GH_NUMBER"
fi

if [ "$SYNC_RELATED" = "1" ] && [ "${#RELATED_ISSUE_NUMBERS[@]}" -gt 0 ]; then
  for related in "${RELATED_ISSUE_NUMBERS[@]}"; do
    if has_seen_issue "$related"; then
      continue
    fi
    mark_seen_issue "$related"
    if ! WPROCKET_SYNC_RELATED=0 WPROCKET_SYNC_SEEN="$SEEN_ISSUES" "$0" "$related" >/dev/null; then
      echo "Warning: failed to sync issue #${related}" >&2
    fi
  done
fi

# Print the path for downstream tooling.
echo "$OUT_FILE"
