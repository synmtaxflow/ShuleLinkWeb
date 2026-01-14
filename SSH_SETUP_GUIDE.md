# SSH Setup Guide kwa CPanel Server

Hii guide inaonyesha jinsi ya ku-setup SSH key authentication kwenye CPanel server.

## Problem

Unapojaribu ku-connect kwenye server:
```bash
ssh username@shulexpert.com
```

Ina-ask password na ina-fail. Hii ina-maana:
- SSH connection inafanya kazi ✅
- Lakini hauna SSH key authentication setup ❌

---

## Solution: Setup SSH Key Authentication

### STEP 1: Generate SSH Key (kama huna)

**Windows PowerShell:**

```powershell
# Generate SSH key
ssh-keygen -t rsa -b 4096 -C "your-email@example.com" -f $env:USERPROFILE\.ssh\cpanel_deploy

# Press Enter kwa passphrase (au weka password kama unataka)
# Press Enter tena kwa confirm
```

**Matokeo:**
- Private key: `C:\Users\YourUsername\.ssh\cpanel_deploy`
- Public key: `C:\Users\YourUsername\.ssh\cpanel_deploy.pub`

---

### STEP 2: Get Public Key Content

**Windows PowerShell:**

```powershell
Get-Content $env:USERPROFILE\.ssh\cpanel_deploy.pub
```

**Copy output yote** - itaonekana kama:
```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQC... your-email@example.com
```

---

### STEP 3: Add Public Key kwenye CPanel Server

#### Option A: Kwa CPanel File Manager

1. **Login kwenye CPanel:**
   - Nenda: https://shulexpert.com:2083 (au cPanel URL yako)
   - Login na credentials zako

2. **Open File Manager:**
   - Nenda kwenye **File Manager**
   - Enable "Show Hidden Files" (dotfiles)

3. **Navigate to .ssh folder:**
   - Nenda kwenye home directory
   - Click **.ssh** folder (au create kama haipo)
   - Kama haipo, create folder: `.ssh`

4. **Create/Edit authorized_keys:**
   - Kwenye `.ssh` folder, create file: `authorized_keys`
   - Au edit kama ipo tayari
   - Paste public key yako (kutoka STEP 2)
   - Save file

5. **Set Permissions:**
   - Right-click `.ssh` folder → **Change Permissions**
   - Set: `700` (rwx------)
   - Right-click `authorized_keys` file → **Change Permissions**
   - Set: `600` (rw-------)

#### Option B: Kwa SSH (kama unaweza ku-login kwa password)

```bash
# Login kwenye server (kwa password)
ssh username@shulexpert.com

# Kwenye server, run:
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys

# Paste public key yako, save (Ctrl+X, Y, Enter)
chmod 600 ~/.ssh/authorized_keys
exit
```

#### Option C: Kwa CPanel Terminal (kama ipo enabled)

1. **Login kwenye CPanel**
2. **Open Terminal** (kwenye Advanced section)
3. **Run commands:**
   ```bash
   mkdir -p ~/.ssh
   chmod 700 ~/.ssh
   echo "ssh-rsa YOUR_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
   chmod 600 ~/.ssh/authorized_keys
   ```

---

### STEP 4: Test SSH Connection

**Windows PowerShell:**

```powershell
# Test connection kwa SSH key
ssh -i $env:USERPROFILE\.ssh\cpanel_deploy username@shulexpert.com
```

**Kama inafanya kazi:**
- Haipaswi ku-ask password
- Ina-login automatically
- Unaona server prompt

**Kama bado ina-ask password:**
1. Check permissions:
   ```bash
   # Kwenye server
   ls -la ~/.ssh
   # Should show: drwx------ .ssh
   # Should show: -rw------- authorized_keys
   ```

2. Check public key iko kwenye authorized_keys:
   ```bash
   cat ~/.ssh/authorized_keys
   ```

3. Check SSH server config (kama una root access):
   ```bash
   sudo nano /etc/ssh/sshd_config
   # Ensure: PubkeyAuthentication yes
   # Ensure: AuthorizedKeysFile .ssh/authorized_keys
   ```

---

### STEP 5: Get Private Key kwa GitHub Secret

**Windows PowerShell:**

```powershell
Get-Content $env:USERPROFILE\.ssh\cpanel_deploy
```

**Copy output yote** including:
```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA...
...
-----END RSA PRIVATE KEY-----
```

**⚠️ IMPORTANT:** 
- Hii ni SECRET - usiishare na mtu yeyote!
- Iwe ndani ya GitHub Secrets tu

---

### STEP 6: Add kwenye GitHub Secrets

1. **Nenda GitHub:**
   - https://github.com/synmtaxflow/ShuleLinkWeb
   - **Settings** → **Secrets and variables** → **Actions**

2. **Add Secret: CPANEL_SSH_KEY**
   - Name: `CPANEL_SSH_KEY`
   - Value: Paste private key yako (kutoka STEP 5)
   - Click **Add secret**

3. **Add Other Secrets:**
   - `CPANEL_HOST`: `shulexpert.com`
   - `CPANEL_USERNAME`: Username yako ya CPanel
   - `CPANEL_SSH_PORT`: `22` (au port yako)

---

## Alternative: Kama Huna SSH Access

Kama hauwezi ku-setup SSH key kwa sababu ya permissions, unaweza:

### Option 1: Request SSH Access kwa Hosting Provider

- Contact hosting provider yako
- Request SSH access au SSH key setup
- Wataweza ku-setup kwa ajili yako

### Option 2: Use CPanel API (kama ipo)

- Kama CPanel ina API access
- Unaweza ku-use CPanel API kwa deployment
- Hii inahitaji additional setup

### Option 3: Manual Deployment

- Deploy manually kwa kutumia CPanel File Manager
- Au kwa kutumia FTP/SFTP
- Hii haitakuwa automatic, lakini inafanya kazi

---

## Troubleshooting

### Error: Permission denied (publickey)

**Solution:**
1. Check public key iko kwenye authorized_keys:
   ```bash
   cat ~/.ssh/authorized_keys
   ```

2. Check permissions:
   ```bash
   chmod 700 ~/.ssh
   chmod 600 ~/.ssh/authorized_keys
   ```

3. Check SSH key format:
   - Public key lazima i-start na `ssh-rsa` au `ssh-ed25519`
   - Lazima iwe single line

### Error: Could not resolve hostname

**Solution:**
- Use IP address badala ya domain
- Au check DNS settings

### Error: Connection timeout

**Solution:**
- Check firewall settings
- Check SSH port (22 au 2222)
- Contact hosting provider

---

## Summary

Baada ya ku-complete setup:

✅ **SSH Key Generated**
✅ **Public Key Added kwenye Server**
✅ **Private Key Added kwenye GitHub Secrets**
✅ **SSH Connection Working (bila password)**
✅ **GitHub Actions Ready kwa Deployment**

---

## Next Steps

1. Complete SSH setup (steps hapo juu)
2. Test SSH connection
3. Add GitHub Secrets
4. Test deployment:
   ```bash
   git push origin main
   ```
5. Check GitHub Actions: https://github.com/synmtaxflow/ShuleLinkWeb/actions
