# Hospital Setup / Settings Module - API Development Plan v1.0

**System:** Multi-Tenant SaaS Hospital Management System (HMS)  
**Framework:** Laravel 12 + MySQL 8  
**Module Name:** Hospital Setup / Settings (V1)  
**Base Route:** `/api/v1/settings`  
**Status:** Complete Specification  
**Date:** April 2026

---

## A. Module Overview

The Hospital Setup / Settings module provides a comprehensive tenant-scoped configuration system for managing all hospital operational, clinical, and system preferences. It enables Tenant Admins to customize hospital identity, branding, notifications, billing, clinical workflows, integrations, and security policies without affecting other tenants in the SaaS platform.

**Architecture:**
- Tenant-isolated settings (each tenant has independent configuration)
- Section-based organization (general, branding, clinical, billing, etc.)
- Encrypted storage for sensitive fields (SMTP password, API keys)
- Full audit trail with user/IP/action tracking
- RESTful endpoints with granular RBAC permission control
- Support for 15 configurable sections + audit logging

**Key Principle:** Settings are tenant-scoped. Super Admin manages platform defaults; Tenant Admin manages only their tenant's settings.

---

## B. Main Features

1. **Multi-Section Settings Management**
   - 15 distinct configuration sections
   - Write once, read many (WORM) audit pattern for historical tracking
   - Section-based granular permissions
   - Real-time cache invalidation on updates

2. **Sensitive Data Protection**
   - AES-256 encryption for passwords, API keys, secrets
   - Masked response format (e.g., "smtp_password": "********")
   - Empty-value update safety (don't overwrite if empty payload)
   - Secure credential rotation support

3. **Audit & Compliance**
   - Complete audit log per setting update
   - Track: which user, when, what changed (old/new values)
   - IP address and User-Agent logging
   - Tenant boundary enforcement in audit queries

4. **Email & SMS Testing**
   - Test endpoints for email config validation
   - Test endpoints for SMS provider validation
   - Real-time provider health checks
   - Error reporting for troubleshooting

5. **Localization & Internationalization**
   - Multi-language support tracking
   - Timezone management
   - Date/time/number format customization
   - Currency configuration

6. **Clinical Workflow Defaults**
   - UHID, OPD, IPD prefix configuration
   - Appointment slot defaults
   - Prescription and lab order naming
   - Discharge/admission workflow settings

7. **Billing & Finance Defaults**
   - Invoice/receipt numbering schemes
   - Tax configuration
   - Auto-generate invoice numbers
   - Discount approval workflows

8. **Integration & API**
   - HL7, FHIR, webhook, third-party API enable/disable
   - Payment gateway configuration
   - PACS integration settings
   - Webhook URL management

9. **Security Policy Enforcement**
   - Password complexity requirements
   - Session timeout configuration
   - MFA and IP whitelist settings
   - Login attempt limits and lockout duration

10. **Document Templates**
    - Prescription template customization
    - Invoice/receipt templates
    - Discharge summary templates
    - Consent form templates
    - Lab report templates

---

## C. Development Tasks

### Phase 1: Foundation (Database & Models)
- [ ] Create 16 migration files (15 sections + audit log)
- [ ] Create 16 Eloquent models
- [ ] Add encryption keys to .env config
- [ ] Set up model accessors/mutators for sensitive fields

### Phase 2: API Layer (HTTP, Validation, Resources)
- [ ] Create 15 Form Request validation classes
- [ ] Create 15 API Resource classes
- [ ] Create SettingsController (modular or section-based)
- [ ] Implement request/response formatting

### Phase 3: Business Logic (Services & Repositories)
- [ ] Create SettingsRepository for CRUD operations
- [ ] Create SettingsService for business logic
- [ ] Implement secret masking logic
- [ ] Implement audit logging service

### Phase 4: Routes & Middleware
- [ ] Register 16 routes in api.php
- [ ] Create tenant middleware for route groups
- [ ] Create settings-specific permission middleware
- [ ] Add auth:sanctum, audit middleware

### Phase 5: Testing & Validation
- [ ] Create SettingsModuleTest (feature tests)
- [ ] Implement email test endpoint logic
- [ ] Implement SMS test endpoint logic
- [ ] Add comprehensive validation tests

### Phase 6: Documentation & Seeding
- [ ] Add team documentation
- [ ] Create SettingsSeeder for default values
- [ ] Add endpoint documentation to endpoint.md
- [ ] Create sample curl requests

---

## D. Database Design

### Settings Tables (15 section-specific tables)

Each table follows this base structure:

```sql
CREATE TABLE setting_{section} (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE KEY,
  {section-specific columns}
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 1. Setting Generals

```sql
CREATE TABLE setting_generals (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  hospital_name VARCHAR(255) NOT NULL,
  hospital_code VARCHAR(50) NOT NULL,
  registration_no VARCHAR(100),
  license_no VARCHAR(100),
  email VARCHAR(255),
  phone VARCHAR(20),
  emergency_phone VARCHAR(20),
  website VARCHAR(255),
  address_line_1 VARCHAR(255),
  address_line_2 VARCHAR(255),
  city VARCHAR(100),
  state VARCHAR(100),
  country VARCHAR(100),
  zip_code VARCHAR(20),
  timezone VARCHAR(100) DEFAULT 'UTC',
  currency VARCHAR(3) DEFAULT 'USD',
  language VARCHAR(10) DEFAULT 'en',
  date_format VARCHAR(20) DEFAULT 'YYYY-MM-DD',
  time_format VARCHAR(20) DEFAULT 'HH:mm:ss',
  logo_url TEXT,
  favicon_url TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 2. Setting Brandings

```sql
CREATE TABLE setting_brandings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  primary_color VARCHAR(7),
  secondary_color VARCHAR(7),
  theme_mode VARCHAR(20) DEFAULT 'light',
  login_page_title VARCHAR(255),
  footer_text TEXT,
  watermark_text VARCHAR(255),
  white_label_enabled BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 3. Setting Localizations

```sql
CREATE TABLE setting_localizations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  default_language VARCHAR(10) DEFAULT 'en',
  supported_languages JSON DEFAULT '["en"]',
  timezone VARCHAR(100) DEFAULT 'UTC',
  currency VARCHAR(3) DEFAULT 'USD',
  number_format VARCHAR(20) DEFAULT '1,000.00',
  date_format VARCHAR(20) DEFAULT 'YYYY-MM-DD',
  time_format VARCHAR(20) DEFAULT 'HH:mm:ss',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 4. Setting Notifications

```sql
CREATE TABLE setting_notifications (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  email_notifications_enabled BOOLEAN DEFAULT TRUE,
  sms_notifications_enabled BOOLEAN DEFAULT FALSE,
  push_notifications_enabled BOOLEAN DEFAULT FALSE,
  whatsapp_notifications_enabled BOOLEAN DEFAULT FALSE,
  appointment_reminder_enabled BOOLEAN DEFAULT TRUE,
  billing_alert_enabled BOOLEAN DEFAULT TRUE,
  lab_result_notification_enabled BOOLEAN DEFAULT TRUE,
  discharge_notification_enabled BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 5. Setting Email Configs

```sql
CREATE TABLE setting_email_configs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  mail_driver VARCHAR(50) DEFAULT 'smtp',
  smtp_host VARCHAR(255),
  smtp_port INT,
  smtp_user VARCHAR(255),
  smtp_password LONGTEXT, -- encrypted
  smtp_encryption VARCHAR(20) DEFAULT 'tls',
  from_email VARCHAR(255),
  from_name VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 6. Setting SMS Configs

```sql
CREATE TABLE setting_sms_configs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  provider_name VARCHAR(50),
  api_key LONGTEXT, -- encrypted
  api_secret LONGTEXT, -- encrypted
  sender_id VARCHAR(20),
  base_url VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 7. Setting Billings

```sql
CREATE TABLE setting_billings (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  invoice_prefix VARCHAR(20) DEFAULT 'INV',
  receipt_prefix VARCHAR(20) DEFAULT 'RCP',
  estimate_prefix VARCHAR(20) DEFAULT 'EST',
  refund_prefix VARCHAR(20) DEFAULT 'REF',
  tax_name VARCHAR(50) DEFAULT 'GST',
  tax_percentage DECIMAL(5,2) DEFAULT 18.00,
  invoice_footer TEXT,
  auto_generate_invoice_number BOOLEAN DEFAULT TRUE,
  allow_manual_discount BOOLEAN DEFAULT FALSE,
  require_discount_approval BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 8. Setting Clinicals

```sql
CREATE TABLE setting_clinicals (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  uhid_prefix VARCHAR(20) DEFAULT 'UHID',
  opd_prefix VARCHAR(20) DEFAULT 'OPD',
  ipd_prefix VARCHAR(20) DEFAULT 'IPD',
  prescription_prefix VARCHAR(20) DEFAULT 'RX',
  lab_order_prefix VARCHAR(20) DEFAULT 'LAB',
  radiology_order_prefix VARCHAR(20) DEFAULT 'RAD',
  enable_eprescription BOOLEAN DEFAULT FALSE,
  enable_clinical_notes_template BOOLEAN DEFAULT TRUE,
  enable_icd10 BOOLEAN DEFAULT TRUE,
  enable_followup_reminder BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 9. Setting Appointments

```sql
CREATE TABLE setting_appointments (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  default_slot_duration INT DEFAULT 30,
  max_patients_per_slot INT DEFAULT 3,
  allow_overbooking BOOLEAN DEFAULT FALSE,
  overbooking_limit INT DEFAULT 0,
  booking_lead_days INT DEFAULT 30,
  cancellation_window_hours INT DEFAULT 24,
  auto_confirm_appointments BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 10. Setting IPDs

```sql
CREATE TABLE setting_ipds (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  admission_prefix VARCHAR(20) DEFAULT 'ADM',
  discharge_prefix VARCHAR(20) DEFAULT 'DIS',
  bed_transfer_prefix VARCHAR(20) DEFAULT 'TRF',
  enable_bed_reservation BOOLEAN DEFAULT TRUE,
  allow_direct_admission BOOLEAN DEFAULT FALSE,
  require_guarantor_info BOOLEAN DEFAULT TRUE,
  enable_discharge_approval BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 11. Setting Pharmacies

```sql
CREATE TABLE setting_pharmacies (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  dispense_prefix VARCHAR(20) DEFAULT 'DISP',
  enable_batch_tracking BOOLEAN DEFAULT TRUE,
  enable_expiry_alert BOOLEAN DEFAULT TRUE,
  low_stock_threshold_mode VARCHAR(20) DEFAULT 'percentage',
  allow_partial_dispense BOOLEAN DEFAULT TRUE,
  enforce_fefo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 12. Setting Labs

```sql
CREATE TABLE setting_labs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  sample_prefix VARCHAR(20) DEFAULT 'SAM',
  report_prefix VARCHAR(20) DEFAULT 'REP',
  barcode_enabled BOOLEAN DEFAULT TRUE,
  qr_report_enabled BOOLEAN DEFAULT TRUE,
  critical_alert_enabled BOOLEAN DEFAULT TRUE,
  result_approval_required BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 13. Setting Integrations

```sql
CREATE TABLE setting_integrations (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  hl7_enabled BOOLEAN DEFAULT FALSE,
  fhir_enabled BOOLEAN DEFAULT FALSE,
  webhook_enabled BOOLEAN DEFAULT FALSE,
  api_access_enabled BOOLEAN DEFAULT TRUE,
  third_party_integration_enabled BOOLEAN DEFAULT FALSE,
  pacs_enabled BOOLEAN DEFAULT FALSE,
  payment_gateway_enabled BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 14. Setting Securities

```sql
CREATE TABLE setting_securities (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  password_min_length INT DEFAULT 8,
  password_require_uppercase BOOLEAN DEFAULT TRUE,
  password_require_lowercase BOOLEAN DEFAULT TRUE,
  password_require_number BOOLEAN DEFAULT TRUE,
  password_require_special_char BOOLEAN DEFAULT FALSE,
  password_expiry_days INT DEFAULT 90,
  login_attempt_limit INT DEFAULT 5,
  lockout_duration_minutes INT DEFAULT 30,
  mfa_enabled BOOLEAN DEFAULT FALSE,
  session_timeout_minutes INT DEFAULT 30,
  ip_whitelist JSON,
  trusted_devices_enabled BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 15. Setting Templates

```sql
CREATE TABLE setting_templates (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL UNIQUE,
  prescription_template LONGTEXT,
  invoice_template LONGTEXT,
  lab_report_template LONGTEXT,
  discharge_summary_template LONGTEXT,
  sick_leave_template LONGTEXT,
  consent_form_template LONGTEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
```

### 16. Setting Audit Logs

```sql
CREATE TABLE setting_audit_logs (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED,
  section VARCHAR(100) NOT NULL,
  action VARCHAR(50) DEFAULT 'update',
  old_values LONGTEXT,
  new_values LONGTEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_tenant_section (tenant_id, section),
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_created (created_at),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## E. API Endpoints

### General Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/general` | `settings.read` | Retrieve general hospital settings |
| PUT | `/v1/settings/general` | `settings.update` | Update general hospital settings |

### Branding Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/branding` | `settings.read` | Retrieve branding settings |
| PUT | `/v1/settings/branding` | `settings.branding.update` | Update branding settings |

### Localization Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/localization` | `settings.read` | Retrieve localization settings |
| PUT | `/v1/settings/localization` | `settings.update` | Update localization settings |

### Notification Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/notifications` | `settings.read` | Retrieve notification settings |
| PUT | `/v1/settings/notifications` | `settings.notification.update` | Update notification settings |

### Email Configuration

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/email-config` | `settings.read` | Retrieve email configuration |
| PUT | `/v1/settings/email-config` | `settings.update` | Update email configuration |
| POST | `/v1/settings/email-config/test` | `settings.update` | Test email configuration |

### SMS Configuration

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/sms-config` | `settings.read` | Retrieve SMS configuration |
| PUT | `/v1/settings/sms-config` | `settings.update` | Update SMS configuration |
| POST | `/v1/settings/sms-config/test` | `settings.update` | Test SMS configuration |

### Billing Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/billing` | `settings.read` | Retrieve billing settings |
| PUT | `/v1/settings/billing` | `settings.billing.update` | Update billing settings |

### Clinical Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/clinical` | `settings.read` | Retrieve clinical settings |
| PUT | `/v1/settings/clinical` | `settings.clinical.update` | Update clinical settings |

### Appointment Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/appointments` | `settings.read` | Retrieve appointment settings |
| PUT | `/v1/settings/appointments` | `settings.update` | Update appointment settings |

### IPD / Ward Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/ipd` | `settings.read` | Retrieve IPD settings |
| PUT | `/v1/settings/ipd` | `settings.update` | Update IPD settings |

### Pharmacy Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/pharmacy` | `settings.read` | Retrieve pharmacy settings |
| PUT | `/v1/settings/pharmacy` | `settings.update` | Update pharmacy settings |

### Lab Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/lab` | `settings.read` | Retrieve lab settings |
| PUT | `/v1/settings/lab` | `settings.update` | Update lab settings |

### Integration Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/integrations` | `settings.read` | Retrieve integration settings |
| PUT | `/v1/settings/integrations` | `settings.integration.update` | Update integration settings |

### Security Settings

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/security` | `settings.read` | Retrieve security settings |
| PUT | `/v1/settings/security` | `settings.security.update` | Update security settings |

### Document Templates

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/templates` | `settings.read` | Retrieve document templates |
| PUT | `/v1/settings/templates` | `settings.update` | Update document templates |

### Audit Logs

| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/v1/settings/audit-logs` | `settings.audit.read` | Retrieve settings audit logs |

---

## F. Validation Rules

### General Settings

```php
[
    'hospital_name' => 'required|string|max:255',
    'hospital_code' => 'required|string|max:50',
    'registration_no' => 'nullable|string|max:100',
    'license_no' => 'nullable|string|max:100',
    'email' => 'nullable|email',
    'phone' => 'nullable|string|max:20',
    'emergency_phone' => 'nullable|string|max:20',
    'website' => 'nullable|url',
    'address_line_1' => 'nullable|string|max:255',
    'address_line_2' => 'nullable|string|max:255',
    'city' => 'nullable|string|max:100',
    'state' => 'nullable|string|max:100',
    'country' => 'nullable|string|max:100',
    'zip_code' => 'nullable|string|max:20',
    'timezone' => 'required|timezone',
    'currency' => 'required|in:USD,EUR,INR,GBP,AUD',
    'language' => 'required|in:en,es,fr,de,hi',
    'date_format' => 'required|in:YYYY-MM-DD,DD-MM-YYYY,MM/DD/YYYY',
    'time_format' => 'required|in:HH:mm:ss,hh:mm:ss A',
    'logo_url' => 'nullable|url',
    'favicon_url' => 'nullable|url',
]
```

### Branding Settings

```php
[
    'primary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
    'secondary_color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
    'theme_mode' => 'required|in:light,dark,auto',
    'login_page_title' => 'nullable|string|max:255',
    'footer_text' => 'nullable|string',
    'watermark_text' => 'nullable|string|max:255',
    'white_label_enabled' => 'required|boolean',
]
```

### Localization Settings

```php
[
    'default_language' => 'required|in:en,es,fr,de,hi',
    'supported_languages' => 'required|array|min:1',
    'supported_languages.*' => 'string|in:en,es,fr,de,hi',
    'timezone' => 'required|timezone',
    'currency' => 'required|in:USD,EUR,INR,GBP',
    'number_format' => 'required|in:1,000.00,1.000,00,1000.00',
    'date_format' => 'required|in:YYYY-MM-DD,DD-MM-YYYY',
    'time_format' => 'required|in:HH:mm:ss,hh:mm:ss A',
]
```

### Notification Settings

```php
[
    'email_notifications_enabled' => 'required|boolean',
    'sms_notifications_enabled' => 'required|boolean',
    'push_notifications_enabled' => 'required|boolean',
    'whatsapp_notifications_enabled' => 'required|boolean',
    'appointment_reminder_enabled' => 'required|boolean',
    'billing_alert_enabled' => 'required|boolean',
    'lab_result_notification_enabled' => 'required|boolean',
    'discharge_notification_enabled' => 'required|boolean',
]
```

### Email Configuration

```php
[
    'mail_driver' => 'required|in:smtp,sendmail,mailgun',
    'smtp_host' => 'required_if:mail_driver,smtp|string',
    'smtp_port' => 'required_if:mail_driver,smtp|integer|min:1|max:65535',
    'smtp_user' => 'required_if:mail_driver,smtp|string',
    'smtp_password' => 'nullable|string', // Don't require if empty
    'smtp_encryption' => 'required|in:tls,ssl',
    'from_email' => 'required|email',
    'from_name' => 'required|string|max:255',
]
```

### SMS Configuration

```php
[
    'provider_name' => 'required|in:twilio,exotel,nexmo',
    'api_key' => 'required|string',
    'api_secret' => 'nullable|string', // Don't require if empty
    'sender_id' => 'required|string|max:20',
    'base_url' => 'required|url',
]
```

### Billing Settings

```php
[
    'invoice_prefix' => 'required|string|max:20',
    'receipt_prefix' => 'required|string|max:20',
    'estimate_prefix' => 'required|string|max:20',
    'refund_prefix' => 'required|string|max:20',
    'tax_name' => 'required|string|max:50',
    'tax_percentage' => 'required|numeric|min:0|max:100',
    'invoice_footer' => 'nullable|string',
    'auto_generate_invoice_number' => 'required|boolean',
    'allow_manual_discount' => 'required|boolean',
    'require_discount_approval' => 'required|boolean',
]
```

### Clinical Settings

```php
[
    'uhid_prefix' => 'required|string|max:20',
    'opd_prefix' => 'required|string|max:20',
    'ipd_prefix' => 'required|string|max:20',
    'prescription_prefix' => 'required|string|max:20',
    'lab_order_prefix' => 'required|string|max:20',
    'radiology_order_prefix' => 'required|string|max:20',
    'enable_eprescription' => 'required|boolean',
    'enable_clinical_notes_template' => 'required|boolean',
    'enable_icd10' => 'required|boolean',
    'enable_followup_reminder' => 'required|boolean',
]
```

### Appointment Settings

```php
[
    'default_slot_duration' => 'required|integer|min:5|max:480',
    'max_patients_per_slot' => 'required|integer|min:1|max:50',
    'allow_overbooking' => 'required|boolean',
    'overbooking_limit' => 'required_if:allow_overbooking,true|integer|min:0',
    'booking_lead_days' => 'required|integer|min:1|max:365',
    'cancellation_window_hours' => 'required|integer|min:0|max:168',
    'auto_confirm_appointments' => 'required|boolean',
]
```

### Security Settings

```php
[
    'password_min_length' => 'required|integer|min:6|max:20',
    'password_require_uppercase' => 'required|boolean',
    'password_require_lowercase' => 'required|boolean',
    'password_require_number' => 'required|boolean',
    'password_require_special_char' => 'required|boolean',
    'password_expiry_days' => 'required|integer|min:0|max:365',
    'login_attempt_limit' => 'required|integer|min:3|max:20',
    'lockout_duration_minutes' => 'required|integer|min:5|max:1440',
    'mfa_enabled' => 'required|boolean',
    'session_timeout_minutes' => 'required|integer|min:5|max:1440',
    'ip_whitelist' => 'nullable|array',
    'ip_whitelist.*' => 'ipv4|ipv6',
    'trusted_devices_enabled' => 'required|boolean',
]
```

---

## G. Permissions

### RBAC Permissions Matrix

```
settings.read                      # Read all settings sections
settings.update                    # Update general/clinical/billing/appointment/etc
settings.branding.update           # Update branding only
settings.notification.update       # Update notification settings
settings.billing.update            # Update billing settings
settings.clinical.update           # Update clinical settings
settings.integration.update        # Update integration settings
settings.security.update           # Update security settings (restricted)
settings.audit.read                # View audit logs
```

### Role Mapping

| Role | Permissions |
|------|-------------|
| super-admin | All settings permissions |
| tenant-admin | settings.read, settings.update, settings.branding.update, settings.notification.update, settings.billing.update, settings.clinical.update, settings.integration.update, settings.audit.read |
| hospital-admin | settings.read, settings.billing.update, settings.notification.update |
| doctor | settings.read |
| receptionist | settings.read |

### Middleware Stack

```php
['auth:sanctum', 'tenant', 'audit', 'permission:settings.read']
```

---

## H. Sample API Requests & Responses

### 1. Get General Settings

**Request:**
```bash
GET /api/v1/settings/general HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Settings retrieved successfully",
  "data": {
    "section": "general",
    "settings": {
      "id": 1,
      "tenant_id": 1,
      "hospital_name": "Apollo Hospitals",
      "hospital_code": "APL001",
      "registration_no": "REG2025001",
      "license_no": "LIC2025001",
      "email": "contact@apollo.com",
      "phone": "+91-80-1234-5678",
      "emergency_phone": "+91-80-1234-5679",
      "website": "https://apollo.com",
      "address_line_1": "123 Hospital Road",
      "address_line_2": "Suite 500",
      "city": "Bangalore",
      "state": "Karnataka",
      "country": "India",
      "zip_code": "560001",
      "timezone": "Asia/Kolkata",
      "currency": "INR",
      "language": "en",
      "date_format": "DD-MM-YYYY",
      "time_format": "HH:mm:ss",
      "logo_url": "https://cdn.medcore.com/apollo-logo.png",
      "favicon_url": "https://cdn.medcore.com/apollo-favicon.ico",
      "created_at": "2026-04-06T12:00:00Z",
      "updated_at": "2026-04-06T12:00:00Z"
    }
  }
}
```

### 2. Update Billing Settings

**Request:**
```bash
PUT /api/v1/settings/billing HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
Content-Type: application/json

{
  "invoice_prefix": "AP-INV",
  "receipt_prefix": "AP-RCP",
  "estimate_prefix": "AP-EST",
  "refund_prefix": "AP-REF",
  "tax_name": "GST",
  "tax_percentage": 18.00,
  "invoice_footer": "Thank you for choosing Apollo Hospitals",
  "auto_generate_invoice_number": true,
  "allow_manual_discount": false,
  "require_discount_approval": true
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Settings updated successfully",
  "data": {
    "section": "billing",
    "settings": {
      "id": 1,
      "tenant_id": 1,
      "invoice_prefix": "AP-INV",
      "receipt_prefix": "AP-RCP",
      "estimate_prefix": "AP-EST",
      "refund_prefix": "AP-REF",
      "tax_name": "GST",
      "tax_percentage": 18.00,
      "invoice_footer": "Thank you for choosing Apollo Hospitals",
      "auto_generate_invoice_number": true,
      "allow_manual_discount": false,
      "require_discount_approval": true,
      "created_at": "2026-04-06T12:00:00Z",
      "updated_at": "2026-04-06T13:00:00Z"
    }
  },
  "audit": {
    "action": "update",
    "section": "billing",
    "changes": {
      "old": { "tax_percentage": 15.00 },
      "new": { "tax_percentage": 18.00 }
    }
  }
}
```

### 3. Get Email Configuration (Masked)

**Request:**
```bash
GET /api/v1/settings/email-config HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Settings retrieved successfully",
  "data": {
    "section": "email-config",
    "settings": {
      "id": 1,
      "tenant_id": 1,
      "mail_driver": "smtp",
      "smtp_host": "smtp.gmail.com",
      "smtp_port": 587,
      "smtp_user": "noreply@apollo.com",
      "smtp_password": "********",
      "smtp_encryption": "tls",
      "from_email": "noreply@apollo.com",
      "from_name": "Apollo Hospitals",
      "created_at": "2026-04-06T12:00:00Z",
      "updated_at": "2026-04-06T12:00:00Z"
    }
  }
}
```

### 4. Test Email Configuration

**Request:**
```bash
POST /api/v1/settings/email-config/test HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_email": "admin@apollo.com"
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Email configuration test successful",
  "data": {
    "test_result": "passed",
    "recipient_email": "admin@apollo.com",
    "tested_at": "2026-04-06T13:05:00Z",
    "provider": "Gmail SMTP",
    "response_time_ms": 245
  }
}
```

**Response (422 Unprocessable Entity):**
```json
{
  "status": "error",
  "message": "Email configuration test failed",
  "data": {
    "test_result": "failed",
    "error": "Connection timeout: Unable to connect to SMTP server at smtp.gmail.com:587",
    "tested_at": "2026-04-06T13:05:00Z",
    "suggestions": [
      "Verify SMTP host and port are correct",
      "Check if firewall allows outbound SMTP connections",
      "Verify SMTP credentials are correct"
    ]
  }
}
```

### 5. Get Audit Logs

**Request:**
```bash
GET /api/v1/settings/audit-logs?section=billing&limit=10&page=1 HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Audit logs retrieved successfully",
  "data": [
    {
      "id": 234,
      "tenant_id": 1,
      "user_id": 5,
      "user_name": "Admin User",
      "section": "billing",
      "action": "update",
      "old_values": {
        "tax_percentage": 15.00,
        "require_discount_approval": false
      },
      "new_values": {
        "tax_percentage": 18.00,
        "require_discount_approval": true
      },
      "ip_address": "203.0.113.45",
      "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
      "created_at": "2026-04-06T13:00:00Z"
    },
    {
      "id": 233,
      "tenant_id": 1,
      "user_id": 5,
      "user_name": "Admin User",
      "section": "general",
      "action": "update",
      "old_values": {
        "website": "https://oldapollo.com"
      },
      "new_values": {
        "website": "https://apollo.com"
      },
      "ip_address": "203.0.113.45",
      "user_agent": "Mozilla/5.0",
      "created_at": "2026-04-06T12:30:00Z"
    }
  ],
  "pagination": {
    "total": 156,
    "per_page": 10,
    "current_page": 1,
    "last_page": 16
  }
}
```

### 6. Security Settings (Restricted)

**Request:**
```bash
PUT /api/v1/settings/security HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
Content-Type: application/json

{
  "password_min_length": 10,
  "password_require_uppercase": true,
  "password_require_lowercase": true,
  "password_require_number": true,
  "password_require_special_char": true,
  "password_expiry_days": 60,
  "login_attempt_limit": 3,
  "lockout_duration_minutes": 60,
  "mfa_enabled": true,
  "session_timeout_minutes": 30,
  "ip_whitelist": ["203.0.113.0/24", "198.51.100.0/24"],
  "trusted_devices_enabled": true
}
```

**Response (403 Forbidden):**
```json
{
  "status": "error",
  "message": "Insufficient privileges to update security settings",
  "errors": {
    "permission": "settings.security.update required"
  }
}
```

**Response (200 OK - With Permission):**
```json
{
  "status": "success",
  "message": "Security settings updated successfully",
  "data": {
    "section": "security",
    "settings": {
      "id": 1,
      "tenant_id": 1,
      "password_min_length": 10,
      "password_require_uppercase": true,
      "password_require_lowercase": true,
      "password_require_number": true,
      "password_require_special_char": true,
      "password_expiry_days": 60,
      "login_attempt_limit": 3,
      "lockout_duration_minutes": 60,
      "mfa_enabled": true,
      "session_timeout_minutes": 30,
      "ip_whitelist": ["203.0.113.0/24", "198.51.100.0/24"],
      "trusted_devices_enabled": true,
      "created_at": "2026-04-06T12:00:00Z",
      "updated_at": "2026-04-06T14:00:00Z"
    }
  }
}
```

### 7. Clinical Settings

**Request:**
```bash
PUT /api/v1/settings/clinical HTTP/1.1
Host: hospital.medcore.com
Authorization: Bearer {token}
Content-Type: application/json

{
  "uhid_prefix": "APL",
  "opd_prefix": "OPD",
  "ipd_prefix": "IPD",
  "prescription_prefix": "RX",
  "lab_order_prefix": "LAB",
  "radiology_order_prefix": "XRAY",
  "enable_eprescription": true,
  "enable_clinical_notes_template": true,
  "enable_icd10": true,
  "enable_followup_reminder": true
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Settings updated successfully",
  "data": {
    "section": "clinical",
    "settings": {
      "id": 1,
      "tenant_id": 1,
      "uhid_prefix": "APL",
      "opd_prefix": "OPD",
      "ipd_prefix": "IPD",
      "prescription_prefix": "RX",
      "lab_order_prefix": "LAB",
      "radiology_order_prefix": "XRAY",
      "enable_eprescription": true,
      "enable_clinical_notes_template": true,
      "enable_icd10": true,
      "enable_followup_reminder": true,
      "created_at": "2026-04-06T12:00:00Z",
      "updated_at": "2026-04-06T14:30:00Z"
    }
  }
}
```

---

## I. Suggested Implementation Order

### Week 1: Foundation
1. Create all 16 migration files
2. Create all 16 Eloquent models with mutators/accessors
3. Set up encryption configuration
4. Create SettingsRepository with CRUD methods

### Week 2: API Layer
5. Create 15 Form Request validation classes
6. Create 15 API Resource classes
7. Create SettingsController with modular endpoints
8. Implement secret masking logic in resources

### Week 3: Business Logic & Routing
9. Create SettingsService with business logic
10. Create audit logging service
11. Register all routes in api.php
12. Implement middleware stack

### Week 4: Features & Testing
13. Implement email test endpoint
14. Implement SMS test endpoint
15. Create SettingsModuleTest with comprehensive coverage
16. Add validation tests for all sections

### Week 5: Seeding & Documentation
17. Create SettingsSeeder for default values
18. Add endpoint documentation to endpoint.md
19. Create curl command examples
20. Performance testing and optimization

---

## J. Extra Production Notes

### 1. Encryption Strategy

Store sensitive data using Laravel's encryption:

```php
// In model accessor
protected function getSmtpPasswordAttribute($value)
{
    return $value ? Crypt::decrypt($value) : null;
}

// In model mutator
protected function setSmtpPasswordAttribute($value)
{
    $this->attributes['smtp_password'] = $value ? Crypt::encrypt($value) : null;
}
```

### 2. Masking in API Resources

```php
class EmailConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'mail_driver' => $this->mail_driver,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_user' => $this->smtp_user,
            'smtp_password' => $this->smtp_password ? '********' : null, // Masked
            'smtp_encryption' => $this->smtp_encryption,
            'from_email' => $this->from_email,
            'from_name' => $this->from_name,
        ];
    }
}
```

### 3. Empty Secret Update Safety

```php
public function updateEmailConfig(UpdateEmailConfigRequest $request)
{
    $data = $request->validated();
    
    // Don't update password if empty
    if (empty($data['smtp_password'])) {
        unset($data['smtp_password']);
    }
    
    $emailConfig->update($data);
    return new EmailConfigResource($emailConfig);
}
```

### 4. Tenant Boundary Enforcement

```php
public function getGeneralSettings(Request $request)
{
    $tenantId = $request->attributes->get('tenant_id');
    
    // Ensure settings belong to current tenant
    $settings = SettingGeneral::where('tenant_id', $tenantId)
        ->firstOrFail();
    
    return new GeneralSettingsResource($settings);
}
```

### 5. Audit Logging Pattern

```php
private function auditLog($section, $action, $oldValues, $newValues, $request)
{
    SettingAuditLog::create([
        'tenant_id' => $request->attributes->get('tenant_id'),
        'user_id' => auth()->id(),
        'section' => $section,
        'action' => $action,
        'old_values' => json_encode($oldValues),
        'new_values' => json_encode($newValues),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);
}
```

### 6. Caching Strategy

```php
public function getGeneralSettings(Request $request)
{
    $tenantId = $request->attributes->get('tenant_id');
    $cacheKey = "settings:general:{$tenantId}";
    
    return Cache::remember($cacheKey, 86400, function() use ($tenantId) {
        return SettingGeneral::where('tenant_id', $tenantId)->firstOrFail();
    });
}

// Clear cache on update
public function updateGeneralSettings(UpdateGeneralSettingsRequest $request)
{
    $tenantId = $request->attributes->get('tenant_id');
    Cache::forget("settings:general:{$tenantId}");
    
    // ... update logic
}
```

### 7. Transaction Safety for Critical Updates

```php
public function updateSecuritySettings(UpdateSecuritySettingsRequest $request)
{
    return DB::transaction(function() use ($request) {
        $settings = SettingSecurity::where('tenant_id', $request->tenant_id)
            ->lockForUpdate()
            ->firstOrFail();
        
        $oldValues = $settings->toArray();
        $settings->update($request->validated());
        
        $this->auditLog('security', 'update', $oldValues, $settings->toArray(), $request);
        
        Cache::forget("settings:security:{$request->tenant_id}");
        
        return new SecuritySettingsResource($settings);
    });
}
```

### 8. Email/SMS Testing

```php
public function testEmailConfig(TestEmailConfigRequest $request)
{
    try {
        Mail::raw('Test email from HMS', function($message) use ($request) {
            $message->to($request->recipient_email)
                    ->subject('HMS Settings Test');
        });
        
        return response()->json([
            'status' => 'success',
            'message' => 'Email configuration test successful',
            'test_result' => 'passed',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email configuration test failed',
            'error' => $e->getMessage(),
        ], 422);
    }
}
```

### 9. Seeder with Default Values

```php
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            SettingGeneral::firstOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'hospital_name' => $tenant->name,
                    'timezone' => 'UTC',
                    'currency' => 'USD',
                    'language' => 'en',
                ]
            );
            
            // Create other default settings...
        }
    }
}
```

### 10. Rate Limiting for Tests

```php
protected function setupRouteMiddleware()
{
    Route::middleware('throttle:10,1')->post('/settings/email-config/test', ...);
    Route::middleware('throttle:5,1')->post('/settings/sms-config/test', ...);
}
```

### 11. Validation in Requests

```php
class UpdateSecuritySettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password_min_length' => 'required|integer|min:6|max:20',
            'login_attempt_limit' => 'required|integer|min:3|max:20',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'ip_whitelist' => 'nullable|array',
            'ip_whitelist.*' => 'ipv4|ipv6',
        ];
    }
    
    public function messages(): array
    {
        return [
            'password_min_length.min' => 'Minimum password length must be at least 6',
            'login_attempt_limit.min' => 'Must allow at least 3 login attempts',
            'ip_whitelist.*.ipv4' => 'Each IP must be a valid IPv4 or IPv6 address',
        ];
    }
}
```

### 12. Error Handling

```php
public function handleSettingsError(\Exception $e, Request $request)
{
    return response()->json([
        'status' => 'error',
        'message' => 'Error updating settings',
        'errors' => [
            'general' => 'An unexpected error occurred',
        ],
    ], 422);
}
```

### 13. Performance Optimization

- Implement eager loading for relationships
- Use query caching for read-heavy settings
- Index settings tables by tenant_id and section
- Use pagination for audit logs
- Consider read replicas for audit log queries

### 14. Backwards Compatibility

- Support legacy endpoint paths if migrating from v0
- Deprecated endpoints should return 301 redirects or 410 Gone
- Maintain API versioning strategy

### 15. Documentation

- Add OpenAPI/Swagger documentation
- Include example curl requests in README
- Document encryption/decryption process
- Document seeding strategy
- Include troubleshooting guide for email/SMS testing

---

## Summary Statistics

| Metric | Count |
|--------|-------|
| Database Tables | 16 |
| API Endpoints | 19 |
| Form Requests | 15 |
| API Resources | 15 |
| Permissions | 9 |
| Roles Affected | 5 |
| Estimated Lines of Code | 3000+ |
| Development Time | 5 weeks |
| Test Coverage | 20+ feature tests |

---

**Next Steps:**
1. Review this plan with your team
2. Adjust timelines based on capacity
3. Start Week 1 with migration creation
4. Use this document as your development reference

**Questions? Reference sections:**
- Database details → Section D
- API endpoint specs → Section E
- Validation rules → Section F
- Sample requests → Section H
- Implementation order → Section I
- Production tips → Section J
