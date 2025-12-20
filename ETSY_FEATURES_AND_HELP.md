# Etsy Integration - Features & Help Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Features](#features)
3. [Getting Started](#getting-started)
4. [Using the Integration](#using-the-integration)
5. [Troubleshooting](#troubleshooting)
6. [FAQ](#faq)
7. [Technical Details](#technical-details)

---

## Overview

The Etsy Integration allows you to seamlessly connect your OMC system with your Etsy shop to automatically import orders, manage customer information, and create estimates directly from Etsy sales.

**Current Status:** Phase 1 Complete - OAuth Authentication & Foundation  
**Next Phase:** Order Synchronization (Available after Etsy app approval)

---

## Features

### ✅ Phase 1: Authentication & Foundation (COMPLETE)

#### **Secure OAuth 2.0 Authentication**
- Connect your Etsy shop with a single click
- Industry-standard OAuth 2.0 with PKCE security enhancement
- CSRF protection for secure authorization
- Automatic token refresh (no manual re-authentication needed)

#### **Connection Management**
- Easy one-click connection setup
- View connection status at a glance
- Disconnect anytime with confirmation prompt
- Shop name and ID display when connected

#### **Dashboard Integration**
- **Main Dashboard Card:** Shows Etsy connection status, shop name, and recent orders count
- **Settings Page Section:** Dedicated Etsy configuration area with connection controls
- **Real-time Status:** See connection status and last sync timestamp

#### **Database Infrastructure**
- **Order Storage:** Comprehensive order data storage with customer and shipping information
- **Sync Logging:** Track all synchronization operations with detailed statistics
- **Token Management:** Secure storage of OAuth tokens with automatic expiration handling

#### **User Interface**
- Intuitive connection flow with clear instructions
- Success/error messages for all operations
- Visual status indicators (connection badges, timestamps)
- Mobile-responsive design matching OMC style

---

### 🚧 Phase 2: Order Synchronization (COMING SOON)

#### **Automatic Order Import**
- Sync orders from Etsy with one click
- Import customer names, emails, and contact information
- Import complete shipping addresses
- Store order items and product details
- Track order totals and payment status

#### **Smart Synchronization**
- Detect new orders automatically
- Update existing orders with latest information
- Track fulfillment status changes
- Rate-limited API calls (stays within Etsy's 5 QPS limit)

#### **Order Management**
- View all Etsy orders in dedicated dashboard
- Search and filter orders
- View detailed order information
- Track sync history and statistics

---

### 🎯 Phase 3: Estimate Creation (PLANNED)

#### **Estimate from Etsy Orders**
- Create estimates directly from synced Etsy orders
- Auto-populate customer information
- Pre-fill shipping addresses
- Link estimates to Etsy orders
- Track order-to-estimate conversion

#### **Customer Management**
- Automatically create customer records from Etsy orders
- Link multiple orders to same customer
- Track customer order history
- Export customer data

---

### 🔄 Phase 4: Fulfillment Sync (PLANNED)

#### **Two-Way Synchronization**
- Mark orders as fulfilled in Etsy from OMC
- Add tracking numbers to Etsy orders
- Sync fulfillment status back to Etsy
- Update customers automatically

---

## Getting Started

### Prerequisites
✅ Active Etsy seller account  
✅ OMC system installed and configured  
✅ Etsy API credentials (Keystring and Shared Secret)  
✅ Etsy app approval (for production use)  

### Step 1: Connect Your Etsy Shop

1. **Navigate to Settings**
   - Click "Settings" in the main navigation
   - Scroll down to "Etsy Integration" section

2. **Review Permissions**
   - The integration will request these permissions:
     - Read shop information
     - Read orders and transactions
     - Read product listings
     - Read shipping information
     - Update order fulfillment status

3. **Click "Connect to Etsy"**
   - You'll be redirected to Etsy's authorization page
   - **Important:** Make sure you're logged into your Etsy seller account

4. **Authorize the Application**
   - Review the requested permissions
   - Click "Allow Access" to authorize OMC

5. **Confirmation**
   - You'll be redirected back to OMC
   - Success message will confirm connection
   - Your shop name will be displayed
   - Connection status will show as "Connected"

### Step 2: Verify Connection

After connecting, you should see:
- ✅ Green "Connected" badge in Settings
- Your Etsy shop name displayed
- "Etsy Sales" card on main dashboard (with green status indicator)
- "Sync Orders Now" button available

---

## Using the Integration

### Viewing Connection Status

#### **Main Dashboard**
- Look for the "Etsy Sales" card
- **Connected:** Shows green dot (●), shop name, and recent orders count
- **Not Connected:** Shows gray dot (○) with "Connect your Etsy shop" message
- Click the card to:
  - **If Connected:** Opens Etsy dashboard
  - **If Not Connected:** Opens Settings to connect

#### **Settings Page**
- Navigate to Settings → Etsy Integration section
- **Connected State:**
  - Shows green success banner with shop name
  - Displays last sync timestamp
  - Shows "Sync Orders Now" and "Disconnect" buttons
- **Disconnected State:**
  - Shows blue info banner
  - Lists integration benefits
  - Shows "Connect to Etsy" button

### Syncing Orders (Phase 2 - After App Approval)

1. **Manual Sync**
   - Go to Settings → Etsy Integration
   - Click "Sync Orders Now"
   - Wait for confirmation message
   - Review sync statistics (orders added/updated)

2. **Viewing Synced Orders**
   - Click "Etsy Sales" card on main dashboard
   - View orders table with:
     - Order ID
     - Customer name
     - Items count
     - Total amount
     - Fulfillment status
     - Order date
   - Click "View" to see detailed order information

3. **Sync History**
   - View sync operations log in Etsy dashboard
   - See records processed, added, updated, failed
   - Track API calls made
   - Review error messages if any

### Disconnecting from Etsy

1. Navigate to Settings → Etsy Integration
2. Click "Disconnect" button
3. Confirm the action in the popup
4. Your tokens will be cleared (you can reconnect anytime)
5. Existing synced orders remain in database

**Note:** Disconnecting does NOT delete your synced orders or data. It only removes the active connection and tokens.

---

## Troubleshooting

### Connection Issues

#### **Problem:** "Not connected to Etsy" error
**Solution:**
1. Go to Settings → Etsy Integration
2. Click "Connect to Etsy"
3. Make sure you're logged into your Etsy seller account
4. Authorize the application

#### **Problem:** "Invalid OAuth state" error
**Solution:**
- This is a security check that failed
- Clear your browser cache and cookies
- Try connecting again
- Make sure you complete the authorization within 10 minutes

#### **Problem:** OAuth callback shows "Error" message
**Solution:**
1. Check that your Etsy app is approved (not in pending status)
2. Verify redirect URI in Etsy app settings matches: `http://localhost/omc/public/etsy/oauth_callback.php`
3. Check PHP error log: `C:\xampp\php\logs\php_error_log`
4. Ensure cURL is enabled in PHP

### Sync Issues

#### **Problem:** "Sync failed" error message
**Solution:**
1. Check your internet connection
2. Verify you're still connected (Settings → Etsy Integration)
3. Check if your access token expired (it should auto-refresh)
4. Try disconnecting and reconnecting
5. Review error details in sync history

#### **Problem:** Orders not appearing after sync
**Solution:**
1. Verify you have orders in your Etsy shop
2. Check that orders are marked as "paid" (unpaid orders aren't synced)
3. Review sync history for error messages
4. Check database: Run query `SELECT COUNT(*) FROM etsy_orders;`

### Display Issues

#### **Problem:** Etsy card not showing on dashboard
**Solution:**
1. Refresh the page (Ctrl+F5)
2. Check browser console for JavaScript errors (F12)
3. Verify database connection is working
4. Clear browser cache

#### **Problem:** Shop name shows as "Unknown" or null
**Solution:**
1. Disconnect and reconnect to Etsy
2. This will re-fetch shop information
3. Check database: `SELECT etsy_shop_name FROM settings WHERE id = 1;`

---

## FAQ

### General Questions

**Q: Is my Etsy data secure?**  
A: Yes. We use industry-standard OAuth 2.0 with PKCE security. Your Etsy password is never stored in OMC. Only secure access tokens are stored, and they're automatically refreshed.

**Q: Will this affect my Etsy shop?**  
A: No. The integration only reads your order data. It does not modify your Etsy shop, listings, or orders (unless you explicitly use fulfillment features in Phase 4).

**Q: How often should I sync orders?**  
A: You can sync as often as you like. We recommend syncing once per day or whenever you want to create estimates from new orders.

**Q: Can I use this with multiple Etsy shops?**  
A: Currently, OMC supports one Etsy shop connection per installation. For multiple shops, you would need separate OMC installations.

**Q: What happens if my token expires?**  
A: Tokens are automatically refreshed before they expire (every ~1 hour). You won't need to re-authorize unless you disconnect.

### Technical Questions

**Q: What Etsy API version is used?**  
A: Etsy API v3 (the latest version as of 2025).

**Q: What are the API rate limits?**  
A: Etsy allows 5 queries per second (QPS) and 5,000 queries per day (QPD). Our integration respects these limits.

**Q: Where are my Etsy credentials stored?**  
A: API credentials are stored in the `settings` table in your MySQL database. Access tokens are stored securely and refreshed automatically.

**Q: Can I manually edit synced orders?**  
A: Yes, synced orders are stored in your local database and can be edited. However, changes won't sync back to Etsy (until Phase 4).

**Q: What data is synced from Etsy orders?**  
A: We sync:
- Order ID and receipt number
- Customer name and email
- Complete shipping address
- Order items and quantities
- Order total and payment status
- Fulfillment status
- Order creation date

---

## Technical Details

### OAuth Scopes

The integration requests these Etsy API scopes:

| Scope | Purpose |
|-------|---------|
| `shops_r` | Read shop information (name, ID) |
| `transactions_r` | Read order and transaction data |
| `listings_r` | Read product listings |
| `shipping_r` | Read shipping information |
| `transactions_w` | Update order fulfillment status (Phase 4) |

### Database Tables

#### **settings** (Enhanced)
Stores OAuth credentials and connection status:
- `etsy_api_key` - Your Etsy API keystring
- `etsy_shared_secret` - Your Etsy shared secret
- `etsy_access_token` - Current OAuth access token
- `etsy_refresh_token` - Token for refreshing access
- `etsy_shop_id` - Your Etsy shop ID
- `etsy_shop_name` - Your Etsy shop name
- `etsy_token_expires` - Token expiration timestamp
- `etsy_connected` - Connection status (boolean)
- `etsy_last_sync` - Last successful sync timestamp

#### **etsy_orders** (New)
Stores synced Etsy orders:
- Order identification (ID, receipt number)
- Customer information (name, email)
- Shipping address (all fields)
- Order details (items count, total, status)
- Item data (JSON)
- Full order data (JSON for reference)
- Link to estimate (when created)
- Link to customer record

#### **etsy_sync_log** (New)
Audit log for synchronization operations:
- Sync type (orders, listings, etc.)
- Status (success, failure, partial)
- Statistics (processed, added, updated, failed)
- API calls made
- Error messages
- Timestamps

### Security Features

- **OAuth 2.0 PKCE:** Proof Key for Code Exchange prevents authorization code interception
- **CSRF Protection:** State parameter validation prevents cross-site request forgery
- **Token Refresh:** Automatic refresh before expiration prevents downtime
- **Secure Storage:** Tokens stored in database (recommend encryption for production)
- **Session Management:** Code verifier stored in session, cleared after use
- **Error Logging:** All errors logged to PHP error log for debugging

### API Endpoints Used

- **Authorization:** `https://www.etsy.com/oauth/connect`
- **Token Exchange:** `https://api.etsy.com/v3/public/oauth/token`
- **Shop Info:** `https://openapi.etsy.com/v3/application/shops`
- **Orders (Phase 2):** `https://openapi.etsy.com/v3/shops/{shop_id}/receipts`

---

## Support & Resources

### Getting Help

1. **Check This Guide:** Review the troubleshooting section
2. **Check Error Logs:** Look at PHP and Apache error logs
3. **Database Check:** Verify credentials and connection status in database
4. **Documentation:** Review `ETSY_PHASE1_COMPLETE.md` for technical details

### Useful Commands

**Check Connection Status:**
```sql
SELECT etsy_connected, etsy_shop_name, etsy_last_sync 
FROM settings WHERE id = 1;
```

**View Recent Synced Orders:**
```sql
SELECT etsy_order_id, customer_name, order_total, created_at 
FROM etsy_orders 
ORDER BY created_at DESC 
LIMIT 10;
```

**View Sync History:**
```sql
SELECT sync_type, status, records_processed, completed_at 
FROM etsy_sync_log 
ORDER BY completed_at DESC 
LIMIT 10;
```

**Check Token Expiration:**
```sql
SELECT etsy_token_expires, 
       TIMESTAMPDIFF(MINUTE, NOW(), etsy_token_expires) as minutes_until_expiry 
FROM settings WHERE id = 1;
```

### Etsy Resources

- **Etsy Developer Portal:** https://www.etsy.com/developers/
- **API Documentation:** https://developers.etsy.com/documentation/
- **API Status:** https://status.etsy.com/
- **Rate Limits:** https://developers.etsy.com/documentation/essentials/rate-limits

---

## Version History

### Phase 1 - OAuth & Foundation (Current)
**Release Date:** December 19, 2025  
**Status:** ✅ Complete (Pending Etsy App Approval)

**Features:**
- OAuth 2.0 authentication with PKCE
- Connection management (connect/disconnect)
- Settings page integration
- Main dashboard card
- Database schema for orders and sync logs
- Token management and auto-refresh
- Security features (CSRF, error handling)

### Phase 2 - Order Synchronization (Planned)
**Estimated Release:** After Etsy app approval + 1-2 days

**Features:**
- Manual order sync from Etsy API
- Order import with customer and shipping data
- Etsy dashboard with order listing
- Sync history and statistics
- Error handling and retry logic

### Phase 3 - Estimate Creation (Planned)
**Estimated Release:** Phase 2 + 2-3 days

**Features:**
- Create estimates from Etsy orders
- Auto-populate customer information
- Link estimates to orders
- Customer record creation from orders

### Phase 4 - Fulfillment Sync (Planned)
**Estimated Release:** Phase 3 + 1-2 days

**Features:**
- Mark orders as fulfilled in Etsy
- Add tracking numbers to orders
- Two-way status synchronization
- Automatic customer notifications

---

## Quick Reference Card

### Connection Quick Start
1. Settings → Etsy Integration
2. Click "Connect to Etsy"
3. Log into Etsy → Allow Access
4. Confirm connection success

### Daily Workflow (Phase 2+)
1. Check "Etsy Sales" card on dashboard
2. Click "Sync Orders Now" if needed
3. Review new orders in Etsy dashboard
4. Create estimates from orders
5. Mark as fulfilled when complete

### Troubleshooting Quick Checks
- ✓ Etsy app approved?
- ✓ Logged into correct Etsy account?
- ✓ Internet connection working?
- ✓ PHP cURL extension enabled?
- ✓ Token not expired? (auto-refreshes)

---

**Need More Help?** Check the technical documentation in `ETSY_PHASE1_COMPLETE.md` or review the Etsy Terms of Service compliance document in `ETSY_TOS_COMPLIANCE.md`.

---

*Last Updated: December 19, 2025*  
*OMC Version: 1.0*  
*Etsy Integration Version: Phase 1*
