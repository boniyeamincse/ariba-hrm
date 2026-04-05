# HMS Implementation Task List

## Phase 0 - Project Setup
- [x] Create monorepo structure for backend and frontend
- [x] Initialize Laravel 12 API project with tenancy-ready middleware structure
- [x] Initialize React 19 + TypeScript SPA project
- [x] Configure MySQL 8, Redis, and Docker Compose for local development
- [x] Configure environment files for local, staging, and production
- [x] Set up CI workflow for lint, test, and build checks
- [x] Define coding standards and pre-commit hooks

## Phase 1 - Core Platform (P0)
- [x] Build tenant onboarding flow (create tenant, subdomain, schema provisioning)
- [x] Implement tenant resolution middleware by subdomain
- [x] Implement per-tenant database connection switching
- [x] Build authentication (login, logout, password reset, optional 2FA)
- [x] Implement role-based access control with permission matrix
- [x] Add audit logging for create, update, delete operations
- [x] Build super admin panel shell for tenant management

## Phase 2 - Clinical Foundation (P0)
- [ ] Build patient registration and UHID generation
- [ ] Build patient demographics, history, and visit timeline views
- [ ] Implement OPD queue and consultation workflow
- [ ] Implement e-prescription flow with printable output
- [ ] Implement lab and radiology order creation from consultation
- [ ] Implement IPD admission and bed allocation
- [ ] Implement ward rounds, nursing notes, and medication administration record
- [ ] Implement emergency triage registration and priority handling

## Phase 3 - Operations and Revenue (P0/P1)
- [ ] Build pharmacy drug master and batch inventory
- [ ] Implement prescription dispensing and counter sales
- [ ] Build laboratory test catalog and sample collection tracking
- [ ] Implement result entry, validation, and report generation
- [ ] Build billing charge master and auto-charge aggregation
- [ ] Implement invoice generation, payment capture, and discount approvals
- [ ] Add discharge process with billing clearance integration

## Phase 4 - Advanced Modules (P1/P2)
- [ ] Build appointment scheduling with slot management
- [ ] Add telemedicine appointment support
- [ ] Build insurance and TPA claim workflows
- [ ] Implement inventory and procurement module
- [ ] Implement HR and payroll module
- [ ] Add blood bank workflows
- [ ] Add mortuary management workflows

## Phase 5 - Security, Quality, and Compliance
- [ ] Enforce API rate limits, validation, and authorization policies
- [ ] Add encryption strategy for sensitive medical data fields
- [ ] Add immutable audit trails for clinical and billing events
- [ ] Implement backup and disaster recovery procedures
- [ ] Create OWASP-focused security test checklist
- [ ] Validate HIPAA-aligned access and logging practices

## Phase 6 - Reporting and Analytics
- [ ] Build operational dashboards for patient flow and occupancy
- [ ] Build financial dashboards for revenue and collections
- [ ] Build pharmacy, lab, and inventory analytics reports
- [ ] Add export options (CSV, PDF) for key reports
- [ ] Add role-based report access controls

## Phase 7 - Deployment and Launch
- [ ] Provision staging and production infrastructure on AWS
- [ ] Configure app deployment pipelines and rollback strategy
- [ ] Configure monitoring, alerts, and centralized logging
- [ ] Run UAT with sample hospital workflows
- [ ] Finalize launch checklist and go-live sign-off

## Immediate Next Sprint (Suggested)
- [ ] Complete Phase 0 setup tasks
- [ ] Start Phase 1 tenant onboarding and authentication
- [ ] Deliver first vertical slice: register patient -> OPD consult -> invoice

Reference: See detailed execution plan in docs/sprint-1-plan.md.
