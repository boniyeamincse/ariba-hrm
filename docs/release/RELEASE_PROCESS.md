# Release Process

## Release Cadence

- Weekly planned release window.
- Emergency hotfix release when required.

## Stages

1. Scope freeze and change log draft.
2. Final QA and regression checks.
3. Migration risk review.
4. Staged deployment.
5. Post-release validation.

## Required Gates

- CI green (backend and frontend).
- Security checks pass for changed modules.
- Rollback path documented.
- Documentation updated.

## Rollback Conditions

- Tenant boundary failure.
- High-severity clinical workflow regression.
- Payment flow outage.

## Post-Release Tasks

- Verify production dashboards.
- Publish release notes.
- Open follow-up tasks for known issues.
