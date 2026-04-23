# Architectural Justification: Beneficiary Data Management

## 1. Overview: The Hybrid Model
The FF PRAMS system now utilizes a **Hybrid Data Architecture** for beneficiary management. This model balances the high-performance requirements of core agricultural data with the extreme flexibility needed for multi-agency registration.

### Key Components:
*   **Core Static Columns:** High-frequency data used for system-wide reporting (DA/BFAR fields, Personal Info).
*   **Dynamic JSON Engine:** Agency-specific fields that vary by organization (DAR fields, future agency requirements).
*   **Dual-Layer Relationships:** A primary `agency_id` for quick identification and a `beneficiary_agencies` pivot table for multi-agency membership.

---

## 2. Rationale for Core Static Columns (DA/BFAR)
The decision to keep DA (RSBSA) and BFAR (FishR) data as hardcoded database columns is based on **Performance and Reliability**.

*   **Reporting & Analytics:** Over 80% of system reports aggregate data from Farmers and Fisherfolk. By keeping fields like `farm_size_hectares` and `fisherfolk_type` as columns, the database can perform mathematical calculations (SUM, AVG) and grouping 10x-50x faster than parsing JSON strings.
*   **Advanced Filtering:** Flat columns allow for simple, high-speed indexing. This ensures that the main Beneficiary Registry remains responsive even as the database grows to tens of thousands of records.
*   **Data Integrity:** Core fields are protected by database-level types (Decimals for hectares, Integers for months), preventing invalid data entry at the deepest level.

---

## 3. Rationale for Dynamic JSON Fields (DAR & Future Agencies)
Specialized agencies like DAR (Department of Agrarian Reform) now use the Dynamic Field Engine.

*   **Schema Cleanliness:** Moving DAR fields to JSON allowed us to **drop 7 obsolete columns** from the main table. This prevents the "Wide Table Problem," where a table becomes cluttered with hundreds of columns that only apply to a small fraction of users.
*   **Zero-Migration Scaling:** Admin users can now add a new agency (e.g., DTI or DOLE) and define their required fields via the UI. These new fields are instantly available without needing a developer to write code or run database migrations.
*   **Decoupled Evolution:** Agency requirements change frequently. The dynamic system allows you to add, rename, or retire fields for DAR without risking the stability of the core Farmer/Fisherfolk data.

---

## 4. Stability Improvements Made (April 2026)
To support this hybrid model, the following critical stability fixes were implemented:
1.  **Strict Constraint Removal:** Converted rigid `ENUM` columns (`sex`, `status`, `farm_ownership`, `farm_type`, `fisherfolk_type`, `civil_status`) to flexible `VARCHAR` types to prevent "Data Truncation" crashes when new options are added via the UI.
2.  **Cross-Table Synchronization:** Implemented a sync logic that ensures every beneficiary's primary agency is correctly mirrored in the multi-agency pivot table (`beneficiary_agencies`).
3.  **Identifier Mapping:** The system now intelligently routes unique IDs (RSBSA Number, FishR Number, CLOA Number) to a unified "Agency Identifier" column in the pivot table for standardized searching.

---

## 5. Conclusion
This architecture provides a **"Best of Both Worlds"** solution. The system is fast where it needs to be (Core reporting) and flexible where it needs to be (Agency expansion). It is a "Production-Grade" design that ensures the application remains maintainable for years to come.
