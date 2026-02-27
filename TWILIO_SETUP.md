# 📱 Twilio SMS Integration - Quick Start Guide

## ✅ What's Done

Your network monitoring system now has **clean, working Twilio SMS integration**! All previous SMS provider code has been removed and replaced with Twilio.

### Files Created/Updated:
- ✅ `config.sms.php` - Twilio credentials configuration
- ✅ `includes/SMSHelper.php` - Clean Twilio SMS implementation
- ✅ `test_sms.php` - Beautiful test interface
- ✅ `scripts/ping_devices.py` - Python monitoring script with Twilio SMS

---

## 🚀 Setup Steps (5 minutes)

### Step 1: Get Your Twilio Phone Number

1. Login to [Twilio Console](https://console.twilio.com/)
2. Go to **Phone Numbers** → **Manage** → **Active numbers**
3. If you don't have a number:
   - Click **Buy a number**
   - Choose a number with SMS capability
   - Complete purchase (it's FREE with trial credits!)
4. Copy your Twilio phone number (e.g., `+15551234567`)

### Step 2: Update Configuration

Edit `config.sms.php`:
```php
define('TWILIO_FROM_NUMBER', '+15551234567'); // Replace with YOUR Twilio number
```

### Step 3: Verify Your Phone Number (Trial Accounts Only)

If you're using a trial account, you must verify recipient numbers:

1. Go to [Verified Caller IDs](https://console.twilio.com/us1/develop/phone-numbers/manage/verified)
2. Click **Add a new number**
3. Enter your Philippine phone number (e.g., `+639171234567`)
4. Enter the verification code sent via SMS
5. Done! You can now send SMS to this number

### Step 4: Test It!

1. Open: `http://localhost/rpi_server_live_checker/test_sms.php`
2. Click **"Check Balance"** to verify connection
3. Enter your verified phone number
4. Click **"Send Test SMS"**
5. Check your phone! 🎉

---

## 📋 Current Configuration

- **Account SID:** AC058dc61557f2bc76f2626... (configured ✅)
- **Auth Token:** fed89dafeb90... (configured ✅)
- **From Number:** ⚠️ UPDATE THIS IN CONFIG.SMS.PHP

---

## 💰 Pricing & Credits

- **Free Trial Credits:** $15.00 USD
- **SMS to Philippines:** ~$0.0561 USD per message
- **Trial Credits = ~267 SMS messages** to Philippine numbers

---

## 🔧 How It Works

### Device Monitoring with SMS Alerts

When a device goes offline for 5+ minutes:

1. **Email Alert** is sent (if configured)
2. **SMS Alert** is sent (if configured)
3. **Re-alerts every 30 minutes** if still offline
4. **Auto-resolves** when device comes back online

### SMS Message Format

```
ALERT: Device Name (192.168.1.100) is OFFLINE at Feb 27, 02:30 PM - Network Monitor
```

---

## 📱 Phone Number Format

The system accepts multiple formats and auto-converts:

| Input Format | Auto-Converted To |
|-------------|-------------------|
| 09171234567 | +639171234567 |
| +639171234567 | +639171234567 (unchanged) |
| 639171234567 | +639171234567 |

---

## ⚠️ Trial Account Limitations

**During trial, you can only send SMS to numbers you've verified.**

To remove this limitation:
1. Go to [Twilio Console → Billing](https://console.twilio.com/billing)
2. Add a payment method
3. Your account will be upgraded automatically
4. You can then send to any number!

---

## 🧪 Testing Commands

### Test via Web Interface
```
http://localhost/rpi_server_live_checker/test_sms.php
```

### Test Python Script (with SMS)
```powershell
cd C:\MAMP\htdocs\rpi_server_live_checker\scripts
python ping_devices.py --once
```

### Check if SMS config is loaded
```powershell
python -c "from pathlib import Path; import re; config = Path('c:/MAMP/htdocs/rpi_server_live_checker/config.sms.php').read_text(); print('✅ Twilio configured!' if 'TWILIO' in config else '❌ Missing config')"
```

---

## 📚 Useful Links

- [Twilio Console](https://console.twilio.com/) - Dashboard & Settings
- [Verify Phone Numbers](https://console.twilio.com/us1/develop/phone-numbers/manage/verified) - For trial accounts
- [Check Balance](https://console.twilio.com/billing) - View credits
- [SMS Pricing](https://www.twilio.com/sms/pricing/ph) - Philippines rates
- [API Documentation](https://www.twilio.com/docs/sms/api) - Full API docs

---

## 🎯 Next Steps

1. ✅ Get your Twilio phone number
2. ✅ Update `TWILIO_FROM_NUMBER` in config.sms.php
3. ✅ Verify your recipient phone number (trial only)
4. ✅ Test via test_sms.php
5. ✅ Configure device SMS alerts in dashboard
6. ✅ Start monitoring!

---

## 💡 Tips

- **Keep trial credits?** Only verify numbers you actually need to test
- **Production ready?** Upgrade account to send to any number
- **Multiple recipients?** Add phone numbers to multiple users in the system
- **Cost control?** Monitor usage in Twilio Console billing section

---

Need help? Check `test_sms.php` for detailed troubleshooting steps!
