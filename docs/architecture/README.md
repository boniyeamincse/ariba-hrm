# Architecture Guide

## Scope

Defines the technical boundaries for the MedCore HMS platform as an international multi-tenant SaaS.

## Architecture Summary

- Backend: Laravel modular monolith with domain modules.
- Frontend: React SPA with role-based routing and guarded pages.
- Data: tenant-aware persistence with strict isolation.
- Search: Meilisearch for fast lookup workloads.
- Realtime: queue, alert, and board updates via WebSocket.

## Cross-Cutting Concerns

- Tenant isolation and boundary checks.
- Authentication and permission enforcement.
- Audit logging for all sensitive operations.
- Localization and regional compliance support.
- Disaster recovery and operational resilience.

## Source of Truth

- Product architecture baseline: `../blueprint.md`
- Dashboard role architecture: `../dashboard/ARCHITECTURE.md`
