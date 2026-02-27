# Manual PHPMailer Installation (for MAMP users)

Since you're running in MAMP without Composer, follow these steps to manually install PHPMailer:

## Step 1: Download PHPMailer

1. Go to: https://github.com/PHPMailer/PHPMailer/releases
2. Download the latest release (look for `Source code (zip)`)
3. Extract the ZIP file

## Step 2: Install in Your Project

1. From the extracted folder, find the `PHPMailer-6.x.x` folder (version number may vary)
2. Copy the entire folder to: `c:\MAMP\htdocs\rpi_server_live_checker\`
3. Rename it to exactly: `PHPMailer` (remove the version number)

Your folder structure should look like:
```
c:\MAMP\htdocs\rpi_server_live_checker\
├── PHPMailer/
│   ├── src/
│   │   ├── PHPMailer.php
│   │   ├── SMTP.php
│   │   └── Exception.php
│   └── ...
├── includes/
├── config.email.php
└── ...
```

## Step 3: Verify Installation

1. Open your browser and go to: http://localhost/rpi_server_live_checker/test_email.php
2. The page will show whether PHPMailer is detected
3. If installed correctly, you'll see "PHPMailer: ✓ Available"

## Step 4: Configure Gmail Credentials

Edit `config.email.php` and add your Gmail credentials:
- Your Gmail address
- Your Gmail App Password (not your regular password!)

## Step 5: Test Email Sending

Use the test_email.php page to send a test email and verify everything works.

---

**Note:** The PHPMailer folder is already added to .gitignore, so it won't be committed to your repository.
