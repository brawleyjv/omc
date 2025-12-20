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

---

### **Phase 2: Project-Customer Relationship Changes**

**⚠️ CONFLICT WARNING:** These three files modify the `projects` table structure in specific order!

**5. update_schema.sql** - ⚠️ **DESTRUCTIVE!** 
   - Drops and recreates projects table
   - **WARNING:** This drops `customer_id` column from projects
   - Creates `customer_project` junction table
   - Run this FIRST before other project modifications

**6. add_customer_id_to_projects.sql** - ⚠️ **CONFLICTS with #5!**
   - Adds `customer_id` back to projects table
   - Adds foreign key to customers
   - **Must run AFTER #5** to re-add the column

**7. update_columns.sql** - Modifies `customer_id` to NOT NULL
   - **Must run AFTER #6** since it requires customer_id to exist

---

### **Phase 3: Project-Estimate Integration (December 19, 2025)**

**8. link_projects_estimates.sql** - Project-estimate relationship
   - Makes `customer_name` in estimates nullable
   - Adds `is_project_estimate` column to estimates
   - ✅ **Safe to run** - only modifies estimates table

---

### **Phase 4: Settings Table Enhancements**

**9. add_company_info_fields.sql** - Company info in settings
   - Adds address, phone, email, logo fields
   - ✅ **Safe to run** - uses `ADD COLUMN IF NOT EXISTS`

**10. add_email_settings.sql** - SMTP email configuration
    - Adds SMTP settings to settings table
    - ✅ **Safe to run** - uses `ADD COLUMN IF NOT EXISTS`

---

### **Phase 5: Etsy Integration (December 19, 2025)**

**11. create_etsy_tables.sql** - Etsy OAuth and orders
    - Adds 9 etsy_* columns to settings table
    - Creates `etsy_orders` table
    - Creates `etsy_sync_log` table
    - ✅ **Safe to run** - new tables and columns

**12. create_etsy_order_items.sql** - Etsy product tracking
    - Creates `etsy_order_items` table
    - Creates `etsy_product_mappings` table
    - Adds `has_unlinked_items` to etsy_orders
    - ⚠️ **Must run AFTER #11** (requires etsy_orders table)

---

### **Phase 6: Production & Inventory Tracking (December 19, 2025)**

**13. add_production_tracking.sql** - Complete production lifecycle
    - Adds production fields to projects table (status, inventory, costs)
    - Creates `production_batches` table (includes labor_hours, laser_time, mill_time)
    - Creates `inventory_transactions` table
    - Adds `fulfilled_from_batch` to etsy_order_items
    - ✅ **Safe to run** - new tables and columns
    - **Updated Dec 19:** Includes CNC machine time tracking (laser_time, mill_time)

---

## ⚠️ CRITICAL CONFLICTS IDENTIFIED

### **Projects Table Conflicts:**
- `update_schema.sql` (line 8) - **DROPS** `customer_id` column
- `add_customer_id_to_projects.sql` (line 2) - **ADDS** `customer_id` column back
- `update_columns.sql` (line 1) - **MODIFIES** `customer_id` to NOT NULL

**Resolution:** These must run in order 5 → 6 → 7

### **Settings Table - Multiple Modifications:**
- `add_company_info_fields.sql` - Adds 7 columns
- `add_email_settings.sql` - Adds 7 columns
- `create_etsy_tables.sql` - Adds 9 columns

**Resolution:** All are safe - they use `ADD COLUMN` and won't conflict. Run in any order (9, 10, 11).

---

## Quick Reference: What Each File Does

| File | Tables Modified | Action | Risk Level |
|------|----------------|--------|-----------|
| create_customers_table.sql | customers | CREATE | Low |
| create_projects_table.sql | projects | CREATE | Low |
| create_estimates_table.sql | estimates | CREATE | Low |
| create_equipment_table.sql | equipment | CREATE | Low |
| update_schema.sql | projects, customer_project | DROP/CREATE | **HIGH** |
| add_customer_id_to_projects.sql | projects | ALTER ADD | Medium |
| update_columns.sql | projects | ALTER MODIFY | Low |
| link_projects_estimates.sql | estimates | ALTER MODIFY/ADD | Low |
| add_company_info_fields.sql | settings | ALTER ADD | Low |
| add_email_settings.sql | settings | ALTER ADD | Low |
| create_etsy_tables.sql | settings, etsy_orders, etsy_sync_log | ALTER/CREATE | Low |
| create_etsy_order_items.sql | etsy_order_items, etsy_product_mappings, etsy_orders | CREATE/ALTER | Low |
| add_production_tracking.sql | projects, production_batches, inventory_transactions, etsy_order_items | ALTER/CREATE | Low |

**Note on Migration #13 (add_production_tracking.sql):**
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
   ✅ 6. add_customer_id_to_projects.sql
   ✅ 7. update_columns.sql
   ✅ 8. link_projects_estimates.sql
   ✅ 9. add_company_info_fields.sql
   ✅ 10. add_email_settings.sql
   ✅ 11. create_etsy_tables.sql
   ✅ 12. create_etsy_order_items.sql
   ⬜ 13. add_production_tracking.sql
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
    'update_schema.sql',                    # 5 - DROPS customer_id
    'add_customer_id_to_projects.sql',      # 6 - ADDS customer_id back
    'update_columns.sql',                   # 7 - Modifies customer_id
    'link_projects_estimates.sql',          # 8 - Estimate integration
    'add_company_info_fields.sql',          # 9 - Settings: company
    'add_email_settings.sql',               # 10 - Settings: email
    'create_etsy_tables.sql',               # 11 - Etsy Phase 1
    'create_etsy_order_items.sql',          # 12 - Etsy Phase 2.5
    'add_production_tracking.sql'           # 13 - Production & Inventory
)

$count = 1
foreach ($file in $migrations) {
    Write-Host "[$count/13] Running: $file" -ForegroundColor Yellow
    
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
