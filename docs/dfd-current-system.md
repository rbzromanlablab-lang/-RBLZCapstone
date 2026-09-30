# PARDS Current System DFD — Level 0 and Level 1

These diagrams describe the implemented Property and Accountability Records and
Documentation System (PARDS). They follow the current routes, controllers,
services, and database schema. A rounded node is a process, a square node is
an external entity, and a cylinder is a persistent data store.

## Level 0 — Context Diagram

```mermaid
flowchart LR
    teacher[Teacher / End User]
    staff[Staff]
    admin[Administrator]
    mail[Email / OTP Service]
    scanner[QR Code Scanner]
    system([PARDS])

    teacher -->|registration details, OTP, login, property request| system
    system -->|account status, request status, assigned-property list, receipt| teacher

    staff -->|inventory, assignment, request review, return, disposal request| system
    system -->|inventory availability, request queue, accountability forms, reports| staff

    admin -->|user management, property approval, disposal decision| system
    system -->|dashboards, approval queue, audit information, reports| admin

    system -->|registration OTP| mail
    mail -->|delivery result| system

    scanner -->|property or unit QR token| system
    system -->|property or unit identification| scanner
```

## Level 1 — Main Processes and Data Stores

```mermaid
flowchart TB
    teacher[Teacher / End User]
    staff[Staff]
    admin[Administrator]
    mail[Email / OTP Service]
    scanner[QR Code Scanner]

    p1([1.0 Account and Profile Management])
    p2([2.0 Property and Unit Inventory Management])
    p3([3.0 Property Request Review and Fulfillment])
    p4([4.0 Assignment, Return, and Disposal Management])
    p5([5.0 QR Lookup and Reporting])

    d1[(D1 Users and Role Profiles)]
    d2[(D2 Properties, Units, Categories, Locations)]
    d3[(D3 Property Requests)]
    d4[(D4 Assignments and Returns)]
    d5[(D5 Disposals and Property History)]
    d6[(D6 Profile Photos and Session Data)]

    teacher -->|registration details, OTP, login, profile photo| p1
    staff -->|login, profile update| p1
    admin -->|create or update user, activate or deactivate account| p1
    p1 -->|OTP delivery request| mail
    mail -->|OTP delivery result| p1
    p1 <--> |account and role data| d1
    p1 <--> |profile image and session data| d6
    p1 -->|login result, profile status| teacher
    p1 -->|login result| staff
    p1 -->|user-management result| admin

    staff -->|property details, quantity, serial numbers, location| p2
    admin -->|property details and corrections| p2
    p2 <--> |property catalog, physical units, categories, locations| d2
    p2 -->|inventory availability and QR data| staff
    p2 -->|inventory availability| admin

    teacher -->|item request, quantity, purpose, needed date| p3
    staff -->|staff review and notes| p3
    admin -->|approval, rejection, awaiting-stock decision, selected property| p3
    p3 <--> |request record and status| d3
    p3 <-->|available stock and selected property| d2
    p3 -->|approved request for fulfillment| p4
    p3 -->|request status| teacher
    p3 -->|staff review queue| staff
    p3 -->|admin approval queue| admin

    staff -->|assignment details, selected units, return confirmation, disposal request| p4
    admin -->|disposal approval or cancellation| p4
    p4 <--> |assignment and return records| d4
    p4 <--> |unit assignment status and available quantity| d2
    p4 <--> |disposal decision and property history| d5
    p4 -->|receipt, accountability record, return result| teacher
    p4 -->|assignment and return result| staff
    p4 -->|disposal result| admin

    scanner -->|property or unit QR token| p5
    staff -->|report or QR document request| p5
    admin -->|report or QR document request| p5
    p5 <-->|property and unit lookup data| d2
    p5 <-->|assignment and return data| d4
    p5 <-->|disposal and history data| d5
    p5 -->|property or unit details| scanner
    p5 -->|QR document, PDF/CSV report| staff
    p5 -->|QR document, PDF/CSV report| admin
```

## Process details

| Process | Responsibility | Main data stores |
| --- | --- | --- |
| 1.0 Account and Profile Management | Registers staff/teacher accounts through Gmail OTP, authenticates users, stores role profiles and profile photos, and lets admins manage user status. | D1, D6 |
| 2.0 Property and Unit Inventory Management | Creates and updates property records, generates one property-unit record per physical item, assigns distinct serial numbers and QR tokens, and keeps availability current. | D2 |
| 3.0 Property Request Review and Fulfillment | Records a teacher request; staff forwards it; admin approves, rejects, or marks it awaiting stock; then staff fulfills an approved request using the admin-selected property. | D2, D3, D4 |
| 4.0 Assignment, Return, and Disposal Management | Assigns available property units, produces accountability receipts, records returns, receives disposal requests, and applies the admin's disposal decision. | D2, D4, D5 |
| 5.0 QR Lookup and Reporting | Uses property/unit QR tokens for lookup and creates printable or downloadable property, assignment, and disposal reports. | D2, D4, D5 |

## Request status flow

```mermaid
stateDiagram-v2
    [*] --> pending: Teacher submits request
    pending --> awaiting_admin: Staff reviews and forwards
    awaiting_admin --> approved: Admin selects available property and approves
    awaiting_admin --> awaiting_stock: No sufficient stock
    awaiting_stock --> approved: Stock becomes available and admin approves
    awaiting_admin --> rejected: Admin rejects
    awaiting_stock --> rejected: Admin rejects
    approved --> fulfilled: Staff assigns approved property units
    fulfilled --> [*]
    rejected --> [*]
```

Staff review sets the request status to `awaiting_admin`. The final fulfillment creates an assignment, links it to the property request,
marks the selected property units as assigned, and makes a printable receipt
available to the end user, staff, and administrator.
