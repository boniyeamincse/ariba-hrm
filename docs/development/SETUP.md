# Development Setup

## Prerequisites

- Docker and Docker Compose
- Node.js LTS
- PHP 8.3+ and Composer
- Git

## Local Environment

1. Clone repository.
2. Copy backend env: `backend/.env.example` to `backend/.env`.
3. Copy frontend env template if used.
4. Start containers via project scripts.
5. Run migrations and seeders.

## Daily Workflow

1. Pull latest `main`.
2. Create feature branch.
3. Implement with tests.
4. Run lint and tests.
5. Open PR with checklist.

## Quality Gates Before PR

- Backend tests passing.
- Frontend type check and tests passing.
- No unresolved lint errors.
- Related docs updated.
