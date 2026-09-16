# DFD Level 1 - Proposed System for PARDS

## Overview

The proposed Property Accountability Records and Disposal System (PARDS) is decomposed into five main processes:

1. **Manage User Accounts**
2. **Manage Property Records**
3. **Process Property Assignments**
4. **Process Property Disposals**
5. **Generate Reports and QR Information**

## External Entities
- **Teacher**
- **Staff**
- **Admin**

## Data Stores
- **D1. User Records**
- **D2. Property Records**
- **D3. Assignment Records**
- **D4. Disposal Records**
- **D5. Report and QR Files**

## Process Descriptions and Data Flows

### 1. Manage User Accounts
- **Admin to Process 1:** User Management Data
- **Process 1 to Admin:** User Account Information
- **Process 1 to D1 User Records:** User Information Data
- **D1 User Records to Process 1:** User Record Data
- **Process 1 to Process 3:** User Information Data

### 2. Manage Property Records
- **Staff to Process 2:** Property Record Data
- **Process 2 to Staff:** Property Details
- **Process 2 to D2 Property Records:** Property Information Data
- **D2 Property Records to Process 2:** Property Record Details
- **Process 2 to Process 3:** Property Monitoring Data
- **Process 2 to Process 4:** Property Status Data
- **Process 2 to Process 5:** Property Information Data

### 3. Process Property Assignments
- **Staff to Process 3:** Assignment Verification Data
- **Teacher to Process 3:** Property Acknowledgment Data
- **Teacher to Process 3:** Property Inquiry Data
- **Process 3 to Teacher:** Assigned Property Details
- **Process 3 to Staff:** Assignment Status Information
- **Process 3 to D3 Assignment Records:** Assignment Data
- **D3 Assignment Records to Process 3:** Assignment Record Data
- **Process 3 to Process 5:** Assignment Information Data

### 4. Process Property Disposals
- **Staff to Process 4:** Disposal Verification Data
- **Process 4 to Staff:** Disposal Record Information
- **Process 4 to D4 Disposal Records:** Disposal Data
- **D4 Disposal Records to Process 4:** Disposal Record Data
- **Process 4 to Process 5:** Disposal Information Data

### 5. Generate Reports and QR Information
- **Admin to Process 5:** Report Request Data
- **Staff to Process 5:** QR Generation Request Data
- **Teacher to Process 5:** Query Request Data
- **Process 5 to Admin:** Generated Reports Data
- **Process 5 to Staff:** QR Code Details
- **Process 5 to Teacher:** Query Response Details
- **Process 5 to D5 Report and QR Files:** Generated File Data
- **D5 Report and QR Files to Process 5:** Stored Report and QR Data

## Text Form

```text
ADMIN
  -> (1) Manage User Accounts : User Management Data
  <- (1) Manage User Accounts : User Account Information
  -> (5) Generate Reports and QR Information : Report Request Data
  <- (5) Generate Reports and QR Information : Generated Reports Data

STAFF
  -> (2) Manage Property Records : Property Record Data
  <- (2) Manage Property Records : Property Details
  -> (3) Process Property Assignments : Assignment Verification Data
  <- (3) Process Property Assignments : Assignment Status Information
  -> (4) Process Property Disposals : Disposal Verification Data
  <- (4) Process Property Disposals : Disposal Record Information
  -> (5) Generate Reports and QR Information : QR Generation Request Data
  <- (5) Generate Reports and QR Information : QR Code Details

TEACHER
  -> (3) Process Property Assignments : Property Acknowledgment Data
  -> (3) Process Property Assignments : Property Inquiry Data
  <- (3) Process Property Assignments : Assigned Property Details
  -> (5) Generate Reports and QR Information : Query Request Data
  <- (5) Generate Reports and QR Information : Query Response Details

(1) Manage User Accounts <-> D1 User Records
(2) Manage Property Records <-> D2 Property Records
(3) Process Property Assignments <-> D3 Assignment Records
(4) Process Property Disposals <-> D4 Disposal Records
(5) Generate Reports and QR Information <-> D5 Report and QR Files

(1) Manage User Accounts -> (3) Process Property Assignments : User Information Data
(2) Manage Property Records -> (3) Process Property Assignments : Property Monitoring Data
(2) Manage Property Records -> (4) Process Property Disposals : Property Status Data
(2) Manage Property Records -> (5) Generate Reports and QR Information : Property Information Data
(3) Process Property Assignments -> (5) Generate Reports and QR Information : Assignment Information Data
(4) Process Property Disposals -> (5) Generate Reports and QR Information : Disposal Information Data
```

## Thesis Format

| Process | Input | Output | Data Store |
|---|---|---|---|
| 1. Manage User Accounts | User management data from Admin | User account information to Admin; user information data to Process 3 | D1 User Records |
| 2. Manage Property Records | Property record data from Staff | Property details to Staff; property monitoring data to Process 3; property status data to Process 4; property information data to Process 5 | D2 Property Records |
| 3. Process Property Assignments | Assignment verification data from Staff; property acknowledgment data and property inquiry data from Teacher; user information data from Process 1; property monitoring data from Process 2 | Assigned property details to Teacher; assignment status information to Staff; assignment information data to Process 5 | D3 Assignment Records |
| 4. Process Property Disposals | Disposal verification data from Staff; property status data from Process 2 | Disposal record information to Staff; disposal information data to Process 5 | D4 Disposal Records |
| 5. Generate Reports and QR Information | Report request data from Admin; QR generation request data from Staff; query request data from Teacher; property information data from Process 2; assignment information data from Process 3; disposal information data from Process 4 | Generated reports data to Admin; QR code details to Staff; query response details to Teacher | D5 Report and QR Files |

## Formal Write-Up

The DFD Level 1 for the proposed PARDS shows how the system is divided into five major processes. Process 1 manages user accounts and stores user information in the user records data store. Process 2 manages property records and updates the property records data store. Process 3 handles property assignments by using user information and property monitoring data, then stores the assignment transactions in the assignment records data store. Process 4 manages disposal transactions and stores them in the disposal records data store. Process 5 generates reports and QR-related outputs by using information from property, assignment, and disposal records, then stores the generated files in the report and QR files data store.

The Teacher interacts mainly with the assignment and query functions of the proposed system. The Staff is responsible for entering property information, validating assignments, recording disposals, and requesting QR generation. The Admin manages user accounts and requests reports. Through these connected processes and data stores, the proposed PARDS provides organized, trackable, and report-ready management of school property records.
