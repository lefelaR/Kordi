---
name: commit-message
description: >-
  Write a commit message for the work on the current branch. Use when the
  user runs /-m, asks for a message for git commit -m, or asks for a commit
  message for this branch.
disable-model-invocation: true
---

# Commit message

Print one commit message for the work on the current branch. Do not stage, commit, or push.

## What to summarize

1. Find the base: `git merge-base HEAD origin/HEAD`, then `main`, then `master`. If none exist, there is no branch base.
2. Read the branch work, not the latest commit alone:
   - `git log --oneline --no-merges <base>..HEAD`
   - `git diff <base>...HEAD`
   - `git diff` and `git diff --cached`
3. Read recent style from this repo: `git log -20 --format='%s' --no-merges`.
4. If the branch diff and the working tree are both empty, say there is no work to summarize and stop.

Ignore merge commits, lockfiles, and generated files unless they are the change.

## How to write it

Match the recent non-merge subjects in this repo:

- One or two sentences. No subject/body split, no bullet list, no conventional-commit prefix.
- Start with a verb: Add, Enhance, Implement, Fix, Refactor, or Update.
- Name the user-visible result, then why it was needed.
- Cover the whole branch, including uncommitted work. Do not list files.

```
Add address suggestions on the event location field so a typed place resolves to a real address.
```

Print only the message in a fenced block, ready to pass to `git commit -m`.
