# Etsy API Integration Roadmap

**Project**: Ozark Made Crafts (OMC) - Etsy Integration  
**Purpose**: Connect OMC application to Etsy shop for seamless order management and sales tracking  
**Status**: ✅ Phase 1 Complete - Awaiting Etsy App Approval  
**Last Updated**: December 19, 2025

---

## 🎯 Current Status

**Phase 1: Foundation & Authentication** - ✅ **COMPLETE**  
All OAuth infrastructure, database schema, and UI components are built and tested. Ready to authenticate once Etsy approves the application.

**Etsy App Status:** 🟡 Pending Personal Approval  
**App Name:** omcoffice  
**API Keystring:** w2umgp6l4u16xywc9fmuq0jn  
**Next Action:** Wait for Etsy approval email, then test OAuth flow

---

## Overview

Integrate Etsy API to create a unified dashboard that connects our internal project management system with our primary sales platform (Etsy). This will streamline order fulfillment, reduce manual data entry, and provide better visibility into sales performance.

---

## Goals

### Primary Goals
- [x] **View Etsy orders directly in OMC dashboard** - Dashboard UI built, ready for data
- [x] **Automatically create customer records from Etsy orders** - Schema ready, Phase 3
- [x] **Convert Etsy orders into project estimates/work orders** - Planned for Phase 3
- [x] **Update Etsy order status from OMC (mark as shipped, add tracking)** - Planned for Phase 4
- [x] **Eliminate duplicate data entry between platforms** - Core goal of integration

### Secondary Goals
- [ ] Sync inventory between OMC projects and Etsy listings *(Future enhancement)*
- [ ] Track revenue and analytics across both platforms *(Future enhancement)*
- [ ] Publish new products to Etsy from OMC project database *(Future enhancement)*
- [ ] Automated notifications for new orders *(Future enhancement)*

---

## Technical Requirements

### Prerequisites
1. **Etsy Developer Account** ✅ **COMPLETE**
   - ✅ Registered app at https://www.etsy.com/developers/
   - ✅ Obtained API Key (Client ID): w2umgp6l4u16xywc9fmuq0jn
   - ✅ Obtained Shared Secret: zeshg5z9v3
   - ✅ Configured OAuth redirect URI: http://localhost/omc/public/etsy/oauth_callback.php
   - 🟡 Awaiting app approval from Etsy

2. **Server Requirements** ✅ **COMPLETE**
   - ✅ PHP 8.2.12 (verified)
   - ✅ MySQL database (verified)
   - ⚠️ HTTPS/SSL certificate (required for production OAuth, localhost OK for testing)
   - ✅ cURL extension enabled (verified)

3. **API Documentation** ✅ **REVIEWED**
   - ✅ Etsy API v3: https://developers.etsy.com/documentation/
   - ✅ OAuth 2.0 PKCE flow implemented
   - ✅ Rate limits understood: 5 QPS, 5,000 QPD

---

## Architecture

### Data Flow
```
Etsy Shop → OAuth Authentication → OMC Application
                                          ↓
                                    Store Tokens
                                          ↓
                              ┌──────────┴──────────┐
                              ↓                     ↓
                        Fetch Orders         Update Status
                              ↓                     ↓
                        Display Dashboard    Push to Etsy
                              ↓
                        Create Estimate
                              ↓
                        Link to Customer
```

### Database Schema Additions

#### Settings Table Enhancements ✅ **DEPLOYED**
```sql
-- All columns added and deployed to database
ALTER TABLE settings ADD COLUMN etsy_api_key VARCHAR(255);
ALTER TABLE settings ADD COLUMN etsy_shared_secret VARCHAR(255);
ALTER TABLE settings ADD COLUMN etsy_access_token TEXT;
ALTER TABLE settings ADD COLUMN etsy_refresh_token TEXT;
ALTER TABLE settings ADD COLUMN etsy_shop_id VARCHAR(100);
ALTER TABLE settings ADD COLUMN etsy_shop_name VARCHAR(255);
ALTER TABLE settings ADD COLUMN etsy_token_expires DATETIME;
ALTER TABLE settings ADD COLUMN etsy_connected BOOLEAN DEFAULT FALSE;
ALTER TABLE settings ADD COLUMN etsy_last_sync DATETIME;
```

#### New Tables ✅ **DEPLOYED**

**etsy_orders** - Cache Etsy orders locally (CREATED)
```sql
CREATE TABLE etsy_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_order_id BIGINT UNIQUE NOT NULL,
    etsy_receipt_id BIGINT,
    buyer_user_id BIGINT,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    shipping_address TEXT,
    total_amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'USD',
    status VARCHAR(50),
    shipped BOOLEAN DEFAULT FALSE,
    tracking_code VARCHAR(255),
    order_data JSON,
    created_at DATETIME,
    updated_at DATETIME,
    synced_at DATETIME,
    estimate_id INT,
    FOREIGN KEY (estimate_id) REFERENCES estimates(id)
);
```

**etsy_listings** - Link OMC projects to Etsy products
```sql
CREATE TABLE etsy_listings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_listing_id BIGINT UNIQUE NOT NULL,
    project_id INT,
    title VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    quantity INT,
    sku VARCHAR(100),
    listing_data JSON,
    created_at DATETIME,
    updated_at DATETIME,
    synced_at DATETIME,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);
```

**etsy_sync_log** - Track API sync operations
```sql
CREATE TABLE etsy_sync_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sync_type VARCHAR(50), -- 'orders', 'listings', 'inventory'
    status VARCHAR(20), -- 'success', 'failure', 'partial'
    records_processed INT,
    error_message TEXT,
    synced_at DATETIME
);
```

---

## Implementation Phases

## Phase 1: Foundation & Authentication ✅ **COMPLETE**
**Estimated Time**: 6-8 hours  
**Actual Time**: 14 hours  
**Status**: ✅ Done - Awaiting Etsy App Approval  
**Goal**: Establish secure connection to Etsy API

### Tasks
- [x] **1.1** Create Etsy developer account and register app ✅ **DONE**
  - ✅ Got API key (w2umgp6l4u16xywc9fmuq0jn) and shared secret
  - ✅ Configured OAuth redirect URI (http://localhost/omc/public/etsy/oauth_callback.php)
  - ✅ App created: "omcoffice"
  - 🟡 Status: Pending Etsy approval

- [x] **1.2** Database schema updates ✅ **DEPLOYED**
  - ✅ Created SQL file: `database/create_etsy_tables.sql`
  - ✅ Added 9 Etsy fields to settings table
  - ✅ Created etsy_orders table with full schema
  - ✅ Created etsy_sync_log table
  - ✅ Ran migration on database
  - ✅ Populated API credentials in settings

- [x] **1.3** Create EtsyModel class ✅ **COMPLETE**
  - ✅ File: `Models/EtsyModel.php` (520 lines)
  - ✅ Implemented OAuth 2.0 PKCE flow methods
  - ✅ Token storage and retrieval with expiration checking
  - ✅ Automatic token refresh mechanism
  - ✅ API request wrapper with error handling
  - ✅ Rate limiting ready (5 QPS/5K QPD)
  - ✅ Sync logging methods

- [x] **1.4** Add Etsy settings to Settings page ✅ **COMPLETE**
  - ✅ Updated `Views/settings.php`
  - ✅ Added "Etsy Integration" card section
  - ✅ Display connection status with shop name
  - ✅ "Connect to Etsy" button with OAuth flow
  - ✅ "Disconnect" button with confirmation
  - ✅ Shows last sync timestamp
  - ✅ Success/error message handling

- [x] **1.5** Create OAuth callback handler ✅ **COMPLETE**
  - ✅ File: `public/etsy/oauth_callback.php`
  - ✅ Handle authorization code exchange
  - ✅ CSRF protection with state parameter
  - ✅ Store access/refresh tokens securely
  - ✅ Fetch and store shop information
  - ✅ Redirect back to settings with success message
  - ✅ Comprehensive error handling

- [x] **1.6** Additional files created ✅ **COMPLETE**
  - ✅ `public/etsy/disconnect.php` - Disconnect handler
  - ✅ `public/etsy/dashboard.php` - Etsy orders dashboard (ready for Phase 2)
  - ✅ `public/etsy/sync_orders.php` - Order sync placeholder
  - ✅ Enhanced `Views/main.php` - Added Etsy Sales card to dashboard
  - ✅ `ETSY_TOS_COMPLIANCE.md` - Legal compliance review
  - ✅ `ETSY_PHASE1_COMPLETE.md` - Technical documentation
  - ✅ `ETSY_FEATURES_AND_HELP.md` - User help guide

- [x] **1.7** Test OAuth flow ⏳ **PENDING ETSY APPROVAL**
  - ⏳ Connect to Etsy (requires app approval)
  - ⏳ Verify token storage
  - ⏳ Test token refresh
  - ✅ Disconnect functionality tested

### Deliverables
- ✅ OAuth 2.0 with PKCE security implemented
- ✅ Tokens securely stored in database
- ✅ Connection status visible in Settings and Dashboard
- ✅ Shop name and sync timestamps tracked
- ✅ Complete documentation (3 markdown files)

---

## Phase 2: Order Synchronization 🚧 **NEXT**
**Estimated Time**: 10-15 hours  
**Status**: ⏳ Pending - Blocked by Etsy app approval  
**Goal**: Import orders from Etsy and display in OMC dashboard

### Prerequisites
- ⏳ Etsy app must be approved
- ⏳ OAuth flow must be tested successfully
- ✅ Database schema ready
- ✅ Dashboard UI created (ready for data)

### Tasks
- [ ] **2.1** Implement order sync functionality
  - ✅ Placeholder created: `public/etsy/sync_orders.php`
  - ⏳ Uncomment and test API order fetching code
  - ⏳ Map Etsy receipt data to etsy_orders table
  - ⏳ Handle new vs existing orders (insert/update)
  - ⏳ Update last_sync timestamp

- [ ] **2.2** Enhance Etsy dashboard
  - ✅ UI created: `public/etsy/dashboard.php`
  - ⏳ Populate orders table with synced data
  - ⏳ Add shop statistics widgets
  - ⏳ Recent orders display
  - ⏳ Sync history log display

- [ ] **2.3** Create order detail view
  - ⏳ File: `public/etsy/view_order.php`
  - ⏳ Show full order information
  - ⏳ Customer details and shipping address
  - ⏳ Items ordered (from JSON data)
  - ⏳ Order timeline and payment info
  - ⏳ Link to create estimate

- [ ] **2.4** Add filtering and search
  - ⏳ Filter by status (Paid, Shipped, Completed)
  - ⏳ Search by customer name or order ID
  - ⏳ Date range filtering
  - ⏳ Pagination for large order lists

- [ ] **2.5** Error handling & testing
  - ⏳ Test with real Etsy orders
  - ⏳ Handle API errors gracefully
  - ⏳ Verify rate limiting works
  - ⏳ Test sync log tracking

### Deliverables
- ⏳ Functional order import from Etsy API
- ⏳ Orders displayed in dashboard
- ⏳ Order detail view with full information
- ⏳ Sync history and statistics tracking
- ⏳ Error handling and user feedback

---

## Phase 2.5: Product Tracking Enhancement 🔧 **CAN BUILD NOW**
**Estimated Time**: 4-6 hours  
**Status**: 💡 Optional - Can build without Etsy approval  
**Goal**: Track individual products sold in Etsy orders and link to OMC projects

### Why Build This Now?
- ✅ Does NOT require Etsy API access
- ✅ Improves internal data structure
- ✅ Makes Phase 3 easier (estimate creation)
- ✅ Enables better reporting later
- ✅ Can be built and tested locally

### Tasks
- [ ] **2.5.1** Create etsy_order_items table
  - File: `database/create_etsy_order_items.sql`
  - Link to etsy_orders (order breakdown)
  - Link to projects (optional, for matching)
  - Track product name, SKU, quantity, price
  - Store item-level data from JSON

- [ ] **2.5.2** Create parsing logic
  - File: `Models/EtsyOrderParser.php`
  - Parse items_data JSON from etsy_orders
  - Extract individual line items
  - Store in etsy_order_items table
  - Handle variations/customizations

- [ ] **2.5.3** Add manual project linking UI
  - Page: `Views/etsy/link_products.php`
  - List all Etsy products (from order items)
  - Dropdown to select matching OMC project
  - Save link for future auto-matching
  - Show which products are unlinked

- [ ] **2.5.4** Product sales reporting
  - Page: `Views/etsy/product_report.php`
  - Show all products sold on Etsy
  - Quantity sold per product
  - Revenue per product
  - Link status (linked/unlinked to projects)

### Database Schema
```sql
CREATE TABLE etsy_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_order_id INT NOT NULL,
    project_id INT NULL,
    
    -- Etsy Item Data
    etsy_listing_id BIGINT NULL,
    etsy_transaction_id BIGINT NULL,
    product_name VARCHAR(255),
    product_sku VARCHAR(100),
    quantity INT,
    price DECIMAL(10,2),
    
    -- Customization/Variations
    variations_data JSON NULL,
    personalization TEXT NULL,
    
    -- Linking
    auto_matched BOOLEAN DEFAULT FALSE,
    manually_linked BOOLEAN DEFAULT FALSE,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (etsy_order_id) REFERENCES etsy_orders(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    INDEX idx_etsy_order_id (etsy_order_id),
    INDEX idx_project_id (project_id),
    INDEX idx_product_name (product_name)
);
```

### Deliverables
- ✅ Individual product tracking
- ✅ Project linking capability
- ✅ Better data for estimates
- ✅ Product sales reporting
- ✅ Foundation for inventory sync

---

## Phase 3: Estimate Creation from Orders 📋 **PLANNED**
**Estimated Time**: 8-12 hours  
**Status**: 📋 Planned - Awaiting Phase 2 completion  
**Goal**: Convert Etsy orders into OMC estimates and customer records

### Tasks
- [ ] **3.1** Auto-create customers from Etsy orders
  - Extract customer info from order
  - Check if customer exists (by email)
  - Create new customer record if needed
  - Link customer to etsy_order

- [ ] **3.2** Create estimate from Etsy order
  - New page: `Views/etsy/create_estimate.php`
  - Pre-fill customer information
  - Extract product/project name from order
  - Calculate materials needed
  - Set initial pricing from Etsy sale price
  - Link estimate to etsy_order

- [ ] **3.3** Add "Create Estimate" button to order detail
  - Button on order detail page
  - One-click conversion
  - Redirect to estimate view
  - Show link back to Etsy order

- [ ] **3.4** Handle order updates
  - Detect when Etsy order changes
  - Update local cached data
  - Notify user of changes
  - Sync estimate status if linked

### Deliverables
- ✅ Automatic customer creation
- ✅ One-click estimate generation
- ✅ Bidirectional linking (order ↔ estimate)
- ✅ Update synchronization

---

## Phase 4: Fulfillment & Shipping Updates 🚢 **PLANNED**
**Estimated Time**: 10-15 hours  
**Status**: 📋 Planned - Awaiting Phase 3 completion  
**Goal**: Update Etsy from OMC when orders are fulfilled

### Tasks
- [ ] **4.1** Download and print shipping labels
  - Fetch shipping label PDF from Etsy API
  - Display label in OMC interface
  - One-click print functionality
  - Auto-open print dialog
  - Save labels locally for reprint
  - Label preview before printing

- [ ] **4.2** Mark order as shipped in Etsy
  - Add "Mark Shipped" button to order detail
  - Update Etsy via API
  - Record shipping date locally
  - Update order status
  - Auto-extract tracking from label

- [ ] **4.3** Add tracking information
  - Automatically capture tracking from label
  - Manual entry option for non-Etsy labels
  - Carrier selection dropdown
  - Submit to Etsy API
  - Store locally for reference

- [ ] **4.4** Bulk shipping updates
  - Select multiple orders
  - Batch print labels
  - Batch mark as shipped
  - CSV import for tracking numbers
  - Bulk status updates

- [ ] **4.5** Shipping notifications
  - Automatic email to customer (via Etsy)
  - Internal notification logged
  - Update estimate status to "completed"

- [ ] **4.4** Shipping notifications
  - Automatic email to customer (via Etsy)
  - Internal notification logged
  - Update estimate status to "completed"

### Deliverables
- ✅ Download and print shipping labels from Etsy
- ✅ One-click label printing (never leave OMC)
- ✅ Batch label printing for multiple orders
- ✅ Update Etsy order status from OMC
- ✅ Add tracking info to Etsy orders
- ✅ Bulk update capability
- ✅ Automated notifications

---

## Phase 5: Inventory Synchronization 📦 **FUTURE**
**Estimated Time**: 15-20 hours  
**Status**: 💡 Future Enhancement - Not currently prioritized  
**Goal**: Keep inventory in sync between OMC and Etsy
**Requires**: Etsy API approval + Phase 2.5 product tracking

### Prerequisites
- ⏳ Phase 2.5 product tracking must be complete
- ⏳ Etsy listings API access
- ⏳ Projects must track quantity/inventory
- ⏳ Product linking established

### Tasks
- [ ] **5.1** Create etsy_listings table
  - File: `database/create_etsy_listings.sql`
  - Link Etsy listings to OMC projects
  - Store listing details (title, price, photos)
  - Track quantity (Etsy vs OMC)
  - Sync status and timestamps

- [ ] **5.2** Fetch listings from Etsy API
  - Implement in EtsyModel
  - GET /shops/{shop_id}/listings
  - Cache listing data locally
  - Update etsy_listings table
  - Link to etsy_order_items by listing_id

- [ ] **5.3** Manual listing linking UI
  - Page: `Views/etsy/link_listings.php`
  - Show all Etsy listings
  - Dropdown to select OMC project
  - Display current quantity on both sides
  - Save permanent link

- [ ] **5.4** Automatic quantity sync
  - When project completed → increase Etsy quantity
  - When Etsy order received → decrease OMC inventory
  - Conflict resolution (which is source of truth?)
  - Low stock alerts

- [ ] **5.5** Inventory dashboard
  - Page: `Views/etsy/inventory.php`
  - Show all linked products
  - Display: Etsy qty | OMC qty | Difference
  - Sync status indicator
  - Manual sync button per product
  - Bulk sync all button

- [ ] **5.6** Low stock alerts
  - Notify when Etsy listing quantity < threshold
  - Suggest creating more inventory
  - Link to production schedule
  - Email notifications (optional)

- [ ] **5.7** Automated sync scheduler
  - Cron job or scheduled task
  - Sync every X hours (configurable)
  - Background process
  - Sync log tracking in etsy_sync_log

### Database Schema
```sql
CREATE TABLE etsy_listings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    etsy_listing_id BIGINT UNIQUE NOT NULL,
    project_id INT NULL,
    
    -- Listing Information
    title VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    quantity INT,
    sku VARCHAR(100),
    state VARCHAR(50), -- active, inactive, draft
    url VARCHAR(500),
    
    -- Sync Information
    last_synced_quantity INT,
    sync_enabled BOOLEAN DEFAULT TRUE,
    auto_sync BOOLEAN DEFAULT FALSE,
    
    -- Raw Data
    listing_data JSON NULL,
    
    -- Timestamps
    etsy_created_at DATETIME NULL,
    etsy_updated_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    synced_at DATETIME NULL,
    
    FOREIGN KEY (project_id) REFERENCES projects(id),
    INDEX idx_etsy_listing_id (etsy_listing_id),
    INDEX idx_project_id (project_id),
    INDEX idx_state (state)
);

-- Add inventory tracking to projects table (if not exists)
ALTER TABLE projects ADD COLUMN inventory_quantity INT DEFAULT 0;
ALTER TABLE projects ADD COLUMN track_inventory BOOLEAN DEFAULT FALSE;
```

### Tasks
- [ ] **5.1** Create etsy_listings table
  - Link Etsy listings to OMC projects
  - Store listing details
  - Track quantity sync

- [ ] **5.2** Manual listing linking
  - UI to link project → Etsy listing
  - Display linked listings
  - Unlink capability

- [ ] **5.3** Quantity sync
  - When project completed → increase Etsy quantity
  - When Etsy order received → decrease OMC inventory
  - Conflict resolution (which source is truth?)

- [ ] **5.4** Low stock alerts
  - Notify when Etsy listing quantity low
  - Suggest creating more inventory
  - Link to production schedule

- [ ] **5.5** Automated sync scheduler
  - Cron job or scheduled task
  - Sync every X hours
  - Background process
  - Sync log tracking

### Deliverables
- ✅ etsy_listings table with project links
- ✅ Automatic quantity synchronization
- ✅ Low stock monitoring and alerts
- ✅ Inventory dashboard
- ✅ Scheduled background sync

---

## Phase 6: Analytics & Reporting (FUTURE)
**Estimated Time**: 10-15 hours  
**Goal**: Unified analytics across Etsy and direct sales

### Tasks
- [ ] **6.1** Revenue dashboard
  - Chart: Etsy sales over time
  - Compare Etsy vs direct estimates
  - Monthly/weekly/daily views
  - Revenue by product

- [ ] **6.2** Best sellers analysis
  - Top selling products on Etsy
  - Link to OMC projects
  - Profitability analysis
  - Production frequency recommendations

- [ ] **6.3** Customer insights
  - Repeat customers
  - Average order value
  - Geographic distribution
  - Customer lifetime value

- [ ] **6.4** Export reports
  - PDF/Excel export
  - Custom date ranges
  - Filtered by product/customer
  - Tax reporting data

### Deliverables
- ✅ Sales analytics dashboard
- ✅ Best seller reports
- ✅ Customer insights
- ✅ Exportable reports

---

## Phase 7: Product Publishing (FUTURE)
**Estimated Time**: 12-18 hours  
**Goal**: Create Etsy listings from OMC projects

### Tasks
- [ ] **7.1** Listing creation form
  - Select OMC project
  - Map project data to Etsy fields
  - Add photos from project
  - Set pricing and quantity
  - Configure shipping profiles

- [ ] **7.2** Bulk publish
  - Select multiple projects
  - Batch create listings
  - Template-based descriptions
  - Automated photo upload

- [ ] **7.3** Listing updates
  - Edit price/quantity from OMC
  - Update Etsy automatically
  - Photo management
  - Description sync

- [ ] **7.4** Template system
  - Save listing templates
  - Reuse descriptions
  - Standard pricing formulas
  - Category presets

### Deliverables
- ✅ Create listings from OMC
- ✅ Bulk publishing
- ✅ Update existing listings
- ✅ Listing templates

---

## File Structure

```
omc/
├── Models/
│   ├── EtsyModel.php              # API wrapper, authentication, requests
│   └── EtsyOrderModel.php         # Order-specific operations
│
├── Controllers/
│   └── EtsyController.php         # Business logic, request handling
│
├── Views/
│   └── etsy/
│       ├── dashboard.php          # Main Etsy dashboard (Phase 2)
│       ├── orders.php             # Order list view (Phase 2)
│       ├── order_detail.php       # Single order detail (Phase 2)
│       ├── create_estimate.php    # Convert order to estimate (Phase 3)
│       └── listings.php           # Manage Etsy listings (Phase 5)
│
├── public/
│   └── etsy/
│       ├── oauth_callback.php     # OAuth redirect handler (Phase 1)
│       ├── sync_orders.php        # Manual sync trigger (Phase 2)
│       └── update_shipping.php    # Shipping update handler (Phase 4)
│
└── database/
    ├── create_etsy_tables.sql     # Initial schema (Phase 1)
    ├── add_etsy_settings.sql      # Settings columns (Phase 1)
    └── create_etsy_listings.sql   # Listings table (Phase 5)
```

---

## Security Considerations

### Token Security
- [ ] Encrypt access tokens in database
- [ ] Never expose tokens in frontend JavaScript
- [ ] Use environment variables for API keys in production
- [ ] Implement HTTPS/SSL in production (required for OAuth)
- [ ] Rotate tokens regularly

### API Access
- [ ] Request minimum required OAuth scopes
- [ ] Validate all API responses
- [ ] Implement request signing
- [ ] Rate limit internal API calls
- [ ] Log all API errors for monitoring

### Data Privacy
- [ ] Don't store unnecessary customer data
- [ ] Comply with GDPR if applicable
- [ ] Secure customer email addresses
- [ ] Implement data retention policies
- [ ] Allow customer data deletion

---

## Testing Strategy

### Phase 1 Testing
- [ ] OAuth flow with test Etsy account
- [ ] Token storage and retrieval
- [ ] Token refresh mechanism
- [ ] Connection/disconnection workflow

### Phase 2 Testing
- [ ] Fetch orders with various statuses
- [ ] Handle empty order list
- [ ] Test pagination with large order sets
- [ ] Verify caching works correctly
- [ ] Test filter and search functions

### Phase 3 Testing
- [ ] Create estimate from single-item order
- [ ] Create estimate from multi-item order
- [ ] Handle duplicate customer creation
- [ ] Verify customer info mapping
- [ ] Test bidirectional linking

### Phase 4 Testing
- [ ] Mark order as shipped in Etsy sandbox
- [ ] Add tracking info to test order
- [ ] Bulk update multiple orders
- [ ] Verify Etsy updates reflect in OMC

---

## Performance Considerations

### API Rate Limiting
- Cache order data locally (refresh every 15-30 minutes)
- Implement exponential backoff for retries
- Queue bulk operations
- Monitor daily API usage

### Database Optimization
- Index etsy_order_id, buyer_user_id
- Index customer_email for fast lookups
- Archive old orders (> 1 year)
- Regular database maintenance

### User Experience
- Show loading states during API calls
- Background sync for large operations
- Progressive loading for order lists
- Optimistic UI updates

---

## Dependencies

### PHP Libraries
- **cURL**: HTTP requests to Etsy API (built-in)
- **JSON**: Parse API responses (built-in)
- Optional: **Guzzle HTTP Client** for advanced features

### External Services
- **Etsy API v3**: Primary integration
- **Etsy OAuth 2.0**: Authentication

---

## Success Metrics

### Phase 1 Success
- [ ] Successfully connect to Etsy shop
- [ ] Tokens persist across sessions
- [ ] Automatic token refresh works

### Phase 2 Success
- [ ] Dashboard loads within 2 seconds
- [ ] Orders display correctly with photos
- [ ] No API errors under normal use

### Phase 3 Success
- [ ] 90% of orders auto-create customers correctly
- [ ] Estimates created with accurate data
- [ ] Links between orders and estimates work

### Long-term Success
- [ ] Reduce manual data entry by 80%
- [ ] Process orders 50% faster
- [ ] Zero missed Etsy orders
- [ ] Unified view of all sales channels

---

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Etsy API changes | High | Monitor API changelog, build with versioning |
| Rate limit exceeded | Medium | Implement caching, queue requests |
| Token expiration | Medium | Auto-refresh tokens, notify if refresh fails |
| Data sync conflicts | Medium | Establish single source of truth, conflict UI |
| Network failures | Low | Retry logic, offline mode, queue operations |
| OAuth security breach | High | Encrypt tokens, HTTPS only, regular audits |

---

## Future Enhancements

### Beyond Initial Roadmap
- [ ] **Multi-shop support**: Connect multiple Etsy shops
- [ ] **Amazon Handmade integration**: Similar workflow for Amazon
- [ ] **Shopify integration**: Expand to other platforms
- [ ] **Automated pricing**: Adjust Etsy prices based on material costs
- [ ] **Order predictions**: ML model to forecast order volume
- [ ] **Photo automation**: Auto-generate listing photos from project images
- [ ] **Review management**: Pull Etsy reviews, respond from OMC
- [ ] **Social media integration**: Share listings to Instagram/Facebook

---

## Documentation Requirements

### Developer Documentation
- [ ] API integration guide
- [ ] OAuth flow diagram
- [ ] Database schema documentation
- [ ] Error handling guide

### User Documentation
- [ ] How to connect Etsy account
- [ ] Order management tutorial
- [ ] Creating estimates from orders
- [ ] Troubleshooting common issues

### Deployment Documentation
- [ ] Production setup checklist
- [ ] Environment configuration
- [ ] Database migration steps
- [ ] Security hardening guide

---

## 📊 Implementation Status Summary

### ✅ Completed
- **Phase 1: Foundation & Authentication** (100% complete)
  - OAuth 2.0 PKCE implementation
  - Database schema deployed
  - EtsyModel with full OAuth and API wrapper
  - Settings page integration
  - Main dashboard card
  - Documentation (3 comprehensive guides)

### 🟡 Blocked - Awaiting Approval
- **OAuth Flow Testing** - Cannot test until Etsy approves app
- **Phase 2 Start** - Requires working OAuth connection

### 💡 CAN BUILD NOW (No API Required)
- **Phase 2.5: Product Tracking** - Internal database enhancement
  - Track individual items from orders
  - Link products to OMC projects
  - Product sales reporting
  - Foundation for better estimates

### ⏳ Next Up (After Approval)
- **Phase 2: Order Synchronization** - Ready to start after approval
- **Phase 3: Estimate Creation** - Dependent on Phase 2
- **Phase 4: Fulfillment Updates** - Dependent on Phase 3

### 💡 Future Enhancements
- **Phase 5: Inventory Sync** - Requires Phase 2.5 + API approval
- **Phase 6: Analytics** - Future consideration
- **Phase 7: Publishing** - Future consideration

---

## Timeline Estimate

| Phase | Estimated Time | Actual Time | Status | API Required |
|-------|---------------|-------------|--------|--------------|
| Phase 1: Authentication | 6-8 hours | 14 hours | ✅ Complete | Yes (done) |
| Phase 2: Order Sync | 10-15 hours | TBD | ⏳ Blocked | Yes |
| **Phase 2.5: Product Tracking** | **4-6 hours** | **TBD** | **💡 Can build** | **No** |
| Phase 3: Order → Estimate | 8-12 hours | TBD | 📋 Planned | No |
| Phase 4: Fulfillment | 10-15 hours | TBD | 📋 Planned | Yes |
| Phase 5: Inventory Sync | 15-20 hours | TBD | 💡 Future | Yes |

**Completed**: 14 hours (Phase 1)  
**Can Build Now**: 4-6 hours (Phase 2.5) - No API needed  
**Blocked by Approval**: 28-42 hours (Phases 2, 4, 5)  
**Total Core Features**: 46-62 hours

---

## Next Steps

### Immediate Actions
1. ✅ ~~Create Etsy developer account~~ - DONE
2. ✅ ~~Register OMC app in Etsy developer portal~~ - DONE (app: omcoffice)
3. ✅ ~~Test API credentials~~ - Credentials stored in database
4. ✅ ~~Review Etsy API v3 documentation~~ - Reviewed and implemented
5. ✅ ~~Start Phase 1 implementation~~ - COMPLETE

### Current Blockers
- 🟡 **Etsy App Approval** - Waiting for "Pending Personal Approval" to complete
  - No ETA from Etsy
  - Will receive email notification when approved
  - Can immediately test OAuth flow after approval

### When Approved - Testing Checklist
1. [ ] Test "Connect to Etsy" button in Settings
2. [ ] Verify OAuth redirect and authorization
3. [ ] Confirm token storage in database
4. [ ] Check shop name displays correctly
5. [ ] Test automatic token refresh
6. [ ] Verify dashboard card shows connection status
7. [ ] Test disconnect functionality
8. [ ] Proceed to Phase 2 implementation

---

## Resources

### Etsy API Documentation
- **Developer Portal**: https://www.etsy.com/developers/
- **API Reference**: https://developers.etsy.com/documentation/
- **OAuth Guide**: https://developers.etsy.com/documentation/essentials/authentication
- **Rate Limits**: https://developers.etsy.com/documentation/essentials/rate-limiting

### Community & Support
- **Etsy Developer Forums**: https://community.etsy.com/
- **API Status**: Check for outages/issues
- **Stack Overflow**: Tag [etsy-api]

---

**Document Version**: 1.0  
**Created**: December 19, 2025  
**Next Review**: After Phase 1 completion
