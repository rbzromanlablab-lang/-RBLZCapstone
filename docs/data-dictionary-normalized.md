# PARDS Data Dictionary

This document provides the normalized data dictionary for the Property Accountability Records and Disposal System (PARDS), aligned with the ERD-style database structure.

## Notes

- This dictionary uses the actual Laravel table and column names currently implemented in the project.
- In the ERD, primary keys may appear as `user_id`, `property_id`, `assignment_id`, and similar labels. In the Laravel schema, these are implemented as the default `id` primary key for each table.
- Some tables still keep legacy compatibility columns such as `role`, `category`, `location`, and `disposal_method` so the current app remains functional while using the normalized structure.

## Core Business Tables

1. `users`
2. `admins`
3. `staff`
4. `teachers`
5. `locations`
6. `property_categories`
7. `disposal_methods`
8. `properties`
9. `assignments`
10. `returns`
11. `disposals`
12. `approvals`
13. `property_histories`

## `users`

Purpose: Stores login credentials and shared account information for all system users.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique user identifier |
| `name` | varchar(255) | No |  |  | Full name of the user |
| `email` | varchar(255) | No | Unique | unique | User login email |
| `email_verified_at` | timestamp | Yes |  |  | Email verification timestamp |
| `password` | varchar(255) | No |  |  | Hashed password |
| `role` | enum | No | Index | default `teacher` | Legacy role field for `admin`, `staff`, `teacher` |
| `is_active` | boolean | No | Index | default `true` | Indicates whether the account can log in |
| `remember_token` | varchar(100) | Yes |  |  | Laravel remember-me token |
| `two_factor_code` | varchar(255) | Yes |  |  | Temporary two-factor code |
| `two_factor_expires_at` | timestamp | Yes |  |  | Two-factor code expiry time |
| `created_at` | timestamp | Yes |  |  | Record creation timestamp |
| `updated_at` | timestamp | Yes |  |  | Record update timestamp |

## `admins`

Purpose: Stores admin-specific profile data separated from the main `users` table.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique admin profile identifier |
| `user_id` | bigint | No | FK, Unique | references `users.id` | Linked login account |
| `department` | varchar(255) | Yes |  |  | Department assigned to the admin |

## `staff`

Purpose: Stores staff-specific profile data separated from the main `users` table.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique staff profile identifier |
| `user_id` | bigint | No | FK, Unique | references `users.id` | Linked login account |
| `employee_number` | varchar(255) | Yes | Index |  | Staff employee number |
| `department` | varchar(255) | Yes |  |  | Department assigned to the staff member |

## `teachers`

Purpose: Stores teacher-specific profile data separated from the main `users` table.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique teacher profile identifier |
| `user_id` | bigint | No | FK, Unique | references `users.id` | Linked login account |
| `employee_number` | varchar(255) | Yes | Index |  | Teacher employee number |
| `subject_area` | varchar(255) | Yes |  |  | Teacher specialization or subject area |

## `locations`

Purpose: Stores normalized property and assignment locations.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique location identifier |
| `location_name` | varchar(255) | No | Unique | unique | Main location name |
| `building` | varchar(255) | Yes |  |  | Building name |
| `room` | varchar(255) | Yes |  |  | Room or office number |
| `description` | text | Yes |  |  | Additional location details |

## `property_categories`

Purpose: Stores normalized property categories.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique category identifier |
| `category_name` | varchar(255) | No | Unique | unique | Property category name |
| `description` | text | Yes |  |  | Category description |

## `disposal_methods`

Purpose: Stores normalized disposal methods.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique disposal method identifier |
| `method_name` | varchar(255) | No | Unique | unique | Disposal method name |
| `description` | text | Yes |  |  | Method description |

## `properties`

Purpose: Stores all tracked property items and inventory records.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique property identifier |
| `property_code` | varchar(255) | No | Unique | unique | Property or accountability code |
| `qr_reference` | varchar(255) | No | Unique | unique | QR reference value |
| `property_name` | varchar(255) | No |  |  | Name of the property |
| `description` | text | Yes |  |  | Detailed property description |
| `category` | varchar(255) | Yes |  | legacy compatibility field | Legacy category text |
| `property_category_id` | bigint | Yes | FK | references `property_categories.id` | Normalized category reference |
| `brand` | varchar(255) | Yes |  |  | Property brand |
| `model` | varchar(255) | Yes |  |  | Property model |
| `serial_number` | varchar(255) | Yes | Unique | unique | Serial number of the property |
| `unit_cost` | decimal(12,2) | Yes |  |  | Cost per unit |
| `quantity` | unsigned integer | No |  | default `1` | Current available quantity |
| `unit` | varchar(50) | No |  | default `piece` | Measurement unit of the property |
| `status` | enum | No | Index | default `available` | Inventory status: `available`, `assigned`, `for_disposal`, `disposed` |
| `acquired_at` | date | Yes |  | legacy compatibility field | Original acquisition date field |
| `date_acquired` | date | Yes |  |  | Normalized acquisition date used by the module |
| `condition_status` | varchar(50) | No |  | default `good` | Condition of the property |
| `location` | varchar(255) | Yes |  | legacy compatibility field | Legacy location text |
| `location_id` | bigint | Yes | FK | references `locations.id` | Normalized location reference |
| `qr_token` | varchar(255) | Yes | Unique | unique | QR token used for scan routes |
| `qr_code_path` | varchar(255) | Yes |  |  | Stored path of QR image |
| `created_by` | bigint | Yes | FK | references `users.id`, null on delete | User who encoded the property |
| `staff_id` | bigint | Yes | FK | references `staff.id`, null on delete | Staff profile managing or encoding the property |
| `created_at` | timestamp | Yes |  |  | Record creation timestamp |
| `updated_at` | timestamp | Yes |  |  | Record update timestamp |

## `assignments`

Purpose: Stores property assignment transactions and accountability records.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique assignment identifier |
| `property_id` | bigint | No | FK | references `properties.id`, cascade on delete | Assigned property |
| `teacher_id` | bigint | No | FK | references `users.id`, restrict on delete | User account of the assignee; retained with legacy field name |
| `teacher_profile_id` | bigint | Yes | FK | references `teachers.id`, null on delete | Normalized teacher profile reference when assignee is a teacher |
| `assigned_by` | bigint | Yes | FK | references `users.id`, null on delete | User account that issued the assignment |
| `staff_id` | bigint | Yes | FK | references `staff.id`, null on delete | Staff profile of the user managing the assignment |
| `quantity_assigned` | unsigned integer | No |  | default `1` | Quantity assigned in this transaction |
| `date_assigned` | date | Yes |  |  | Assignment date used by the current module |
| `expected_return_date` | date | Yes |  |  | Planned return date |
| `location` | varchar(255) | Yes |  | legacy compatibility field | Legacy assignment location text |
| `location_id` | bigint | Yes | FK | references `locations.id`, null on delete | Normalized assignment location |
| `assigned_at` | date | No |  |  | Original assignment date field |
| `returned_at` | date | Yes |  |  | Actual return date |
| `remarks` | text | Yes |  |  | Notes about the assignment |
| `status` | enum | No | Index | default `active` | Assignment status: `active`, `returned`, `transferred` |
| `created_at` | timestamp | Yes |  |  | Record creation timestamp |
| `updated_at` | timestamp | Yes |  |  | Record update timestamp |

## `returns`

Purpose: Stores return transactions linked to assignments.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique return identifier |
| `assignment_id` | bigint | No | FK | references `assignments.id`, cascade on delete | Related assignment record |
| `return_date` | date | No |  |  | Date the property was returned |
| `status` | varchar(255) | No |  | default `returned` | Return status value |
| `remarks` | text | Yes |  |  | Notes about the return |

## `disposals`

Purpose: Stores disposal transactions for properties.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique disposal identifier |
| `property_id` | bigint | No | FK | references `properties.id`, cascade on delete | Property being disposed |
| `disposed_by` | bigint | No | FK | references `users.id`, restrict on delete | User account that recorded the disposal |
| `admin_id` | bigint | Yes | FK | references `admins.id`, null on delete | Normalized admin profile reference |
| `quantity_disposed` | unsigned integer | No |  | default `1` | Quantity disposed in this transaction |
| `disposal_date` | date | No | Index |  | Date of disposal |
| `disposal_reason` | text | Yes |  |  | Reason for disposal |
| `disposal_method` | varchar(255) | No |  | legacy compatibility field | Human-readable disposal method |
| `disposal_method_id` | bigint | Yes | FK | references `disposal_methods.id`, null on delete | Normalized disposal method reference |
| `remarks` | text | Yes |  |  | Additional disposal notes |
| `status` | enum | No | Index | default `pending` | Disposal status: `pending`, `approved`, `completed`, `cancelled` |
| `created_at` | timestamp | Yes |  |  | Record creation timestamp |
| `updated_at` | timestamp | Yes |  |  | Record update timestamp |

## `approvals`

Purpose: Stores approval decisions associated with admin actions.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique approval identifier |
| `admin_id` | bigint | Yes | FK | references `admins.id`, null on delete | Admin profile that processed the approval |
| `reference_type` | varchar(255) | No |  |  | Type of approved record or module reference |
| `approval_status` | varchar(255) | No |  | default `pending` | Approval status value |
| `approval_date` | date | Yes |  |  | Date of approval |
| `remarks` | text | Yes |  |  | Approval remarks |

## `property_histories`

Purpose: Stores audit trail records for property-related actions.

| Field | Type | Null | Key | Constraints / Default | Description |
|---|---|---|---|---|---|
| `id` | bigint | No | PK | auto-increment | Unique history identifier |
| `property_id` | bigint | No | FK | references `properties.id`, cascade on delete | Related property |
| `action_type` | varchar(255) | No |  |  | Type of property action |
| `reference` | varchar(255) | Yes |  |  | Linked source reference such as assignment or disposal number |
| `action_date` | date | No |  |  | Date of the recorded action |
| `remarks` | text | Yes |  |  | Additional audit remarks |

## Relationship Summary

```text
users (1) ---- (1) admins
users (1) ---- (1) staff
users (1) ---- (1) teachers

property_categories (1) ----< properties
locations (1) ----< properties
locations (1) ----< assignments
staff (1) ----< properties
staff (1) ----< assignments
teachers (1) ----< assignments
admins (1) ----< disposals
admins (1) ----< approvals
disposal_methods (1) ----< disposals

properties (1) ----< assignments
properties (1) ----< disposals
properties (1) ----< property_histories
assignments (1) ----< returns
```

## Implementation Note

The current Laravel project keeps several compatibility fields so existing screens continue to work during the normalization process. For system documentation and thesis work, the normalized foreign-key based structure should be treated as the primary design.
