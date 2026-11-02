---
name: test
description: >-
  Test code prepared for commit for syntax bugs, deprecations, outdated
  practices, and repeated code. Use when the user runs /test, asks to test
  the commit, or asks to check changes before committing.
disable-model-invocation: true
---

# Test the commit candidate

Test the code prepared for the next commit. Do not stage, commit, push, or edit files.

## What to test

1. If the user names files, test those files.
2. Otherwise run the syntax script from the repository root:

```bash
bash .cursor/skills/test/scripts/check-syntax.sh
```

The script lists staged files, unstaged files, and untracked files that are not ignored. It skips `vendor/`, lockfiles, and generated files.

3. If the output is `# empty`, say there is no code prepared for commit and stop.
4. Read the diff of every listed file (`git diff --cached --` and `git diff --`). Read untracked files in full. Review only the changed lines, plus the definition they call when a finding depends on it.

The site requires PHP 8.3 (`composer.json`, `php:8.3-apache`). The API is Laravel 13 on PHP 8.3 with PHPUnit 12. Judge deprecations against those versions.

## Potential bugs caused by syntax

Report each `fail` line from the script with the checker output and the change that fixes it. A skipped checker because `php` or `node` is missing is a blocker for files of that type.

Also report a syntax bug the checker cannot see when the changed text is still invalid: a Blade or Twig tag that does not close, a SQL string whose quotes do not match, or a PHP file that is not a `.php` file. Do not report style.

## Deprecations

Report a call, signature, or option that PHP 8.3 or, under `api/`, Laravel 13 or PHPUnit 12 already deprecates or has removed. Name the replacement.

A deprecation that starts in PHP 8.4 is a note, not a failure. This project runs PHP 8.3.

## Outdated or not based on best practices

Report a changed line that still runs but is the wrong practice for this codebase:

- A new class under `app/` or a new branch in `index.php` that is not required and constructed in `index.php`.
- SQL, mail, session use, or a redirect inside a view. Views render variables they are given.
- HTML output that is not passed through `htmlspecialchars`, or SQL built by concatenating input.
- A removed-era API that still has a current equivalent (`each`, `create_function`, `utf8_encode`, `mysql_*`, `var` properties, a PHP 4 constructor).

Before calling an extraction a required redesign, read [solid-principles](../solid-principles/SKILL.md). Do not flag quote style, `array()` versus `[]`, or whitespace.

## Repeated code

Report repeated code when the same logic appears twice in the commit candidate, or the change copies logic that already exists. Search with `rg` for a distinctive line from the copy before reporting it.

For each repeat, say where both copies are and how to improve it: name the existing function to call, or the smallest extraction. Do not add a class, interface, or base type that would have only one caller.

## How to report

Lead with blockers. If a section found nothing, say so in one sentence and name what you checked.

Use one table, in this order: syntax, deprecations, best practices, repeated code.

| Result | Location | Finding |
|--------|----------|---------|
| Blocker | `path:line` | What is wrong, why it fails on PHP 8.3 or Laravel 13, and the concrete fix |
| Note | `path:line` | A later-version deprecation, or repeated code with the specific improvement |

- **Blocker** — syntax failure, missing checker, or a deprecation or practice that warns or breaks on PHP 8.3 or Laravel 13.
- **Note** — repeated code, or a practice that still runs correctly.

After the table, add at most three bullets. Use them only for a repeated-code improvement that needs more than one line, or for a checker that did not run. Do not restate the table.
