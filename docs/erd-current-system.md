# PARDS Current System ERD

This ERD reflects the current Laravel migrations and relationships used by the
Property and Accountability Records and Documentation System (PARDS). `PK`
means primary key, `FK` means foreign key, and `UQ` means unique value.

```mermaid
erDiagram
    USERS {
        bigint id PK
        string name
        string email UQ
        string password
        string role
        boolean is_active
        timestamp email_verified_at
        string two_factor_code
        timestamp two_factor_expires_at
        timestamp created_at
        timestamp updated_at
    }

    PROFILE_PHOTOS {
        bigint id PK
        bigint user_id FK_UQ
        string mime_type
        mediumtext contents
        timestamp created_at
        timestamp updated_at
    }

    ADMINS {
        bigint id PK
        bigint user_id FK_UQ
        string department
    }

    STAFF {
        bigint id PK
        bigint user_id FK_UQ
        string employee_number
        string department
    }

    TEACHERS {
        bigint id PK
        bigint user_id FK_UQ
        string employee_number
        string subject_area
    }

    PROPERTY_CATEGORIES {
        bigint id PK
        string category_name UQ
        text description
    }

    LOCATIONS {
        bigint id PK
        string location_name UQ
        string building
        string room
        text description
    }

    PROPERTIES {
        bigint id PK
        string property_code UQ
        string property_name
        bigint property_category_id FK
        bigint location_id FK
        bigint created_by FK
        bigint staff_id FK
        string serial_number UQ
        integer quantity
        string unit
        decimal unit_cost
        string status
        string condition_status
        string department
        string office
        date date_acquired
        string qr_token UQ
    }

    PROPERTY_UNITS {
        bigint id PK
        bigint property_id FK
        bigint assignment_id FK
        string serial_number UQ
        string qr_token UQ
        string status
        timestamp created_at
        timestamp updated_at
    }

    ASSIGNMENTS {
        bigint id PK
        bigint property_id FK
        bigint teacher_id FK
        bigint teacher_profile_id FK
        bigint assigned_by FK
        bigint staff_id FK
        bigint location_id FK
        integer quantity_assigned
        string department
        date date_assigned
        date expected_return_date
        date returned_at
        string status
    }

    PROPERTY_REQUESTS {
        bigint id PK
        bigint requested_by FK
        bigint reviewed_by FK
        bigint processed_by FK
        bigint selected_property_id FK
        bigint assignment_id FK_UQ
        string requested_item_name
        integer requested_quantity
        date needed_by
        text purpose
        string status
        timestamp reviewed_at
        timestamp processed_at
    }

    RETURNS {
        bigint id PK
        bigint assignment_id FK
        bigint returned_by FK
        date return_date
        string status
        text returned_serial_numbers
        text remarks
    }

    DISPOSAL_METHODS {
        bigint id PK
        string method_name UQ
        text description
    }

    DISPOSALS {
        bigint id PK
        bigint property_id FK
        bigint assignment_id FK
        bigint disposed_by FK
        bigint processed_by FK
        bigint admin_id FK
        bigint disposal_method_id FK
        integer quantity_disposed
        date disposal_date
        string status
        text disposal_reason
        timestamp processed_at
    }

    PROPERTY_HISTORIES {
        bigint id PK
        bigint property_id FK
        string action_type
        string reference
        date action_date
        text remarks
    }

    APPROVALS {
        bigint id PK
        bigint admin_id FK
        string reference_type
        string approval_status
        date approval_date
        text remarks
    }

    USERS ||--o| PROFILE_PHOTOS : has
    USERS ||--o| ADMINS : has_role_profile
    USERS ||--o| STAFF : has_role_profile
    USERS ||--o| TEACHERS : has_role_profile
    USERS ||--o{ PROPERTIES : creates
    STAFF o|--o{ PROPERTIES : manages
    PROPERTY_CATEGORIES o|--o{ PROPERTIES : classifies
    LOCATIONS o|--o{ PROPERTIES : stores

    PROPERTIES ||--o{ PROPERTY_UNITS : contains
    PROPERTIES ||--o{ ASSIGNMENTS : assigned_through
    PROPERTIES o|--o{ PROPERTY_REQUESTS : selected_for
    PROPERTIES ||--o{ DISPOSALS : disposed_through
    PROPERTIES ||--o{ PROPERTY_HISTORIES : records

    USERS ||--o{ ASSIGNMENTS : receives_as_assignee
    USERS o|--o{ ASSIGNMENTS : assigns
    TEACHERS o|--o{ ASSIGNMENTS : profile_for_assignee
    STAFF o|--o{ ASSIGNMENTS : staff_assignment_record
    LOCATIONS o|--o{ ASSIGNMENTS : assigned_at
    ASSIGNMENTS o|--o{ PROPERTY_UNITS : includes_units
    ASSIGNMENTS ||--o{ RETURNS : returned_through
    USERS o|--o{ RETURNS : records_return

    USERS ||--o{ PROPERTY_REQUESTS : requests
    USERS o|--o{ PROPERTY_REQUESTS : reviews
    USERS o|--o{ PROPERTY_REQUESTS : processes
    ASSIGNMENTS o|--o| PROPERTY_REQUESTS : fulfills

    ASSIGNMENTS o|--o{ DISPOSALS : source_assignment
    USERS o|--o{ DISPOSALS : requests_disposal
    USERS o|--o{ DISPOSALS : processes_disposal
    ADMINS o|--o{ DISPOSALS : admin_review
    DISPOSAL_METHODS o|--o{ DISPOSALS : method
    ADMINS o|--o{ APPROVALS : records
```

## Relationship rules

- Every account is stored in `users`. A role of `admin`, `staff`, or `teacher`
  has at most one matching role-profile row. The role is validated by the
  application; the database does not require exactly one profile row.
- A property is a product record. `property_units` holds every physical item,
  so a quantity of five laptops has five unique serial numbers and QR tokens.
- An assignment gives one or more available property units to one user. The
  assignment's `teacher_id` is the actual assignee and supports both teacher
  and staff users despite its legacy column name.
- A property request belongs to its requester and moves through staff review,
  admin approval, property selection, and a single resulting assignment. Its
  `assignment_id` is unique, so an assignment can fulfill at most one request.
- A return belongs to an assignment. A disposal may refer to a property and,
  when applicable, the source assignment.

## Deliberately not drawn as foreign-key links

- `approvals.reference_type` identifies the kind of record being approved, but
  the current table has no `reference_id` foreign key. It is therefore not
  linked to another entity in the ERD.
- `properties.category`, `properties.location`, `assignments.location`, and
  older date/reference columns remain as compatibility fields. The normalized
  relationships use `property_category_id`, `location_id`, and the current
  foreign-key columns shown above.
- Laravel support tables (`sessions`, `password_reset_tokens`, `cache`,
  `jobs`, `job_batches`, and `failed_jobs`) are framework infrastructure and
  are excluded from the operational ERD.
