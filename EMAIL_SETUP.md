# 📧 Email Notification System Setup

## Overview

The Tabibnet appointment system has a complete email notification system that sends:
1. **Confirmation Email** - When a patient books an appointment
2. **Reminder Email** - Daily reminder for appointments scheduled for today
3. **Cancellation Email** - When an appointment is cancelled (by patient or doctor)

## Email Flow

### 1. Appointment Booking
```
Patient books appointment → Confirmation email sent to patient
```

### 2. Appointment Cancellation
```
Patient/Doctor cancels → Cancellation email notifies patient
```

### 3. Daily Reminders (via Cron)
```
Cron job (daily) → Command runs → Sends reminder emails for today's appointments
```

## Configuration

### Step 1: Update `.env` File

The email configuration is in `.env`:

```env
MAILER_DSN=gmail://zidayoub085@gmail.com:tabibnet@default
```

**Options:**

#### Gmail Configuration (Recommended)
```env
MAILER_DSN=gmail://your_email@gmail.com:your_app_password@default
```

**Note:** If you use Gmail with 2FA:
1. Go to https://myaccount.google.com/apppasswords
2. Select "Mail" and "Windows Computer"
3. Copy the 16-character app password
4. Use this password in the MAILER_DSN

#### SMTP Configuration (Generic)
```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

#### Testing (No Email Sending)
```env
MAILER_DSN=null://null
```
Use this for development when you don't want to send real emails.

### Step 2: Test Email Configuration

Visit this URL to test if your email configuration works:
```
http://localhost:8000/test-email
```

This will send test versions of:
- ✅ Confirmation email
- ✅ Reminder email  
- ✅ Cancellation email

Check your email inbox for these test emails.

## Setting Up Daily Reminder Cron Job

### On Linux/Mac:

1. Open crontab editor:
```bash
crontab -e
```

2. Add this line to run the reminder command every day at 9 AM:
```cron
0 9 * * * cd /path/to/services-medical && php bin/console app:send-appointment-reminders >> var/log/reminders.log 2>&1
```

Replace `/path/to/services-medical` with your actual project path.

### On Windows (using Task Scheduler):

1. Open Task Scheduler
2. Create a new basic task
3. Set trigger: Daily at 9:00 AM
4. Set action: Start a program
   - Program: `php.exe` (full path, e.g., `C:\xampp\php\php.exe`)
   - Arguments: `C:\xampp\htdocs\Medecal\services-medical\bin\console app:send-appointment-reminders`
   - Start in: `C:\xampp\htdocs\Medecal\services-medical`

### Manual Testing:

To manually test the reminder command:
```bash
php bin/console app:send-appointment-reminders
```

## Email Files Location

- **Service:** `src/Service/EmailService.php` - Handles all email sending
- **Command:** `src/Command/SendAppointmentRemindersCommand.php` - Daily reminder scheduler
- **Templates:**
  - `templates/emails/appointment_confirmation.html.twig` - Booking confirmation
  - `templates/emails/appointment_reminder.html.twig` - Daily reminder
  - `templates/emails/appointment_cancellation.html.twig` - Cancellation notice

## Email Customization

To customize email content, edit the Twig templates in `templates/emails/`:

Each template receives:
- `patient` - Patient object with firstName, lastName, email
- `doctor` - Doctor object with firstName, lastName, specialite, email
- `rendezVous` - Appointment object with date, time, message

## Troubleshooting

### Emails not sending?

1. **Check .env configuration:**
   ```bash
   php bin/console config:dump-reference framework mailer
   ```

2. **Verify Gmail app password:**
   - Make sure you're using the 16-character app password, not your regular password
   - Enable "Less secure app access" if not using app password

3. **Check error logs:**
   ```bash
   tail -f var/log/dev.log
   ```

4. **Test with null mailer first:**
   ```env
   MAILER_DSN=null://null
   ```

5. **Check sender address:**
   - Verify `noreply@medinest.com` is a valid address or update in `EmailService.php`

### Reminders not running?

1. Verify cron job is active:
   ```bash
   crontab -l
   ```

2. Check cron logs:
   ```bash
   tail -f var/log/reminders.log
   ```

3. Verify the command runs manually:
   ```bash
   php bin/console app:send-appointment-reminders
   ```

## Email Content Variables

### Appointment Object
- `rendezVous.appointmentDate` - Date/time of appointment
- `rendezVous.message` - Patient message/notes
- `rendezVous.statut` - Status (en_attente, termine, annule)

### Patient Object
- `patient.firstName` - Patient first name
- `patient.lastName` - Patient last name
- `patient.email` - Patient email address
- `patient.phone` - Patient phone number

### Doctor Object
- `doctor.firstName` - Doctor first name
- `doctor.lastName` - Doctor last name
- `doctor.specialite` - Doctor specialty
- `doctor.email` - Doctor email address

## Email Features

✅ **Automatic Confirmation** - Sent when appointment is booked
✅ **Automatic Cancellation Notice** - Sent when appointment is cancelled
✅ **Daily Reminders** - Sent every morning for today's appointments
✅ **Patient Ownership Verification** - Only patients see their own feedback
✅ **Error Handling** - Gracefully handles email failures
✅ **Beautiful HTML Templates** - Professional email designs
✅ **Multilingual Support** - French content with English fallback

---

**Last Updated:** February 8, 2026
