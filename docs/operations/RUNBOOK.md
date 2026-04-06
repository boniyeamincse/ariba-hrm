# Operations Runbook

## Scope

Operational procedures for uptime, incidents, and recovery for MedCore HMS deployments.

## Daily Checks

- API health and latency.
- Queue backlog and failed jobs.
- Database utilization and slow queries.
- Storage growth and backup completion.

## Incident Response

1. Detect and classify severity.
2. Assign incident owner.
3. Contain impact (feature flag, route throttle, rollback).
4. Communicate status to stakeholders.
5. Resolve and publish post-incident summary.

## Backup and Recovery

- Verify daily backup completion.
- Run restore drill periodically.
- Validate tenant-level restoration procedure.

## Release Day Checklist

- Migration risk reviewed.
- Rollback strategy prepared.
- Monitoring dashboards open.
- On-call engineer assigned.
