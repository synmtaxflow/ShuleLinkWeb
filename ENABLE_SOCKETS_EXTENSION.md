# Jinsi ya Kuwezesha PHP Sockets Extension kwenye XAMPP

## Hatua za Kuwezesha Sockets Extension

### Hatua 1: Fungua php.ini File

1. **Njia ya 1: Kupitia XAMPP Control Panel**
   - Fungua **XAMPP Control Panel**
   - Bofya **Config** karibu na **Apache**
   - Chagua **PHP (php.ini)**
   - File itafunguliwa kwenye text editor

2. **Njia ya 2: Kupitia File Explorer**
   - Nenda kwenye: `C:\xampp\php\`
   - Pata file `php.ini`
   - Bofya kulia → **Open with** → Chagua **Notepad** au text editor yoyote

### Hatua 2: Tafuta na Wezesha Sockets Extension

1. **Tafuta line hii** kwenye php.ini:
   ```
   ;extension=sockets
   ```

2. **Ondoa semicolon (;)** mbele ya extension:
   ```
   ;extension=sockets    ← HII (ime-comment)
   ```
   
   Badilisha kuwa:
   ```
   extension=sockets     ← HII (imewezeshwa)
   ```

3. **Hakikisha** line inaonekana hivi:
   ```
   extension=sockets
   ```
   **SIO:**
   ```
   ;extension=sockets
   ```

### Hatua 3: Hifadhi File

1. Bofya **File** → **Save** (au Ctrl+S)
2. Hakikisha mabadiliko yamehifadhiwa

### Hatua 4: Restart Apache

1. Kwenye **XAMPP Control Panel**
2. Bofya **Stop** kwenye Apache (ikiwa inaendesha)
3. Subiri sekunde 2-3
4. Bofya **Start** kwenye Apache tena

### Hatua 5: Thibitisha Extension Imewezeshwa

1. Fungua **Command Prompt** au **PowerShell**
2. Run command hii:
   ```bash
   php -m | findstr -i socket
   ```

3. Ikiwa unaona `sockets` kwenye output, extension imewezeshwa kikamilifu!

## Troubleshooting

### Problem: Bado haifanyi kazi baada ya restart

**Solutions:**
1. Hakikisha umebadilisha line sahihi (sio duplicate)
2. Restart Apache tena
3. Restart XAMPP Control Panel
4. Restart computer (kama bado haifanyi kazi)

### Problem: Sijapata line `;extension=sockets`

**Solution:**
1. Tafuta sehemu ya `[Extensions]` kwenye php.ini
2. Ongeza line hii mwishoni:
   ```
   extension=sockets
   ```

### Problem: Apache haistart baada ya mabadiliko

**Solution:**
1. Revert mabadiliko (weka semicolon tena)
2. Angalia syntax errors kwenye php.ini
3. Hakikisha umebadilisha line moja tu

## Verification Command

Run command hii kuthibitisha:
```bash
php -m | findstr -i socket
```

Ikiwa unaona `sockets`, extension imewezeshwa!

## Alternative: Check kwenye Browser

Unaweza pia ku-check kwa kuunda file `phpinfo.php`:

1. Unda file `phpinfo.php` kwenye `C:\xampp\htdocs\`
2. Andika:
   ```php
   <?php
   phpinfo();
   ?>
   ```
3. Fungua browser: `http://localhost/phpinfo.php`
4. Tafuta "sockets" kwenye page
5. Ikiwa unaona "sockets" na "enabled", extension imewezeshwa

## Important Notes

- **Hakikisha** umerestart Apache baada ya mabadiliko
- **Sio lazima** restart computer (lakini inaweza kusaidia)
- **Extension** lazima iwe enabled kwa ajili ya ZKTeco device integration

## Location ya php.ini

Kwa XAMPP kwenye Windows:
```
C:\xampp\php\php.ini
```

---

**Baada ya kuwezesha extension na kurestart Apache, jaribu ku-test connection tena!**

