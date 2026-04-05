# Sprint 1 Execution Board

## Board Rules
- WIP limit: maximum 3 active implementation tickets at a time
- Blocked tickets must list blocker ID and owner
- A ticket moves to Done only after review + tests pass

## Priority Legend
- P0: Critical for sprint goal
- P1: High impact, can follow P0 items
- P2: Nice to have in this sprint

## Dependencies Legend
- "None" means independent
- Multiple dependencies mean all must be complete first

## To Do

| Ticket ID | Priority | Estimate | Owner | Depends On | Notes |
|---|---|---:|---|---|---|
| HMS-S1-001 | P0 | 3h | TL | None | Create monorepo structure |
| HMS-S1-002 | P0 | 4h | BE | HMS-S1-001 | Laravel 12 API scaffold |
| HMS-S1-003 | P0 | 4h | FE | HMS-S1-001 | React 19 + TS scaffold |
| HMS-S1-004 | P0 | 5h | DO | HMS-S1-002, HMS-S1-003 | Docker Compose services |
| HMS-S1-005 | P1 | 3h | DO | HMS-S1-004 | Env templates for local/staging/prod |
| HMS-S1-006 | P0 | 5h | DO | HMS-S1-002, HMS-S1-003 | CI lint/test/build pipeline |
| HMS-S1-007 | P1 | 3h | TL | HMS-S1-001 | Coding standards and hooks |
| HMS-S1-008 | P0 | 6h | BE | HMS-S1-002 | Tenant resolver middleware skeleton |
| HMS-S1-009 | P0 | 6h | BE | HMS-S1-008 | Tenant DB switching skeleton |
| HMS-S1-010 | P0 | 6h | BE | HMS-S1-002 | Auth endpoint skeleton |
| HMS-S1-011 | P0 | 6h | FE | HMS-S1-003, HMS-S1-010 | Auth UI and session handling |
| HMS-S1-012 | P0 | 6h | BE | HMS-S1-002 | Patient registration API |
| HMS-S1-013 | P0 | 6h | FE | HMS-S1-003, HMS-S1-012 | Patient registration UI |
| HMS-S1-014 | P0 | 5h | BE | HMS-S1-012 | OPD consult API |
| HMS-S1-015 | P0 | 5h | FE | HMS-S1-014 | OPD consult UI |
| HMS-S1-016 | P0 | 5h | BE | HMS-S1-014 | Invoice API |
| HMS-S1-017 | P0 | 5h | FE | HMS-S1-016 | Invoice UI |
| HMS-S1-018 | P0 | 5h | QA | HMS-S1-013, HMS-S1-015, HMS-S1-017 | Integration test for vertical slice |
| HMS-S1-019 | P1 | 3h | QA | HMS-S1-018 | Smoke tests and demo checklist |
| HMS-S1-020 | P1 | 2h | PO | HMS-S1-019 | Sprint demo and retrospective |

## In Progress

| Ticket ID | Priority | Estimate | Owner | Depends On | Blocker |
|---|---|---:|---|---|---|
| None | - | - | - | - | - |

## Review

| Ticket ID | Priority | Estimate | Owner | Depends On | Reviewer |
|---|---|---:|---|---|---|
| None | - | - | - | - | - |

## Done

| Ticket ID | Priority | Estimate | Owner | Depends On | Completed On |
|---|---|---:|---|---|---|
| None | - | - | - | - | - |

## Critical Path
1. HMS-S1-001 -> HMS-S1-002 -> HMS-S1-008 -> HMS-S1-009
2. HMS-S1-001 -> HMS-S1-003 -> HMS-S1-011
3. HMS-S1-012 -> HMS-S1-014 -> HMS-S1-016 -> HMS-S1-017 -> HMS-S1-018

## Daily Standup Update Format
- Yesterday: completed ticket IDs
- Today: active ticket IDs
- Blockers: ticket IDs and required help
- Risks: scope, dependency, or environment concerns
