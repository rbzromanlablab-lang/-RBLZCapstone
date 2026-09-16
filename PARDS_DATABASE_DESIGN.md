# PARDS Database Design

This document defines the database structure for the Laravel-based Property Accountability Records and Disposal System (PARDS).

## 1. Migration Plan

Recommended migration order:

1. Update `users` table to support role-based access.
2. Create `properties` table.
3. Create `assignments` table.
4. Create `disposals` table.

Suggested Laravel migration files:

```text
database/migrations/
|-- 0001_01_01_000000_create_users_table.php
|-- xxxx_xx_xx_xxxxxx_add_role_to_users_table.php
|-- xxxx_xx_xx_xxxxxx_create_properties_table.php
|-- xxxx_xx_xx_xxxxxx_create_assignments_table.php
|-- xxxx_xx_xx_xxxxxx_create_disposals_table.php
```

Migration notes:

- `users` already exists in the current Laravel project, so only a role-related update is needed.
- `properties` should be created before `assignments` and `disposals` because both depend on it.
- `assignments` should reference the teacher from the `users` table.
- `disposals` should reference the user who processed or recorded the disposal.

## 2. Table List

Main tables for this phase:

1. `users`
2. `properties`
3. `assignments`
4. `disposals`

## 3. Fields for Each Table

### `users`

Purpose:
Stores all authenticated users of the system, including Admin, Staff, and Teacher.

Fields:

| Field | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint | primary key | User ID |
| `name` | string | required | Full name |
| `email` | string | unique, required | Login email |
| `email_verified_at` | timestamp | nullable | Laravel default |
| `password` | string | required | Hashed password |
| `role` | enum or string | required | `admin`, `staff`, `teacher` |
| `remember_token` | string | nullable | Laravel default |
| `created_at` | timestamp | | Laravel default |
| `updated_at` | timestamp | | Laravel default |

Recommended role values:

```text
admin
staff
teacher
```

Suggested indexing:

- unique index on `email`
- index on `role`

### `properties`

Purpose:
Stores all school or institutional properties being tracked in PARDS.

Fields:

| Field | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint | primary key | Property ID |
| `property_code` | string | unique, required | Unique accountability/property code |
| `qr_reference` | string | unique, required | Unique QR identifier or QR payload reference |
| `property_name` | string | required | Name of the property |
| `description` | text | nullable | Detailed description |
| `category` | string | nullable | Property category |
| `brand` | string | nullable | Brand or manufacturer |
| `model` | string | nullable | Model name or number |
| `serial_number` | string | nullable, unique optional | Manufacturer serial number |
| `unit_cost` | decimal(12,2) | nullable | Cost per property item |
| `quantity` | integer | default 1 | Quantity tracked |
| `status` | enum or string | required | `available`, `assigned`, `for_disposal`, `disposed` |
| `acquired_at` | date | nullable | Date acquired |
| `created_by` | foreignId | nullable | User who encoded the property |
| `created_at` | timestamp | | Laravel default |
| `updated_at` | timestamp | | Laravel default |

Suggested status values:

```text
available
assigned
for_disposal
disposed
```

Suggested indexing:

- unique index on `property_code`
- unique index on `qr_reference`
- optional unique index on `serial_number`
- index on `status`
- foreign key index on `created_by`

### `assignments`

Purpose:
Tracks accountability assignment of a property to a teacher.

Fields:

| Field | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint | primary key | Assignment ID |
| `property_id` | foreignId | required | Linked property |
| `teacher_id` | foreignId | required | Linked teacher from `users` table |
| `assigned_by` | foreignId | nullable | User who created the assignment, usually admin or staff |
| `assigned_at` | date | required | Date property was assigned |
| `returned_at` | date | nullable | Date property was returned |
| `remarks` | text | nullable | Assignment notes |
| `status` | enum or string | required | `active`, `returned`, `transferred` |
| `created_at` | timestamp | | Laravel default |
| `updated_at` | timestamp | | Laravel default |

Suggested status values:

```text
active
returned
transferred
```

Suggested indexing:

- index on `property_id`
- index on `teacher_id`
- index on `assigned_by`
- index on `status`

Business rule note:

- A teacher is stored in `users` with `role = teacher`.
- One property can have many assignment records over time, but normally only one active assignment at a time.

### `disposals`

Purpose:
Tracks disposal actions or disposal records for properties.

Fields:

| Field | Type | Constraints | Description |
|---|---|---|---|
| `id` | bigint | primary key | Disposal ID |
| `property_id` | foreignId | required | Linked property |
| `disposed_by` | foreignId | required | User who disposed or recorded disposal |
| `disposal_date` | date | required | Date of disposal |
| `disposal_type` | string | required | Example: sale, transfer, condemnation, recycling |
| `reason` | text | nullable | Reason for disposal |
| `remarks` | text | nullable | Additional notes |
| `status` | enum or string | required | `pending`, `approved`, `completed`, `cancelled` |
| `created_at` | timestamp | | Laravel default |
| `updated_at` | timestamp | | Laravel default |

Suggested status values:

```text
pending
approved
completed
cancelled
```

Suggested indexing:

- index on `property_id`
- index on `disposed_by`
- index on `status`
- index on `disposal_date`

Business rule note:

- A property should generally have at most one completed disposal record.
- Once disposal is completed, the related property status should become `disposed`.

## 4. Relationships Between Tables

### Core relationships

1. `users` to `assignments`
   - One teacher user can have many assignments.
   - `assignments.teacher_id` references `users.id`.

2. `users` to `assignments` as encoder or issuer
   - One admin or staff user can create many assignments.
   - `assignments.assigned_by` references `users.id`.

3. `users` to `disposals`
   - One user can record many disposals.
   - `disposals.disposed_by` references `users.id`.

4. `users` to `properties`
   - One user can create many properties.
   - `properties.created_by` references `users.id`.

5. `properties` to `assignments`
   - One property can have many assignment history records.
   - `assignments.property_id` references `properties.id`.

6. `properties` to `disposals`
   - One property can have one or more disposal records in history, depending on business rules.
   - `disposals.property_id` references `properties.id`.

### Relationship summary

```text
users (1) ----< properties.created_by
users (1) ----< assignments.teacher_id
users (1) ----< assignments.assigned_by
users (1) ----< disposals.disposed_by

properties (1) ----< assignments.property_id
properties (1) ----< disposals.property_id
```

## Recommended Foreign Key Rules

### `properties.created_by`

- `foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()`

### `assignments.property_id`

- `foreignId('property_id')->constrained('properties')->cascadeOnDelete()`

### `assignments.teacher_id`

- `foreignId('teacher_id')->constrained('users')->restrictOnDelete()`

### `assignments.assigned_by`

- `foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete()`

### `disposals.property_id`

- `foreignId('property_id')->constrained('properties')->cascadeOnDelete()`

### `disposals.disposed_by`

- `foreignId('disposed_by')->constrained('users')->restrictOnDelete()`

## Recommended Laravel Model Relationship Outline

This is only for relationship planning and not for controller or view generation yet.

### `User`

- `hasMany(Property::class, 'created_by')`
- `hasMany(Assignment::class, 'teacher_id')`
- `hasMany(Assignment::class, 'assigned_by')`
- `hasMany(Disposal::class, 'disposed_by')`

### `Property`

- `belongsTo(User::class, 'created_by')`
- `hasMany(Assignment::class)`
- `hasMany(Disposal::class)`

### `Assignment`

- `belongsTo(Property::class)`
- `belongsTo(User::class, 'teacher_id')`
- `belongsTo(User::class, 'assigned_by')`

### `Disposal`

- `belongsTo(Property::class)`
- `belongsTo(User::class, 'disposed_by')`

## Final Design Notes

- `users` is a single table for authentication and role management.
- Teachers are not stored in a separate table in this phase; they are filtered from `users` using `role = teacher`.
- `assignments` acts as the accountability history table between a property and a teacher.
- `disposals` acts as the disposal transaction history table and records the responsible user.
- The database is ready for Laravel migrations next, but no controllers or views are included in this step.
