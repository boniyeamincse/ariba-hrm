# Sprint 1 Plan (10 Working Days)

## Sprint Goal
Establish a production-ready development foundation, begin core multi-tenant platform work, and deliver the first end-to-end flow skeleton:
patient registration -> OPD consult -> invoice (basic path).

Execution board: see docs/sprint-1-board.md

## Team Roles (Suggested Owners)
- Product Owner (PO)
- Tech Lead (TL)
- Backend Engineer (BE)
- Frontend Engineer (FE)
- DevOps Engineer (DO)
- QA Engineer (QA)

## Definition of Done (Sprint-Level)
- Each completed ticket has code, review, and tests where applicable
- CI passes for all merged changes
- API contracts documented for implemented endpoints
- Demo available for completed user-facing flows

## Ticket List With Estimates and Owners

| ID | Ticket | Estimate | Owner |
|---|---|---:|---|
| HMS-S1-001 | Create monorepo structure (backend/frontend/shared) | 3h | TL |
| HMS-S1-002 | Initialize Laravel 12 API with base module layout | 4h | BE |
| HMS-S1-003 | Initialize React 19 + TypeScript SPA shell | 4h | FE |
| HMS-S1-004 | Configure Docker Compose (app, mysql, redis) | 5h | DO |
| HMS-S1-005 | Configure env templates for local/staging/prod | 3h | DO |
| HMS-S1-006 | Set up CI pipeline (lint, unit test, build) | 5h | DO |
| HMS-S1-007 | Add coding standards and pre-commit hooks | 3h | TL |
| HMS-S1-008 | Implement subdomain tenant resolver middleware skeleton | 6h | BE |
| HMS-S1-009 | Implement tenant DB connection switching skeleton | 6h | BE |
| HMS-S1-010 | Implement auth endpoints (login/logout/reset skeleton) | 6h | BE |
| HMS-S1-011 | Build frontend auth pages and token/session handling | 6h | FE |
| HMS-S1-012 | Create patient registration schema and API endpoint (basic) | 6h | BE |
| HMS-S1-013 | Build patient registration UI form (basic) | 6h | FE |
| HMS-S1-014 | Create OPD consult entity + API endpoint (basic) | 5h | BE |
| HMS-S1-015 | Build OPD consult UI screen (basic) | 5h | FE |
| HMS-S1-016 | Create invoice entity + API endpoint (basic) | 5h | BE |
| HMS-S1-017 | Build invoice UI view for created consult | 5h | FE |
| HMS-S1-018 | Add integration test for vertical slice skeleton flow | 5h | QA |
| HMS-S1-019 | Add smoke tests and sprint demo checklist | 3h | QA |
| HMS-S1-020 | Sprint demo and retrospective | 2h | PO |

## Day-by-Day Plan

## Day 1
- HMS-S1-001 Monorepo structure
- HMS-S1-002 Laravel initialization
- HMS-S1-003 React initialization
- HMS-S1-007 Coding standards + hooks

## Day 2
- HMS-S1-004 Docker Compose setup
- HMS-S1-005 Environment templates
- HMS-S1-006 CI pipeline bootstrap

## Day 3
- HMS-S1-008 Tenant resolver middleware skeleton
- HMS-S1-009 Tenant DB switching skeleton (start)

## Day 4
- HMS-S1-009 Tenant DB switching skeleton (finish)
- HMS-S1-010 Auth endpoint skeleton
- HMS-S1-011 Frontend auth pages (start)

## Day 5
- HMS-S1-011 Frontend auth pages (finish)
- HMS-S1-012 Patient registration API (start)
- HMS-S1-013 Patient registration UI (start)

## Day 6
- HMS-S1-012 Patient registration API (finish)
- HMS-S1-013 Patient registration UI (finish)
- HMS-S1-014 OPD consult API (start)

## Day 7
- HMS-S1-014 OPD consult API (finish)
- HMS-S1-015 OPD consult UI

## Day 8
- HMS-S1-016 Invoice API
- HMS-S1-017 Invoice UI

## Day 9
- HMS-S1-018 Integration test for vertical slice
- HMS-S1-019 Smoke tests + demo checklist
- Bug fix window

## Day 10
- End-to-end sprint demo
- HMS-S1-020 Retrospective and Sprint 2 planning

## Risks and Mitigations
- Risk: Tenant DB switching complexity delays feature work
  - Mitigation: Time-box architecture decisions to Day 3/4; use skeleton implementation first
- Risk: API and UI contract drift
  - Mitigation: Freeze minimal request/response contracts before Day 5
- Risk: CI instability slows merges
  - Mitigation: Keep first CI version minimal and deterministic

## Sprint Exit Deliverables
- Running backend and frontend in Docker locally
- CI pipeline for lint, test, and build
- Tenant resolution and DB switching skeleton
- Auth skeleton end-to-end (API + UI)
- Basic vertical slice path operational:
  patient registration -> OPD consult -> invoice
