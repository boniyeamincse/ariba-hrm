# Contributing Guide

## Branching

- `main`: stable integration branch.
- `feature/<area>-<short-name>`: feature development.
- `hotfix/<issue>`: urgent production fix.

## Commit Style

- Use focused commits per concern.
- Prefer descriptive messages.
- Include docs update in same PR when behavior changes.

## Pull Request Checklist

- Scope is clear and testable.
- Acceptance criteria included.
- Screenshots attached for UI changes.
- Migration impact documented.
- Security impact reviewed.

## Code Review Expectations

- Confirm tenant-scope and permission checks.
- Confirm API response contract consistency.
- Confirm null/edge-case handling.
- Confirm test coverage for happy path and failure path.
