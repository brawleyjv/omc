# Project-Estimate Integration Plan

**Goal**: Seamlessly integrate project creation with the estimate system, allowing projects to be created with or without customers.

---

## Current State Analysis

### Projects Table
- Tracks: `laser_time`, `router_time`, `labor_hours`
- Missing: Material tracking, estimate linkage
- Issue: Time estimates are separate from cost estimates

### Estimates Table
- Tracks: Complete materials, labor, machine time, custom items
- Requires: Customer information
- Has: Comprehensive pricing calculations

---

## Proposed Solution

### Database Changes

#### 1. Add estimate_id to projects table
```sql
ALTER TABLE projects ADD COLUMN estimate_id INT NULL;
ALTER TABLE projects ADD CONSTRAINT fk_project_estimate 
    FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE SET NULL;
ALTER TABLE projects ADD INDEX idx_estimate_id (estimate_id);
```

#### 2. Make customer_id optional in estimates
```sql
ALTER TABLE estimates MODIFY COLUMN customer_id INT NULL;
ALTER TABLE estimates ADD COLUMN is_project_estimate BOOLEAN DEFAULT FALSE;
ALTER TABLE estimates ADD COLUMN project_name VARCHAR(255) NULL;
```

#### 3. Track estimate source
This allows us to distinguish between:
- Customer estimates (traditional workflow)
- Project estimates (no customer, internal planning)
- Project-to-customer estimates (started as project, customer added later)

---

## Workflow Options

### Option 1: Enhanced "Add Project" Page (Recommended)
**Flow**: Create Project → Auto-Generate Estimate → Link Both

**Advantages**:
- Single-page experience
- All information collected upfront
- Automatic estimate creation
- Can add customer later if needed

**User Experience**:
1. User goes to "Add Project"
2. Page has THREE sections:
   - **Basic Info**: Name, dates, description
   - **Time Estimates**: Laser, router, labor (existing)
   - **Materials** (NEW): Select materials from database with quantities
3. On "Create Project" button click:
   - Create project record
   - Auto-create linked estimate with materials + time
   - Redirect to estimate page (pre-filled) for review/refinement
4. User can add customer later from estimate view

### Option 2: Two-Step Process
**Flow**: Create Project → Navigate to "Create Estimate from Project"

**Advantages**:
- Keeps project creation simple
- Estimates optional (not all projects need pricing)
- Clear separation of concerns

**User Experience**:
1. Create project with time estimates (current flow)
2. From project list, click "Create Estimate" button
3. Opens estimate form pre-filled with project data
4. User adds materials and finalizes estimate

### Option 3: Hybrid Approach (BEST)
**Flow**: Simple project creation, with optional "Quick Estimate" checkbox

**Advantages**:
- Flexibility for different use cases
- Doesn't force estimate creation
- Clean UX for both workflows

**User Experience**:
1. Add Project page has checkbox: "Create estimate now?"
2. If checked: Material selection appears (dynamic form)
3. If unchecked: Traditional simple project creation
4. Either way, "Create Estimate" button appears in project list

---

## Implementation Plan

### Phase 1: Database Schema (30 minutes)
- [ ] Create SQL migration file
- [ ] Add estimate_id to projects
- [ ] Make customer_id optional in estimates
- [ ] Add is_project_estimate flag
- [ ] Test migration on development database

### Phase 2: Update Models (1 hour)
- [ ] Update ProjectModel to handle estimate_id
- [ ] Update EstimateModel to allow null customer_id
- [ ] Add method: `createEstimateFromProject($projectId)`
- [ ] Add method: `linkProjectToEstimate($projectId, $estimateId)`
- [ ] Add method: `getEstimateForProject($projectId)`

### Phase 3: Enhance Add Project Page (2-3 hours)
- [ ] Add checkbox "Create estimate with this project?"
- [ ] Add collapsible "Materials" section (hidden by default)
- [ ] Use JavaScript to show/hide materials section
- [ ] Material selection uses same UI as create_new_estimate.php
- [ ] Submit creates both project AND estimate if checked

### Phase 4: Update Project List View (1 hour)
- [ ] Add "Estimate" column showing estimate number if linked
- [ ] Add "Create Estimate" button for projects without estimates
- [ ] Add "View Estimate" button for projects with estimates
- [ ] Show estimate status badge (Draft, Sent, Approved, etc.)

### Phase 5: Create "Quick Estimate from Project" Page (2 hours)
- [ ] New file: `Views/estimate/create_from_project.php`
- [ ] Accept project_id parameter
- [ ] Pre-fill time estimates from project
- [ ] Pre-fill project name
- [ ] Allow material selection
- [ ] On submit: create estimate and link to project

### Phase 6: Update Estimate Views (1 hour)
- [ ] Show linked project name in estimate header
- [ ] Add "View Project" button if linked
- [ ] Allow customer to be added later to project estimates
- [ ] Show "Project Estimate" badge if no customer

### Phase 7: Navigation & UX (1 hour)
- [ ] Add "Estimates" tab to project detail view
- [ ] Add "Project" tab to estimate detail view
- [ ] Update breadcrumbs for navigation
- [ ] Add conversion option: Project Estimate → Customer Estimate

---

## Recommended Workflow (Hybrid Approach)

### For Internal Projects (No Customer Yet)
```
1. Add Project (basic info + time estimates)
2. Check "Create estimate now?" checkbox
3. Add materials in collapsible section
4. Click "Create Project & Estimate"
5. System creates both, links them
6. Redirects to estimate for review
7. Later: Add customer to estimate when order confirmed
```

### For Customer Projects (Known Customer)
```
1. Create Customer (if new)
2. Add Project with customer name
3. Check "Create estimate now?"
4. Add materials
5. Click "Create Project & Estimate"
6. In estimate page, select customer from dropdown
7. Send estimate to customer
```

### For Simple Projects (Just Tracking)
```
1. Add Project (basic info + time estimates)
2. DON'T check "Create estimate"
3. Click "Create Project"
4. Project saved, no estimate created
5. Later: Click "Create Estimate" from project list if needed
```

---

## Enhanced Features

### Smart Material Suggestions
When creating estimate from project:
- Analyze project name/description
- Suggest commonly used materials for similar projects
- Show recently used materials
- Allow saving material "templates" for project types

### Estimate Status on Projects
Show project list with estimate info:
```
Project Name         | Due Date   | Estimate    | Status
--------------------|-----------|-------------|----------
Ammo Box            | 2024-01-15| EST-2024-001| Approved
Deer Coaster        | 2024-01-20| (No estimate)| -
Custom Sign         | 2024-01-25| EST-2024-002| Draft
```

### Conversion Path
Allow converting project estimates to customer estimates:
1. Project estimate created (no customer)
2. Customer places order
3. Click "Convert to Customer Estimate"
4. Select customer from dropdown
5. Estimate gains customer_id, customer gets notified

---

## File Structure

```
database/
└── link_projects_estimates.sql       # Schema changes

Models/
├── ProjectModel.php                  # Add estimate methods
└── EstimateModel.php                 # Allow null customer

Views/
├── projects/
│   ├── add_project.php              # Enhanced with materials section
│   ├── list_projects.php            # Show estimate status
│   └── view_project.php             # Show linked estimate
└── estimate/
    ├── create_from_project.php      # Quick estimate from project
    └── convert_to_customer.php      # Convert project→customer estimate
```

---

## UI Mockup: Enhanced Add Project Page

```
┌─────────────────────────────────────────────┐
│  Create New Project                         │
├─────────────────────────────────────────────┤
│                                             │
│  BASIC INFORMATION                          │
│  Project Name: [_______________]            │
│  Customer: [_______________] (optional)     │
│  Design Date: [__/__/____]                  │
│  Due Date: [__/__/____]                     │
│                                             │
│  TIME ESTIMATES                             │
│  Laser Time: [___] minutes                  │
│  Router Time: [___] minutes                 │
│  Labor Hours: [___] hours                   │
│                                             │
│  ☐ Create estimate with this project?      │
│  ↓ (if checked, section below appears)     │
│                                             │
│  MATERIALS (Optional)                       │
│  ┌─────────────────────────────────────┐   │
│  │ Material      | Qty | Unit | Cost   │   │
│  │ Oak Board     | 5   | ea   | $12.50 │   │
│  │ [+ Add Material]                    │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  DESCRIPTION                                │
│  [_____________________________________]    │
│  [_____________________________________]    │
│                                             │
│  [Create Project] [Create Project & Estimate]│
└─────────────────────────────────────────────┘
```

---

## Benefits of This Approach

✅ **Flexibility**: Works for projects with or without customers
✅ **No Duplicate Data**: Time estimates entered once, used in estimate
✅ **Seamless Integration**: Projects and estimates properly linked
✅ **Progressive Enhancement**: Can add customer later when order comes in
✅ **Better Tracking**: See estimate status from project list
✅ **Internal Planning**: Create projects for inventory/R&D without customer
✅ **Conversion Path**: Easy to convert project estimate → customer estimate

---

## Migration Path for Existing Data

```sql
-- Link existing projects to estimates by matching names
UPDATE projects p
JOIN estimates e ON p.project_name = e.project_name
SET p.estimate_id = e.id
WHERE p.estimate_id IS NULL;
```

---

## Timeline Estimate

| Phase | Time | Complexity |
|-------|------|------------|
| Phase 1: Database | 30 min | Low |
| Phase 2: Models | 1 hour | Medium |
| Phase 3: Add Project Enhancement | 2-3 hours | Medium |
| Phase 4: List View Update | 1 hour | Low |
| Phase 5: Quick Estimate Page | 2 hours | Medium |
| Phase 6: Estimate Views | 1 hour | Low |
| Phase 7: Navigation & UX | 1 hour | Low |

**Total**: 8.5-10.5 hours

---

## Next Steps

1. Review this plan and provide feedback
2. Decide on preferred workflow (Option 1, 2, or 3)
3. Create database migration file
4. Begin Phase 1 implementation
5. Test with sample projects

---

**Questions to Answer:**
1. Should ALL projects require estimates, or keep it optional?
2. Do you want material suggestions based on project name/type?
3. Should we track estimate revisions for projects?
4. Do you want batch operations (create multiple project estimates at once)?
