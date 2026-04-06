# Test Strategy

## Quality Objective

Protect patient safety workflows, tenant isolation, and financial correctness through layered automated and manual testing.

## Test Layers

- Unit tests: domain services, helpers, permission gates.
- Feature/API tests: auth, patient, OPD/IPD, lab, pharmacy, billing.
- Frontend tests: component behavior, route guards, form validation.
- End-to-end smoke tests: critical workflows from login to billing.

## Minimum Coverage Focus

- P0 clinical workflows.
- Payment and invoice calculations.
- Role-based access and forbidden routes.
- Tenant boundary checks.

## Non-Functional Testing

- Load test for queue and billing hotspots.
- Security tests for OWASP high-risk categories.
- Resilience tests for queue/job retry behavior.
