# 🚀 Render.com Deployment Guide - MAGIA LUPOS

## Step 1: GitHub pe Code Push Karo

```bash
# Project folder mein jaao
cd c:\xampp1\htdocs\jewellery-management-system

# Git initialize karo (agar pehle nahi kiya)
git init
git add .
git commit -m "Production ready - Docker deployment"

# GitHub pe new repository banao: github.com/new
# Phir ye commands chalao:
git remote add origin https://github.com/YOUR_USERNAME/jewellery-ms.git
git branch -M main
git push -u origin main
```

---

## Step 2: Render.com pe Deploy Karo

### Option A - Blueprint (Automatic - Recommended)
1. [render.com](https://render.com) pe login karo
2. Dashboard → **New** → **Blueprint**
3. GitHub repo select karo
4. `render.yaml` automatically detect hoga
5. **Apply** click karo
6. Database + Web Service dono automatically ban jayenge ✅

### Option B - Manual
1. **New** → **Web Service**
2. GitHub repo connect karo
3. Settings:
   - **Environment:** `Docker`
   - **Dockerfile Path:** `./Dockerfile`
   - **Plan:** Free

---

## Step 3: Environment Variables Set Karo

Render Dashboard → Your Service → **Environment** tab:

| Variable | Value |
|----------|-------|
| `APP_KEY` | `base64:XXXX` (generate karo: `php artisan key:generate --show`) |
| `APP_NAME` | `MAGIA LUPOS` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-app.onrender.com` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | (database se copy karo) |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | (database se copy karo) |
| `DB_USERNAME` | (database se copy karo) |
| `DB_PASSWORD` | (database se copy karo) |

---

## Step 4: Free MySQL Database

Render pe free MySQL nahi hai. Ye free options use karo:

### Option A: PlanetScale (Recommended - Free 5GB)
1. [planetscale.com](https://planetscale.com) → Sign up
2. **New Database** → Name: `jewellery-ms`
3. **Connect** → **Laravel** select karo
4. Connection string copy karo

### Option B: Clever Cloud (Free 256MB MySQL)
1. [clever-cloud.com](https://www.clever-cloud.com) → Sign up
2. **Create** → **Add-on** → **MySQL**
3. Free plan → Create
4. Dashboard se credentials copy karo

### Option C: Railway MySQL (Free $5 credit)
1. [railway.app](https://railway.app) → Sign up with GitHub
2. **New Project** → **MySQL**
3. Variables tab se credentials copy karo

---

## Step 5: APP_KEY Generate Karo

Local machine pe ye command chalao:
```bash
php artisan key:generate --show
```
Output copy karo (base64:XXXX...) aur Render mein `APP_KEY` mein paste karo.

---

## Step 6: Deploy!

1. Sab environment variables set karne ke baad
2. Render automatically redeploy karega
3. **Logs** tab mein dekho - migrations run hongi
4. 5-10 minute mein app live ho jayegi

---

## Troubleshooting

### "Storage not writable" error
Render pe storage persistent nahi hoti. Images/uploads ke liye **Cloudinary** ya **AWS S3** use karo.

### "Migration failed" error  
Database credentials dobara check karo. Logs mein exact error dekho.

### App slow hai
Free tier pe cold start hota hai (30-60 sec). Normal hai.

---

## 💡 Best Alternative: Railway.app

Railway Laravel ke saath **sabse aasaan** hai:

```
1. railway.app → Login with GitHub
2. New Project → Deploy from GitHub Repo
3. MySQL Plugin add karo
4. Environment variables set karo
5. Deploy! ✅
```

Railway mein PHP/Laravel automatically detect hota hai - Docker ki zaroorat nahi!
