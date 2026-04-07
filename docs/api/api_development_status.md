# API Development Status

Below is the API development status of the Hospital Management System (HMS) organized by module. This document tracks the implementation progress of controllers and routes. 

## ✅ Developed / Completed Modules

| Module Name | Status | Controllers |
| --- | --- | --- |
| **Authentication & Security** | ✅ Completed | `AuthController`, `V1\Auth\AuthController` |
| **RBAC (Role Based Access Control)** | ✅ Completed | `V1\Rbac\RbacController` |
| **System Settings** | ✅ Completed | `V1\Settings\SettingsController` |
| **Tenant & Admin Management** | ✅ Completed | `Admin\TenantController`, `UserManagementController` |
| **Patient Registration & Profiles** | ✅ Completed | `PatientController`, `PatientMedicalHistoryController`, `VisitController` |
| **OPD (Outpatient Department)** | ✅ Completed | `OpdController`, `OpdQueueController`, `VitalsController` |
| **Consultation & Assessment** | ✅ Completed | `ConsultationController`, `ReferralController` |
| **Prescription Management** | ✅ Completed | `PrescriptionController`, `PrescriptionItemController` |
| **Appointments & Scheduling** | ✅ Completed | `AppointmentController` |
| **IPD (Inpatient Department)** | ✅ Completed | `IpdController`, `DischargeController` |
| **Emergency Room (ER) / Triage** | ✅ Completed | `EmergencyController` |
| **Pharmacy Management** | ✅ Completed | `PharmacyController` |
| **Lab & Pathology** | ✅ Completed | `LabController`, `InvestigationOrderController` |
| **Billing & Accounting** | ✅ Completed | `BillingController` |
| **Inventory & Procurement** | ✅ Completed | `InventoryController` |
| **Human Resources (HR) & Payroll** | ✅ Completed | `HrController` |
| **Insurance / TPA** | ✅ Completed | `InsuranceController` |
| **Blood Bank Management** | ✅ Completed | `BloodBankController` |
| **Mortuary Services** | ✅ Completed | `MortuaryController` |
| **Dashboard & Reporting** | ✅ Completed | `RoleDashboardController`, `DashboardController`, `MenuController` |

---

## ⏳ Partial / In-Progress Modules

| Module Name | Status | Details |
| --- | --- | --- |
| **Ward & Bed Management** | ⏳ Partial | Currently managed broadly within `IpdController`. Needs dedicated sub-module for visual bed tracking. |
| **Telemedicine** | ⏳ Partial | Partially implemented within `AppointmentController` (Session creation context). |
| **Medical Documents / Certificates** | ⏳ Partial | Sick leaves are in `ConsultationController`. Death/birth certificates generally need wider scopes. |

---

## ❌ Pending / Not Developed Modules

The following conventional HMS modules are not yet identified in the backend:

| Module Name | Status | Description / Next Steps |
| --- | --- | --- |
| **Operation Theater (OT) Management** | ❌ Pending | OT scheduling, surgery tracking, anesthesia, and recovery room records. |
| **Radiology / Imaging (PACS)** | ❌ Pending | Dedicated imaging center orders (DICOM/PACS integration), scan results, and imaging reports. |
| **Ambulance Management** | ❌ Pending | Dispatching, GPS tracking, drivers roster, and trip maintenance. |
| **Diet & Nutrition (Canteen)** | ❌ Pending | Patient meal plans, dietitian consultations, and hospital canteen management. |
| **Maternity / Obstetrics** | ❌ Pending | Antenatal records, labor and delivery tracking, and newborn forms. |
| **Bio-Medical Waste Management** | ❌ Pending | Safely tracking the disposal of clinical wastes per regulations. |
| **Machine / Asset Maintenance** | ❌ Pending | Biomedical equipment scheduling, repairs, and calibration management. |
| **Patient Portal (CRM) / Feedback** | ❌ Pending | External-facing feedback tracking, patient inquiries, and CRM interactions. |
| **Laundry Services** | ❌ Pending | Linen stock, washing batches, and distribution tracking across wards. |

*Note: Update this status document continuously as development progresses.*
