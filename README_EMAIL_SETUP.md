# Email Setup Guide for TurbineCare

This guide will help you configure email notifications for the TurbineCare application using various free email providers.

## Recommended Options for Testing

### Option 1: Mailtrap (Recommended for Development/Testing) ⭐
**Best for testing - emails don't go to real inboxes**

1. Sign up at [mailtrap.io](https://mailtrap.io) (free tier: 100 emails/month)
2. Create a new inbox
3. Copy the SMTP credentials
4. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@turbinecare.com
MAIL_FROM_NAME="TurbineCare Notifications"
```

### Option 2: Brevo (formerly SendinBlue) - Free Production Emails
**Best for production - 300 free emails/day**

1. Sign up at [brevo.com](https://www.brevo.com) (free tier: 300 emails/day)
2. Go to SMTP & API → SMTP
3. Generate SMTP key
4. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_email@example.com
MAIL_PASSWORD=your_generated_smtp_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_verified_email@example.com
MAIL_FROM_NAME="TurbineCare Notifications"
```

### Option 3: Gmail SMTP (Personal Use)
**Note: Requires app-specific password**

1. Enable 2-factor authentication on your Gmail account
2. Generate an app-specific password:
   - Google Account → Security → 2-Step Verification → App passwords
3. Update your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_specific_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_gmail@gmail.com
MAIL_FROM_NAME="TurbineCare Notifications"
```

## Setup Instructions

1. **Choose your preferred option** from above
2. **Update your `.env` file** with the appropriate credentials
3. **Test the configuration**:
   ```bash
   php artisan tinker
   Mail::raw('Test email', function ($message) {
       $message->to('your-test-email@example.com')->subject('Test');
   });
   ```
4. **Or use our test command**:
   ```bash
   php artisan send:test-notification-email your-email@example.com
   ```

## Important Notes

- **For development/testing**: Use Mailtrap to avoid sending emails to real users
- **For production**: Use Brevo for reliable delivery with good free tier
- **Gmail limitations**: 500 emails/day, may be flagged as spam
- **Always verify your sender email** with the provider for better deliverability

## Troubleshooting

### Common Issues:
1. **Connection refused**: Check host and port settings
2. **Authentication failed**: Verify username/password
3. **TLS/SSL errors**: Try different encryption settings (tls/ssl)
4. **Gmail "Less secure apps"**: Use app-specific password instead

### Testing Commands:
```bash
# Test basic email configuration
php artisan tinker
Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });

# Send test notification email
php artisan send:test-notification-email your-email@example.com

# Create test notifications in system
php artisan create:test-notifications
```

## Current Configuration

The system is currently using the `log` mailer, which saves emails to the Laravel log files instead of sending them. This is safe for development but you'll need to switch to a real mailer for testing actual email delivery. 