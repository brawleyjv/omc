# Email Configuration Guide

This guide explains how to configure email settings for sending estimates to customers.

## Overview

The OMC system can send estimates via email using SMTP (Simple Mail Transfer Protocol). You need to configure your email server settings before you can send estimates.

## Accessing Email Settings

1. Log into the OMC admin panel
2. Navigate to **Settings** (`Views/settings.php`)
3. Scroll to the **Email Configuration** section

## Required Information

You'll need the following information from your email provider:

- **SMTP Server**: The mail server address (e.g., `smtp.gmail.com`)
- **SMTP Port**: The port number (typically `587` for TLS or `465` for SSL)
- **Encryption**: Security protocol (`TLS` recommended, or `SSL`, or `None`)
- **Username**: Your email account username (usually your full email address)
- **Password**: Your email account password or app-specific password
- **From Email**: The email address that appears as the sender
- **From Name**: The name that appears as the sender (e.g., your company name)

## Configuration by Email Provider

### Gmail

1. **SMTP Server**: `smtp.gmail.com`
2. **SMTP Port**: `587`
3. **Encryption**: `TLS`
4. **Username**: Your Gmail address (e.g., `yourname@gmail.com`)
5. **Password**: You must create an **App Password**
   - Visit: https://myaccount.google.com/apppasswords
   - Generate a new app password for "Mail"
   - Use this 16-character password instead of your regular Gmail password
6. **From Email**: Your Gmail address
7. **From Name**: Your company name or personal name

**Note**: Standard Gmail passwords won't work if you have 2-Step Verification enabled. You MUST use an App Password.

### Outlook/Office 365

1. **SMTP Server**: `smtp.office365.com`
2. **SMTP Port**: `587`
3. **Encryption**: `TLS`
4. **Username**: Your Outlook/Office 365 email address
5. **Password**: Your email password
6. **From Email**: Your Outlook email address
7. **From Name**: Your company name

### Yahoo Mail

1. **SMTP Server**: `smtp.mail.yahoo.com`
2. **SMTP Port**: `587`
3. **Encryption**: `TLS`
4. **Username**: Your Yahoo email address
5. **Password**: You may need to generate an app-specific password
6. **From Email**: Your Yahoo email address
7. **From Name**: Your company name

### Custom/Business Email (cPanel, GoDaddy, etc.)

1. **SMTP Server**: Usually `mail.yourdomain.com` or `smtp.yourdomain.com`
   - Check with your hosting provider for the exact server address
2. **SMTP Port**: Typically `587` (TLS) or `465` (SSL)
3. **Encryption**: `TLS` or `SSL` (recommended)
4. **Username**: Your full email address (e.g., `info@yourbusiness.com`)
5. **Password**: Your email password
6. **From Email**: Your business email address
7. **From Name**: Your business name

## Step-by-Step Setup

1. **Navigate to Settings**
   - URL: `http://localhost/omc/Views/settings.php`
   - Or click "Settings" → "Pricing Settings" from the main dashboard

2. **Scroll to Email Configuration Section**
   - Located between Company Information and Pricing Rates

3. **Fill in All Fields**
   - Enter your SMTP server details
   - Use the appropriate port for your encryption type
   - Select the correct encryption method (TLS recommended)
   - Enter your email credentials
   - Set the "From Name" to your company name
   - Set the "From Email" to the email address you want customers to see

4. **Save Settings**
   - Click the "💾 Save Settings" button at the bottom of the page
   - You should see a success message

5. **Test Email Configuration**
   - Go to Estimates → View an estimate
   - Click the email icon or "Email Estimate" button
   - Try sending a test email to yourself
   - Check your inbox (and spam folder) for the email

## Troubleshooting

### "Email is not configured" Error

**Problem**: You see "Email is not configured. Please set up SMTP settings in the Settings page."

**Solution**: 
- Make sure ALL required fields are filled in (SMTP Host, Username, and Password at minimum)
- Click "Save Settings" after entering the information

### "Failed to send email" Error

**Problem**: Email sending fails even with settings configured.

**Possible Solutions**:

1. **Check Gmail App Password**
   - If using Gmail, make sure you're using an App Password, not your regular password
   - Regenerate the app password if needed

2. **Verify SMTP Server Address**
   - Double-check the SMTP server address with your email provider
   - Make sure there are no typos

3. **Check Port Number**
   - Port 587 is for TLS
   - Port 465 is for SSL
   - Match the port to your encryption setting

4. **Firewall/Antivirus**
   - Some firewalls or antivirus software block SMTP connections
   - Temporarily disable to test
   - Add an exception for your SMTP server

5. **Email Provider Blocks**
   - Some providers require you to enable "less secure app access"
   - Check your email provider's security settings

### Emails Going to Spam

**Problem**: Emails are sent successfully but land in spam folder.

**Solutions**:
- Use a business email address instead of free email services
- Make sure the "From Email" matches the SMTP username
- Consider setting up SPF and DKIM records for your domain (advanced)
- Ask recipients to add your email to their contacts

## Security Best Practices

1. **Use App-Specific Passwords**
   - For Gmail and other services that support it
   - Never use your main email password if app passwords are available

2. **Always Use Encryption**
   - Select TLS or SSL, never "None"
   - TLS (port 587) is recommended

3. **Protect Your Settings**
   - The password is stored in the database
   - Make sure your database is secure
   - Don't share your settings.php or database backups publicly

4. **Use a Dedicated Email**
   - Consider creating a dedicated email like `estimates@yourbusiness.com`
   - This separates business emails from personal emails

## Database Schema

Email settings are stored in the `settings` table with the following fields:

```sql
smtp_host VARCHAR(255)         -- SMTP server address
smtp_port INT DEFAULT 587      -- SMTP port number
smtp_username VARCHAR(255)     -- SMTP login username
smtp_password VARCHAR(255)     -- SMTP login password
smtp_from_email VARCHAR(100)   -- Sender email address
smtp_from_name VARCHAR(100)    -- Sender display name
smtp_encryption VARCHAR(10)    -- Encryption type (tls/ssl/none)
```

## Related Files

- **Settings View**: `Views/settings.php` - User interface for configuration
- **Settings Model**: `Models/Settings.php` - Database operations
- **Email Sending**: `Views/estimate/email_estimate.php` - Email functionality
- **Database Schema**: `database/add_email_settings.sql` - SQL for production deployment

## Support

If you continue to have issues:

1. Check your email provider's documentation for SMTP settings
2. Contact your email provider's support
3. Check the PHP error logs for detailed error messages
4. Verify your hosting provider allows outbound SMTP connections

---

**Last Updated**: December 19, 2025
