# GitHub Actions Deployment Setup Guide

Hii guide inaonyesha jinsi ya ku-setup automatic deployment kwenye CPanel server kwa kutumia GitHub Actions.

## Requirements

1. CPanel server na SSH access
2. GitHub repository: https://github.com/synmtaxflow/ShuleLinkWeb.git
3. SSH key kwa server access

## Setup Steps

### 1. Generate SSH Key (kama huna)

Kwenye local machine yako, generate SSH key:

```bash
ssh-keygen -t rsa -b 4096 -C "your-email@example.com" -f ~/.ssh/cpanel_deploy
```

### 2. Add SSH Key kwenye CPanel Server

Copy public key kwenye server:

```bash
ssh-copy-id -i ~/.ssh/cpanel_deploy.pub username@your-server.com
```

Au manually, add content ya `~/.ssh/cpanel_deploy.pub` kwenye:
```
~/.ssh/authorized_keys
```

### 3. Add GitHub Secrets

Kwenye GitHub repository, nenda kwenye:
**Settings → Secrets and variables → Actions → New repository secret**

Add secrets zifuatazo:

#### Required Secrets:

1. **CPANEL_HOST**
   - Value: IP address au domain ya CPanel server yako
   - Example: `192.168.1.100` au `server.example.com`

2. **CPANEL_USERNAME**
   - Value: Username ya CPanel account
   - Example: `shulexpert`

3. **CPANEL_SSH_KEY**
   - Value: Private SSH key (content ya `~/.ssh/cpanel_deploy`)
   - Copy nzima ya private key including `-----BEGIN RSA PRIVATE KEY-----` na `-----END RSA PRIVATE KEY-----`

4. **CPANEL_SSH_PORT** (Optional)
   - Value: SSH port (default ni 22)
   - Example: `22` au `2222`

### 4. Server Path Configuration

Hakikisha path kwenye server ni sahihi. Kwenye workflow file, path ni:
```
/home/shulexpert/repositories/shuleXpert
```

Badilisha kama inahitajika kwa server yako.

### 5. Test Deployment

1. Commit na push mabadiliko yoyote kwenye `main` branch
2. GitHub Actions ita-run automatically
3. Check deployment status kwenye **Actions** tab kwenye GitHub repository

## Workflow Files

### deploy.yml
Simple deployment workflow - ina-upload files moja kwa moja kwenye server.

### deploy-advanced.yml
Advanced deployment workflow - ina-create tar.gz package na ina-backup .env file.

## Troubleshooting

### SSH Connection Issues

1. Hakikisha SSH key iko kwenye authorized_keys kwenye server
2. Test SSH connection manually:
   ```bash
   ssh -i ~/.ssh/cpanel_deploy username@server.com
   ```

### Permission Issues

Kwenye server, hakikisha permissions ni sahihi:
```bash
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

### Composer Issues

Hakikisha composer iko installed kwenye server:
```bash
which composer
composer --version
```

### Laravel Issues

Hakikisha .env file iko kwenye server na configurations zote ni sahihi.

## Manual Deployment

Kama GitHub Actions haifanyi kazi, unaweza ku-deploy manually:

```bash
# Kwenye server
cd /home/shulexpert/repositories/shuleXpert
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

## Notes

- Database migrations zime-comment out kwa sababu za usalama
- Uncomment `php artisan migrate --force` kama unataka auto-migrate
- Backup database kabla ya ku-run migrations kwenye production
