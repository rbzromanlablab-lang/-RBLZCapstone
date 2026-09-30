# PARDS Current System Data Dictionary

This data dictionary documents the current database design for the Property and
Accountability Records and Documentation System (PARDS). It is based on the
Laravel migrations used by the application.

**Notation:** `PK` = primary key, `FK` = foreign key, `UQ` = unique, `NN` =
not null, and `NULL` = optional value. Dates and timestamps are stored in UTC
by the application and displayed in Philippine time where applicable.

## 1. User and access tables

### `users`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique account identifier. |
| name | varchar(255), NN | Full name of the account holder. |
| email | varchar(255), UQ, NN | Gmail address used to sign in. |
| email_verified_at | timestamp, NULL | When registration email verification was completed. |
| password | varchar(255), NN | Hashed account password. |
| role | enum: admin, staff, teacher; NN | Access role and dashboard type. |
| is_active | boolean, NN, default true | Indicates whether the account may sign in. |
| remember_token | varchar(100), NULL | Laravel persistent-login token. |
| two_factor_code | varchar(255), NULL | Temporary administrator verification code. |
| two_factor_expires_at | timestamp, NULL | Expiration time of the administrator verification code. |
| created_at | timestamp, NULL | When the account was created. |
| updated_at | timestamp, NULL | When the account was last updated. |

### `profile_photos`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique profile-photo identifier. |
| user_id | bigint, FK -> users.id, UQ, NN | Account that owns the photo; one photo per user. |
| mime_type | varchar(32), NN | Image file type, such as `image/jpeg` or `image/png`. |
| contents | mediumtext, NN | Base64-encoded image content. |
| created_at | timestamp, NULL | When the photo was saved. |
| updated_at | timestamp, NULL | When the photo was last replaced. |

### `admins`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique administrator profile identifier. |
| user_id | bigint, FK -> users.id, UQ, NN | User account with the administrator role. |
| department | varchar(255), NULL | Administrator department. |

### `staff`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique staff profile identifier. |
| user_id | bigint, FK -> users.id, UQ, NN | User account with the staff role. |
| employee_number | varchar(255), NULL, indexed | System-generated or recorded staff employee number. |
| department | varchar(255), NULL | Staff department. |

### `teachers`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique teacher profile identifier. |
| user_id | bigint, FK -> users.id, UQ, NN | User account with the teacher role. |
| employee_number | varchar(255), NULL, indexed | System-generated or recorded teacher employee number. |
| subject_area | varchar(255), NULL | Teacher subject area or specialization. |

## 2. Inventory reference tables

### `property_categories`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique category identifier. |
| category_name | varchar(255), UQ, NN | Category name, such as Laptop or Furniture. |
| description | text, NULL | Optional category description. |

### `locations`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique location identifier. |
| location_name | varchar(255), UQ, NN | Name of the property location. |
| building | varchar(255), NULL | Building name or code. |
| room | varchar(255), NULL | Room or office number. |
| description | text, NULL | Additional location details. |

### `properties`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique property record identifier. |
| property_code | varchar(255), UQ, NN | Inventory/property code. |
| qr_reference | varchar(255), UQ, NN | Legacy QR reference retained for existing records. |
| property_name | varchar(255), NN | Name of the property item or product. |
| description | text, NULL | Item description. |
| category | varchar(255), NULL | Legacy free-text category. |
| property_category_id | bigint, FK -> property_categories.id, NULL | Normalized property category. |
| brand | varchar(255), NULL | Product brand. |
| model | varchar(255), NULL | Product model. |
| serial_number | varchar(255), UQ, NULL | Legacy/main serial number; physical-item serials are held in `property_units`. |
| unit_cost | decimal(12,2), NULL | Cost per item. |
| quantity | unsigned integer, NN, default 1 | Current quantity available in inventory. |
| unit | varchar(50), NN, default piece | Unit of measure. |
| status | enum: available, assigned, for_disposal, disposed | Inventory status. |
| date_acquired | date, NULL | Date the item was acquired. |
| acquired_at | date, NULL | Legacy acquisition-date column. |
| condition_status | varchar(50), NN, default good | Condition: new, good, fair, poor, or damaged. |
| location | varchar(255), NULL | Legacy free-text location. |
| location_id | bigint, FK -> locations.id, NULL | Normalized current location. |
| office | varchar(255), NULL | Office responsible for the item. |
| department | varchar(255), NULL | Department responsible for the item. |
| qr_token | varchar(255), UQ, NULL | Token used to look up the property QR code. |
| qr_code_path | varchar(255), NULL | Stored QR code image path, when available. |
| created_by | bigint, FK -> users.id, NULL | User who created the property record. |
| staff_id | bigint, FK -> staff.id, NULL | Staff profile managing the property record. |
| created_at | timestamp, NULL | When the property record was created. |
| updated_at | timestamp, NULL | When the property record was last updated. |

### `property_units`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique physical-item identifier. |
| property_id | bigint, FK -> properties.id, NN | Parent property/product record. |
| assignment_id | bigint, FK -> assignments.id, NULL | Current assignment, when the unit is assigned. |
| serial_number | varchar(255), UQ, NN | Unique serial number for this physical item. |
| qr_token | varchar(255), UQ, NULL | Unique QR lookup token for this physical item. |
| status | enum: available, assigned, disposed; NN | Current physical-unit status. |
| created_at | timestamp, NULL | When the physical unit was created. |
| updated_at | timestamp, NULL | When the physical unit was last updated. |

## 3. Request, accountability, and return tables

### `property_requests`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique property-request identifier. |
| requested_by | bigint, FK -> users.id, NN | Teacher/end-user who submitted the request. |
| requested_item_name | varchar(255), NN | Requested item name. |
| requested_quantity | unsigned integer, NN, default 1 | Quantity requested. |
| needed_by | date, NULL | Requested date of need. |
| purpose | text, NN | Intended use of the property. |
| additional_notes | text, NULL | Additional requester notes. |
| status | varchar(50), NN, default pending | `pending`, `awaiting_admin`, `awaiting_stock`, `approved`, `rejected`, or `fulfilled`. |
| reviewed_by | bigint, FK -> users.id, NULL | Staff member who reviewed and forwarded the request. |
| reviewed_at | timestamp, NULL | When the staff review was completed. |
| review_notes | text, NULL | Staff review notes. |
| processed_by | bigint, FK -> users.id, NULL | Administrator who made the decision. |
| processed_at | timestamp, NULL | When the administrator processed the request. |
| response_notes | text, NULL | Administrator decision notes. |
| selected_property_id | bigint, FK -> properties.id, NULL | Available property selected by the administrator. |
| assignment_id | bigint, FK -> assignments.id, UQ, NULL | Resulting assignment after staff fulfills the request. |
| created_at | timestamp, NULL | When the request was submitted. |
| updated_at | timestamp, NULL | When the request was last updated. |

### `assignments`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique accountability/assignment identifier. |
| property_id | bigint, FK -> properties.id, NN | Assigned property/product record. |
| teacher_id | bigint, FK -> users.id, NN | Actual recipient of the property; the legacy name supports both teacher and staff accounts. |
| teacher_profile_id | bigint, FK -> teachers.id, NULL | Teacher profile where the assignee is a teacher. |
| assigned_by | bigint, FK -> users.id, NULL | User who issued the property. |
| staff_id | bigint, FK -> staff.id, NULL | Staff profile that processed the assignment. |
| quantity_assigned | unsigned integer, NN, default 1 | Number of units assigned. |
| date_assigned | date, NULL | Assignment date displayed on forms. |
| assigned_at | date, NN | Legacy assignment date retained by the schema. |
| expected_return_date | date, NULL | Expected date of return. |
| returned_at | date, NULL | Date all/part of the assignment was returned. |
| location | varchar(255), NULL | Legacy free-text assignment location. |
| location_id | bigint, FK -> locations.id, NULL | Normalized assignment location. |
| department | varchar(255), NULL | Recipient/assignment department. |
| remarks | text, NULL | Assignment notes. |
| status | enum: active, returned, transferred, disposed | Assignment lifecycle status. |
| created_at | timestamp, NULL | When the assignment record was created. |
| updated_at | timestamp, NULL | When the assignment record was last updated. |

### `returns`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique return-record identifier. |
| assignment_id | bigint, FK -> assignments.id, NN | Assignment being returned. |
| returned_by | bigint, FK -> users.id, NULL | User who recorded the return. |
| return_date | date, NN | Date the item was returned. |
| status | varchar(255), NN, default returned | Return status. |
| returned_serial_numbers | text, NULL | Serial number(s) of returned physical units. |
| remarks | text, NULL | Return notes or condition details. |

## 4. Disposal, approval, and audit tables

### `disposal_methods`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique disposal-method identifier. |
| method_name | varchar(255), UQ, NN | Method, such as auction, donation, recycling, transfer, condemnation, or destruction. |
| description | text, NULL | Optional method description. |

### `disposals`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique disposal-record identifier. |
| property_id | bigint, FK -> properties.id, NN | Property being disposed. |
| assignment_id | bigint, FK -> assignments.id, NULL | Source assignment when disposal is for an assigned item. |
| disposed_by | bigint, FK -> users.id, NULL | User who submitted the disposal record/request. |
| admin_id | bigint, FK -> admins.id, NULL | Administrator profile that reviewed the disposal. |
| processed_by | bigint, FK -> users.id, NULL | User who processed the final decision. |
| quantity_disposed | unsigned integer, NN, default 1 | Number of physical units disposed. |
| disposal_date | date, NN | Date of disposal. |
| disposal_method | varchar(255), NN | Legacy/free-text disposal method. |
| disposal_method_id | bigint, FK -> disposal_methods.id, NULL | Normalized disposal method. |
| disposal_reason | text, NULL | Reason for disposal. |
| remarks | text, NULL | Submitter notes. |
| response_notes | text, NULL | Administrator response or decision notes. |
| processed_at | timestamp, NULL | When the disposal was reviewed. |
| status | enum: pending, approved, completed, cancelled | Disposal workflow status. |
| created_at | timestamp, NULL | When the disposal record was created. |
| updated_at | timestamp, NULL | When the disposal record was last updated. |

### `approvals`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique approval identifier. |
| admin_id | bigint, FK -> admins.id, NULL | Administrator profile that recorded the approval. |
| reference_type | varchar(255), NN | Type of record being approved. |
| approval_status | varchar(255), NN, default pending | Approval decision/status. |
| approval_date | date, NULL | Date of decision. |
| remarks | text, NULL | Approval notes. |

### `property_histories`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | bigint, PK | Unique property-history identifier. |
| property_id | bigint, FK -> properties.id, NN | Property affected by the recorded action. |
| action_type | varchar(255), NN | Action, such as assigned, returned, updated, or disposed. |
| reference | varchar(255), NULL | Reference to the related record, for example `assignment:12`. |
| action_date | date, NN | Date the action took place. |
| remarks | text, NULL | Additional audit details. |

## 5. Authentication support tables

### `password_reset_tokens`

| Field | Type / constraint | Description |
| --- | --- | --- |
| email | varchar(255), PK | Email address requesting a password reset. |
| token | varchar(255), NN | Password-reset token. |
| created_at | timestamp, NULL | Time the reset token was created. |

### `sessions`

| Field | Type / constraint | Description |
| --- | --- | --- |
| id | varchar(255), PK | Laravel session identifier. |
| user_id | bigint, NULL, indexed | Authenticated user identifier when present. |
| ip_address | varchar(45), NULL | Client IP address. |
| user_agent | text, NULL | Browser/client identification. |
| payload | longtext, NN | Encrypted Laravel session data. |
| last_activity | integer, NN, indexed | Last session activity time as a Unix timestamp. |

## Notes for documentation

- `property_units` is the source of truth for every physical item. If five
  laptops are added, five `property_units` rows are created with different
  serial numbers and QR tokens.
- `properties.quantity` stores currently available stock. Assignment, return,
  and disposal operations update both the quantity and the related unit status.
- `property_requests.assignment_id` is unique so one property request can
  result in at most one assignment and one receiving receipt.
- Some legacy text fields remain for compatibility (`category`, `location`,
  `qr_reference`, `acquired_at`, and `disposal_method`). The normalized foreign
  keys are the preferred relationships for new data.
