# Database Migration Order Guide

**IMPORTANT:** Run these SQL files in this EXACT order on your production server.

## Critical Notes
- ⚠️ **ALWAYS backup your production database before running ANY migrations**
- ⚠️ Several files modify the same tables - order matters!
- ✅ All files have been tested on development database
- 📅 Created: December 19, 2025

---

## Migration Order (Run in this sequence)

### **Phase 1: Base Tables (Already Applied in Production?)**

These may already exist on your production server. Check first:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'projects';
SHOW TABLES LIKE 'customers';
SHOW TABLES LIKE 'estimates';
```

If they DON'T exist, run these:

**1. create_customers_table.sql** - Creates customers table

**2. create_projects_table.sql** - Creates projects table  

**3. create_estimates_table.sql** - Creates estimates table

**4. create_equipment_table.sql** - Creates equipment table

**5. add_username_column.sql** - Adds username column to users table
   - Adds unique username field
   - Auto-generates usernames for existing users
   - ✅ **Safe to run** - only modifies users table

---

### **Phase 2: Project-Customer Relationship Changes**

**⚠️ CONFLICT WARNING:** These files modify the `projects` table structure in specific order!

**6. update_schema.sql** - ⚠️ **DESTRUCTIVE!** 
   - Drops and recreates projects table
   - **WARNING:** This drops `customer_id` column from projects
   - Creates `customer_project` junction table
   - Run this FIRST before other project modifications
   - **NOTE:** create_customer_project_table.sql is REDUNDANT with this - don't run both!

**7. add_customer_id_to_projects.sql** - ⚠️ **CONFLICTS with #6!**
   - Adds `customer_id` back to projects table
   - Adds foreign key to customers
   - **Must run AFTER #6** to re-add the column

**8. update_columns.sql** - Modifies `customer_id` to NOT NULL
   - **Must run AFTER #7** since it requires customer_id to exist

**8.5. add_estimate_id_to_projects.sql** - Adds estimate_id to projects
   - Adds `estimate_id` column to link projects to estimates
   - **Must run BEFORE #9** (required for project-estimate linking)
   - Created: December 20, 2025

---

### **Phase 3: Project-Estimate Integration (December 19, 2025)**

**9. link_projects_estimates.sql** - Project-estimate relationship
   - Makes `customer_name` in estimates nullable
   - Adds `is_project_estimate` column to estimates
   - ✅ **Safe to run** - only modifies estimates table

---

### **Phase 4: Settings Table Enhancements**

**10. add_company_info_fields.sql** - Company info in settings
   - Adds address, phone, email, logo fields
   - ✅ **Safe to run** - uses `ADD COLUMN IF NOT EXISTS`

**11. add_email_settings.sql** - SMTP email configuration
    - Adds SMTP settings to settings table
    - ✅ **Safe to run** - uses `ADD COLUMN IF NOT EXISTS`

**12. remove_unused_rates.sql** - Cleanup setup table
    - Removes overhead_rate and packaging_rate columns
    - ⚠️ **Must run AFTER setup table exists**
    - Purpose: Removes unused columns from estimates

---

### **Phase 5: Etsy Integration (December 19, 2025)**

**13. create_etsy_tables.sql** - Etsy OAuth and orders
    - Adds 9 etsy_* columns to settings table
    - Creates `etsy_orders` table
    - Creates `etsy_sync_log` table
    - ✅ **Safe to run** - new tables and columns

**14. create_etsy_order_items.sql** - Etsy product tracking
    - Creates `etsy_order_items` table
    - Creates `etsy_product_mappings` table
    - Adds `has_unlinked_items` to etsy_orders
    - ⚠️ **Must run AFTER #13** (requires etsy_orders table)

---

### **Phase 6: Production & Inventory Tracking (December 19, 2025)**

**15. add_production_tracking.sql** - Complete production lifecycle
    - Adds production fields to projects table (status, inventory, costs)
    - Creates `production_batches` table (includes labor_hours, laser_time, mill_time)
    - Creates `inventory_transactions` table
    - Adds `fulfilled_from_batch` to etsy_order_items
    - ✅ **Safe to run** - new tables and columns
    - **Updated Dec 19:** Includes CNC machine time tracking (laser_time, mill_time)

---

### **Phase 7: Cost Integration & Reports (December 20, 2025)**

**16. sync_project_cost_from_estimates.sql** - Sync project costs
    - Updates `projects.cost_per_unit` from linked estimate totals
    - Only affects projects with production_status = 'ready' or 'active'
    - ⚠️ **Must run AFTER #9** (requires estimate_id in projects)
    - ⚠️ **Must run AFTER #15** (requires cost_per_unit column)
    - Purpose: Populates cost data for inventory value calculations

**17. sync_batch_costs_from_estimates.sql** - Sync batch production costs
    - Updates `production_batches` material_cost and labor_cost from estimates
    - Calculates: material_cost = estimate.materials_cost × quantity_produced
    - Calculates: labor_cost = (estimate.labor_cost + machine_cost) × quantity_produced
    - ⚠️ **Must run AFTER #15** (requires production_batches table)
    - ⚠️ **Must run AFTER #9** (requires projects linked to estimates)
    - Purpose: Populates batch costs for profit analysis reports

---

## 📋 **Complete Migration List (All 18 Files)**

### **Required Migrations (18 files - run in this order):**
1. create_customers_table.sql
2. create_projects_table.sql  
3. create_estimates_table.sql
4. create_equipment_table.sql
5. add_username_column.sql
6. update_schema.sql (creates customer_project table)
7. add_customer_id_to_projects.sql
8. update_columns.sql
8.5. add_estimate_id_to_projects.sql ⚠️ **NEW - REQUIRED!**
9. link_projects_estimates.sql
10. add_company_info_fields.sql
11. add_email_settings.sql
12. remove_unused_rates.sql
13. create_etsy_tables.sql
14. create_etsy_order_items.sql
15. add_production_tracking.sql
16. sync_project_cost_from_estimates.sql
17. sync_batch_costs_from_estimates.sql

### **Optional/Redundant (1 file - DO NOT RUN):**
18. **create_customer_project_table.sql** ⚠️ **DO NOT RUN!**
    - This is REDUNDANT with `update_schema.sql` (#6)
    - Migration #6 already creates the `customer_project` table
    - Running both will cause a conflict/error
    - **Keep file for reference only**

---

## ⚠️ CRITICAL CONFLICTS IDENTIFIED

### **Projects Table Conflicts:**
- `update_schema.sql` (#6) - **DROPS** `customer_id` column
- `add_customer_id_to_projects.sql` (#7) - **ADDS** `customer_id` column back
- `update_columns.sql` (#8) - **MODIFIES** `customer_id` to NOT NULL

**Resolution:** These must run in order 6 → 7 → 8

### **Settings Table - Multiple Modifications:**
- `add_company_info_fields.sql` - Adds 7 columns
- `add_email_settings.sql` - Adds 7 columns
- `remove_unused_rates.sql` - Removes 2 columns
- `create_etsy_tables.sql` - Adds 9 columns

**Resolution:** All are safe - they use `ADD/DROP COLUMN` and won't conflict. Run in order (10, 11, 12, 13).

---

## Quick Reference: What Each File Does

| File | Tables Modified | Action | Risk Level |
|------|----------------|--------|-----------|
| create_customers_table.sql | customers | CREATE | Low |
| create_projects_table.sql | projects | CREATE | Low |
| create_estimates_table.sql | estimates | CREATE | Low |
| create_equipment_table.sql | equipment | CREATE | Low |
| add_username_column.sql | users | ALTER ADD | Low |
| update_schema.sql | projects, customer_project | DROP/CREATE | **HIGH** |
| add_customer_id_to_projects.sql | projects | ALTER ADD | Medium |
| update_columns.sql | projects | ALTER MODIFY | Low |
| link_projects_estimates.sql | estimates | ALTER MODIFY/ADD | Low |
| add_company_info_fields.sql | settings | ALTER ADD | Low |
| add_email_settings.sql | settings | ALTER ADD | Low |
| remove_unused_rates.sql | setup | ALTER DROP | Low |
| create_etsy_tables.sql | settings, etsy_orders, etsy_sync_log | ALTER/CREATE | Low |
| create_etsy_order_items.sql | etsy_order_items, etsy_product_mappings, etsy_orders | CREATE/ALTER | Low |
| add_production_tracking.sql | projects, production_batches, inventory_transactions, etsy_order_items | ALTER/CREATE | Low |
| sync_project_cost_from_estimates.sql | projects | UPDATE | Low |
| sync_batch_costs_from_estimates.sql | production_batches | UPDATE | Low |

**Note on Migration #15 (add_production_tracking.sql):**
- `laser_time` and `mill_time` columns store time in **MINUTES** (not hours)
- `labor_hours` column stores time in **HOURS**
- This matches actual CNC machine readouts (minutes) vs timesheet format (hours)
- UI converts minutes to hours for total time calculations

---

## Safe Execution Methods

### **Method 1: IONOS Production Server (phpMyAdmin)**

Since your production server is IONOS with phpMyAdmin access:

1. **Backup Database First!**
   - Login to phpMyAdmin
   - Select database `dbs14052036`
   - Click "Export" tab
   - Select "Quick" export method
   - Click "Go" and save the SQL file
   - Name it: `backup_before_migration_2025-12-19.sql`

2. **Run Each Migration File:**
   - Open each SQL file (in order 1-12) in a text editor
   - Copy the entire contents
   - In phpMyAdmin, click "SQL" tab
   - Paste the SQL code
   - Click "Go"
   - Check for success message (green) or errors (red)
   - **If error occurs, STOP and fix before continuing!**

3. **Track Your Progress:**
   ```
   ✅ 1. create_customers_table.sql
   ✅ 2. create_projects_table.sql
   ✅ 3. create_estimates_table.sql
   ✅ 4. create_equipment_table.sql
   ✅ 5. update_schema.sql
   ✅ 1. create_customers_table.sql
   ✅ 2. create_projects_table.sql
   ✅ 3. create_estimates_table.sql
   ✅ 4. create_equipment_table.sql
   ⬜ 5. add_username_column.sql
   ✅ 6. update_schema.sql
   ✅ 7. add_customer_id_to_projects.sql
   ✅ 8. update_columns.sql
   ✅ 9. link_projects_estimates.sql
   ✅ 10. add_company_info_fields.sql
   ✅ 11. add_email_settings.sql
   ⬜ 12. remove_unused_rates.sql
   ✅ 13. create_etsy_tables.sql
   ✅ 14. create_etsy_order_items.sql
   ⬜ 15. add_production_tracking.sql
   ⬜ 16. sync_project_cost_from_estimates.sql
   ⬜ 17. sync_batch_costs_from_estimates.sql
   ```

4. **Verify After Each Migration:**
   - Check "Structure" tab to see new tables/columns
   - Look for error messages
   - Don't proceed if you see errors

---

### **Method 2: Local Development (XAMPP/PowerShell)**

Run this from XAMPP directory on your local Windows development machine:

```powershell
# Backup first!
.\mysql\bin\mysqldump.exe -u root dbs14052036 > backup_before_migration.sql

# Define migrations in exact order
$migrations = @(
    'create_customers_table.sql',           # 1
    'create_projects_table.sql',            # 2
    'create_estimates_table.sql',           # 3
    'create_equipment_table.sql',           # 4
    'add_username_column.sql',              # 5 - Adds username to users
    'update_schema.sql',                    # 6 - DROPS customer_id
    'add_customer_id_to_projects.sql',      # 7 - ADDS customer_id back
    'update_columns.sql',                   # 8 - Modifies customer_id
    'link_projects_estimates.sql',          # 9 - Estimate integration
    'add_company_info_fields.sql',          # 10 - Settings: company
    'add_email_settings.sql',               # 11 - Settings: email
    'remove_unused_rates.sql',              # 12 - Cleanup setup table
    'create_etsy_tables.sql',               # 13 - Etsy Phase 1
    'create_etsy_order_items.sql',          # 14 - Etsy Phase 2
    'add_production_tracking.sql',          # 15 - Production & Inventory
    'sync_project_cost_from_estimates.sql', # 16 - Sync project costs
    'sync_batch_costs_from_estimates.sql'   # 17 - Sync batch costs
)

$count = 1
foreach ($file in $migrations) {
    Write-Host "[$count/17] Running: $file" -ForegroundColor Yellow
    
    # Check if file exists
    if (!(Test-Path "database\$file")) {
        Write-Host "⚠️  WARNING: File not found, skipping: $file" -ForegroundColor DarkYellow
        $count++
        continue
    }
    
    # Run migration
    .\mysql\bin\mysql.exe -u root dbs14052036 < "database\$file"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ SUCCESS: $file" -ForegroundColor Green
    } else {
        Write-Host "❌ FAILED: $file" -ForegroundColor Red
        Write-Host "STOPPING - Fix error before continuing!" -ForegroundColor Red
        break
    }
    
    $count++
}

Write-Host "`nMigration complete!" -ForegroundColor Green
```

---

## Recommendation for Future

Moving forward, let's use **numbered migration files**:
- `migrations/001_create_etsy_tables.sql`
- `migrations/002_create_etsy_order_items.sql`
- `migrations/003_add_production_tracking.sql`

And create a `schema_migrations` table to track what's been applied.

---

## Files NOT Listed (Already Applied?)

These files exist but may be old/already applied:
- `dbs14052036.sql` - Full database dump (backup?)
- `remove_unused_rates.sql` - Purpose unclear
- `add_username_column.sql` - Purpose unclear
- `create_customer_project_table.sql` - Covered by update_schema.sql

Check if these are needed before running.
