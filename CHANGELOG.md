# ChangeLog

## 0.1.3 Under development

- Bug #23: Add `Super-Linter` badge to `README.md` for improved visibility on code quality checks (@terabytesoftw)

## 0.1.2 January 27, 2026

- Bug #14: Update badge styles and links in `README.md` for improved visibility and accuracy (@terabytesoftw)
- Bug #15: Remove redundant `Basic Usage` section from `README.md` (@terabytesoftw)
- Dep #16: Bump `php-forge/actions` from `1` to `2` (@dependabot)
- Dep #17: Update `symplify/easy-coding-standard` requirement from `^12.5` to `^13.0` (@dependabot)
- Enh #20: Add `php-forge/coding-standard` to development dependencies for code quality checks (@terabytesoftw)
- Bug #21: Remove outdated GitHub templates and code of conduct files; add linter configuration and feature SVGs (@terabytesoftw)
- Dep #22: Bump `php-forge/support` from `0.2` to `0.3` (@terabytesoftw)

## 0.1.1 August 17, 2025

- Bug #8: Add comprehensive PHPDoc for all classes (@terabytesoftw)
- Bug #9: Exclude `phpstan-console.neon` from the package in `.gitattributes` (@terabytesoftw)
- Enh #10: Add `FUNDING.yml` to support funding model platforms (@terabytesoftw)
- Bug #11: Update workflow actions to use `v1` stable version instead of `main`, update `LICENSE.md` (@terabytesoftw)
- Bug #12: Update `php-forge/support` version to `^0.2` in `composer.json`, refactor test method invocation in `WorkerDebugModuleTest.php` class (@terabytesoftw)

## 0.1.0 August 16, 2025

- Enh #1: Initial commit (@terabytesoftw)
- Enh #2: Introduce `WorkerDebugModule`, `WorkerProfilingPanel`, and `WorkerTimelinePanel` implementation with tests (@terabytesoftw)
- Bug #3: Refactor header retrieval in `WorkerDebugModule`, `WorkerProfilingPanel`, and `WorkerTimelinePanel` to use `Request` instead of `Response` and update tests accordingly (@terabytesoftw)
- Bug #4: Replace `TimeFunctions` with `MockerFunctions` in test cases and introduce `MockerFunctions` for mocking microtime behavior (@terabytesoftw)
- Bug #5: Update `README.md` and remove outdated documentation files for worker-debug extension (@terabytesoftw)
- Bug #6: Remove minimum stability and prefer stable settings from `composer.json` (@terabytesoftw)
- Bug #7: Update branch alias version in `composer.json` from `0.1.x-dev` to `0.2.x-dev` (@terabytesoftw)
