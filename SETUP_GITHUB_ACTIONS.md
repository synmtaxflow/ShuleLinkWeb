# Step-by-Step Guide: Setup GitHub Actions kwa Automatic Deployment

Hii ni guide ya hatua kwa hatua ya jinsi ya ku-setup automatic deployment kutoka GitHub hadi CPanel server.

## Prerequisites

1. CPanel server na SSH access
2. GitHub account na repository: https://github.com/synmtaxflow/ShuleLinkWeb.git
3. Terminal/Command Prompt kwa ku-run commands

---

## STEP 1: Generate SSH Key

### Windows (PowerShell):

```powershell
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "your-email@example.com" -f $env:USERPROFILE\.ssh\cpanel_deploy

# Press Enter kwa passphrase (au weka password kama unataka)
# Press Enter tena kwa confirm passphrase
```

### Linux/Mac:

```bash
ssh-keygen -t rsa -b 4096 -C "your-email@example.com" -f ~/.ssh/cpanel_deploy
```

**Matokeo:**
- Private key: `~/.ssh/cpanel_deploy` (HII NI SECRET - usiishare!)
- Public key: `~/.ssh/cpanel_deploy.pub` (Hii ndiyo utai-add kwenye server)

---

## STEP 2: Copy Public Key kwenye CPanel Server

### Option A: Kwa ssh-copy-id (Linux/Mac):

```bash
ssh-copy-id -i ~/.ssh/cpanel_deploy.pub username@your-server.com
```

### Option B: Manual (Windows/Linux/Mac):

1. **Read public key:**
   ```bash
   # Windows PowerShell
   Get-Content $env:USERPROFILE\.ssh\cpanel_deploy.pub
   
   # Linux/Mac
   cat ~/.ssh/cpanel_deploy.pub
   ```

2. **Copy output yote** (kutoka `ssh-rsa` hadi mwisho)

3. **Login kwenye CPanel server:**
   ```bash
   ssh username@your-server.com
   ```

4. **Add key kwenye authorized_keys:**
   ```bash
   # Kwenye server
   mkdir -p ~/.ssh
   chmod 700 ~/.ssh
   nano ~/.ssh/authorized_keys
   ```

5. **Paste public key** na save (Ctrl+X, Y, Enter)

6. **Set permissions:**
   ```bash
   chmod 600 ~/.ssh/authorized_keys
   ```

7. **Test connection:**
   ```bash
   # Kutoka local machine
   ssh -i ~/.ssh/cpanel_deploy username@your-server.com
   ```

---

## STEP 3: Get Private Key Content (kwa GitHub Secret)

### Windows PowerShell:

```powershell
Get-Content $env:USERPROFILE\.ssh\cpanel_deploy
```

### Linux/Mac:

```bash
cat ~/.ssh/cpanel_deploy
```

**Copy output yote** including:
```
-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----
```

**⚠️ IMPORTANT:** Hii ni secret - usiishare na mtu yeyote!

---

## STEP 4: Setup GitHub Secrets

1. **Fungua GitHub Repository:**
   - Nenda: https://github.com/synmtaxflow/ShuleLinkWeb

2. **Nenda kwenye Settings:**
   - Click **Settings** tab (kwenye top navigation)
   - Kwenye left sidebar, click **Secrets and variables**
   - Click **Actions**

3. **Add Secrets:**

   #### Secret 1: CPANEL_HOST
   - Click **New repository secret**
   - Name: `CPANEL_HOST`
   - Secret: IP address au domain ya server yako
     - Example: `shulexpert.com` (au IP address ya server)
     - Au: `192.168.1.100`
   - Click **Add secret**

   #### Secret 2: CPANEL_USERNAME
   - Click **New repository secret**
   - Name: `CPANEL_USERNAME`
   - Secret: Username ya CPanel account
     - Example: `shulexpert` (au username yako ya CPanel)
   - Click **Add secret**

   #### Secret 3: CPANEL_SSH_KEY
   - Click **New repository secret**
   - Name: `CPANEL_SSH_KEY`
   - Secret: Paste private key yako (kutoka STEP 3)
     - Include `-----BEGIN RSA PRIVATE KEY-----` na `-----END RSA PRIVATE KEY-----`
   - Click **Add secret**

   #### Secret 4: CPANEL_SSH_PORT (Optional)
   - Click **New repository secret**
   - Name: `CPANEL_SSH_PORT`
   - Secret: `22` (au port yako ya SSH)
   - Click **Add secret**

---

## STEP 5: Verify Server Path

Hakikisha path kwenye server ni sahihi:

1. **Check workflow file:**
   - File: `.github/workflows/deploy.yml`
   - Line: `target: "~/repositories/ShuleXpert"`

2. **Badilisha kama inahitajika:**
   - Kama path yako ni tofauti, edit workflow file
   - Badilisha `~/repositories/ShuleXpert` na path yako

3. **Hakikisha folder ipo kwenye server:**
   ```bash
   ssh username@your-server.com
   mkdir -p ~/repositories/ShuleXpert
   ```

---

## STEP 6: Test Deployment

1. **Make small change:**
   ```bash
   # Kwenye local machine
   cd c:\xampp\htdocs\shuleLink
   echo "# Test deployment" >> README.md
   git add README.md
   git commit -m "Test deployment"
   git push origin main
   ```

2. **Check GitHub Actions:**
   - Nenda: https://github.com/synmtaxflow/ShuleLinkWeb/actions
   - Unaona workflow run ina-start
   - Wait kwa completion (kawaida 2-5 minutes)

3. **Check status:**
   - ✅ Green checkmark = Success
   - ❌ Red X = Error (click kwa details)

4. **Check server:**
   ```bash
   ssh username@your-server.com
   cd ~/repositories/ShuleXpert
   ls -la
   # Files zina-update
   ```

---

## STEP 7: Verify Deployment

1. **Check files kwenye server:**
   ```bash
   ssh username@your-server.com
   cd ~/repositories/ShuleXpert
   git log -1  # Check latest commit
   ```

2. **Check Laravel caches:**
   ```bash
   php artisan config:show  # Should show cached config
   ```

3. **Test website:**
   - Visit website yako
   - Hakikisha changes zimeonekana

---

## Troubleshooting

### Error: SSH Connection Failed

**Solution:**
1. Test SSH connection manually:
   ```bash
   ssh -i ~/.ssh/cpanel_deploy username@your-server.com
   ```

2. Hakikisha public key iko kwenye server:
   ```bash
   ssh username@your-server.com
   cat ~/.ssh/authorized_keys
   ```

3. Check firewall settings kwenye server

### Error: Permission Denied

**Solution:**
```bash
# Kwenye server
chmod -R 755 ~/repositories/ShuleXpert
chown -R username:username ~/repositories/ShuleXpert
```

### Error: Composer Not Found

**Solution:**
```bash
# Kwenye server
which composer
# Kama haipo, install composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Error: Path Not Found

**Solution:**
1. Check path kwenye workflow file
2. Create folder kwenye server:
   ```bash
   mkdir -p ~/repositories/ShuleXpert
   ```

---

## Summary

Baada ya ku-complete steps hizi:

✅ **Unapopush kwenye GitHub:**
```bash
git push origin main
```

✅ **GitHub Actions ita:**
- Checkout code
- Upload files kwenye server
- Run composer install
- Clear & cache Laravel
- Deploy automatically

✅ **Hakuna haja ya:**
- ❌ Ku-clone manually
- ❌ Ku-login kwenye server
- ❌ Ku-run commands manually

✅ **Everything happens automatically!**

---

## Next Steps

1. Complete setup steps hapo juu
2. Test na small change
3. Monitor kwenye GitHub Actions tab
4. Enjoy automatic deployment! 🚀
