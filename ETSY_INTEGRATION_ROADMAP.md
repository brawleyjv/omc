# Etsy API Integration Roadmap

**Project**: Ozark Made Crafts (OMC) - Etsy Integration  
**Purpose**: Connect OMC application to Etsy shop for seamless order management and sales tracking  
**Status**: Planning Phase  
**Last Updated**: December 19, 2025

---

## Overview

Integrate Etsy API to create a unified dashboard that connects our internal project management system with our primary sales platform (Etsy). This will streamline order fulfillment, reduce manual data entry, and provide better visibility into sales performance.

---

## Goals

### Primary Goals
- [ ] View Etsy orders directly in OMC dashboard
- [ ] Automatically create customer records from Etsy orders
- [ ] Convert Etsy orders into project estimates/work orders
- [ ] Update Etsy order status from OMC (mark as shipped, add tracking)
- [ ] Eliminate duplicate data entry between platforms

### Secondary Goals
- [ ] Sync inventory between OMC projects and Etsy listings
- [ ] Track revenue and analytics across both platforms
- [ ] Publish new products to Etsy from OMC project database
- [ ] Automated notifications for new orders

---

## Technical Requirements

### Prerequisites
1. **Etsy Developer Account**
   - Register app at https://www.etsy.com/developers/
   - Obtain API Key (Client ID)
   - Obtain Shared Secret
   - Configure OAuth redirect URI

2. **Server Requirements**
   - PHP 7.4+ (already met)
   - MySQL database (already met)
   - HTTPS/SSL certificate (required for production OAuth)
   - cURL extension enabled

3. **API Documentation**
   - Etsy API v3: https://developers.etsy.com/documentation/
   - OAuth 2.0 flow documentation
   - Rate limits: 10,000 requests/day per app

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

#### Settings Table Enhancements
```sql
ALTER TABLE settings ADD COLUMN etsy_api_key VARCHAR(255);
ALTER TABLE settings ADD COLUMN etsy_shared_secret VARCHAR(255);
ALTER TABLE settings ADD COLUMN etsy_access_token TEXT;
ALTER TABLE settings ADD COLUMN etsy_refresh_token TEXT;
ALTER TABLE settings ADD COLUMN etsy_shop_id VARCHAR(100);
ALTER TABLE settings ADD COLUMN etsy_token_expires DATETIME;
ALTER TABLE settings ADD COLUMN etsy_connected BOOLEAN DEFAULT FALSE;
```

#### New Tables

**etsy_orders** - Cache Etsy orders locally
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

## Phase 1: Foundation & Authentication (PRIORITY)
**Estimated Time**: 6-8 hours  
**Goal**: Establish secure connection to Etsy API

### Tasks
- [ ] **1.1** Create Etsy developer account and register app
  - Get API key and shared secret
  - Configure OAuth redirect URI
  - Test credentials in Postman/API testing tool

- [ ] **1.2** Database schema updates
  - Create SQL file: `database/create_etsy_tables.sql`
  - Add Etsy fields to settings table
  - Create etsy_orders table
  - Create etsy_sync_log table
  - Run migration on development database

- [ ] **1.3** Create EtsyModel class
  - File: `Models/EtsyModel.php`
  - Implement OAuth 2.0 flow methods
  - Token storage and retrieval
  - Token refresh mechanism
  - Basic API request wrapper

- [ ] **1.4** Add Etsy settings to Settings page
  - Update `Views/settings.php`
  - Add "Etsy Integration" card section
  - Display connection status
  - Add "Connect to Etsy" button
  - Show shop name when connected

- [ ] **1.5** Create OAuth callback handler
  - File: `public/etsy/oauth_callback.php`
  - Handle authorization code exchange
  - Store access/refresh tokens
  - Redirect back to settings with success message

- [ ] **1.6** Test OAuth flow end-to-end
  - Connect to Etsy
  - Verify token storage
  - Test token refresh
  - Handle disconnection

### Deliverables
- ✅ Working OAuth connection to Etsy
- ✅ Tokens securely stored in database
- ✅ Connection status visible in settings
- ✅ Documentation: Etsy setup instructions

---

## Phase 2: Dashboard & Order Viewing
**Estimated Time**: 10-15 hours  
**Goal**: Display Etsy orders in OMC

### Tasks
- [ ] **2.1** Create Etsy dashboard page
  - File: `Views/etsy/dashboard.php`
  - Show shop statistics
    - Today's sales total
    - This week's sales
    - Total orders count
    - Unshipped orders count
  - Display recent orders (last 10)
  - Quick stats widgets

- [ ] **2.2** Create order list view
  - File: `Views/etsy/orders.php`
  - Fetch orders from Etsy API
  - Display in table format
  - Columns: Order #, Customer, Items, Total, Status, Date
  - Filter by status (Paid, Shipped, Completed)
  - Search functionality
  - Pagination

- [ ] **2.3** Create order detail view
  - File: `Views/etsy/order_detail.php`
  - Show full order information
  - Customer details
  - Shipping address
  - Items ordered with photos
  - Order timeline
  - Payment info

- [ ] **2.4** Implement order caching
  - Store fetched orders in etsy_orders table
  - Avoid repeated API calls
  - Background sync mechanism
  - Manual refresh button

- [ ] **2.5** Add Etsy widget to main dashboard
  - Update `Views/main.php`
  - Add "Etsy Sales" card
  - Show quick stats
  - Link to full Etsy dashboard

- [ ] **2.6** Error handling & rate limiting
  - Implement retry logic
  - Handle API errors gracefully
  - Cache results to avoid rate limits
  - Display user-friendly error messages

### Deliverables
- ✅ Functional Etsy dashboard
- ✅ Order list with filtering
- ✅ Order detail view
- ✅ Cached orders for performance
- ✅ Widget on main dashboard

---

## Phase 3: Order → Estimate Workflow
**Estimated Time**: 8-12 hours  
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

## Phase 4: Fulfillment & Shipping Updates
**Estimated Time**: 10-15 hours  
**Goal**: Update Etsy from OMC when orders are fulfilled and print shipping labels

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

## Phase 5: Inventory Synchronization (FUTURE)
**Estimated Time**: 15-20 hours  
**Goal**: Keep inventory in sync between OMC and Etsy

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
- ✅ Linked products/listings
- ✅ Automatic quantity updates
- ✅ Stock level monitoring
- ✅ Scheduled sync process

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

## Timeline Estimate

| Phase | Estimated Time | Priority |
|-------|---------------|----------|
| Phase 1: Authentication | 6-8 hours | HIGH |
| Phase 2: Dashboard & Orders | 10-15 hours | HIGH |
| Phase 3: Order → Estimate | 8-12 hours | MEDIUM |
| Phase 4: Fulfillment & Shipping Labels | 10-15 hours | MEDIUM |
| Phase 5: Inventory Sync | 15-20 hours | LOW |
| Phase 6: Analytics | 10-15 hours | LOW |
| Phase 7: Publishing | 12-18 hours | LOW |

**Total High Priority**: 16-23 hours  
**Total Phases 1-4 (Core Features)**: 34-50 hours  
**Total All Phases**: 71-103 hours

---

## Next Steps

### Immediate Actions
1. [ ] Create Etsy developer account
2. [ ] Register OMC app in Etsy developer portal
3. [ ] Test API credentials in Postman
4. [ ] Review Etsy API v3 documentation
5. [ ] Start Phase 1 implementation

### Questions to Answer Before Starting
1. What is your Etsy shop name/ID?
2. How many orders per day/week do you receive?
3. What are the most time-consuming manual tasks with Etsy?
4. Do you need real-time sync or periodic sync?
5. Are there specific order statuses you want to track?

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
