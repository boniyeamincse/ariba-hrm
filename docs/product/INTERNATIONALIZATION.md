# Internationalization and Localization

## Goal

Enable MedCore HMS to operate across countries with local language, timezone, date/time, and regulatory requirements.

## Language Strategy

- Externalize all user-facing strings.
- Maintain locale bundles per language.
- Start with English baseline; add regional packs incrementally.

## Locale Requirements

- Date format per locale.
- 12h/24h time preference support.
- Number and currency formatting per region.
- Address format variations.

## Clinical and Legal Localization

- Support country-specific identifiers.
- Support region-specific insurance fields.
- Add configurable legal text for forms and consent.

## UX Requirements

- No hard-coded text in components.
- Support dynamic text expansion in UI layout.
- Ensure right-to-left readiness for future locales.

## Operational Requirements

- Per-tenant timezone settings.
- Local holiday calendar compatibility for appointment scheduling.
- Region-aware notification templates.
