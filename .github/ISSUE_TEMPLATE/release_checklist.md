---
name: Release
about: Use this template when a new release is planned.
title: New Release
labels: ''
assignees: ''

---

## Release checklist
This card identifies all of the tasks that need to be completed for the major release. Each task owner is responsible to communicate in this card about status and check when done and ready.
These tasks need to be completed *before* we can proceed with the release:
- [ ]  Merge all PRs for this release.
- [ ]  Close all issue cards that will ship in this release.
- [ ]  Identify any non-merged items that are needed for this release (comment below).
    - [ ]  Get all of them completed, through QA, and merged.
- [ ]  Do final QA on the completed release branch (one last check to ensure everything works as expected when merged together). @wp-media/qa
    - [ ]  E2E is passing on smoke tests. Share results in #engineering-qa
- [ ]  Release any dependencies, such as Lazy Load Common library or other modules/libraries.
- [ ]  [Change the versions](https://www.notion.so/Release-a-new-version-of-WP-Rocket-c197d3ca7bad4765810c83f4f1a4fb6f?pvs=21) in the plugin
- [ ]  Create the PR from `develop` to `trunk`
    - [ ]  Get the changelog from Product team and add it to the PR
    - [ ]  [Release Alpha](https://www.notion.so/Release-a-new-version-of-WP-Rocket-c197d3ca7bad4765810c83f4f1a4fb6f?pvs=21) or [Release Final](https://www.notion.so/Release-a-new-version-of-WP-Rocket-c197d3ca7bad4765810c83f4f1a4fb6f?pvs=21)
    - [ ]  Make sure that github tag/release was created properly
    - [ ]  Check the github actions to make sure that deploy workflow finished without a problem
- [ ]  [Change the version](https://www.notion.so/Release-a-new-version-of-WP-Rocket-c197d3ca7bad4765810c83f4f1a4fb6f?pvs=21) on wp-rocket.me
- [ ]  Ping the whole team in `#wp-rocket-dev` channel about the release so product team can decide what are the next steps
