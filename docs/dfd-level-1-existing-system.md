# DFD Level 1 - Existing System for Property Accountability and Disposal

## Overview

The existing property accountability and disposal process is decomposed into five main processes:

1. **Receive Property and Personnel Information**
2. **Record Property Inventory**
3. **Prepare Property Accountability**
4. **Process Property Return or Disposal**
5. **Prepare Property Reports**

This Level 1 DFD expands the Level 0 existing process by showing the internal manual records, forms, and report files used by the Supply Office before the proposed PARDS automation.

## External Entities

- **Teacher / End-User**
- **Supply Office Staff**
- **Administrator**

## Data Stores

- **D1. Personnel List**
- **D2. Property Inventory Logbook**
- **D3. Accountability Forms**
- **D4. Return and Disposal Records**
- **D5. Property Report Files**

## Process Descriptions and Data Flows

### 1. Receive Property and Personnel Information

- **Teacher / End-User to Process 1:** Personnel Information / Property Request
- **Administrator to Process 1:** Personnel Approval / Authorization
- **Process 1 to D1 Personnel List:** Personnel Record Data
- **D1 Personnel List to Process 1:** Existing Personnel Details
- **Process 1 to Process 3:** Verified Personnel Information

### 2. Record Property Inventory

- **Supply Office Staff to Process 2:** Property Details
- **Process 2 to Supply Office Staff:** Updated Inventory Details
- **Process 2 to D2 Property Inventory Logbook:** Property Inventory Data
- **D2 Property Inventory Logbook to Process 2:** Property Record Details
- **Process 2 to Process 3:** Available Property Information
- **Process 2 to Process 4:** Property Status Information
- **Process 2 to Process 5:** Inventory Summary Data

### 3. Prepare Property Accountability

- **Supply Office Staff to Process 3:** Accountability Form Details
- **Teacher / End-User to Process 3:** Acknowledgment / Signature
- **Process 3 to Teacher / End-User:** Issued Property Details
- **Process 3 to D3 Accountability Forms:** Accountability Record Data
- **D3 Accountability Forms to Process 3:** Accountability Record Details
- **Process 3 to Process 5:** Accountability Summary Data

### 4. Process Property Return or Disposal

<!-- - **Teacher / End-User to Process 4:** Returned Item / Disposal Request -->
- **Supply Office Staff to Process 4:** Inspection and Disposal Details
- **Administrator to Process 4:** Disposal Approval
- **Process 4 to Teacher / End-User:** Return / Disposal Status
- **Process 4 to D4 Return and Disposal Records:** Return / Disposal Data
- **D4 Return and Disposal Records to Process 4:** Return / Disposal Record Details
- **Process 4 to Process 5:** Disposal Summary Data

### 5. Prepare Property Reports

- **Administrator to Process 5:** Report Request
- **Supply Office Staff to Process 5:** Report Preparation Details
- **Process 5 to Administrator:** Prepared Property Reports
- **Process 5 to Supply Office Staff:** Report Copies
- **Process 5 to D5 Property Report Files:** Generated Report Data
- **D5 Property Report Files to Process 5:** Stored Report Details

## Text Form

```text
TEACHER / END-USER
  -> (1) Receive Property and Personnel Information : Personnel Information / Property Request
  -> (3) Prepare Property Accountability : Acknowledgment / Signature
  <- (3) Prepare Property Accountability : Issued Property Details
  -> (4) Process Property Return or Disposal : Returned Item / Disposal Request
  <- (4) Process Property Return or Disposal : Return / Disposal Status

SUPPLY OFFICE STAFF
  -> (2) Record Property Inventory : Property Details
  <- (2) Record Property Inventory : Updated Inventory Details
  -> (3) Prepare Property Accountability : Accountability Form Details
  -> (4) Process Property Return or Disposal : Inspection and Disposal Details
  -> (5) Prepare Property Reports : Report Preparation Details
  <- (5) Prepare Property Reports : Report Copies

ADMINISTRATOR
  -> (1) Receive Property and Personnel Information : Personnel Approval / Authorization
  -> (4) Process Property Return or Disposal : Disposal Approval
  -> (5) Prepare Property Reports : Report Request
  <- (5) Prepare Property Reports : Prepared Property Reports

(1) Receive Property and Personnel Information <-> D1 Personnel List
(2) Record Property Inventory <-> D2 Property Inventory Logbook
(3) Prepare Property Accountability <-> D3 Accountability Forms
(4) Process Property Return or Disposal <-> D4 Return and Disposal Records
(5) Prepare Property Reports <-> D5 Property Report Files

(1) Receive Property and Personnel Information -> (3) Prepare Property Accountability : Verified Personnel Information
(2) Record Property Inventory -> (3) Prepare Property Accountability : Available Property Information
(2) Record Property Inventory -> (4) Process Property Return or Disposal : Property Status Information
(2) Record Property Inventory -> (5) Prepare Property Reports : Inventory Summary Data
(3) Prepare Property Accountability -> (5) Prepare Property Reports : Accountability Summary Data
(4) Process Property Return or Disposal -> (5) Prepare Property Reports : Disposal Summary Data
```

## Thesis Format

| Process | Input | Output | Data Store |
|---|---|---|---|
| 1. Receive Property and Personnel Information | Personnel information/property request from Teacher or End-User; personnel approval/authorization from Administrator | Verified personnel information to Process 3 | D1 Personnel List |
| 2. Record Property Inventory | Property details from Supply Office Staff | Updated inventory details to Supply Office Staff; available property information to Process 3; property status information to Process 4; inventory summary data to Process 5 | D2 Property Inventory Logbook |
| 3. Prepare Property Accountability | Accountability form details from Supply Office Staff; acknowledgment/signature from Teacher or End-User; verified personnel information from Process 1; available property information from Process 2 | Issued property details to Teacher or End-User; accountability summary data to Process 5 | D3 Accountability Forms |
| 4. Process Property Return or Disposal | Returned item/disposal request from Teacher or End-User; inspection and disposal details from Supply Office Staff; disposal approval from Administrator; property status information from Process 2 | Return/disposal status to Teacher or End-User; disposal summary data to Process 5 | D4 Return and Disposal Records |
| 5. Prepare Property Reports | Report request from Administrator; report preparation details from Supply Office Staff; inventory summary data from Process 2; accountability summary data from Process 3; disposal summary data from Process 4 | Prepared property reports to Administrator; report copies to Supply Office Staff | D5 Property Report Files |

## Formal Write-Up

The DFD Level 1 for the existing property accountability and disposal process shows how the manual system is divided into five major processes. Process 1 receives property request and personnel information, then verifies the details using the personnel list. Process 2 records property information in the inventory logbook and provides property status data for accountability and disposal processing. Process 3 prepares property accountability forms, records the issued items, and provides issued property details to the teacher or end-user. Process 4 handles returned items and disposal requests by using property status information, inspection details, and administrator approval, then stores the transaction in the return and disposal records. Process 5 prepares property reports by gathering inventory, accountability, return, and disposal information from the manual records.

In the existing process, the Teacher or End-User mainly submits requests, acknowledges issued properties, and returns or reports items for disposal. The Supply Office Staff records property details, prepares accountability forms, inspects returned or disposable items, and prepares report copies. The Administrator provides authorization, approves disposal actions, and receives property reports. Since the process depends on logbooks, forms, and report files, tracking property status and preparing reports requires manual checking across multiple records.
