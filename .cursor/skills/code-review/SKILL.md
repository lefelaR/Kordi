---
name: code-review
description: >-
  Review code for bugs, security issues, and other defects. Use when the user
  runs /code-review, asks for a code review, or asks to check code for
  potential issues.
disable-model-invocation: true
---

# Code Review

Review the code the user pointed at and report real issues. Do not edit files unless the user asks for fixes.

## What to review

1. If the user names files, a diff, a PR, or a branch, review that.
2. Otherwise review the current branch diff against the default base branch, including uncommitted changes: `git diff $(git merge-base HEAD origin/HEAD 2>/dev/null || git merge-base HEAD main 2>/dev/null || git merge-base HEAD master)..HEAD` plus `git diff` and `git diff --cached`.
3. If that diff is empty, review the files the user has open. If none are open, say so and stop.

Read the changed code and the callers, callees, and config it depends on. Do not review generated files, lockfiles, or vendored dependencies unless the change is inside them.

## What to look for

Report only issues you can ground in the code. Prefer defects over style.

- **Correctness:** wrong behavior, broken edge cases, bad assumptions, races, off-by-one errors, null or empty handling, resource leaks
- **Security:** injection, authz gaps, secret exposure, unsafe deserialization, path traversal, XSS, CSRF, missing output encoding
- **Reliability:** swallowed errors, partial failure, missing timeouts, unchecked external input, data loss
- **Contracts:** callers and callees that disagree on types, nullability, status codes, or side effects

Skip nits, naming preference, and speculative refactors. If you are unsure an issue is real, omit it.

## How to report

Lead with the highest-severity issue. If there are no issues, say that in one sentence and name what you reviewed.

Use this table, highest severity first:

| Severity | Location | Finding |
|----------|----------|---------|
| Critical | `path:line` | What is wrong, why it matters, and the concrete fix |
| High | `path:line` | ... |
| Medium | `path:line` | ... |

Severity:

- **Critical** — exploitable security hole, data loss, or a bug that breaks the main path
- **High** — likely bug or security weakness on a realistic path
- **Medium** — real defect with limited impact

After the table, add at most three bullets for residual risk or tests that would catch the findings. Do not restate the table.
