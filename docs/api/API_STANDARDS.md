# API Standards

## Design Principles

- RESTful resource naming.
- Tenant-safe by default.
- Consistent request validation and error payloads.
- Backward-compatible evolution where possible.

## Endpoint Conventions

- Use plural resources: `/patients`, `/appointments`.
- Use nested resources only when ownership is strict.
- Prefer filtering/query params for search/list operations.

## Response Contract

Success shape:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

Error shape:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

## Versioning

- Use `/api/v1` as baseline path for public versioning.
- Non-breaking additions can be released without version bump.
- Breaking changes require migration note and deprecation period.

## Security and Observability

- Every protected route requires auth middleware.
- Every privileged route requires permission check.
- High-risk endpoints must be audit logged.
