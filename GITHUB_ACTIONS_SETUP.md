# GitHub Actions Auto-Deployment Setup Guide

## 🚀 Automatic Deployment to InfinityFree

Yeh workflow automatically aapke code ko `magiajewllery.great-site.net` pe deploy karega jab bhi aap `main` branch pe push karenge.

## ⚙️ Setup Steps

### Step 1: GitHub Secrets Add Karein

1. GitHub repository pe jaayein: `https://github.com/abdulrehman5533/laravel`
2. **Settings** tab pe click karein
3. Left sidebar mein **Secrets and variables** > **Actions** pe click karein
4. **New repository secret** button pe click karein

### Step 2: FTP Credentials Add Karein

Do secrets add karein:

#### Secret 1: FTP_USERNAME
```
Name: FTP_USERNAME
Value: if0_41999489
```

#### Secret 2: FTP_PASSWORD
```
Name: FTP_PASSWORD
Value: BG1rgNVPVRc
```

### Step 3: Workflow Trigger Karein

Jab bhi aap `main` branch pe push karenge, deployment automatic ho jaayegi!

Manual trigger ke liye:
1. **Actions** tab pe jaayein
2. "Deploy to InfinityFree" workflow select karein
3. **Run workflow** button pe click karein

## 📋 Deployment Process

Workflow yeh steps follow karegi:

1. ✅ Code checkout karegi
2. ✅ PHP dependencies install karegi (Composer)
3. ✅ Frontend assets build karegi (Vite)
4. ✅ Application key generate karegi
5. ✅ Production optimization karegi
6. ✅ FTP se InfinityFree pe upload karegi

## 🌐 Domain Information

- **Domain**: `https://magiajewllery.great-site.net`
- **FTP Host**: `ftpupload.net`
- **FTP Username**: `if0_41999489`
- **FTP Directory**: `/htdocs/`

## ⚠️ Important Notes

### Files Jo Upload HONGI:
- ✅ `app/` folder
- ✅ `bootstrap/` folder
- ✅ `config/` folder
- ✅ `database/` folder
- ✅ `public/` folder contents
- ✅ `resources/` folder
- ✅ `routes/` folder
- ✅ `storage/` folder
- ✅ `vendor/` folder
- ✅ `.htaccess` files
- ✅ `artisan` file

### Files Jo Upload NAHI Hongi:
- ❌ `.git/` folder
- ❌ `node_modules/` folder
- ❌ `.github/` folder
- ❌ `tests/` folder
- ❌ `.env` file
- ❌ `.md` files (documentation)
- ❌ `docker/` folder
- ❌ `python-ai-agent/` folder

## 🔧 Manual Setup (First Time Only)

InfinityFree pe pehli baar setup ke liye:

### 1. Create .env File on Server
FTP se connect karein aur `htdocs/.env` file create karein with your credentials.

### 2. Create Storage Link
FTP se `public/storage` folder manually create karein.

### 3. Set Permissions
Via FTP ya File Manager:
- `storage/` → 755
- `bootstrap/cache/` → 755
- `public/storage/` → 755

### 4. Import Database
InfinityFree cPanel > phpMyAdmin > Database import karein.

## 🎯 Workflow Commands

### Trigger Deployment
```bash
# Code push karein
git add .
git commit -m "Your changes"
git push origin main
```

### Check Deployment Status
1. GitHub > Actions tab
2. Latest workflow run dekhein
3. Success/Failure status check karein

### Manual Trigger
1. GitHub > Actions tab
2. "Deploy to InfinityFree" select karein
3. "Run workflow" > "Run workflow" button click karein

## 📞 Troubleshooting

### Issue: FTP Connection Failed
**Solution:**
- Verify FTP credentials in GitHub Secrets
- Check InfinityFree account is active

### Issue: Deployment Failed
**Solution:**
- Check Actions tab for error logs
- Verify file permissions on server
- Ensure .env file exists on server

### Issue: App Not Working After Deployment
**Solution:**
- Clear browser cache
- Check `.htaccess` file uploaded correctly
- Verify database connection in `.env`
- Check error logs in `storage/logs/`

## ✅ Verification Checklist

After deployment:
- [ ] Visit: `https://magiajewllery.great-site.net`
- [ ] Check if homepage loads
- [ ] Try login
- [ ] Test database connection
- [ ] Verify all modules work
- [ ] Check error logs if any issues

## 🔐 Security

- FTP credentials GitHub Secrets mein secure hain
- `.env` file repository mein nahi hai
- Sensitive files workflow se excluded hain
- Production optimization enabled hai

## 📈 Benefits

✅ **Automatic Deployment** - Har push pe auto deploy
✅ **No Manual FTP** - Code push karo, baaki automatic
✅ **Build Process** - Assets automatically build hote hain
✅ **Optimization** - Production ready code deploy hota hai
✅ **Fast** - Usually 2-5 minutes mein complete

---

**Ab bas code push karein aur deployment automatic ho jaayegi!** 
